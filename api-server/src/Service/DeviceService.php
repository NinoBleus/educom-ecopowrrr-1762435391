<?php
namespace App\Service;

use App\Entity\Device;
use App\Repository\DeviceRepository;
use App\Repository\CustomerRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class DeviceService
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
        private readonly CustomerRepository $customerRepository,

    ) {}

    public function createDevice(array $params): Device 
    {
        $deviceType = $params['deviceType'] ?? null;
        $serialNumber = $params['serialNumber'] ?? null;
        $customerId = $params['customerId'] ?? null;
        if(!$deviceType || !$serialNumber || !$customerId) {
            throw new BadRequestHttpException('deviceType, serialNumber and customerId are required!');
        }

        $customer = $this->findCustomerById($customerId);
        if($customer === null){
            throw new BadRequestHttpException(sprintf('Customer with id %s could not be found', $customerId));
        }

        if ($this->deviceRepository->findOneBy([
            'serial_number' => $serialNumber,
            'customer_id' => $customer
        ]) !== null) {
            throw new BadRequestHttpException('Device with this serial number already exists for the selected customer');
        }

        $result = $this->deviceRepository->saveDevice([
            'deviceType' => $deviceType,
            'serialNumber' => $serialNumber,
            'customer_id' => $customer
        ]);

        return $result;
    }

    private function findCustomerById($id) {
        return $this->customerRepository->fetchCustomer($id);
    }

    
}
