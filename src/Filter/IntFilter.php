<?php

declare(strict_types=1);

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;

class IntFilter implements FilterInterface
{
    public function filter(QueryBuilder $queryBuilder, object $filter, array $fields = []): QueryBuilder
    {
        foreach ($fields as $field => $originalValueName) {
            foreach ($this->getValueNames($originalValueName) as $operation => $valueName) {
                if (!is_null($filter->$valueName)) {
                    $this
                        ->andWhere($queryBuilder, "$field $operation :$valueName")
                        ->setParameter($valueName, $filter->$valueName);
                }
            }
        }

        return $queryBuilder;
    }

    protected function andWhere(QueryBuilder $queryBuilder, string $where): QueryBuilder
    {
        return $queryBuilder->andWhere($where);
    }

    protected function getValueNames(string $valueName): array
    {
        $ucfValueName = ucfirst($valueName);

        return [
            ">=" => "min$ucfValueName",
            "<=" => "max$ucfValueName",
            "=" => $valueName,
        ];
    }
}
