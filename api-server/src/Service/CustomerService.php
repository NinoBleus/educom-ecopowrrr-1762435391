<?php
namespace App\Service;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Infrastructure\AddressLookupClient;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly AddressLookupClient $addresses,
    ) {}
    
    public function createCustomer(array $params): Customer 
    {
        $firstName = $params['firstName'] ?? null;
        $lastName = $params['lastName'] ?? null;
        $postcode = $params['postcode'] ?? null;
        $houseNumber = $params['houseNumber'] ?? null;
        $iban = $params['iban'] ?? null;
        if (!$firstName || !$lastName || !$postcode || !$houseNumber || !$iban) {
            throw new BadRequestHttpException('firstName, lastName, iban, postcode and houseNumber are required');
        }
        $postcode = strtoupper(str_replace(' ', '', $postcode));
        $houseNumber = (string) $houseNumber;

        if ($this->customerRepository->findOneBy(['iban' => $iban]) !== null) {
            throw new BadRequestHttpException('A customer with this IBAN already exists');
        }

        if ($this->customerRepository->findOneBy(['postcode' => $postcode, 'house_number' => $houseNumber]) !== null) {
            throw new BadRequestHttpException('A customer with this postcode and house number already exists');
        }
       
        try {
            $address = $this->addresses->fetchFull($postcode, $houseNumber);
        } catch (\RuntimeException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        $result = $this->customerRepository->saveCustomer([
            'firstName'   => $firstName,
            'lastName'    => $lastName,
            'iban'        => $iban,
            'postcode'    => $address['postcode'] ?? $postcode,
            'houseNumber' => $address['number'] ?? $houseNumber,
            'street'      => $address['street'] ?? null,
            'city'        => $address['city'] ?? $address['municipality'] ?? null,
            'latitude'    => $address['geo']['lat'] ?? null,
            'longitude'   => $address['geo']['lon'] ?? null,
        ]);

        return $result;
    }
    
}
