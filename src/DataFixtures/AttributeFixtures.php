<?php

namespace App\DataFixtures;

use App\Entity\BasicAttributes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AttributeFixtures extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $defaultAttributes = [
          "estimatedLifetime" => 1,
          "lifetime" => 1,
          "useWearTear" => 3,
          "weight" => 1,
          "volume" => 1
        ];

        foreach ($defaultAttributes as $defaultAttribute => $defaultValue):
            $basicAttribute = new BasicAttributes();
            $basicAttribute->setName($defaultAttribute);
            $basicAttribute->setValue($defaultValue);
            $manager->persist($basicAttribute);
            $manager->flush();
        endforeach;

    }

    /**
     * @inheritDoc
     */
    public static function getGroups(): array
    {
        return ['attributes'];
    }
}
