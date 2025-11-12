<?php

namespace App\Entity;

use App\Repository\ReadingBatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReadingBatchRepository::class)]
class ReadingBatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $collected_at = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $total_usage_kwh = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sourche_message_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCollectedAt(): ?\DateTimeImmutable
    {
        return $this->collected_at;
    }

    public function setCollectedAt(\DateTimeImmutable $collected_at): static
    {
        $this->collected_at = $collected_at;

        return $this;
    }

    public function getTotalUsageKwh(): ?string
    {
        return $this->total_usage_kwh;
    }

    public function setTotalUsageKwh(string $total_usage_kwh): static
    {
        $this->total_usage_kwh = $total_usage_kwh;

        return $this;
    }

    public function getSourcheMessageId(): ?string
    {
        return $this->sourche_message_id;
    }

    public function setSourcheMessageId(?string $sourche_message_id): static
    {
        $this->sourche_message_id = $sourche_message_id;

        return $this;
    }
}
