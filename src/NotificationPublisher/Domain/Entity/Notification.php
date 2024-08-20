<?php

namespace App\NotificationPublisher\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: "notifications")]
class Notification
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private ?Uuid $id;

    #[ORM\Column(type: "string")]
    private string $recipient;

    #[ORM\Column(type: "string")]
    private string $content;

    #[ORM\Column(type: "string")]
    private string $channel;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTime $sentAt;

    public function __construct(
        Uuid $id,
        string $recipient,
        string $content,
        string $channel,
        string $status,
        ?\DateTime $sentAt = null
    ) {
        $this->id = $id;
        $this->recipient = $recipient;
        $this->content = $content;
        $this->channel = $channel;
        $this->status = $status;
        $this->sentAt = $sentAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function markAsSent(): void
    {
        $this->status = 'sent';
        $this->sentAt = new \DateTime();
    }

    public function markAsFailed(): void
    {
        $this->status = 'failed';
    }
}
