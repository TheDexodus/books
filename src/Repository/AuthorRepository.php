<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @method Author|null find($id, $lockMode = null, $lockVersion = null)
 * @method Author|null findOneBy(array $criteria, array $orderBy = null)
 * @method Author[] findAll()
 * @method Author[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    /**
     * @param int[] $authorsIds
     * @return Author[]
     */
    public function findByIds(array $authorsIds): array
    {
        $queryBuilder = $this->createQueryBuilder('Author');
        $queryBuilder
            ->andWhere($queryBuilder->expr()->in('Author.id', ':ids'))
            ->setParameter('ids', $authorsIds);

        $result = $queryBuilder->getQuery()->getResult();

        if (count($result) !== count($authorsIds)) {
            $existingIds = array_map(fn(Author $author) => $author->getId(), $result);
            $missingIds = array_diff($authorsIds, $existingIds);

            $violations = new ConstraintViolationList();

            foreach ($missingIds as $id) {
                $violations->add(
                    new ConstraintViolation(
                        message: "Author with id $id not found.",
                        messageTemplate: null,
                        parameters: [],
                        root: $authorsIds,
                        propertyPath: 'authorsIds',
                        invalidValue: $id,
                    ),
                );
            }

            throw new ValidationFailedException($authorsIds, $violations);
        }

        return $result;
    }
}
