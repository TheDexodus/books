<?php

declare(strict_types=1);

namespace App\Resolver\Book;

use App\Dto\Filter\BooksFilter;
use App\Entity\Book;
use App\Filter\IntFilter;
use App\Filter\LikeFilter;
use App\Repository\BookRepository;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class BookQueryResolverMap extends ResolverMap
{
    public function __construct(
        private BookRepository $bookRepository,
        private DenormalizerInterface $denormalizer,
        private LikeFilter $likeFilter,
        private IntFilter $intFilter,
    ) {
    }

    protected function map()
    {
        return [
            "Query" => [
                'book' => $this->book(...),
                'books' => $this->books(...),
            ],
        ];
    }

    protected function book($value, ArgumentInterface $args): ?Book
    {
        return $this->bookRepository->find($args['id']);
    }

    protected function books($value, ArgumentInterface $args): array
    {
        $filter = $this->denormalizer->denormalize($args['filter'] ?? [], BooksFilter::class);
        $queryBuilder = $this->bookRepository->createQueryBuilder('Book');

        $this->likeFilter->filter($queryBuilder, $filter, ['name', 'description']);
        $this->intFilter->filter($queryBuilder, $filter, ['Book.publishYear' => 'publishYear']);

        return $queryBuilder->getQuery()->getResult();
    }
}
