<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
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
        $idCache = 'getAllUsers';
        $context = SerializationContext::create()->setGroups(["getAllUsers"]);

        $jsonUser = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $context) {
            echo "MISE EN CACHE";
            $item->tag('UserCache');

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
            $context = SerializationContext::create()->setGroups(["getUser"]);

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
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $apiToken = $request->headers->get('apiToken');
        /** @var User $connectedUSer */
        $connectedUSer = $userRepository->findOneBy(['apiToken' => $apiToken]);

        if ($connectedUSer !== null) {
            if ($connectedUSer->getRole() === 'admin') {
                $cache->invalidateTags(["getUser", "getAllUsers"]);
                $entityManager->remove($user);
                $entityManager->flush();
                return new JsonResponse(null, Response::HTTP_NO_CONTENT);
            } else {
                return new JsonResponse(['message' => 'Vous devez être connecté'], Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new JsonResponse(['message' => 'Vous devez être connecté'], Response::HTTP_UNAUTHORIZED);
        }
    }

    // create
    #[Route('/api/user', name: 'user.create', methods: ['POST'])]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserRepository $userRepository,
    ) :JsonResponse
    {
        $apiToken = $request->headers->get('apiToken');
        /** @var User $connectedUSer */
        $connectedUSer = $userRepository->findOneBy(['apiToken' => $apiToken]);

        if ($connectedUSer !== null) {
            if ($connectedUSer->getRole() === 'admin') {
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

                $context = SerializationContext::create()->setGroups(["getAllUsers"]);

                $jsonUser = $serializer->serialize($newUser, 'json', $context /*['groups' => 'getUser']*/);
                return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);

            } else {
                return new JsonResponse(['message' => 'Vous devez être connecté'], Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new JsonResponse(['message' => 'Vous devez être connecté'], Response::HTTP_UNAUTHORIZED);
        }
    }

    // update
    #[Route('/api/user/{idUser}', name: 'user.update', methods: ['PATCH'])]
    #[ParamConverter("user", class: 'App\Entity\User', options: ["id" => "idUser"])]
    public function updateUser(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UserRepository $UserRepository,
        ValidatorInterface $validator,
        UserRepository $userRepository,
    ): JsonResponse {

        $apiToken = $request->headers->get('apiToken');
        /** @var User $connectedUSer */
        $connectedUSer = $userRepository->findOneBy(['apiToken' => $apiToken]);

        if ($connectedUSer !== null) {
            if ($connectedUSer->getRole() === 'admin') {
                $updateUser = $serializer->deserialize(
                    $request->getContent(),
                    User::class,
                    'json'
                );

                $user->setUsername($updateUser->getUsername() ? $updateUser->getUsername() : $user->getUsername());
                $user->setEmail($updateUser->getEmail() ? $updateUser->getEmail() : $user->getEmail());
                $user->setPassword($updateUser->getPassword() ? $updateUser->getPassword() : $user->getPassword());
                $user->setPhoto($updateUser->getPhoto() ? $updateUser->getPhoto() : $user->getPhoto());

                $errors = $validator->validate($user);
                if ($errors->count() >0) {
                    return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
                }

                $entityManager->persist($user);
                $entityManager->flush();

                $context = SerializationContext::create()->setGroups(["getAllUsers"]);

                $jsonUSer = $serializer->serialize($user, 'json', $context);
                return new JsonResponse($jsonUSer, Response::HTTP_CREATED, [], true);

            } else {
                return new JsonResponse(['message' => 'Vous devez être connecté'], Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return new JsonResponse(['message' => 'Vous devez être connecté'], Response::HTTP_UNAUTHORIZED);
        }
    }

    #[Route('/api/login', name: 'user.login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $context = SerializationContext::create()->setGroups(["login"]);

        $data = json_decode($request->getContent(), true);
        $username = $data['username'];
        $password = $data['password'];

        $user = $userRepository->findOneBy(['username' => $username]);
        if ($user !== null) {
            if ($password !== $user->getPassword()) {
                return new JsonResponse(['message' => 'Le mot de passe est invalide'], Response::HTTP_UNAUTHORIZED);
            }
            $generatedToken = $serializer->serialize($user, 'json');

            $user->setApiToken(base64_encode($generatedToken));
            $entityManager->flush();

            return new JsonResponse($serializer->serialize($user, 'json', $context), Response::HTTP_OK, [], true);
        } else {
            return new JsonResponse(['message' => 'Le nom d\'utilisateur est invalide'], Response::HTTP_UNAUTHORIZED);
        }
    }

    #[Route('/api/logout/{idUser}', name: 'user.logout', methods: ['POST'])]
    #[ParamConverter("user", class: 'App\Entity\User', options: ["id" => "idUser"])]
    public function logout(
        User $user,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["login"]);
        $user->setApiToken(null);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Vous êtes déconnecté'], Response::HTTP_UNAUTHORIZED);
    }
}

