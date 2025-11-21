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

    public function getPeriodFor(\DateTimeInterface $timestamp): BuyPricePeriod
    {
        $period = $this->buyPricePeriodRepository->findLatestPeriodBefore($timestamp);
        if ($period) {
            return $period;
        }

        $earliest = $this->buyPricePeriodRepository->findEarliestPeriod();
        if ($earliest === null) {
            throw new \RuntimeException('No buy price periods have been configured yet.');
        }

        $validFrom = $earliest->getValidFrom();
        if ($validFrom === null || $timestamp < $validFrom) {
            return $earliest;
        }

        throw new \RuntimeException(sprintf(
            'No buy price period covers %s. Please add a price period that starts on or before this date.',
            $timestamp->format(\DateTimeInterface::ATOM)
        ));
    }



}
