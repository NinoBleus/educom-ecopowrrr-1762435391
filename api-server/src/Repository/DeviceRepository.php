<?php

namespace App\Repository;

use App\Entity\Device;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

        public function saveDevice(array $params): ?Device
    {
        $device = null;

        if (!empty($params['id'])) {
            $device = $this->find($params['id']);

            if ($device === null) {
                return null;
            }
        }

        if ($device === null) {
            $device = new Device();
        }

        if (array_key_exists('customer_id', $params)) {
            $device->setCustomerId($params['customer_id']);
        }

        if (array_key_exists('deviceType', $params)) {
            $device->setDeviceType($params['deviceType']);
        }

        if (array_key_exists('serialNumber', $params)) {
            $device->setSerialNumber($params['serialNumber']);
        }

        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();

        return $device;
    }
    
    public function fetchDeviceById($deviceId) {
        return($this->find($deviceId));       
    }

    public function fetchDevicesFromCustomer(Customer $customer) {
        return($this->findBy(['customer_id' => $customer]));
    }

    public function deleteDeviceReading($id) {

    $device = $this->find($id);
    if($device) {
        $this->getEntityManager()->remove($device);
        $this->getEntityManager()->flush();
        return(true);
    }

    return(false);
    }
}
