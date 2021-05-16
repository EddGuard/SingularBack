<?php

namespace App\Entity;

use App\Repository\ActiveRecordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActiveRecordRepository::class)
 */
class ActiveRecord
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Active::class, inversedBy="activeRecord", cascade={"persist", "remove"})
     */
    private $active;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $dateRecord = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activeObject;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getactive(): ?Active
    {
        return $this->active;
    }

    public function setactive(?Active $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDateRecord(): ?array
    {
        return $this->dateRecord;
    }

    public function setDateRecord(?array $dateRecord): self
    {
        $this->dateRecord = $dateRecord;

        return $this;
    }

    public function getActiveObject(): ?string
    {
        return $this->activeObject;
    }

    public function setActiveObject(?string $activeObject): self
    {
        $this->activeObject = $activeObject;

        return $this;
    }
}
