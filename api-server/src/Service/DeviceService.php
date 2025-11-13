<?php
namespace App\Service;

use App\Repository\DeviceRepository;

class DeviceService
{
    public function __construct(
        private readonly DeviceRepository $deviceRepository,
    ) {}

    
}
