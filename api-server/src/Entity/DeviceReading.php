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
    private ?Device $device = null;

    #[ORM\ManyToOne(inversedBy: 'deviceReadings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BuyPricePeriod $price_period = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDeviceId(): ?Device
    {
        return $this->device;
    }

    public function setDeviceId(?Device $device): static
    {
        $this->device = $device;

        return $this;
    }

    public function getPricePeriodId(): ?BuyPricePeriod
    {
        return $this->price_period;
    }

    public function setPricePeriodId(?BuyPricePeriod $price_period): static
    {
        $this->price_period = $price_period;

        return $this;
    }
}
