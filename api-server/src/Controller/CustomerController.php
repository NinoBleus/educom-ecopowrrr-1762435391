<?php

namespace App\Controller;

use App\Service\CustomerService;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/customers', name: 'customer_')]
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

        return $this->json($customer, JsonResponse::HTTP_CREATED);
    }
}
