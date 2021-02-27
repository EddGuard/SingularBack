<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RolesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ApiResource(
 *     attributes={
 *          "normalization_context"={"groups"={"role"}},
 *      },
 *      collectionOperations={
 *         "get"={"security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"},
 *         "post"={
 *              "denormalization_context"={"groups"={"role.write"}},
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"},
 *     },
 *     itemOperations={
 *         "get"={"security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"},
 *         "put"={
 *                "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')",
 *                "denormalization_context"={"groups"={"role.update"}}
 *          },
 *         "delete"={"security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"}
 *     }
 * )
 * @ORM\Entity(repositoryClass=RolesRepository::class)
 */
class Roles
{
    const ROLE_ADMIN = 'admin';
    const ROLE_USERS = 'users';
    const ROLE_TASKMASTER = 'taskmaster';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"role", "user"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

    /**
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "role", "role.write", "role.update",
     *     "user", "user.write", "user.update"
     * })
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="groups", cascade={"persist"})
     * @JoinTable(name="user_role")
     * @Groups({"role.write", "role.update"})
     */
    private $users;

    /**
     * @Assert\Type(type="integer")
     * @ORM\Column(type="integer", length=255, options={"default" = 0})
     * @Groups({
     *     "role", "role.write", "role.update",
     *     "user", "user.write", "user.update"
     * })
     */
    private $weight;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @ORM\PrePersist()
     */
    public function updateRole(string $name)
    {
        $name = str_replace(" ", "_", $name);
        $this->role = strtoupper('ROLE_'.trim($name));
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updateRole($name);

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $users): self
    {
        if (!$this->users->contains($users)) {
            $this->users[] = $users;
            $users->addGroup($this);
        }

        return $this;
    }

    public function removeUser(User $users): self
    {
        if ($this->users->contains($users)) {
            $this->users->removeElement($users);
        }

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }
}
