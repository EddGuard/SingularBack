<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
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
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "active", "activeType"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({
     *     "active", "active.write", "active.update", "activeType"
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
     *     "active", "active.write", "active.update", "activeType"
     * })
     */
    private $measurementData;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update", "activeType"
     * })
     */
    private $measurementUnit;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update", "activeType"
     * })
     */
    private $useWearTear;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update", "activeType"
     * })
     */
    private $estimatedLifetime;

    /**
     * @ORM\Column(type="float")
     * @Groups({
     *     "active", "active.write", "active.update", "activeType"
     * })
     */
    private $lifetime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update", "activeType"
     * })
     */
    private $lifetimeMeasurementUnit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *     "active", "active.write", "active.update", "activeType"
     * })
     */
    private $customAttributes;

    /**
     * @ORM\OneToOne(targetEntity=ActiveRecord::class, mappedBy="active", cascade={"persist", "remove"})
     * @Groups({
     *     "active", "active.write", "active.update", "activeType"
     * })
     */
    private $activeRecord;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/fileFormat")
     * @Groups({"active", "active.write", "active.update", "activeType"})
     */
    public $file;

    /**
     * @ORM\ManyToOne(targetEntity=ActiveType::class, inversedBy="actives")
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $activeType;

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

    public function getActiveType(): ?ActiveType
    {
        return $this->activeType;
    }

    public function setActiveType(?ActiveType $activeType): self
    {
        $this->activeType = $activeType;

        return $this;
    }
}
