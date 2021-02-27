<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *  @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"user"}},
 *      },
 *      collectionOperations={
 *          "post"={
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')",
 *              "validation_groups"={"Default", "Create"},
 *              "denormalization_context"={"groups"={"user.write"}}
 *          },
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"},
 *      },
 *      itemOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"},
 *          "put"={
 *              "denormalization_context"={"groups"={"user.update"}},
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          },
 *          "delete"={"security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"},
 *          "request_password"={
 *              "method"="POST",
 *              "path"="user/request_password",
 *              "controller"=RequestUserPasswordAction::class,
 *              "defaults"={"_api_receive"=false},
 *              "denormalization_context"={"groups"={"request-password"}}
 *          },
 *          "reset_password"={
 *              "method"="PUT",
 *              "path"="user/reset_password/{token}",
 *              "controller"=ResetUserPasswordAction::class,
 *              "defaults"={"_api_receive"=false},
 *              "denormalization_context"={"groups"={"reset-password"}}
 *          },
 *      }
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email", "username", "deletedAt"}, ignoreNull=false)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface, EncoderAwareInterface
{
    const CURRENT_PASSWORD_ALGORITHM = 'argon2i';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *     "user"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({
     *     "user", "user.write", "user.update"
     * })
     */
    private $username;

    /**
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "user", "user.write", "user.update"
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "user", "user.write", "user.update"
     * })
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "user", "user.write", "user.update"
     * })
     */
    private $email;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Roles", mappedBy="users", cascade={"persist"})
     * @ApiSubresource(maxDepth=1)
     * @Groups({
     *     "user.write", "user.update"
     * })
     */
    private $groups;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     * @Groups({"user"})
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"user"})
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"user"})
     */
    protected $deletedAt;

    /**
     * The salt to use for hashing
     *
     * @var string
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $salt;

    /**
     * @ORM\Column(name="encoder", type="string", options={"default" = "argon2i"})
     */
    private $encoder = User::CURRENT_PASSWORD_ALGORITHM;

    /**
     * @Gedmo\Versioned()
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Type(type="string")
     * @Assert\Length(min=8, max=32)
     * @Groups({
     *     "user.write", "user.update", "reset-password"
     * })
     */
    private $plainPassword;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this User.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt(?string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the User, clear it here
        // $this->plainPassword = null;
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

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /*public function getRoles(): ?Roles
    {
        return $this->roles;
    }*/

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        $roles = $this->groups->map(function (Roles $role) {
            return $role->getRole();
        })->toArray();

        if (count($roles)) {
            return $roles;
        }

        return [];
    }

    /**
     * Returns createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Returns updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets deletedAt.
     *
     * @param \DateTime|null $deletedAt
     *
     * @return \App\Entity\User
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Returns deletedAt.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Is deleted?
     *
     * @return bool
     */
    public function isDeleted()
    {
        return null !== $this->deletedAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Roles[]
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function removeGroup(Roles $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
            $group->removeUser($this);
        }

        return $this;
    }

    public function addGroup(Roles $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
            $group->addUser($this);
        }
        return $this;
    }

    public function getEncoderName()
    {
        return $this->encoder;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }
}
