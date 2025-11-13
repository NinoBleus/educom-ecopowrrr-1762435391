<?php

namespace App\Repository;

use App\Entity\DeviceReading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeviceReading>
 */
class DeviceReadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeviceReading::class);
    }

    public function saveDeviceReading(array $params): ?DeviceReading
    {
        $deviceReading = null;

        if (!empty($params['id'])) {
            $deviceReading = $this->find($params['id']);

            if ($deviceReading === null) {
                return null; // service layer can detect not found
            }
        }

        if ($deviceReading === null) {
            $deviceReading = new DeviceReading();
        }

        if (array_key_exists('readingTimestamp', $params)) {
            $deviceReading->setReadingTimestamp($params['readingTimestamp']);
        }

        if (array_key_exists('kwhGenerated', $params)) {
            $deviceReading->setKwhGenerated($params['kwhGenerated']);
        }

        //check in service if device_id is a valid option
        if (array_key_exists('device_id', $params)) {
            $deviceReading->setDeviceId($params['device_id']);
        }

        //check in service if price_period_id is a valid option
        if (array_key_exists('pricePeriodId', $params)) {
            $deviceReading->setPricePeriodId($params['pricePeriodId']);
        }
        
        

        $this->getEntityManager()->persist($deviceReading);
        $this->getEntityManager()->flush();

        return $deviceReading;
    }
    
    public function readDeviceReading($deviceReadingId) {
        return($this->find($deviceReadingId));       
    }

    public function deleteDeviceReading($id) {

    $deviceReading = $this->find($id);
    if($deviceReading) {
        $this->getEntityManager()->remove($deviceReading);
        $this->getEntityManager()->flush();
        return(true);
    }

    return(false);
    }
}
