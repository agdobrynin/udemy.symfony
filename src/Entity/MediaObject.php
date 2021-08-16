<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreateMediaObjectAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @ApiResource(
 *      iri="http://schema.org/MediaObject",
 *      normalizationContext={
 *          "groups"={"media_object_read"}
 *      },
 *      collectionOperations={
 *         "post"={
 *             "controller"=CreateMediaObjectAction::class,
 *             "deserialize"=false,
 *             "security"="is_granted('ROLE_USER')",
 *             "validation_groups"={"Default", "media_object_create"},
 *             "openapi_context"={
 *                 "requestBody"={
 *                     "content"={
 *                         "multipart/form-data"={
 *                             "schema"={
 *                                 "type"="object",
 *                                 "properties"={
 *                                     "file"={
 *                                         "type"="string",
 *                                         "format"="binary"
 *                                     }
 *                                 }
 *                             }
 *                         }
 *                     }
 *                 }
 *             }
 *         },
 *         "get"
 *     },
 *     itemOperations={
 *         "get"
 *     }
 * )
 * @Vich\Uploadable
 */
class MediaObject
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"media_object_read", "get:read_post:with_author", "read_post:full"})
     */
    public $contentUrl;

    /**
     * @var File|null
     *
     * @Assert\NotNull(groups={"media_object_create"})
     * @Vich\UploadableField(mapping="media_object", fileNameProperty="fileName")
     */
    private $file;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true, options={"default": null})
     * @Groups({"get:read_post:with_author", "read_post:full", "put:write", "put:read", "post:write"})
     */
    public $fileName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", options={"default": ""})
     * @Groups({"get-blog-post-with-author"})
     */
    public $mimeType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFile($file): self
    {
        $this->file = $file;

        if ($this->file instanceof File) {
            $this->mimeType = $this->file->getMimeType() ?? '';
        }

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function __toString(): ?string
    {
        return $this->id . ':' . $this->fileName;
    }
}
