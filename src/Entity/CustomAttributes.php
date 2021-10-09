<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CustomAttributesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *  @ApiResource(attributes={
 *          "normalization_context"={"groups"={"customAttribute"}},
 *      },
 *      collectionOperations={
 *          "post"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "validation_groups"={"Default", "Create"},
 *              "denormalization_context"={"groups"={"customAttribute.write"}}
 *          },
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *          "put"={
 *              "denormalization_context"={"groups"={"customAttribute.update"}},
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "delete"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      })
 * @ORM\Entity(repositoryClass=CustomAttributesRepository::class)
 */
class CustomAttributes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "customAttribute", "activeType", "active", "unit", "activeType.write", "activeType.update"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "customAttribute", "activeType", "active", "unit",
     *     "customAttribute.write", "customAttribute.update", "activeType.write", "activeType.update"
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "customAttribute", "activeType", "active", "unit",
     *     "customAttribute.write", "customAttribute.update", "activeType.write", "activeType.update"
     * })
     */
    private $value;

    /**
     * @ORM\ManyToMany(targetEntity=ActiveType::class, inversedBy="customAttributes")
     */
    private $activeType;

    /**
     * @ORM\ManyToOne(targetEntity=Unit::class, inversedBy="customAttributes")
     * @Groups({
     *     "customAttribute", "activeType", "active", "activeType.write", "activeType.update"
     * })
     */
    private $unit;

    public function __construct()
    {
        $this->activeType = new ArrayCollection();
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return Collection|activeType[]
     */
    public function getActiveType(): Collection
    {
        return $this->activeType;
    }

    public function addActiveType(activeType $activeType): self
    {
        if (!$this->activeType->contains($activeType)) {
            $this->activeType[] = $activeType;
        }

        return $this;
    }

    public function removeActiveType(activeType $activeType): self
    {
        $this->activeType->removeElement($activeType);

        return $this;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): self
    {
        $this->unit = $unit;

        return $this;
    }
}
