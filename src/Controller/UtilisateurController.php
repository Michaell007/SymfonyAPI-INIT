<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurController extends AbstractController
{

    private $userRepo;
    private $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher, UtilisateurRepository $userRepo) {
        $this->passwordHasher = $passwordHasher;
        $this->userRepo = $userRepo;
    }

    #[Route('api/user', name: 'app_utilisateur', methods: ["GET"])]
    public function index(Request $request): JsonResponse
    {

        // get data request
        $request_data = json_decode($request->getContent(), true);
        $user = new Utilisateur();

        // check if username exist
        $verifUsername = $this->userRepo->findOneByUsername($request_data["username"]);
        if ($verifUsername != null) {
            return $this->json([
                'code' => 401,
                'message' => "Ce username d'utilisateur est déjà utilisé."
            ]);
        }

        $user->setUsername($request_data["username"]);
        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $this->passwordHasher->hashPassword($user, $request_data["password"]);
        $user->setPassword($hashedPassword);
        // save user
        $this->userRepo->save($user, true);

        return $this->json($user->data());
    }
}
