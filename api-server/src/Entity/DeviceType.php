<?php

namespace App\Entity;

use App\Repository\DeviceTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceTypeRepository::class)]
class DeviceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $device_type_id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $desccription = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDesccription(): ?string
    {
        return $this->desccription;
    }

    public function setDesccription(string $desccription): static
    {
        $this->desccription = $desccription;

        return $this;
    }
}
