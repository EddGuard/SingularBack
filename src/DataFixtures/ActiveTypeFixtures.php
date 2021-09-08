<?php

namespace App\DataFixtures;

use App\Entity\ActiveType;
use App\Entity\BasicAttributes;
use App\Repository\BasicAttributesRepository;
use ContainerIh0zBIx\getBasicAttributesRepositoryService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ActiveTypeFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * @var BasicAttributesRepository
     */
    private BasicAttributesRepository $basicAttributesRepository;

    public function __construct(BasicAttributesRepository $basicAttributesRepository)
    {
        $this->basicAttributesRepository = $basicAttributesRepository;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $defaultType = new ActiveType();
        $defaultType->setName('default');
        $basicAttributes = $this->basicAttributesRepository->findAll();

        foreach ($basicAttributes as $basicAttribute):
            $defaultType->addBasicAttribute($basicAttribute);
            $manager->persist($defaultType);
        endforeach;

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public static function getGroups(): array
    {
        return ['types'];
    }
}
