<?php

namespace App\Filter\ActiveRecord;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class ArrayDateFilter extends AbstractContextAwareFilter
{
    protected array $properties = ["dateRecord"];

    /**
     * @inheritDoc
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // TODO: Implement filterProperty() method.
        if ($property !== "dateRecord") {
            return;
        }

        $filter = "o.dateRecord LIKE '%$value%'";

        $queryBuilder->andWhere($filter);
    }

    /**
     * @inheritDoc
     */
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description["similar_$property"] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter in field: ' . $property,
                    'name' => $property,
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }
}
