<?php

declare(strict_types=1);

namespace App\Resolver\Author;

use App\Dto\Filter\AuthorsFilter;
use App\Entity\Author;
use App\Filter\IntFilter;
use App\Filter\LikeFilter;
use App\Repository\AuthorRepository;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AuthorQueryResolverMap extends ResolverMap
{
    public function __construct(
        private AuthorRepository $authorRepository,
        private DenormalizerInterface $denormalizer,
        private LikeFilter $likeFilter,
        private IntFilter $intFilter,
    ) {
    }

    protected function map()
    {
        return [
            'Query' => [
                'author' => $this->author(...),
                'authors' => $this->authors(...),
            ],
        ];
    }

    protected function author($value, ArgumentInterface $args): ?Author
    {
        return $this->authorRepository->find($args['id']);
    }

    protected function authors($value, ArgumentInterface $args): array
    {
        $filter = $this->denormalizer->denormalize($args['filter'] ?? [], AuthorsFilter::class);
        $queryBuilder = $this->authorRepository->createQueryBuilder('Author');

        $this->likeFilter->filter($queryBuilder, $filter, ['firstName', 'lastName', 'patronymic']);
        $this->intFilter->filter($queryBuilder, $filter, ['Author.countBooks' => 'countBooks']);

        return $queryBuilder->getQuery()->getResult();
    }
}
