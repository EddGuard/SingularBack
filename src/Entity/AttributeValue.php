<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AttributeValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(attributes={
 *          "normalization_context"={"groups"={"attributeValue"}},
 *      },
 *      collectionOperations={
 *          "post"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "validation_groups"={"Default", "Create"},
 *              "denormalization_context"={"groups"={"attributeValue.write"}}
 *          },
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *          "put"={
 *              "denormalization_context"={"groups"={"attributeValue.update"}},
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "delete"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      })
 * @ORM\Entity(repositoryClass=AttributeValueRepository::class)
 */
class AttributeValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "attributeValue", "activeType", "active", "unit", "active.write", "active.update", "activeType.write", "activeType.update"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "attributeValue", "activeType", "active", "unit", "active.write", "active.update", "activeType.write", "activeType.update"
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "attributeValue", "activeType", "active", "unit", "active.write", "active.update", "activeType.write", "activeType.update"
     * })
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=Active::class, inversedBy="basicAttributes", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $activeBasics;

    /**
     * @ORM\ManyToOne(targetEntity=Active::class, inversedBy="customAttributes", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $activeCustoms;

    /**
     * @ORM\ManyToOne(targetEntity=ActiveType::class, inversedBy="basicAttributes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $activeTypeBasics;

    /**
     * @ORM\ManyToOne(targetEntity=ActiveType::class, inversedBy="customAttributes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $activeTypeCustoms;

    /**
     * @ORM\ManyToOne(targetEntity=Unit::class, inversedBy="attributeValues", cascade={"persist"})
     * @Groups({
     *     "attributeValue", "activeType", "active", "attributeValue.write", "attributeValue.update",
     *     "activeType.write", "active.write", "activeType.update", "active.update"
     * })
     */
    private $unit;

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

    public function getActiveBasics(): ?active
    {
        return $this->activeBasics;
    }

    public function setActiveBasics(?active $active): self
    {
        $this->activeBasics = $active;

        return $this;
    }

    public function getActiveCustoms(): ?active
    {
        return $this->activeCustoms;
    }

    public function setActiveCustoms(?active $active): self
    {
        $this->activeCustoms = $active;

        return $this;
    }

    public function getActiveTypeBasics(): ?activeType
    {
        return $this->activeTypeBasics;
    }

    public function setActiveTypeBasics(?activeType $activeType): self
    {
        $this->activeTypeBasics = $activeType;

        return $this;
    }

    public function getActiveTypeCustoms(): ?activeType
    {
        return $this->activeTypeCustoms;
    }

    public function setActiveTypeCustoms(?activeType $activeType): self
    {
        $this->activeTypeCustoms = $activeType;

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
