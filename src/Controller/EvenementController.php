<?php

namespace App\Controller;

use Cloudinary\Api\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EvenementRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Evenement;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Uid\Uuid;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Exception;

class EvenementController extends AbstractController
{
    #[Route('/evenement', name: 'app_evenement')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EvenementController.php',
        ]);
    }

    // getAll
    #[Route('/api/evenements', name: 'evenement.getAll', methods: ['GET'])]
    public function getAllEvenements(
        EvenementRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getAllEvenements';
        $context = SerializationContext::create()->setGroups(["getAllEvenements"]);

        $jsonEvenement = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $context) {
            echo "MISE EN CACHE";
            $item->tag('EvenementCache');

            $evenement = $repository->findAll();
            return $serializer->serialize($evenement, 'json', $context);

        } );
        return new JsonResponse($jsonEvenement, 200, [], true);
    }

    // getById
    #[Route('/api/evenement/{idEvenement}', name: 'evenement.getEvenement', methods: ['GET'])]
    #[ParamConverter("evenement", class: 'App\Entity\Evenement', options: ["id" => "idEvenement"])]
    public function getEvenement(
        Evenement $evenement,
        EvenementRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getEvenement';
        $jsonEvenement = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request, $evenement) {
            $item->tag("getEvenement");
            $context = SerializationContext::create()->setGroups('getEvenement');

            $evenements = $repository->find($evenement);
            return $serializer->serialize($evenements, 'json', $context);
        });

        return new JsonResponse($jsonEvenement, Response::HTTP_OK, [], true);
    }

    // delete
    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/api/evenement/{idEvenement}', name: 'evenements.deleteEvenement', methods: ['DELETE'])]
    #[ParamConverter("evenement", class: 'App\Entity\Evenement', options: ["id" => "idEvenement"])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function deleteEvenement(
        Evenement $evenement,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["getEvenement", "getAllEvenements"]);
        $entityManager->remove($evenement);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // create
    #[Route('/api/evenement', name: 'evenement.create', methods: ['POST'])]
    // #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createEvenement(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        try {
            $requestInfos = $request->request->all();
            $imageFile = $request->files->get('image');
            if ( !$imageFile ) {
                throw  new Exception("Field 'image' of type 'File' missing in request.");
            }
            Configuration::instance('cloudinary://643476132488446:NlYPKcRrfx479KEfbD7Ecsqhnsk@dida5huld?secure=true');
            // Envoyer l'image vers Cloudinary
    
            // Upload the image
            $upload = new UploadApi();
            $id = Uuid::v6();
            $cloudinaryResponse = $upload->upload($imageFile->getPathname(), ['public_id' => $id]);
            if ( $cloudinaryResponse instanceof ApiResponse) {
                $eventImgUrl = 'https://res.cloudinary.com/dida5huld/image/upload/v1688393382/'.$id;
            } else {
                throw new Exception("Cloudinary response not type of ApiResponse");
            }
            $newEvenement = $serializer->deserialize(json_encode($requestInfos),Evenement::class,'json');
            $newEvenement->setImage($eventImgUrl);
            $errors = $validator->validate($newEvenement);
            if ($errors->count() >0) {
                return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
            }
            $entityManager->persist($newEvenement);
            $entityManager->flush();
            $context = SerializationContext::create()->setGroups(["getAllEvenements"]);
    
            $jsonEvenement = $serializer->serialize($newEvenement, 'json', $context );
            return new JsonResponse($jsonEvenement, Response::HTTP_CREATED, [], true);
    
        } catch ( Exception $e ) {
            return new JsonResponse($e->getMessage(), 500 , [] , true);
        }
    }

    // update
    #[Route('/api/evenement/{idEvenement}', name: 'evenement.update', methods: ['PATCH'])]
    #[ParamConverter("evenement", class: 'App\Entity\Evenement', options: ["id" => "idEvenement"])]
    // #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function updateEvenement(
        Evenement $evenement,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): JsonResponse {
        $updateEvenement = $serializer->deserialize(
            $request->getContent(),
            Evenement::class,
            'json'
        );

        $evenement->setName($updateEvenement->getName() ? $updateEvenement->getName() : $evenement->getName());
        $evenement->setDescription($updateEvenement->getDescription() ? $updateEvenement->getDescription() : $evenement->getDescription());

        $errors = $validator->validate($evenement);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($evenement);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(["getAllEvenements"]);

        $jsonEvevement = $serializer->serialize($evenement, 'json', $context);
        return new JsonResponse($jsonEvevement, Response::HTTP_CREATED, [], true);
    }

    //getEventsByType
    #[Route('/api/evenementsByType', name: 'evenement.getByType', methods: ['GET'])]
    public function getEvenementsByType(
        EvenementRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getEvenementsByType';
        $context = SerializationContext::create()->setGroups(["getEvenementsByType"]);

        //$type = $request->request->get('type');
        $data = json_decode($request->getContent(), true);
        $type = $data['type'];

        $jsonEvenements = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $context, $type) {
            echo "MISE EN CACHE";
            $item->tag('EvenementCache');

            $evenements = $repository->findByType($type);
            return $serializer->serialize($evenements, 'json', $context);
        } );
        return new JsonResponse($jsonEvenements, 200, [], true);
    }
}
