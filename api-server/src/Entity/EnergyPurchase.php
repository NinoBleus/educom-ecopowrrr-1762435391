<?php

namespace App\Entity;

use App\Repository\EnergyPurchaseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EnergyPurchaseRepository::class)]
class EnergyPurchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $customer_id = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $device_id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $reading_id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $price_period_id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $kwh = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount_eur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $purchase_date = null;

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

    public function getDeviceId(): ?Uuid
    {
        return $this->device_id;
    }

    public function setDeviceId(?Uuid $device_id): static
    {
        $this->device_id = $device_id;

        return $this;
    }

    public function getReadingId(): ?string
    {
        return $this->reading_id;
    }

    public function setReadingId(string $reading_id): static
    {
        $this->reading_id = $reading_id;

        return $this;
    }

    public function getPricePeriodId(): ?string
    {
        return $this->price_period_id;
    }

    public function setPricePeriodId(string $price_period_id): static
    {
        $this->price_period_id = $price_period_id;

        return $this;
    }

    public function getKwh(): ?string
    {
        return $this->kwh;
    }

    public function setKwh(string $kwh): static
    {
        $this->kwh = $kwh;

        return $this;
    }

    public function getAmountEur(): ?string
    {
        return $this->amount_eur;
    }

    public function setAmountEur(string $amount_eur): static
    {
        $this->amount_eur = $amount_eur;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeImmutable
    {
        return $this->purchase_date;
    }

    public function setPurchaseDate(\DateTimeImmutable $purchase_date): static
    {
        $this->purchase_date = $purchase_date;

        return $this;
    }
}
