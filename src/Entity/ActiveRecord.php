<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ActiveRecordRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Filter\ActiveRecord\ArrayDateFilter;

/**
 * @ApiResource(attributes={
 *          "normalization_context"={"groups"={"activeRecord"}},
 *      },
 *      collectionOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_FULLY')"}
 *      })
 * @ORM\Entity(repositoryClass=ActiveRecordRepository::class)
 * @ApiFilter(ArrayDateFilter::class, properties={"dateRecord"})
 */
class ActiveRecord
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "active", "activeRecord"
     * })
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Active::class, inversedBy="activeRecord", cascade={"persist", "remove"})
     * @Groups({
     *     "activeRecord"
     * })
     */
    private $active;

    /**
     * @ORM\Column(type="array")
     * @Groups({
     *     "activeRecord"
     * })
     */
    private $dateRecord = [];

    /**
     * @ORM\Column(type="array")
     * @Groups({
     *     "activeRecord"
     * })
     */
    private $activeObject = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActive(): ?Active
    {
        return $this->active;
    }

    public function setActive(?Active $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDateRecord(): ?array
    {
        return $this->dateRecord;
    }

    public function setDateRecord(array $dateRecord): self
    {
        $this->dateRecord = $dateRecord;

        return $this;
    }

    public function getActiveObject(): ?array
    {
        return $this->activeObject;
    }

    public function setActiveObject(array $activeObject): self
    {
        $this->activeObject = $activeObject;

        return $this;
    }
}
