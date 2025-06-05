<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[] findAll()
 * @method Book[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @param Author $author
     * @return Book[]
     */
    public function findWithOnlyOneAuthor(Author $author): array
    {
        $queryBuilder = $this->createQueryBuilder('Book');
        $queryBuilder
            ->innerJoin('Book.authors', 'Author')
            ->groupBy('Book.id')
            ->andHaving('COUNT(Author.id) = 1')
            ->andHaving('MIN(Author.id) = :author')
            ->setParameter('author', $author->getId());

        return $queryBuilder->getQuery()->getResult();
    }
}
