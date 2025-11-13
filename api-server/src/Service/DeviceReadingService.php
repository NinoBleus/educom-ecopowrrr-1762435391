<?php
namespace App\Service;

use App\Repository\DeviceReadingRepository;

class DeviceReadingService
{
    public function __construct(
        private readonly DeviceReadingRepository $deviceReadingRepository,
    ) {}

    
}
