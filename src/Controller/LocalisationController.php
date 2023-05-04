<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LocalisationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Localisation;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

class LocalisationController extends AbstractController
{
    #[Route('/localisation', name: 'app_localisation')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/LocalisationController.php',
        ]);
    }

    // getAll
    #[Route('/api/localisations', name: 'localisation.getAll', methods: ['GET'])]
    public function getAllLocalisations(
        LocalisationRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getAllLocalisations';
        $context = SerializationContext::create()->setGroups(["getAllLocalisations"]);

        $jsonLocalisation = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $context) {
            echo "MISE EN CACHE";
            $item->tag('LocalisationCache');
            $context = SerializationContext::create()->setGroups(["getLocalisation"]);

            $localisation = $repository->findAll();
            return $serializer->serialize($localisation, 'json', $context /*['groups' => 'getAllLocalisations']*/);

        } );
        return new JsonResponse($jsonLocalisation, 200, [], true);
    }

    // getById
    #[Route('/api/localisation/{idLocalisation}', name: 'localisation.getLocalisation', methods: ['GET'])]
    #[ParamConverter("localisation", class: 'App\Entity\Localisation', options: ["id" => "idLocalisation"])]
    public function getLocalisation(
        Localisation $localisation,
        LocalisationRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getLocalisation';
        $jsonLocalisation = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request, $localisation) {
            $item->tag("getLocalisation");
            $context = SerializationContext::create()->setGroups('getLocalisation');

            $localisations = $repository->find($localisation);
            return $serializer->serialize($localisations, 'json', $context);
        });

        return new JsonResponse($jsonLocalisation, Response::HTTP_OK, [], true);
    }

    // delete
    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/api/localisation/{idLocalisation}', name: 'localisation.deleteLocalisation', methods: ['DELETE'])]
    #[ParamConverter("localisation", class: 'App\Entity\Localisation', options: ["id" => "idLocalisation"])]
    public function deleteLocalisation(
        Localisation $localisation,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["getLocalisation"]);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // create
    #[Route('/api/localisation', name: 'localisation.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createLocalisation(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $newLocalisation = $serializer->deserialize(
            $request->getContent(),
            Localisation::class,
            'json');

        $errors = $validator->validate($newLocalisation);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($newLocalisation);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(["getAllLocalisation"]);

        $jsonLocalisation = $serializer->serialize($newLocalisation, 'json', $context /*['groups' => 'getLocalisation']*/);
        return new JsonResponse($jsonLocalisation, Response::HTTP_CREATED, [], true);
    }

    // update
    #[Route('/api/localisation/{idLocalisation}', name: 'localisation.update', methods: ['PUT'])]
    #[ParamConverter("localisation", class: 'App\Entity\Localisation', options: ["id" => "idLocalisation"])]
    public function updateLocalisation(
        Localisation $localisation,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        LocalisationRepository $LocalisationRepository,
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $updateLocalisation = $serializer->deserialize(
            $request->getContent(),
            Localisation::class,
            'json'
        );

        $localisation->setRue($updateLocalisation->getRue() ? $updateLocalisation->getRue() : $localisation->getRue());
        $localisation->setCP($updateLocalisation->getCP() ? $updateLocalisation->getCP() : $localisation->getCP());
        $localisation->setVille($updateLocalisation->getVille() ? $updateLocalisation->getVille() : $localisation->getVille());

        $errors = $validator->validate($localisation);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $id = $content['idLocalisation'];

        $entityManager->persist($localisation);
        $entityManager->flush();

        $location = $urlGenerator->generate("localisations.getLocalisation", ['idLocalisation' => $localisation->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups(["getAllLocalisation"]);

        $jsonBoutique = $serializer->serialize($localisation, 'json', $context);
        return new JsonResponse($jsonBoutique, Response::HTTP_CREATED, [$location => ''], true);
    }
}
