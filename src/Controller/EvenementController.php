<?php

namespace App\Controller;

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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

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
            $context = SerializationContext::create()->setGroups(["getEvenement"]);

            $Evenement = $repository->findAll();
            return $serializer->serialize($Evenement, 'json', $context /*['groups' => 'getAllEvenements']*/);

        } );
        return new JsonResponse($jsonEvenement, 200, [], true);

        // return $this->json($repository->findEvenements($page, $limit), 200, [], ['groups' => 'getAllEvenements']);
    }
}
