<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ActiveRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=ActiveRepository::class)
 * @UniqueEntity(fields={"reference"}, ignoreNull=false)
 */
class Active
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $reference;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     */
    private $entryDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $measurementData;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $measurementUnit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $useWearTear;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $estimatedLifetime;

    /**
     * @ORM\Column(type="float")
     */
    private $lifetime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lifetimeMeasurementUnit;

    /**
     * @ORM\OneToOne(targetEntity=ActiveRecord::class, mappedBy="activeId", cascade={"persist", "remove"})
     */
    private $activeRecord;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getEntryDate(): ?\DateTimeInterface
    {
        return $this->entryDate;
    }

    public function setEntryDate(\DateTimeInterface $entryDate): self
    {
        $this->entryDate = $entryDate;

        return $this;
    }

    public function getMeasurementUnit(): ?string
    {
        return $this->measurementUnit;
    }

    public function setMeasurementUnit(?string $measurementUnit): self
    {
        $this->measurementUnit = $measurementUnit;

        return $this;
    }

    public function getUseWearTear(): ?float
    {
        return $this->useWearTear;
    }

    public function setUseWearTear(?float $useWearTear): self
    {
        $this->useWearTear = $useWearTear;

        return $this;
    }

    public function getEstimatedLifetime(): ?string
    {
        return $this->estimatedLifetime . ' ' . $this->lifetimeMeasurementUnit;
    }

    public function setEstimatedLifetime(?float $estimatedLifetime): self
    {
        $this->estimatedLifetime = $estimatedLifetime;

        return $this;
    }

    public function getLifetime(): ? string
    {
        return $this->lifetime . ' ' . $this->lifetimeMeasurementUnit;
    }

    public function setLifetime(float $lifetime): self
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    public function getMeasurementData(): ? string
    {
        return $this->measurementData . $this->measurementUnit;
    }

    public function setMeasurementData(?float $measurementData): self
    {
        $this->measurementData = $measurementData;

        return $this;
    }

    public function getLifetimeMeasurementUnit(): ?string
    {
        return $this->lifetimeMeasurementUnit;
    }

    public function setLifetimeMeasurementUnit(?string $lifetimeMeasurementUnit): self
    {
        $this->lifetimeMeasurementUnit = $lifetimeMeasurementUnit;

        return $this;
    }

    public function getActiveRecord(): ?ActiveRecord
    {
        return $this->activeRecord;
    }

    public function setActiveRecord(?ActiveRecord $activeRecord): self
    {
        $this->activeRecord = $activeRecord;

        // set (or unset) the owning side of the relation if necessary
        $newActiveId = null === $activeRecord ? null : $this;
        if ($activeRecord->getActiveId() !== $newActiveId) {
            $activeRecord->setActiveId($newActiveId);
        }

        return $this;
    }
}
