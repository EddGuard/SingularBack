<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ActiveTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
 */
class ActiveType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "activeType", "active", "active.write", "active.update"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "activeType", "activeType.write",
     *     "activeType.update", "active"
     * })
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Active::class, mappedBy="activeType")
     */
    private $actives;

    /**
     * @ORM\ManyToMany(targetEntity=BasicAttributes::class, mappedBy="activeTypes")
     * @Groups({
     *     "activeType", "activeType.write",
     *     "activeType.update", "active"
     * })
     */
    private $basicAttributes;

    /**
     * @ORM\ManyToMany(targetEntity=CustomAttributes::class, mappedBy="activeType")
     * @Groups({
     *     "activeType", "activeType.write",
     *     "activeType.update", "active"
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
     * @return Collection|BasicAttributes[]
     */
    public function getBasicAttributes(): Collection
    {
        return $this->basicAttributes;
    }

    public function addBasicAttribute(BasicAttributes $basicAttribute): self
    {
        if (!$this->basicAttributes->contains($basicAttribute)) {
            $this->basicAttributes[] = $basicAttribute;
            $basicAttribute->addActiveType($this);
        }

        return $this;
    }

    public function removeBasicAttribute(BasicAttributes $basicAttribute): self
    {
        if ($this->basicAttributes->removeElement($basicAttribute)) {
            $basicAttribute->removeActiveType($this);
        }

        return $this;
    }

    /**
     * @return Collection|CustomAttributes[]
     */
    public function getCustomAttributes(): Collection
    {
        return $this->customAttributes;
    }

    public function addCustomAttribute(CustomAttributes $customAttribute): self
    {
        if (!$this->customAttributes->contains($customAttribute)) {
            $this->customAttributes[] = $customAttribute;
            $customAttribute->addActiveType($this);
        }

        return $this;
    }

    public function removeCustomAttribute(CustomAttributes $customAttribute): self
    {
        if ($this->customAttributes->removeElement($customAttribute)) {
            $customAttribute->removeActiveType($this);
        }

        return $this;
    }
}
