<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Repository\CustomerRepository;
use App\Repository\DeviceReadingRepository;
use App\Entity\Customer;

class ReportService
{
    private const MONTH_LABELS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maart',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Augustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'December',
    ];

    public function __construct(
        private readonly CustomerRepository $customerRepository,
        private readonly DeviceReadingRepository $deviceReadingRepository,
    ) {}

    public function buildCustomerAnnualReportSpreadsheet(int $year): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Customer overview');
        $worksheet->fromArray(
            ['Customer', 'Year', 'Total revenue (€)', 'Total kWh generated', 'Total kWh used'],
            null,
            'A1'
        );

        $customers = $this->fetchCustomersWithAnnualData($year);

        if (count($customers) === 0) {
            $worksheet->setCellValue('A2', 'No customer data available');
            return $spreadsheet;
        }

        $excelRow = 2;
        foreach ($customers as $customerData) {
            $name = $customerData['name'] ?? sprintf('Customer %d', $customerData['id'] ?? $excelRow - 1);
            $worksheet->setCellValue("A{$excelRow}", $name);
            $worksheet->setCellValue("B{$excelRow}", $year);
            $worksheet->setCellValue("C{$excelRow}", round($customerData['total_revenue'] ?? 0, 2));
            $worksheet->setCellValue("D{$excelRow}", round($customerData['total_kwh_generated'] ?? 0, 3));
            $worksheet->setCellValue("E{$excelRow}", round($customerData['total_kwh_used'] ?? 0, 3));
            ++$excelRow;
        }

        $worksheet->setCellValue("A{$excelRow}", 'Total');
        $worksheet->setCellValue(
            "C{$excelRow}",
            sprintf('=SUM(C2:C%d)', $excelRow - 1)
        );
        $worksheet->setCellValue(
            "D{$excelRow}",
            sprintf('=SUM(D2:D%d)', $excelRow - 1)
        );
        $worksheet->setCellValue(
            "E{$excelRow}",
            sprintf('=SUM(E2:E%d)', $excelRow - 1)
        );

        $this->autoSizeColumns($worksheet, ['A', 'B', 'C', 'D', 'E']);

        return $spreadsheet;
    }

    public function buildRevenueForecastReportSpreadsheet(?int $year = null): Spreadsheet
    {
        $targetYear = $year ?? (int) date('Y');
        $historyRows = $this->deviceReadingRepository->fetchMonthlyRevenueHistory();

        $history = array_map(static fn (array $row): array => [
            'year' => (int) $row['year'],
            'month' => (int) $row['month'],
            'revenue' => (float) $row['totalRevenue'],
            'kwh' => (float) $row['totalKwh'],
        ], $historyRows);

        usort(
            $history,
            static fn (array $left, array $right): int => $left['year'] <=> $right['year'] ?: $left['month'] <=> $right['month']
        );

        $rows = $this->buildMonthlyRevenueForecastRows($history, $targetYear);

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle(sprintf('Omzet %d', $targetYear));
        $worksheet->fromArray(
            ['Maand', 'Omzet (€)', 'Prognose (€)', 'Verschil (€)', 'Cumulatieve omzet (€)'],
            null,
            'A1'
        );

        $excelRow = 2;
        $cumulative = 0.0;
        foreach ($rows as $row) {
            $cumulative += $row['revenue'];
            $variance = $row['forecast'] - $row['revenue'];

            $worksheet->setCellValue("A{$excelRow}", $row['label']);
            $worksheet->setCellValue("B{$excelRow}", round($row['revenue'], 2));
            $worksheet->setCellValue("C{$excelRow}", round($row['forecast'], 2));
            $worksheet->setCellValue("D{$excelRow}", round($variance, 2));
            $worksheet->setCellValue("E{$excelRow}", round($cumulative, 2));
            ++$excelRow;
        }

        $lastDataRow = $excelRow - 1;
        $worksheet->setCellValue("A{$excelRow}", 'Totaal');
        $worksheet->setCellValue("B{$excelRow}", sprintf('=SUM(B2:B%d)', $lastDataRow));
        $worksheet->setCellValue("C{$excelRow}", sprintf('=SUM(C2:C%d)', $lastDataRow));
        $worksheet->setCellValue("D{$excelRow}", sprintf('=SUM(D2:D%d)', $lastDataRow));
        $worksheet->setCellValue("E{$excelRow}", sprintf('=E%d', $lastDataRow));

        $worksheet->setCellValue('G1', 'Jaar');
        $worksheet->setCellValue('H1', $targetYear);
        $worksheet->setCellValue('G2', 'Totale omzet (€)');
        $worksheet->setCellValue('H2', sprintf('=B%d', $excelRow));
        $worksheet->setCellValue('G3', 'Verwachte omzet (€)');
        $worksheet->setCellValue('H3', sprintf('=C%d', $excelRow));
        $worksheet->setCellValue('G4', 'Verwachting verschil (€)');
        $worksheet->setCellValue('H4', sprintf('=D%d', $excelRow));

        $this->autoSizeColumns($worksheet, ['A', 'B', 'C', 'D', 'E', 'G', 'H']);

        return $spreadsheet;
    }

    public function buildMunicipalityOverviewSpreadsheet(): Spreadsheet
    {
        $municipalities = $this->deviceReadingRepository->fetchMunicipalityTotals();

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('Municipality overview');
        $worksheet->fromArray(
            ['Gemeente', 'Provincie', 'Omzet (€)', 'kWh opgewekt', 'kWh ingekocht', 'kWh overschot'],
            null,
            'A1'
        );

        if (count($municipalities) === 0) {
            $worksheet->setCellValue('A2', 'No municipality data available');
            return $spreadsheet;
        }

        $excelRow = 2;
        foreach ($municipalities as $municipality) {
            $generated = $municipality['totalKwhGenerated'] ?? 0.0;
            $used = $municipality['totalKwhUsed'] ?? 0.0;
            $surplus = $generated - $used;

            $worksheet->setCellValue("A{$excelRow}", $municipality['municipality'] ?? 'Onbekend');
            $worksheet->setCellValue("B{$excelRow}", $municipality['province'] ?? 'Onbekend');
            $worksheet->setCellValue("C{$excelRow}", round((float) ($municipality['totalRevenue'] ?? 0.0), 2));
            $worksheet->setCellValue("D{$excelRow}", round((float) $generated, 3));
            $worksheet->setCellValue("E{$excelRow}", round((float) $used, 3));
            $worksheet->setCellValue("F{$excelRow}", round((float) $surplus, 3));
            ++$excelRow;
        }

        $worksheet->setCellValue("A{$excelRow}", 'Totaal');
        $worksheet->setCellValue("C{$excelRow}", sprintf('=SUM(C2:C%d)', $excelRow - 1));
        $worksheet->setCellValue("D{$excelRow}", sprintf('=SUM(D2:D%d)', $excelRow - 1));
        $worksheet->setCellValue("E{$excelRow}", sprintf('=SUM(E2:E%d)', $excelRow - 1));
        $worksheet->setCellValue("F{$excelRow}", sprintf('=SUM(F2:F%d)', $excelRow - 1));

        $this->autoSizeColumns($worksheet, ['A', 'B', 'C', 'D', 'E', 'F']);

        return $spreadsheet;
    }

    private function fetchCustomersWithAnnualData(int $year): array
    {
        $deviceReadings = $this->customerRepository->fetchCustomerAnualDeviceReadingData(null, $year);
        $customers = [];

        foreach ($this->customerRepository->findAll() as $customer) {
            $customerId = $customer->getId();
            if ($customerId === null) {
                continue;
            }

            $customers[$customerId] = [
                'id' => $customerId,
                'name' => $this->formatCustomerName($customer),
                'total_revenue' => 0.0,
                'total_kwh_generated' => 0.0,
                'total_kwh_used' => 0.0,
            ];
        }

        foreach ($deviceReadings as $reading) {
            $device = $reading->getDeviceId();
            $customer = $device?->getCustomerId();
            $customerId = $customer?->getId();

            if ($customerId === null) {
                continue;
            }

            if (!isset($customers[$customerId])) {
                $customers[$customerId] = [
                    'id' => $customerId,
                    'name' => $this->formatCustomerName($customer),
                    'total_revenue' => 0.0,
                    'total_kwh_generated' => 0.0,
                    'total_kwh_used' => 0.0,
                ];
            }

            $kwhGenerated = (float) $reading->getKwhGenerated();
            $kwhUsed = $reading->getKwhUsed();
            $kwhUsedValue = $kwhUsed !== null ? (float) $kwhUsed : 0.0;
            $pricePerKwh = $reading->getPricePeriodId()?->getPricePerKwh();
            $revenue = $pricePerKwh !== null ? $kwhUsedValue * (float) $pricePerKwh : 0.0;

            $customers[$customerId]['total_kwh_generated'] += $kwhGenerated;
            $customers[$customerId]['total_kwh_used'] += $kwhUsedValue;
            $customers[$customerId]['total_revenue'] += $revenue;
        }

        $customers = array_values($customers);
        usort(
            $customers,
            static fn (array $left, array $right): int => strcmp($left['name'], $right['name'])
        );

        return $customers;
    }

    /**
     * @param array<int, array{year: int, month: int, revenue: float, kwh: float}> $history
     * @return array<int, array{month: int, label: string, revenue: float, forecast: float}>
     */
    private function buildMonthlyRevenueForecastRows(array $history, int $targetYear): array
    {
        $monthlyActuals = array_fill(1, 12, 0.0);
        foreach ($history as $entry) {
            if ($entry['year'] === $targetYear) {
                $monthlyActuals[$entry['month']] = $entry['revenue'];
            }
        }

        $trendSource = array_values(array_filter(
            $history,
            static fn (array $entry): bool => $entry['year'] < $targetYear
        ));

        if (count($trendSource) === 0) {
            $trendSource = $history;
        }

        if (count($trendSource) === 0) {
            $startYear = $targetYear;
            $startMonth = 1;
            $slope = 0.0;
            $intercept = 0.0;
        } else {
            usort(
                $trendSource,
                static fn (array $left, array $right): int => $left['year'] <=> $right['year'] ?: $left['month'] <=> $right['month']
            );
            $startYear = $trendSource[0]['year'];
            $startMonth = $trendSource[0]['month'];
            [$slope, $intercept] = $this->calculateTrendCoefficients($trendSource, $startYear, $startMonth);
        }

        $rows = [];
        for ($month = 1; $month <= 12; ++$month) {
            $index = $this->monthsBetween($startYear, $startMonth, $targetYear, $month);
            if ($index < 0) {
                $index = 0;
            }
            $forecast = max(0.0, $intercept + ($slope * $index));

            $rows[] = [
                'month' => $month,
                'label' => self::MONTH_LABELS[$month] ?? sprintf('Maand %d', $month),
                'revenue' => $monthlyActuals[$month],
                'forecast' => $forecast,
            ];
        }

        return $rows;
    }

    /**
     * @param array<int, array{year: int, month: int, revenue: float, kwh: float}> $entries
     */
    private function calculateTrendCoefficients(array $entries, int $startYear, int $startMonth): array
    {
        $count = count($entries);
        if ($count === 0) {
            return [0.0, 0.0];
        }

        $sumX = 0.0;
        $sumY = 0.0;
        $sumXY = 0.0;
        $sumX2 = 0.0;

        foreach ($entries as $entry) {
            $x = (float) $this->monthsBetween($startYear, $startMonth, $entry['year'], $entry['month']);
            $y = (float) $entry['revenue'];

            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $denominator = ($count * $sumX2) - ($sumX * $sumX);
        if (abs($denominator) < 1e-6) {
            $slope = 0.0;
            $intercept = $count > 0 ? $sumY / $count : 0.0;
        } else {
            $slope = (($count * $sumXY) - ($sumX * $sumY)) / $denominator;
            $intercept = ($sumY - ($slope * $sumX)) / $count;
        }

        return [$slope, $intercept];
    }

    private function monthsBetween(int $startYear, int $startMonth, int $endYear, int $endMonth): int
    {
        return (($endYear - $startYear) * 12) + ($endMonth - $startMonth);
    }

    private function formatCustomerName(Customer $customer): string
    {
        $fullName = trim(sprintf('%s %s', $customer->getFirstName() ?? '', $customer->getLastName() ?? ''));

        return $fullName !== '' ? $fullName : sprintf('Customer %d', $customer->getId());
    }

    private function autoSizeColumns(Worksheet $worksheet, array $columns): void
    {
        foreach ($columns as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
