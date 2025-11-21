<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\DeviceReading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function saveCustomer(array $params): ?Customer
    {
        $customer = null;

        if (!empty($params['id'])) {
            $customer = $this->find($params['id']);

            if ($customer === null) {
                return null; // service layer can detect not found
            }
        }

        if ($customer === null) {
            $customer = new Customer();
        }

        if (array_key_exists('firstName', $params)) {
            $customer->setFirstName($params['firstName']);
        }

        if (array_key_exists('lastName', $params)) {
            $customer->setLastName($params['lastName']);
        }

        if (array_key_exists('iban', $params)) {
            $customer->setIban($params['iban'] ?: null);
        }

        if (array_key_exists('postcode', $params)) {
            $customer->setPostcode($params['postcode'] ?: null);
        }

        if (array_key_exists('houseNumber', $params)) {
            $customer->setHouseNumber($params['houseNumber'] ?: null);
        }

        if (array_key_exists('street', $params)) {
            $customer->setStreet($params['street'] ?: null);
        }

        if (array_key_exists('city', $params)) {
            $customer->setCity($params['city'] ?: null);
        }

        if (array_key_exists('municipality', $params)) {
            $customer->setMunicipality($params['municipality'] ?: null);
        }

        if (array_key_exists('province', $params)) {
            $customer->setProvince($params['province'] ?: null);
        }

        if (array_key_exists('latitude', $params)) {
            $customer->setLatitude($params['latitude'] ?: null);
        }

        if (array_key_exists('longitude', $params)) {
            $customer->setLongitude($params['longitude'] ?: null);
        }

        $this->getEntityManager()->persist($customer);
        $this->getEntityManager()->flush();

        return $customer;
    }
    
    public function fetchCustomer($customerId) {
        return($this->find($customerId));       
    }

    public function deleteCustomer($id) 
    {

    $customer = $this->find($id);
    if($customer) {
        $this->getEntityManager()->remove($customer);
        $this->getEntityManager()->flush();
        return(true);
    }

    return(false);
    }

    /**
     * @return DeviceReading[]
     */
    public function fetchCustomerAnualDeviceReadingData(?int $customerId = null, int $year): array
    {
        $startOfYear = new \DateTimeImmutable(sprintf('%04d-01-01 00:00:00', $year));
        $startOfNextYear = $startOfYear->modify('+1 year');

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('deviceReading')
            ->from(DeviceReading::class, 'deviceReading')
            ->innerJoin('deviceReading.device', 'device')
            ->innerJoin('device.customer_id', 'customer');

        if ($customerId !== null) {
            $queryBuilder
                ->andWhere('customer.id = :customerId')
                ->setParameter('customerId', $customerId);
        }

        return $queryBuilder
            ->andWhere('deviceReading.reading_timestamp >= :startOfYear')
            ->andWhere('deviceReading.reading_timestamp < :startOfNextYear')
            ->setParameter('startOfYear', $startOfYear)
            ->setParameter('startOfNextYear', $startOfNextYear)
            ->getQuery()
            ->getResult();
    }
}
