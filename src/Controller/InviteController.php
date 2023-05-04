<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\InviteRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Invite;
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

class InviteController extends AbstractController
{
    #[Route('/invite', name: 'app_invite')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/InviteController.php',
        ]);
    }

    #[Route('/api/invites', name: 'invite.getAll', methods: ['GET'])]
    public function getAllInvites(
        InviteRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getAllInvites';
        $context = SerializationContext::create()->setGroups(["getAllInvites"]);

        $jsonInvite = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $context) {
            echo "MISE EN CACHE";
            $item->tag('inviteCache');
            $context = SerializationContext::create()->setGroups(["getInvite"]);

            $invite = $repository->findAll();
            return $serializer->serialize($invite, 'json', $context /*['groups' => 'getAllInvites']*/);

        } );
        return new JsonResponse($jsonInvite, 200, [], true);
    }

    // getById
    #[Route('/api/invite/{idInvite}', name: 'invite.getInvite', methods: ['GET'])]
    #[ParamConverter("invite", class: 'App\Entity\Invite', options: ["id" => "idInvite"])]
    public function getInvite(
        Invite $invite,
        InviteRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getInvite';
        $jsonInvite = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request, $invite) {
            $item->tag("getInvite");
            $context = SerializationContext::create()->setGroups('getInvite');

            $categories = $repository->find($invite);
            return $serializer->serialize($categories, 'json', $context);
        });

        return new JsonResponse($jsonInvite, Response::HTTP_OK, [], true);
    }

    // delete
    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/api/invite/{idInvite}', name: 'categories.deleteInvite', methods: ['DELETE'])]
    #[ParamConverter("invite", class: 'App\Entity\Invite', options: ["id" => "idInvite"])]
    public function deleteInvite(
        Invite $Invite,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["getInvite"]);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // create
    #[Route('/api/invite', name: 'invite.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createInvite(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $newInvite = $serializer->deserialize(
            $request->getContent(),
            Invite::class,
            'json');

        $errors = $validator->validate($newInvite);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($newInvite);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(["getAllCategories"]);

        $jsonInvite = $serializer->serialize($newInvite, 'json', $context /*['groups' => 'getInvite']*/);
        return new JsonResponse($jsonInvite, Response::HTTP_CREATED, [], true);
    }

    // update
    #[Route('/api/invite/{idInvite}', name: 'invite.update', methods: ['PUT'])]
    #[ParamConverter("invite", class: 'App\Entity\Invite', options: ["id" => "idInvite"])]
    public function updateInvite(
        Invite $invite,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        InviteRepository $InviteRepository,
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $updateInvite = $serializer->deserialize(
            $request->getContent(),
            Invite::class,
            'json'
        );

        $invite->setName($updateInvite->getName() ? $updateInvite->getName() : $invite->getName());

        $errors = $validator->validate($invite);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $id = $content['idInvite'];

        $entityManager->persist($invite);
        $entityManager->flush();

        $location = $urlGenerator->generate("categories.getInvite", ['idInvite' => $invite->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups(["getAllCategories"]);

        $jsonBoutique = $serializer->serialize($invite, 'json', $context);
        return new JsonResponse($jsonBoutique, Response::HTTP_CREATED, [$location => ''], true);
    }
}
