<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Exception\InvalidArgumentException;
use App\Repository\ActiveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\MediaObject;
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
 *              "denormalization_context"={"groups"={"active.write"}},
 *              "validate"=false
 *          },
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *          "put"={
 *              "denormalization_context"={"groups"={"active.update"}},
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "validate"=false
 *          },
 *          "delete"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      })
 * @ORM\Entity(repositoryClass=ActiveRepository::class)
 * @UniqueEntity(fields={"reference"}, ignoreNull=false)
 * @ApiFilter(SearchFilter::class, properties={
 *     "reference": "iexact",
 *     "activeType.name": "ipartial"
 * })
 * @ApiFilter(DateFilter::class, properties={"entryDate"})
 * @ApiFilter(OrderFilter::class, properties={
 *     "entryDate",
 *     "activeType.name"
 * }, arguments={"orderParameterName"="order"})
 */
class Active
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "active", "activeType", "activeRecord"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({
     *     "active", "active.write", "active.update", "activeType", "activeRecord"
     * })
     */
    private $reference;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     * @Groups({
     *     "active", "active.update"
     * })
     */
    private $entryDate;

    /**
     * @ORM\OneToOne(targetEntity=ActiveRecord::class, mappedBy="active", cascade={"persist", "remove"})
     * @Groups({
     *     "active"
     * })
     */
    private $activeRecord;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class, cascade={"persist"})
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

    /**
     * @ORM\OneToMany(targetEntity=AttributeValue::class, mappedBy="activeBasics", cascade={"remove"})
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $basicAttributes;

    /**
     * @ORM\OneToMany(targetEntity=AttributeValue::class, mappedBy="activeCustoms", cascade={"remove"})
     * @Groups({
     *     "active", "active.write", "active.update"
     * })
     */
    private $customAttributes;

    public function __construct()
    {
        $this->attributeValues = new ArrayCollection();
    }

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

    /**
     * @return Collection|AttributeValue[]
     */
    public function getBasicAttributes(): Collection
    {
        return $this->basicAttributes;
    }

    public function addBasicAttributes(AttributeValue $attributeValue): self
    {
        if (!empty($this->basicAttributes)) {
            if (is_array($this->basicAttributes)) {
                $alreadyExist = in_array($attributeValue, $this->basicAttributes);
            } else {
                $alreadyExist = $this->basicAttributes->contains($attributeValue);
            }
            if (!$alreadyExist) {
                $this->basicAttributes[] = $attributeValue;
                $attributeValue->setActiveBasics($this);
            }
        } else {
            $this->basicAttributes[] = $attributeValue;
            $attributeValue->setActiveBasics($this);
        }

        return $this;
    }

    public function removeBasicAttributes(AttributeValue $attributeValue): self
    {
        if ($this->basicAttributes->removeElement($attributeValue)) {
            // set the owning side to null (unless already changed)
            if ($attributeValue->getActiveBasics() === $this) {
                $attributeValue->setActiveBasics(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AttributeValue[]
     */
    public function getCustomAttributes(): Collection
    {
        return $this->customAttributes;
    }

    public function addCustomAttributes(AttributeValue $attributeValue): self
    {
        if (!empty($this->customAttributes)) {
            if (is_array($this->basicAttributes)) {
                $alreadyExist = in_array($attributeValue, $this->customAttributes);
            } else {
                $alreadyExist = $this->customAttributes->contains($attributeValue);
            }
            if (!$alreadyExist) {
                $this->customAttributes[] = $attributeValue;
                $attributeValue->setActiveCustoms($this);
            }
        } else {
            $this->customAttributes[] = $attributeValue;
            $attributeValue->setActiveCustoms($this);
        }

        return $this;
    }

    public function removeCustomAttributes(AttributeValue $attributeValue): self
    {
        if ($this->customAttributes->removeElement($attributeValue)) {
            // set the owning side to null (unless already changed)
            if ($attributeValue->getActiveCustoms() === $this) {
                $attributeValue->setActiveCustoms(null);
            }
        }

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function removeAllCustomAttributes()
    {
        foreach ($this->customAttributes as $customAttribute) {
            $customAttribute->setActiveCustoms(null);
        }
        $this->customAttributes = new ArrayCollection();
        return $this;
    }
}
