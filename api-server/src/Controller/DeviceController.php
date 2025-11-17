<?php

namespace App\Controller;

use App\Service\DeviceService;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/device', name: 'devices_')]
final class DeviceController extends AbstractController
{
    #[Route(name: 'create', methods: ['POST'])]
    public function create(Request $request, DeviceService $deviceService): JsonResponse
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return $this->json(['error' => 'Invalid JSON payload'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $device = $deviceService->createDevice($payload);

        return $this->json($device, JsonResponse::HTTP_CREATED, [], ['groups' => 'device:read']);
    }
}
