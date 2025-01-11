<?php

namespace App\Controller;

use App\Repository\BookReadRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\BookRead;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HomeController handles the main functionalities of the home page.
 * It retrieves the books read by the user and all available books.
 * 
 * This controller is responsible for displaying the home page and handling the addition of new readings.
 */
class HomeController extends AbstractController
{
    /**
     * @var BookReadRepository
     */
    private $readBookRepository;

    /**
     * @var BookRepository
     */
    private $bookRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Constructor to initialize repositories and services.
     *
     * @param BookReadRepository $bookReadRepository
     * @param BookRepository $bookRepository
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(BookReadRepository $bookReadRepository, BookRepository $bookRepository, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->readBookRepository = $bookReadRepository;
        $this->bookRepository = $bookRepository;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * Displays the home page with the list of books read by the user and all available books.
     * 
     * This method retrieves the user's read books and all available books from the database, 
     * then renders the home page with the retrieved data.
     *
     * @return Response
     */
    #[Route('/', name: 'app.home')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('auth.login');
        }

        // Log the type of the user object
        $this->logger->info('User object type: ' . get_class($user));

        /** @var \App\Entity\User $user */
        // Retrieve the books read by the user
        $booksRead = $this->readBookRepository->findByUserId($user->getId(), false);

        // Fetch all books from the repository
        $allBooks = $this->bookRepository->findAllBooks();

        // Render the home page with the retrieved data
        return $this->render('pages/home.html.twig', [
            'booksRead' => $booksRead,
            'allBooks' => $allBooks,
        ]);
    }

    /**
     * Handles the addition of a new reading.
     * 
     * This method handles the POST request to add a new reading. It checks if the user is authenticated, 
     * then retrieves the book data from the request. If the book exists, it creates a new BookRead entity 
     * and persists it to the database.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/add-reading', name: 'add_reading', methods: ['POST'])]
    public function addReading(Request $request): Response
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['status' => 'error', 'message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
            }

            // Log the type of the user object
            $this->logger->info('User object type: ' . get_class($user));

            // Validate the book ID and rating from the request
            $bookId = $request->request->get('bookId');
            $rating = $request->request->get('rating');
            $isRead = $request->request->get('isRead');

            // Check if the book exists
            $book = $this->bookRepository->find($bookId);
            if (!$book) {
                return new JsonResponse(['status' => 'error', 'message' => 'Book not found'], Response::HTTP_BAD_REQUEST);
            }

            // Create a new BookRead entity and set its properties
            $bookRead = new BookRead();
            $bookRead->setBookId($bookId);
            $bookRead->setRating($rating);
            $bookRead->setIsRead($isRead);
            $bookRead->setCreatedAt(new \DateTime());
            $bookRead->setUpdatedAt(new \DateTime());

            // Persist the new reading to the database
            $entityManager = $this->entityManager;
            $entityManager->persist($bookRead);
            $entityManager->flush();

            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            // Log the exception message and return an error response
            $this->logger->error('Error adding reading: ' . $e->getMessage());
            return new JsonResponse(['status' => 'error', 'message' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}