<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BasicAttributesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(attributes={
 *          "normalization_context"={"groups"={"basicAttribute"}},
 *      },
 *      collectionOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"}
 *      })
 * @ORM\Entity(repositoryClass=BasicAttributesRepository::class)
 */
class BasicAttributes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "basicAttribute", "activeType", "active", "unit"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * * @Groups({
     *     "basicAttribute", "activeType", "active", "unit"
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * * @Groups({
     *     "basicAttribute", "activeType", "active", "unit"
     * })
     */
    private $value;

    /**
     * @ORM\ManyToMany(targetEntity=ActiveType::class, inversedBy="basicAttributes")
     */
    private $activeTypes;

    /**
     * @ORM\ManyToOne(targetEntity=Unit::class, inversedBy="basicAttributes")
     * @Groups({
     *     "basicAttribute", "activeType", "active"
     * })
     */
    private $unit;

    public function __construct()
    {
        $this->activeTypes = new ArrayCollection();
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
    public function getActiveTypes(): Collection
    {
        return $this->activeTypes;
    }

    public function addActiveType(activeType $activeType): self
    {
        if (!$this->activeTypes->contains($activeType)) {
            $this->activeTypes[] = $activeType;
        }

        return $this;
    }

    public function removeActiveType(activeType $activeType): self
    {
        $this->activeTypes->removeElement($activeType);

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
