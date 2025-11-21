<?php

namespace App\Controller;

use App\Service\CustomerService;
use App\Service\ReportService;
use JsonException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/customer', name: 'customer_')]
final class CustomerController extends AbstractController
{
    #[Route(name: 'create', methods: ['POST'])]
    public function create(Request $request, CustomerService $customerService): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return $this->json(['error' => 'Invalid JSON payload'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $customer = $customerService->createCustomer($payload);

        return $this->json($customer, JsonResponse::HTTP_CREATED, [], ['groups' => 'customer:read']);
    }

    #[Route('/report/{year}', name: 'report', methods: ['GET'], defaults: ['year' => null], requirements: ['year' => '\d{4}'])]
    public function downloadAnnualReport(?int $year, Request $request, ReportService $reportService): StreamedResponse
    {
        $selectedYear = $year ?? $request->query->getInt('year', (int) date('Y'));
        $spreadsheet = $reportService->buildCustomerAnnualReportSpreadsheet($selectedYear);

        $response = new StreamedResponse(static function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $filename = sprintf('customer-report-%d.xlsx', $selectedYear);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/report/forecast/{year}', name: 'report_forecast', methods: ['GET'], defaults: ['year' => null], requirements: ['year' => '\d{4}'])]
    public function downloadRevenueForecastReport(?int $year, Request $request, ReportService $reportService): StreamedResponse
    {
        $selectedYear = $year ?? $request->query->getInt('year', (int) date('Y'));
        $spreadsheet = $reportService->buildRevenueForecastReportSpreadsheet($selectedYear);

        $response = new StreamedResponse(static function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $filename = sprintf('revenue-forecast-%d.xlsx', $selectedYear);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/report/municipality', name: 'report_municipality', methods: ['GET'])]
    public function downloadMunicipalityOverviewReport(ReportService $reportService): StreamedResponse
    {
        $spreadsheet = $reportService->buildMunicipalityOverviewSpreadsheet();

        $response = new StreamedResponse(static function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $filename = 'municipality-overview.xlsx';
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
