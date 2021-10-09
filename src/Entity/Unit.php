<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(attributes={
 *          "normalization_context"={"groups"={"unit"}},
 *      },
 *      collectionOperations={
 *          "post"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "validation_groups"={"Default", "Create"},
 *              "denormalization_context"={"groups"={"unit.write"}}
 *          },
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *          "put"={
 *              "denormalization_context"={"groups"={"unit.update"}},
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "delete"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      })
 * @ORM\Entity(repositoryClass=UnitRepository::class)
 */
class Unit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "unit", "attributeValue", "activeType", "active", "basicAttribute", "customAttribute", "activeType.write", "activeType.update"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "unit", "attributeValue", "activeType", "active", "basicAttribute", "customAttribute",
     *     "unit.write", "attributeValue.write", "active.write", "activeType.write",
     *     "basicAttribute.write", "customAttribute.write",
     *     "unit.update", "attributeValue.update", "active.update", "activeType.update",
     *     "basicAttribute.update", "customAttribute.update"
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({
     *     "unit", "attributeValue", "activeType", "active", "basicAttribute", "customAttribute"
     * })
     */
    private $readOnly;

    /**
     * @ORM\OneToMany(targetEntity=AttributeValue::class, mappedBy="unit")
     * @Groups({"unit"})
     */
    private $attributeValues;

    /**
     * @ORM\OneToMany(targetEntity=BasicAttributes::class, mappedBy="unit")
     * @Groups({"unit"})
     */
    private $basicAttributes;

    /**
     * @ORM\OneToMany(targetEntity=CustomAttributes::class, mappedBy="unit")
     * @Groups({"unit"})
     */
    private $customAttributes;

    public function __construct()
    {
        $this->attributeValues = new ArrayCollection();
        $this->basicAttributes = new ArrayCollection();
        $this->customAttributes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getReadOnly(): ?bool
    {
        return $this->readOnly;
    }

    public function setReadOnly(bool $readOnly): self
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    /**
     * @return Collection|attributeValue[]
     */
    public function getAttributeValues(): Collection
    {
        return $this->attributeValues;
    }

    public function addAttributeValue(attributeValue $attributeValue): self
    {
        if (!$this->attributeValues->contains($attributeValue)) {
            $this->attributeValues[] = $attributeValue;
            $attributeValue->setUnit($this);
        }

        return $this;
    }

    public function removeAttributeValue(attributeValue $attributeValue): self
    {
        if ($this->attributeValues->removeElement($attributeValue)) {
            // set the owning side to null (unless already changed)
            if ($attributeValue->getUnit() === $this) {
                $attributeValue->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|basicAttributes[]
     */
    public function getBasicAttributes(): Collection
    {
        return $this->basicAttributes;
    }

    public function addBasicAttribute(basicAttributes $basicAttribute): self
    {
        if (!$this->basicAttributes->contains($basicAttribute)) {
            $this->basicAttributes[] = $basicAttribute;
            $basicAttribute->setUnit($this);
        }

        return $this;
    }

    public function removeBasicAttribute(basicAttributes $basicAttribute): self
    {
        if ($this->basicAttributes->removeElement($basicAttribute)) {
            // set the owning side to null (unless already changed)
            if ($basicAttribute->getUnit() === $this) {
                $basicAttribute->setUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|customAttributes[]
     */
    public function getCustomAttributes(): Collection
    {
        return $this->customAttributes;
    }

    public function addCustomAttribute(customAttributes $customAttribute): self
    {
        if (!$this->customAttributes->contains($customAttribute)) {
            $this->customAttributes[] = $customAttribute;
            $customAttribute->setUnit($this);
        }

        return $this;
    }

    public function removeCustomAttribute(customAttributes $customAttribute): self
    {
        if ($this->customAttributes->removeElement($customAttribute)) {
            // set the owning side to null (unless already changed)
            if ($customAttribute->getUnit() === $this) {
                $customAttribute->setUnit(null);
            }
        }

        return $this;
    }
}
