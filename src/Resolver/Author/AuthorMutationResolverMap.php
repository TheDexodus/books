<?php

declare(strict_types=1);

namespace App\Resolver\Author;

use App\Dto\Input\Author\CreateAuthorInput;
use App\Dto\Input\Author\EditAuthorInput;
use App\Entity\Author;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthorMutationResolverMap extends ResolverMap
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private AuthorRepository $authorRepository,
        private BookRepository $bookRepository,
    ) {
    }

    protected function map()
    {
        return [
            'Mutation' => [
                'createAuthor' => $this->createAuthor(...),
                'editAuthor' => $this->editAuthor(...),
                'deleteAuthor' => $this->deleteAuthor(...),
            ],
        ];
    }

    protected function createAuthor($value, ArgumentInterface $args): Author
    {
        /**
         * @var CreateAuthorInput $createAuthorInput
         */
        $createAuthorInput = $this->denormalizer->denormalize($args['input'], CreateAuthorInput::class);
        $violations = $this->validator->validate($createAuthorInput);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($createAuthorInput, $violations);
        }

        $author = new Author();
        $author->firstName = $createAuthorInput->firstName;
        $author->lastName = $createAuthorInput->lastName;
        $author->patronymic = $createAuthorInput->patronymic;

        $this->entityManager->persist($author);
        $this->entityManager->flush();

        return $author;
    }

    protected function editAuthor($value, ArgumentInterface $args): ?Author
    {
        $author = $this->authorRepository->find($args['id']);

        if (is_null($author)) {
            return null;
        }

        /**
         * @var EditAuthorInput $editAuthorInput
         */
        $editAuthorInput = $this->denormalizer->denormalize($args['input'], EditAuthorInput::class);
        $violations = $this->validator->validate($editAuthorInput);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($editAuthorInput, $violations);
        }

        $author->firstName = $editAuthorInput->firstName ?? $author->firstName;
        $author->lastName = $editAuthorInput->lastName ?? $author->lastName;
        $author->patronymic = $editAuthorInput->patronymic ?? $author->patronymic;

        $this->entityManager->flush();

        return $author;
    }

    protected function deleteAuthor($value, ArgumentInterface $args): bool
    {
        $force = $args['force'] ?? false;
        $author = $this->authorRepository->find($args['id']);

        if (is_null($author)) {
            return false;
        }

        $books = $this->bookRepository->findWithOnlyOneAuthor($author);

        if ($force && !empty($books)) {
            foreach ($books as $book) {
                $this->entityManager->remove($book);
            }
        } elseif (!empty($books)) {
            throw new ValidationFailedException($author, new ConstraintViolationList([
                new ConstraintViolation(
                    message: 'Cannot delete the author because some books have only this author.',
                    messageTemplate: 'Cannot delete the author because some books have only this author.',
                    parameters: [],
                    root: $author,
                    propertyPath: '',
                    invalidValue: $author
                ),
            ]));
        }

        $this->entityManager->remove($author);
        $this->entityManager->flush();

        return true;
    }
}
