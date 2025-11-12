<?php

namespace App\Entity;

use App\Repository\CustomerAddressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerAddressRepository::class)]
class CustomerAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $customer_id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $address_id = null;

    #[ORM\Column]
    private ?bool $is_primary = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $valid_from = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $valid_to = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

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

    public function getAddressId(): ?string
    {
        return $this->address_id;
    }

    public function setAddressId(string $address_id): static
    {
        $this->address_id = $address_id;

        return $this;
    }

    public function isPrimary(): ?bool
    {
        return $this->is_primary;
    }

    public function setIsPrimary(bool $is_primary): static
    {
        $this->is_primary = $is_primary;

        return $this;
    }

    public function getValidFrom(): ?\DateTime
    {
        return $this->valid_from;
    }

    public function setValidFrom(\DateTime $valid_from): static
    {
        $this->valid_from = $valid_from;

        return $this;
    }

    public function getValidTo(): ?\DateTime
    {
        return $this->valid_to;
    }

    public function setValidTo(\DateTime $valid_to): static
    {
        $this->valid_to = $valid_to;

        return $this;
    }
}
