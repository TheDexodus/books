<?php

declare(strict_types=1);

namespace App\Resolver\Book;

use App\Dto\Input\Author\EditAuthorInput;
use App\Dto\Input\Book\CreateBookInput;
use App\Dto\Input\Book\EditBookInput;
use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookMutationResolverMap extends ResolverMap
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuthorRepository $authorRepository,
        private BookRepository $bookRepository,
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {
    }

    protected function map()
    {
        return [
            'Mutation' => [
                'createBook' => $this->createBook(...),
                'editBook' => $this->editBook(...),
                'deleteBook' => $this->deleteBook(...),
            ],
        ];
    }

    protected function createBook($value, ArgumentInterface $args): Book
    {
        /**
         * @var CreateBookInput $createBookInput
         */
        $createBookInput = $this->denormalizer->denormalize($args['input'], CreateBookInput::class);
        $violations = $this->validator->validate($createBookInput);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($createBookInput, $violations);
        }

        $book = new Book();
        $book->name = $createBookInput->name;
        $book->description = $createBookInput->description;
        $book->publishYear = intval((new DateTimeImmutable())->format('Y'));

        foreach ($this->authorRepository->findByIds($createBookInput->authorsIds) as $author) {
            $author->books->add($book);
            $book->authors->add($author);
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $book;
    }

    protected function editBook($value, ArgumentInterface $args): ?Book
    {
        $book = $this->bookRepository->find($args['id']);

        if (is_null($book)) {
            return null;
        }

        /**
         * @var EditBookInput $editBookInput
         */
        $editBookInput = $this->denormalizer->denormalize($args['input'], EditBookInput::class);
        $violations = $this->validator->validate($editBookInput);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($editBookInput, $violations);
        }

        $book->name = $editBookInput->name ?? $book->name;
        $book->description = $editBookInput->description ?? $book->description;

        if (!is_null($editBookInput->authorsIds)) {
            foreach ($book->authors as $author) {
                $author->books->removeElement($book);
                $book->authors->removeElement($author);
            }

            foreach ($this->authorRepository->findByIds($editBookInput->authorsIds) as $author) {
                if (!$author->books->contains($book)) {
                    $author->books->add($book);
                }
                if (!$book->authors->contains($author)) {
                    $book->authors->add($author);
                }
            }
        }

        $this->entityManager->flush();

        return $book;
    }

    protected function deleteBook($value, ArgumentInterface $args): bool
    {
        $book = $this->bookRepository->find($args['id']);

        if (is_null($book)) {
            return false;
        }

        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return true;
    }
}
