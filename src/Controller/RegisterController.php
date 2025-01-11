<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * RegisterController handles user registration functionalities.
 * It processes registration requests and manages user creation.
 */
class RegisterController extends AbstractController
{
    /**
     * Handles the registration of a new user.
     *
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/register', name: 'auth.register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Create a new User entity
        $user = new User();

        // Check if the request method is POST
        if ($request->isMethod('POST')) {
            // Get user data from the request
            $email = $request->request->get('user_email');
            $password = $request->request->get('user_password');
            $passwordConfirm = $request->request->get('user_password_confirm');

            // Validation de l'email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'L\'adresse email n\'est pas valide.');
                return $this->redirectToRoute('auth.register');
            }

            // Vérifier si l'email existe déjà
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée.');
                return $this->redirectToRoute('auth.register');
            }

            // Validation du mot de passe
            if (strlen($password) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
                return $this->redirectToRoute('auth.register');
            }

            if ($password !== $passwordConfirm) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('auth.register');
            }

            // Set user email
            $user->setEmail($email);

            // Hash the password before saving
            $hashedPassword = $userPasswordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            // Persist the user entity to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect to a success page or login
            $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('auth.login');
        }

        // Render the registration form
        return $this->render('auth/register.html.twig');
    }
}