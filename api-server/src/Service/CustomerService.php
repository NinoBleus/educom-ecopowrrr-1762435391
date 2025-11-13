<?php
namespace App\Service;

use App\Repository\CustomerRepository;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
    ) {}

    
}
