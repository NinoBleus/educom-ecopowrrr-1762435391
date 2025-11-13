<?php

namespace App\Entity;

use App\Repository\BuyPricePeriodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?string $price_per_kwh = null;

    /**
     * @var Collection<int, DeviceReading>
     */
    #[ORM\OneToMany(targetEntity: DeviceReading::class, mappedBy: 'price_period_id', orphanRemoval: true)]
    private Collection $deviceReadings;

    public function __construct()
    {
        $this->deviceReadings = new ArrayCollection();
    }

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

    public function getPricePerKwh(): ?string
    {
        return $this->price_per_kwh;
    }

    public function setPricePerKwh(string $price_per_kwh): static
    {
        $this->price_per_kwh = $price_per_kwh;

        return $this;
    }

    /**
     * @return Collection<int, DeviceReading>
     */
    public function getDeviceReadings(): Collection
    {
        return $this->deviceReadings;
    }

    public function addDeviceReading(DeviceReading $deviceReading): static
    {
        if (!$this->deviceReadings->contains($deviceReading)) {
            $this->deviceReadings->add($deviceReading);
            $deviceReading->setPricePeriodId($this);
        }

        return $this;
    }

    public function removeDeviceReading(DeviceReading $deviceReading): static
    {
        if ($this->deviceReadings->removeElement($deviceReading)) {
            // set the owning side to null (unless already changed)
            if ($deviceReading->getPricePeriodId() === $this) {
                $deviceReading->setPricePeriodId(null);
            }
        }

        return $this;
    }
}
