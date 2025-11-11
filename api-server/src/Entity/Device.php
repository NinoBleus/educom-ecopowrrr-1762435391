<?php

namespace App\Entity;

use App\Enum\orderStatus;
use App\Repository\DeviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $device_id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $customer_id = null;

    #[ORM\Column(length: 50)]
    private ?string $device_name = null;

    #[ORM\Column(length: 50)]
    private ?string $serial_nummer = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $device_type_id = null;

    #[ORM\Column(enumType: orderStatus::class)]
    private ?orderStatus $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $installed_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $decommissioned_at = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCustomerId(): ?string
    {
        return $this->customer_id;
    }

    public function setCustomerId(string $customer_id): static
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getDeviceName(): ?string
    {
        return $this->device_name;
    }

    public function setDeviceName(string $device_name): static
    {
        $this->device_name = $device_name;

        return $this;
    }

    public function getSerialNummer(): ?string
    {
        return $this->serial_nummer;
    }

    public function setSerialNummer(string $serial_nummer): static
    {
        $this->serial_nummer = $serial_nummer;

        return $this;
    }

    public function getDeviceTypeId(): ?string
    {
        return $this->device_type_id;
    }

    public function setDeviceTypeId(string $device_type_id): static
    {
        $this->device_type_id = $device_type_id;

        return $this;
    }

    public function getStatus(): ?orderStatus
    {
        return $this->status;
    }

    public function setStatus(orderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getInstalledAt(): ?\DateTimeImmutable
    {
        return $this->installed_at;
    }

    public function setInstalledAt(\DateTimeImmutable $installed_at): static
    {
        $this->installed_at = $installed_at;

        return $this;
    }

    public function getDecommissionedAt(): ?\DateTimeImmutable
    {
        return $this->decommissioned_at;
    }

    public function setDecommissionedAt(\DateTimeImmutable $decommissioned_at): static
    {
        $this->decommissioned_at = $decommissioned_at;

        return $this;
    }
}
