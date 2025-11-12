<?php

namespace App\Entity;

use App\Enum\orderStatus;
use App\Enum\processingStatus;
use App\Repository\DeviceMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DeviceMessageRepository::class)]
class DeviceMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $message_id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $device_id = null;

    #[ORM\Column(enumType: orderStatus::class)]
    private ?orderStatus $device_status = null;

    #[ORM\Column]
    private ?\DateTime $message_datetime = null;

    #[ORM\Column]
    private array $raw_json = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $processed_at = null;

    #[ORM\Column(enumType: processingStatus::class)]
    private ?processingStatus $processing_status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessageId(): ?string
    {
        return $this->message_id;
    }

    public function setMessageId(string $message_id): static
    {
        $this->message_id = $message_id;

        return $this;
    }

    public function getDeviceId(): ?Uuid
    {
        return $this->device_id;
    }

    public function setDeviceId(Uuid $device_id): static
    {
        $this->device_id = $device_id;

        return $this;
    }

    public function getDeviceStatus(): ?orderStatus
    {
        return $this->device_status;
    }

    public function setDeviceStatus(orderStatus $device_status): static
    {
        $this->device_status = $device_status;

        return $this;
    }

    public function getMessageDatetime(): ?\DateTime
    {
        return $this->message_datetime;
    }

    public function setMessageDatetime(\DateTime $message_datetime): static
    {
        $this->message_datetime = $message_datetime;

        return $this;
    }

    public function getRawJson(): array
    {
        return $this->raw_json;
    }

    public function setRawJson(array $raw_json): static
    {
        $this->raw_json = $raw_json;

        return $this;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processed_at;
    }

    public function setProcessedAt(\DateTimeImmutable $processed_at): static
    {
        $this->processed_at = $processed_at;

        return $this;
    }

    public function getProcessingStatus(): ?processingStatus
    {
        return $this->processing_status;
    }

    public function setProcessingStatus(processingStatus $processing_status): static
    {
        $this->processing_status = $processing_status;

        return $this;
    }
}
