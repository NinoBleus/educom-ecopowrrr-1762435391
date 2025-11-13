<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?Uuid $id = null;

    #[ORM\Column(length: 50)]
    private ?string $device_type = null;

    #[ORM\Column(length: 50)]
    private ?string $serial_number = null;

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?customer $customer_id = null;

    /**
     * @var Collection<int, DeviceReading>
     */
    #[ORM\OneToMany(targetEntity: DeviceReading::class, mappedBy: 'device_id', orphanRemoval: true)]
    private Collection $deviceReadings;

    public function __construct()
    {
        $this->deviceReadings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDeviceType(): ?string
    {
        return $this->device_type;
    }

    public function setDeviceType(string $device_type): static
    {
        $this->device_type = $device_type;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serial_number;
    }

    public function setSerialNumber(string $serial_number): static
    {
        $this->serial_number = $serial_number;

        return $this;
    }

    public function getCustomerId(): ?customer
    {
        return $this->customer_id;
    }

    public function setCustomerId(?customer $customer_id): static
    {
        $this->customer_id = $customer_id;

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
            $deviceReading->setDeviceId($this);
        }

        return $this;
    }

    public function removeDeviceReading(DeviceReading $deviceReading): static
    {
        if ($this->deviceReadings->removeElement($deviceReading)) {
            // set the owning side to null (unless already changed)
            if ($deviceReading->getDeviceId() === $this) {
                $deviceReading->setDeviceId(null);
            }
        }

        return $this;
    }
}
