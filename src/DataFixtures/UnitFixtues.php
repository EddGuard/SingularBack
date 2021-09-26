<?php

namespace App\DataFixtures;

use App\Entity\Unit;
use App\Repository\BasicAttributesRepository;
use App\Repository\UnitRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UnitFixtues extends Fixture implements FixtureGroupInterface
{

    /**
     * @var BasicAttributesRepository
     */
    private BasicAttributesRepository $basicAttributesRepository;
    /**
     * @var UnitRepository
     */
    private UnitRepository $unitRepository;

    public function __construct(BasicAttributesRepository $basicAttributesRepository, UnitRepository $unitRepository)
    {
        $this->basicAttributesRepository = $basicAttributesRepository;
        $this->unitRepository = $unitRepository;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $defaultUnits = [
            "Year/s",
            "Month/s",
            "Week/s",
            "Hour/s",
            "Kg",
            "L",
            "Km",
            "m",
            "g",
            "ml"
        ];

        foreach ($defaultUnits as $defaultUnit):
            $unit = new Unit();
            $unit->setName($defaultUnit);
            $unit->setReadOnly(true);
            $manager->persist($unit);
        endforeach;
        $manager->flush();

        foreach ($this->basicAttributesRepository->findAll() as $basicAttributes):
            if ($basicAttributes->getName() == "weight"){
                $basicAttributes->setUnit($this->unitRepository->findOneBy(["name" => "Kg"]));
                $manager->persist($basicAttributes);
            }
            if ($basicAttributes->getName() == "volume"){
                $basicAttributes->setUnit($this->unitRepository->findOneBy(["name" => "L"]));
                $manager->persist($basicAttributes);
            }
            if (in_array($basicAttributes->getName(),["estimatedLifetime", "lifetime"])){
                $basicAttributes->setUnit($this->unitRepository->findOneBy(["name" => "Year/s"]));
                $manager->persist($basicAttributes);
            }
        endforeach;
        $manager->flush();

    }

    /**
     * @inheritDoc
     */
    public static function getGroups(): array
    {
        return ['units'];
    }
}
