<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Exception\InvalidArgumentException;
use App\Repository\ActiveRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ApiResource(attributes={
 *          "normalization_context"={"groups"={"active"}},
 *      },
 *      collectionOperations={
 *          "post"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "validation_groups"={"Default", "Create"},
 *              "denormalization_context"={"groups"={"active.write"}}
 *          },
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *          "put"={
 *              "denormalization_context"={"groups"={"active.update"}},
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "delete"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      })
 * @ORM\Entity(repositoryClass=ActiveRepository::class)
 * @UniqueEntity(fields={"reference"}, ignoreNull=false)
 * @ApiFilter(SearchFilter::class, properties={
 *     "reference": "iexact",
 *     "type": "iexact",
 *     "measurementData": "iexact",
 *     "measurementUnit": "iexact",
 *     "lifetime": "iexact",
 *     "estimatedLifetime": "iexact",
 *     "lifetimeMeasurementUnit": "iexact"
 * })
 * @ApiFilter(DateFilter::class, properties={"entryDate"})
 * @ApiFilter(OrderFilter::class, properties={
 *     "entryDate",
 *     "type",
 *     "lifetime"
 * }, arguments={"orderParameterName"="order"})
 */
class Active
{
    const AVAILABLE_TYPES = [
        'ENGINE',
        'PIPE',
        'WHEEL',
        'ELECTRONICS',
        'HOME',
        'COSMETICS',
        'FAGRILE'
    ];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "active"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $reference;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     * @Groups({
     *     "active"
     * })
     */
    private $entryDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $measurementData;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $measurementUnit;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $useWearTear;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $estimatedLifetime;

    /**
     * @ORM\Column(type="float")
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $lifetime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $lifetimeMeasurementUnit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $customAttributes;

    /**
     * @ORM\OneToOne(targetEntity=ActiveRecord::class, mappedBy="active", cascade={"persist", "remove"})
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
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

    public function getLifetime(): ?string
    {
        return $this->lifetime . ' ' . $this->lifetimeMeasurementUnit;
    }

    public function setLifetime(float $lifetime): self
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    public function getMeasurementData(): ?string
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        if (in_array($type, self::AVAILABLE_TYPES)) {
            $this->type = $type;
        } else {
            throw new InvalidArgumentException("El tipo de activo debe ser uno de los siguientes: " . implode(', ', self::AVAILABLE_TYPES));
        }

        return $this;
    }

    public function getCustomAttributes(): ?string
    {
        return $this->customAttributes;
    }

    public function setCustomAttributes(string $customAttributes): self
    {
        $this->customAttributes = $customAttributes;

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
        if ($activeRecord->getActive()->getId() !== $newActiveId) {
            $activeRecord->setActive($newActiveId);
        }

        return $this;
    }
}
