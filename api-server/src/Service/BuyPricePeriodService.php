<?php
namespace App\Service;

use App\Repository\BuyPricePeriodRepository;

class BuyPricePeriodService
{
    public function __construct(
        private readonly BuyPricePeriodRepository $buyPricePeriodRepository,
    ) {}

    
}
