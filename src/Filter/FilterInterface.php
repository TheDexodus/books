<?php

declare(strict_types=1);

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;

interface FilterInterface
{
    public function filter(QueryBuilder $queryBuilder, object $filter, array $fields = []): QueryBuilder;
}
