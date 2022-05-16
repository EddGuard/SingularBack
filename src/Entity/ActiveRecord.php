<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ActiveRecordRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Filter\ActiveRecord\ArrayDateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

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
 * @ApiFilter(SearchFilter::class, properties={"active.id"})
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
     * @ORM\Column(type="json")
     * @Assert\Type(type="array")
     * @Groups({
     *     "activeRecord"
     * })
     */
    private $activeObject = [];

    /**
     * @var User $createdBy
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * @Groups({ "active", "activeType", "activeRecord"})
     */
    protected $createdBy;

    /**
     * @Gedmo\Blameable(on="update")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     * @Groups({ "active", "activeType", "activeRecord"})
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $updatedBy;

    /**
     * @Gedmo\Timestampable(on="update")
     * @Groups({ "active", "activeType", "activeRecord"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @Gedmo\Timestampable(on="create")
     * @Groups({ "active", "activeType", "activeRecord"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

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

    /**
     * Returns createdBy.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
