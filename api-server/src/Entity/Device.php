<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'UNIQ_DEVICE_SERIAL_CUSTOMER', columns: ['serial_number', 'customer_id'])
])]
class Device
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    #[Groups(['device:read', 'customer:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read', 'customer:read'])]
    private ?string $device_type = null;

    #[ORM\Column(length: 50)]
    #[Groups(['device:read'])]
    private ?string $serial_number = null;

    #[ORM\ManyToOne(inversedBy: 'devices')]
    #[ORM\JoinColumn(name: 'customer_id', nullable: false)]
    #[Groups(['device:read'])]
    private ?Customer $customer_id = null;

    /**
     * @var Collection<int, DeviceReading>
     */
    #[ORM\OneToMany(targetEntity: DeviceReading::class, mappedBy: 'device_id', orphanRemoval: true)]
    #[Ignore]
    private Collection $deviceReadings;

    public function __construct()
    {
        $this->deviceReadings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
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

    public function getCustomerId(): ?Customer
    {
        return $this->customer_id;
    }

    public function setCustomerId(?Customer $customer_id): static
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
