<?php

namespace App\Entity;

use App\Repository\DeviceReadingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DeviceReadingRepository::class)]
class DeviceReading
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $reading_timestamp = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $kwh_generated = null;

    #[ORM\ManyToOne(inversedBy: 'deviceReadings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?device $device_id = null;

    #[ORM\ManyToOne(inversedBy: 'deviceReadings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?buypriceperiod $price_period_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getReadingTimestamp(): ?\DateTimeImmutable
    {
        return $this->reading_timestamp;
    }

    public function setReadingTimestamp(\DateTimeImmutable $reading_timestamp): static
    {
        $this->reading_timestamp = $reading_timestamp;

        return $this;
    }

    public function getKwhGenerated(): ?string
    {
        return $this->kwh_generated;
    }

    public function setKwhGenerated(string $kwh_generated): static
    {
        $this->kwh_generated = $kwh_generated;

        return $this;
    }

    public function getDeviceId(): ?device
    {
        return $this->device_id;
    }

    public function setDeviceId(?device $device_id): static
    {
        $this->device_id = $device_id;

        return $this;
    }

    public function getPricePeriodId(): ?buypriceperiod
    {
        return $this->price_period_id;
    }

    public function setPricePeriodId(?buypriceperiod $price_period_id): static
    {
        $this->price_period_id = $price_period_id;

        return $this;
    }
}
