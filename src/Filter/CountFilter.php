<?php

declare(strict_types=1);

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;

class CountFilter extends IntFilter implements FilterInterface
{
    public function filter(QueryBuilder $queryBuilder, object $filter, array $fields = []): QueryBuilder
    {
        $mappedFields = [];

        foreach ($fields as $field => $originalValueName) {
            $joinName = ucfirst(explode('.', $field)[1]);

            if (str_ends_with($joinName, 'ies')) {
                $joinName = substr($joinName, 0, -3) . 'y';
            }

            if (str_ends_with($joinName, 's')) {
                $joinName = substr($joinName, 0, -1);
            }

            $mappedFields["COUNT($joinName)"] = $originalValueName;

            if (array_key_exists($joinName, $queryBuilder->getAllAliases())) {
                continue;
            }

            foreach ($this->getValueNames($originalValueName) as $valueName) {
                if (!is_null($filter->$valueName)) {
                    $queryBuilder->leftJoin($field, $joinName);
                    $queryBuilder->groupBy($queryBuilder->getRootAliases()[0] . '.id');
                    break;
                }
            }
        }

        parent::filter($queryBuilder, $filter, $mappedFields);

        return $queryBuilder;
    }

    protected function andWhere(QueryBuilder $queryBuilder, string $where): QueryBuilder
    {
        return $queryBuilder->andHaving($where);
    }
}
