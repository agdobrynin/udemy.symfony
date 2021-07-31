<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BlogPostRepository::class)
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
 *          "post"={"access_control"="is_granted('IS_AUTHENTICATED_FULLY')"}
 *      }
 * )
 * @UniqueEntity(
 *     fields={"slug", "author"},
 *     errorPath="slug",
 *     message="Для этого автора имя файла {{ value }} (slug) уже используется. Придуймайте другое имя файла."
 * )
 */
class BlogPost
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
     * @Groups({"put:write", "put:read"})
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
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual("today")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     */
    private $updateAt;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=140)
     * @Groups({"put:write", "put:read"})
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
     * @Groups("put:read")
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post")
     */
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->updateAt = new \DateTime('now');
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

    /**
     * @return \DateTime
     */
    public function getUpdateAt(): \DateTime
    {
        return $this->updateAt;
    }

    /**
     * @param \DateTime $updateAt
     */
    public function setUpdateAt(\DateTime $updateAt): void
    {
        $this->updateAt = $updateAt;
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

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getComment(): Collection
    {
        return $this->comments;
    }
}
