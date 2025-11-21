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

        if (array_key_exists('kwhUsed', $params)) {
            $deviceReading->setKwhUsed($params['kwhUsed']);
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

    /**
     * @return array<int, array{year: int, month: int, totalKwh: float, totalRevenue: float}>
     */
    public function fetchMonthlyRevenueHistory(?int $startYear = null): array
    {
        $queryBuilder = $this->createQueryBuilder('reading')
            ->select('reading.reading_timestamp AS readingTimestamp')
            ->addSelect('reading.kwh_used AS kwhUsed')
            ->addSelect('pricePeriod.price_per_kwh AS pricePerKwh')
            ->innerJoin('reading.price_period', 'pricePeriod')
            ->orderBy('reading.reading_timestamp', 'ASC');

        if ($startYear !== null) {
            $startDate = new \DateTimeImmutable(sprintf('%04d-01-01 00:00:00', $startYear));
            $queryBuilder
                ->andWhere('reading.reading_timestamp >= :startOfPeriod')
                ->setParameter('startOfPeriod', $startDate);
        }

        $rows = $queryBuilder->getQuery()->getArrayResult();

        $monthlyTotals = [];
        foreach ($rows as $row) {
            $timestamp = $row['readingTimestamp'] ?? null;
            if ($timestamp === null) {
                continue;
            }

            if (!$timestamp instanceof \DateTimeInterface) {
                $timestamp = new \DateTimeImmutable($timestamp);
            }

            $year = (int) $timestamp->format('Y');
            $month = (int) $timestamp->format('n');
            $index = sprintf('%04d-%02d', $year, $month);

            if (!isset($monthlyTotals[$index])) {
                $monthlyTotals[$index] = [
                    'year' => $year,
                    'month' => $month,
                    'totalKwh' => 0.0,
                    'totalRevenue' => 0.0,
                ];
            }

            $kwhUsed = $row['kwhUsed'] ?? null;
            $kwh = $kwhUsed !== null ? (float) $kwhUsed : 0.0;
            $pricePerKwh = isset($row['pricePerKwh']) ? (float) $row['pricePerKwh'] : 0.0;

            $monthlyTotals[$index]['totalKwh'] += $kwh;
            $monthlyTotals[$index]['totalRevenue'] += $kwh * $pricePerKwh;
        }

        ksort($monthlyTotals);

        return array_values($monthlyTotals);
    }

    /**
     * @return array<int, array{municipality: string, province: string|null, totalKwhGenerated: float, totalKwhUsed: float, totalRevenue: float}>
     */
    public function fetchMunicipalityTotals(): array
    {
        $rows = $this->createQueryBuilder('reading')
            ->select('customer.municipality AS municipality')
            ->addSelect('customer.province AS province')
            ->addSelect('SUM(COALESCE(reading.kwh_generated, 0)) AS totalKwhGenerated')
            ->addSelect('SUM(COALESCE(reading.kwh_used, 0)) AS totalKwhUsed')
            ->addSelect('SUM(COALESCE(reading.kwh_used, 0) * COALESCE(price.price_per_kwh, 0)) AS totalRevenue')
            ->innerJoin('reading.device', 'device')
            ->innerJoin('device.customer_id', 'customer')
            ->innerJoin('reading.price_period', 'price')
            ->groupBy('customer.municipality')
            ->addGroupBy('customer.province')
            ->orderBy('customer.municipality', 'ASC')
            ->addOrderBy('customer.province', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(static function (array $row): array {
            $municipality = $row['municipality'] ?? 'Onbekend';
            $province = $row['province'] ?? null;

            return [
                'municipality' => $municipality !== null && $municipality !== '' ? $municipality : 'Onbekend',
                'province' => $province !== null && $province !== '' ? $province : null,
                'totalKwhGenerated' => isset($row['totalKwhGenerated']) ? (float) $row['totalKwhGenerated'] : 0.0,
                'totalKwhUsed' => isset($row['totalKwhUsed']) ? (float) $row['totalKwhUsed'] : 0.0,
                'totalRevenue' => isset($row['totalRevenue']) ? (float) $row['totalRevenue'] : 0.0,
            ];
        }, $rows);
    }
}
