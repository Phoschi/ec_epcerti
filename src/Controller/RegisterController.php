<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'auth.register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setEmail($request->request->get('user_email'));
            
            if ($request->request->get('user_password') === $request->request->get('user_password_confirm')) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $request->request->get('user_password')
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('auth.login');
            }
        }

        return $this->render('auth/register.html.twig');
    }
}