<?php

namespace App\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Active;
use Doctrine\ORM\EntityManagerInterface;

class ActiveDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Active::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $active = $this->entityManager
            ->getRepository(Active::class)
            ->find($id);

        $activeArray = array();

        $activeArray[] = $active;

        return $activeArray;
    }
}