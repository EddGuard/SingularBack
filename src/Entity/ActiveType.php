<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ActiveTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * @ApiResource(attributes={
 *          "normalization_context"={"groups"={"activeType"}},
 *      },
 *      collectionOperations={
 *          "post"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "validation_groups"={"Default", "Create"},
 *              "denormalization_context"={"groups"={"activeType.write"}}
 *          },
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *          "put"={
 *              "denormalization_context"={"groups"={"activeType.update"}},
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "delete"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      })
 * @ORM\Entity(repositoryClass=activeTypeRepository::class)
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "ipartial"
 * })
 * @ApiFilter(OrderFilter::class, properties={
 *     "id"
 * }, arguments={"orderParameterName"="order"})
 */
class ActiveType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "activeType", "active"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "activeType", "active"
     * })
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Active::class, mappedBy="activeType", cascade={"remove"})
     */
    private $actives;

    /**
     * @ORM\OneToMany(targetEntity=AttributeValue::class, mappedBy="activeTypeBasics", cascade={"remove"})
     * @Groups({
     *     "activeType", "active"
     * })
     */
    private $basicAttributes;

    /**
     * @ORM\OneToMany(targetEntity=AttributeValue::class, mappedBy="activeTypeCustoms", cascade={"remove"})
     * @Groups({
     *     "activeType", "active"
     * })
     */
    private $customAttributes;

    public function __construct()
    {
        $this->actives = new ArrayCollection();
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

    /**
     * @return Collection|Active[]
     */
    public function getActives(): Collection
    {
        return $this->actives;
    }

    public function addActive(Active $active): self
    {
        if (!$this->actives->contains($active)) {
            $this->actives[] = $active;
            $active->setActiveType($this);
        }

        return $this;
    }

    public function removeActive(Active $active): self
    {
        if ($this->actives->removeElement($active)) {
            // set the owning side to null (unless already changed)
            if ($active->getActiveType() === $this) {
                $active->setActiveType(null);
            }
        }

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
            if (!$this->basicAttributes->contains($attributeValue)) {
                $this->basicAttributes[] = $attributeValue;
                $attributeValue->setActiveTypeBasics($this);
            }
        }else{
            $this->basicAttributes[] = $attributeValue;
            $attributeValue->setActiveTypeBasics($this);
        }

        return $this;
    }

    public function removeBasicAttributes(AttributeValue $attributeValue): self
    {
        if ($this->basicAttributes->removeElement($attributeValue)) {
            // set the owning side to null (unless already changed)
            if ($attributeValue->getActiveTypeBasics() === $this) {
                $attributeValue->setActiveTypeBasics(null);
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
            if (!$this->customAttributes->contains($attributeValue)) {
                $this->customAttributes[] = $attributeValue;
                $attributeValue->setActiveTypeCustoms($this);
            }
        }else{
            $this->customAttributes[] = $attributeValue;
            $attributeValue->setActiveTypeCustoms($this);
        }

        return $this;
    }

    public function removeCustomAttributes(AttributeValue $attributeValue): self
    {
        if ($this->customAttributes->removeElement($attributeValue)) {
            // set the owning side to null (unless already changed)
            if ($attributeValue->getActiveTypeCustoms() === $this) {
                $attributeValue->setActiveTypeCustoms(null);
            }
        }

        return $this;
    }

    public function removeAllCustomAttributes(){
        if (!empty($this->customAttributes)) {
            foreach ($this->customAttributes as $customAttribute) {
                $customAttribute->setActiveTypeCustoms(null);
            }
            $this->customAttributes = new ArrayCollection();
        }
        return $this;
    }
}
