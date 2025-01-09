<?php

namespace App\Controller;

use App\Repository\BookReadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private BookReadRepository $readBookRepository;

    public function __construct(BookReadRepository $bookReadRepository)
    {
        $this->readBookRepository = $bookReadRepository;
    }

    #[Route('/', name: 'app.home')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('auth.login');
        }

        /** @var \App\Entity\User $user */
        $booksRead = $this->readBookRepository->findByUserId($user->getId(), false);

        return $this->render('pages/home.html.twig', [
            'booksRead' => $booksRead,
        ]);
    }
}
