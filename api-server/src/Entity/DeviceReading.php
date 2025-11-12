<?php

namespace App\Entity;

use App\Enum\orderStatus;
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

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $batch_id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $device_id = null;

    #[ORM\Column]
    private ?\DateTime $reading_timestamp = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $device_total_yield_kwh = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $device_month_yield_kwh = null;

    #[ORM\Column(enumType: orderStatus::class)]
    private ?orderStatus $device_status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getBatchId(): ?string
    {
        return $this->batch_id;
    }

    public function setBatchId(string $batch_id): static
    {
        $this->batch_id = $batch_id;

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

    public function getReadingTimestamp(): ?\DateTime
    {
        return $this->reading_timestamp;
    }

    public function setReadingTimestamp(\DateTime $reading_timestamp): static
    {
        $this->reading_timestamp = $reading_timestamp;

        return $this;
    }

    public function getDeviceTotalYieldKwh(): ?string
    {
        return $this->device_total_yield_kwh;
    }

    public function setDeviceTotalYieldKwh(string $device_total_yield_kwh): static
    {
        $this->device_total_yield_kwh = $device_total_yield_kwh;

        return $this;
    }

    public function getDeviceMonthYieldKwh(): ?string
    {
        return $this->device_month_yield_kwh;
    }

    public function setDeviceMonthYieldKwh(string $device_month_yield_kwh): static
    {
        $this->device_month_yield_kwh = $device_month_yield_kwh;

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
}
