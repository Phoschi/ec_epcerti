<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 * BookRepository handles the data access for Book entities.
 * This class provides methods to retrieve Book data from the database.
 */
class BookRepository extends ServiceEntityRepository
{
    /**
     * Initializes the repository with the ManagerRegistry and the Book entity.
     *
     * @param ManagerRegistry $registry The ManagerRegistry instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Finds books by a specific example field.
     * This method creates a query builder to filter books by the example field,
     * orders the results by ID in ascending order, and limits the results to 10.
     *
     * @param mixed $value The value to search for in the example field.
     * @return Book[] Returns an array of Book objects that match the criteria.
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Finds a single book by a specific field.
     * This method creates a query builder to filter books by the specified field
     * and returns the first matching book or null if not found.
     *
     * @param mixed $value The value to search for in the field.
     * @return Book|null Returns a Book object or null if not found.
     */
    public function findOneBySomeField($value): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Retrieves all books from the database.
     * This method creates a query builder to retrieve all books and orders the results by ID in ascending order.
     *
     * @return Book[] Returns an array of all Book objects.
     */
    public function findAllBooks(): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
