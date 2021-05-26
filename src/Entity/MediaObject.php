<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Controller\MediaObject\CreateMediaObjectAction;
use App\Controller\MediaObject\GetMediaObjectAction;
use App\Controller\MediaObject\GetMediaObjectThumbnailBigAction;
use App\Controller\MediaObject\GetMediaObjectThumbnailSmallAction;
use App\Filter\MediaObjectChatFilter;

/**
 * @ApiResource(iri="http://schema.org/MediaObject",
 *     attributes={
 *          "normalization_context"={"groups"={"media-object"}}
 *     },
 *     collectionOperations={
 *          "get"={"security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"},
 *          "post"={
 *              "controller"=CreateMediaObjectAction::class,
 *              "defaults"={"_api_receive"=false},
 *              "denormalization_context"={
 *                  "groups"={"post"}
 *              }
 *          }
 *     },
 *     itemOperations={
 *          "get"={"controller"=GetMediaObjectAction::class},
 *          "delete"={"security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"},
 *          "special_thumbnail_big"={
 *              "method"="GET",
 *              "path"="/media_objects/{id}/thumbnail_big",
 *              "controller"=GetMediaObjectThumbnailBigAction::class
 *          },
 *          "special_thumbnail_small"={
 *              "method"="GET",
 *              "path"="/media_objects/{id}/thumbnail_small",
 *              "controller"=GetMediaObjectThumbnailSmallAction::class
 *          }
 *     }
 * )
 * @ApiFilter(MediaObjectChatFilter::class, properties={"chat"})
 * @ORM\Table(schema="App", name="mediaObject")
 * @ORM\Entity(repositoryClass="App\Repository\MediaObjectRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 * @Gedmo\Loggable(logEntryClass="App\Entity\Log\LogEntry")
 */
class MediaObject
{
    const PATH = '/api/media_objects';
    const PATH_SIMPLE = 'api/media_objects';
    const TYPE_USER = 'USER';

    const max_size = "20M";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "media-object", "user",
     *     "request", "workOrder", "task", "location", "resource", "element",
     *     "message", "message.write", "record.read"
     * })
     */
    private $id;

    /**
     * @Gedmo\Versioned()
     * @ORM\Column(type="string", length=255)
     * @Groups({"media-object", "task", "vendor", "location", "message", "resource", "element", "request", "workOrder", "record.read"})
     */
    private $path;

    /**
     * @Gedmo\Versioned()
     * @var string|null
     * @ORM\Column(type="string", length=255, options={"default" = "''"})
     * @Groups({"media-object", "task", "vendor", "location", "message", "resource", "element", "request", "workOrder", "record.read"})
     */
    private $pathThumbnailSmall;

    /**
     * @Gedmo\Versioned()
     * @var string|null
     * @ORM\Column(type="string", length=255, options={"default" = "''"})
     * @Groups({"media-object", "task", "vendor", "location", "message", "resource", "element", "request", "workOrder", "record.read"})
     */
    private $pathThumbnailBig;

    /**
     * @Gedmo\Versioned()
     * @ORM\Column(type="string", length=255)
     * @Groups({"media-object", "task", "vendor", "location", "message", "resource", "element", "request", "workOrder", "record.read"})
     */
    private $mimetype;

    /**
     * @Gedmo\Versioned()
     * @var string|null
     * @ORM\Column(type="string", length=255)
     * @Groups({"media-object", "message"})
     */
    private $name;

    /**
     * @var File|null
     * @Assert\NotNull()
     * @Assert\File(
     *     maxSize=MediaObject::max_size,
     *     mimeTypes = {
     *      "application/pdf", "application/x-pdf",
     *      "image/jpeg", "image/jpg", "image/png", "application/octet-stream",
     *      "video/mp4", "video/m4v", "video/x-m4v", "video/JPEG", "video/quicktime", "video/x-msvideo",
     *      "audio/mp4", "audio/mpeg", "text/plain",
     *      "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *      "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *      "application/vnd.ms-powerpoint", "application/vnd.openxmlformats-officedocument.presentationml.presentation"
     *     },
     *     mimeTypesMessage = "mime.type.valid",
     *     maxSizeMessage = "mime.max.size",
     *     uploadIniSizeErrorMessage = "mime.iniSizeUpload"
     * )
     * @Groups({"post"})
     */
    public $file;

    /**
     * @Gedmo\Versioned()
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Groups({"media-object", "post"})
     * @Assert\Choice({
     *     MediaObject::TYPE_USER,
     *     MediaObject::TYPE_TICKET,
     *     MediaObject::TYPE_TASK,
     *     MediaObject::TYPE_VENDOR,
     *     MediaObject::TYPE_MAINTENANCE_ELEMENT,
     *     MediaObject::TYPE_RESOURCE,
     *     MediaObject::TYPE_LOCATION,
     *     MediaObject::TYPE_MESSAGE
     * })
     */
    private $type;

    /**
     * @Groups({"post"})
     */
    private $entityId;

    /**
     * @Gedmo\Versioned()
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"media-object", "message"})
     */
    private $size;

    /**
     * @Gedmo\Versioned()
     * @var User The user this file is.
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="mediaObjects")
     */
    public $user;


    /**
     * @Gedmo\Versioned()
     * @ORM\ManyToOne(targetEntity="App\Entity\MaintenanceElement", inversedBy="mediaObjects")
     */
    private $maintenanceElement;


    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     * @Groups({"media-object"})
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"media-object"})
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *     "media-object", "user"
     * })
     */
    protected $deletedAt;

    /**
     * @var User $createdBy
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    protected $createdBy;

    /**
     * @var User $updatedBy
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    protected $updatedBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function setMimetype(string $mimetype): self
    {
        $this->mimetype = $mimetype;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPathThumbnailSmall(): ?string
    {
        return $this->pathThumbnailSmall;
    }

    public function setPathThumbnailSmall(string $pathThumbnailSmall): self
    {
        $this->pathThumbnailSmall = $pathThumbnailSmall;

        return $this;
    }

    public function getPathThumbnailBig(): ?string
    {
        return $this->pathThumbnailBig;
    }

    public function setPathThumbnailBig(string $pathThumbnailBig): self
    {
        $this->pathThumbnailBig = $pathThumbnailBig;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
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
     * Returns createdBy.
     *
     * @return \App\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Returns updatedBy.
     *
     * @return \App\Entity\User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Sets deletedAt.
     *
     * @param \DateTime|null $deletedAt
     *
     * @return $this
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

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}