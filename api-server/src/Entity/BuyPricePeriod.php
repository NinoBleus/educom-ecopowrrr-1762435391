<?php

namespace App\Entity;

use App\Repository\BuyPricePeriodRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BuyPricePeriodRepository::class)]
class BuyPricePeriod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $valid_from = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $valid_to = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $buy_price_per_kwh = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

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

    public function setValidTo(?\DateTime $valid_to): static
    {
        $this->valid_to = $valid_to;

        return $this;
    }

    public function getBuyPricePerKwh(): ?string
    {
        return $this->buy_price_per_kwh;
    }

    public function setBuyPricePerKwh(string $buy_price_per_kwh): static
    {
        $this->buy_price_per_kwh = $buy_price_per_kwh;

        return $this;
    }
}
