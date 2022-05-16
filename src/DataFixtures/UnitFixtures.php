<?php

namespace App\DataFixtures;

use App\Entity\Unit;
use App\Repository\BasicAttributesRepository;
use App\Repository\UnitRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UnitFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
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
            "ml",
            "%"
        ];

        foreach ($defaultUnits as $defaultUnit):
            $unit = new Unit();
            $unit->setName($defaultUnit);
            $unit->setReadOnly(true);
            $manager->persist($unit);
        endforeach;
        $manager->flush();

        foreach ($this->basicAttributesRepository->findAll() as $basicAttribute):
            if ($basicAttribute->getName() == "weight"){
                $unit = $this->unitRepository->findOneBy(["name" => "Kg"]);
                $basicAttribute->setUnit($unit);
                $unit->addBasicAttribute($basicAttribute);
                $manager->persist($unit);
                $manager->persist($basicAttribute);
            }
            if ($basicAttribute->getName() == "volume"){
                $unit = $this->unitRepository->findOneBy(["name" => "L"]);
                $basicAttribute->setUnit($unit);
                $unit->addBasicAttribute($basicAttribute);
                $manager->persist($unit);
                $manager->persist($basicAttribute);
            }
            if (in_array($basicAttribute->getName(),["estimatedLifetime", "lifetime"])){
                $unit = $this->unitRepository->findOneBy(["name" => "Year/s"]);
                $basicAttribute->setUnit($unit);
                $unit->addBasicAttribute($basicAttribute);
                $manager->persist($unit);
                $manager->persist($basicAttribute);
            }
            if ($basicAttribute->getName() == "useWearTear"){
                $unit = $this->unitRepository->findOneBy(["name" => "%"]);
                $basicAttribute->setUnit($unit);
                $unit->addBasicAttribute($basicAttribute);
                $manager->persist($unit);
                $manager->persist($basicAttribute);
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

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [AttributeFixtures::class];
    }
}
