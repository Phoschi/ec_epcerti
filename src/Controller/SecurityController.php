<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * SecurityController handles user authentication functionalities.
 * It manages login and logout actions for users.
 */
class SecurityController extends AbstractController
{
    /**
     * Handles the login process.
     *
     * This method handles user login.
     * It retrieves the last authentication error and the last username entered by the user.
     * Then, it renders the login page with the last username and any error message.
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/login', name: 'auth.login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Render the login page with the last username and any error message
        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * This method handles user logout.
     *
     * The method can remain empty, as it will be intercepted by the security firewall's logout key.
     */
    #[Route('/logout', name: 'auth.logout')]
    public function logout(): void
    {
        // This method can remain empty, as it will be intercepted by the security firewall's logout key
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
