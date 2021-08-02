<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BlogPostRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(
 *     itemOperations={
 *          "get",
 *          "put"={
 *              "denormalization_context"={"groups"={"put:write"}},
 *              "normalization_context"={"groups"={"put:read"}},
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object.getAuthor() === user"
 *          }
 *     },
 *     collectionOperations={
 *          "get",
 *          "post"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "denormalization_context"={"groups"={"post:write"}},
 *          }
 *      }
 * )
 * @UniqueEntity(
 *     fields={"slug", "author"},
 *     errorPath="slug",
 *     message="Для этого автора имя файла {{ value }} (slug) уже используется. Придуймайте другое имя файла."
 * )
 */
class BlogPost implements AuthorEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=10)
     * @Groups({"put:write", "put:read", "post:write"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="post")
     * @ORM\JoinColumn(nullable=false)
     * @Groups ("put:read")
     */
    private $author;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"put:read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"put:read"})
     */
    private $updateAt;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=140)
     * @Groups({"put:write", "put:read", "post:write"})
     */
    private $content;

    /**
     * @ORM\Column(type="string", length="255", nullable="true")
     * @Assert\NotBlank
     * @Assert\Length(min=5)
     * @Assert\Regex(
     *     pattern="/^([a-z_\-0-9]+)$/",
     *     message="Имя публикации может содержать латинские буквы в нижнем регистре (маленькие буквы), цифры, тире"
     * )
     * @Groups({"put:read", "post:write"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post")
     * @ApiSubresource
     */
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdateAt(): \DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): self
    {
        $this->updateAt = $updateAt;

        return $this;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(UserInterface $user): AuthorEntityInterface
    {
        $this->author = $user;

        return $this;
    }

    public function getComment(): Collection
    {
        return $this->comments;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setDatesAutomatically()
    {
        $this->setUpdateAt(new \DateTime());

        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime());
        }
    }
}
