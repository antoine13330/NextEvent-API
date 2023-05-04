<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\User;
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

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    // getAll
    #[Route('/api/users', name: 'user.getAll', methods: ['GET'])]
    public function getAllUsers(
        UserRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getAllUser';
        $context = SerializationContext::create()->setGroups(["getAllUser"]);

        $jsonUser = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $context) {
            echo "MISE EN CACHE";
            $item->tag('UserCache');
            $context = SerializationContext::create()->setGroups(["getUser"]);

            $user = $repository->findAll();
            return $serializer->serialize($user, 'json', $context);

        } );
        return new JsonResponse($jsonUser, 200, [], true);
    }

    // getById
    #[Route('/api/user/{idUser}', name: 'user.getOneUser', methods: ['GET'])]
    #[ParamConverter("user", class: 'App\Entity\User', options: ["id" => "idUser"])]
    public function getOneUser(
        User $user,
        UserRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getUser';
        $jsonUser = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request, $user) {
            $item->tag("getUser");
            $context = SerializationContext::create()->setGroups('getUser');

            $users = $repository->find($user);
            return $serializer->serialize($users, 'json', $context);
        });

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    // delete
    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/api/user/{idUser}', name: 'user.deleteUser', methods: ['DELETE'])]
    #[ParamConverter("user", class: 'App\Entity\User', options: ["id" => "idUser"])]
    public function deleteUser(
        User $user,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["getUser"]);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // create
    #[Route('/api/user', name: 'user.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $newUser = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json');

        $errors = $validator->validate($newUser);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($newUser);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(["getAllUser"]);

        $jsonUser = $serializer->serialize($newUser, 'json', $context /*['groups' => 'getUser']*/);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    // update
    #[Route('/api/user/{idUser}', name: 'user.update', methods: ['PUT'])]
    #[ParamConverter("user", class: 'App\Entity\User', options: ["id" => "idUser"])]
    public function updateUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UserRepository $UserRepository,
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $updateUser = $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json'
        );

        $user->setUsername($updateUser->getUsername() ? $updateUser->getUsername() : $user->getUsername());
        $user->setEmail($updateUser->getEmail() ? $updateUser->getEmail() : $user->getEmail());
        $user->setPassword($updateUser->getPassword() ? $updateUser->getPassword() : $user->getPassword());

        $errors = $validator->validate($user);
        if ($errors->count() >0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $id = $content['idUser'];

        $entityManager->persist($user);
        $entityManager->flush();

        $location = $urlGenerator->generate("users.getUser", ['idUser' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $context = SerializationContext::create()->setGroups(["getAllUser"]);

        $jsonBoutique = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonBoutique, Response::HTTP_CREATED, [$location => ''], true);
    }
}

