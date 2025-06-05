<?php

declare(strict_types=1);

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;

class LikeFilter implements FilterInterface
{
    public function filter(QueryBuilder $queryBuilder, object $filter, array $fields = []): QueryBuilder
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        foreach ($fields as $field) {
            if (!is_null($filter->$field)) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->like("$rootAlias.$field", ":$field"))
                    ->setParameter($field, $filter->$field);
            }
        }

        return $queryBuilder;
    }
}
