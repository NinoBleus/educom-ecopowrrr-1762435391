<?php
namespace App\Service;

use App\Repository\BuyPricePeriodRepository;
use App\Entity\BuyPricePeriod;

class BuyPricePeriodService
{
    public function __construct(
        private readonly BuyPricePeriodRepository $buyPricePeriodRepository,
    ) {}

    public function saveBuyPricePeriod($params) {
        $result = $this->buyPricePeriodRepository->saveBuyPricePeriod($params);
    }

    public function getPeriodFor(\DateTimeInterface $timestamp): ?BuyPricePeriod
    {
        $period = $this->buyPricePeriodRepository->findLatestPeriodBefore($timestamp);
        if (!$period) {
            return null;
        }

        $validTo = $period->getValidTo();
        if ($validTo !== null && $timestamp >= $validTo) {
            return null;
        }

        return $period;
    }



}
