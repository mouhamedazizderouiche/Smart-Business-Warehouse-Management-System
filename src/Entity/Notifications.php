<?php
namespace App\Entity;

use App\Repository\NotificationsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: NotificationsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Notifications
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid")]
    private ?Uuid $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: "boolean")]
    private bool $isRead = false;

    #[ORM\Column(type: "boolean")]
    private bool $isForAdmins = false;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
    #[ORM\PrePersist] 
    public function generateUuid(): void
    {
        if ($this->id === null) {
            $this->id = Uuid::v4();
        }
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function isForAdmins(): bool { return $this->isForAdmins; }
    public function setIsForAdmins(bool $isForAdmins): self { $this->isForAdmins = $isForAdmins; return $this; }
}
