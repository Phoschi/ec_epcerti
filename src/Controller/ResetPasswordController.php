<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    #[Route('', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, TokenGeneratorInterface $tokenGenerator): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('no-reply@example.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('reset_password/email.html.twig')
                    ->context([
                        'resetToken' => $token,
                    ]);

                $mailer->send($email);
                $this->addFlash('success', 'Un email de réinitialisation vous a été envoyé.');
                return $this->redirectToRoute('auth.login');
            }

            $this->addFlash('error', 'Cette adresse email n\'existe pas.');
        }

        return $this->render('reset_password/request.html.twig');
    }

    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('auth.login');
        }

        if ($request->isMethod('POST')) {
            $user->setResetToken(null);
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $request->request->get('password')
                )
            );

            $entityManager->flush();
            $this->addFlash('success', 'Mot de passe mis à jour avec succès.');
            return $this->redirectToRoute('auth.login');
        }

        return $this->render('reset_password/reset.html.twig', ['token' => $token]);
    }
}
