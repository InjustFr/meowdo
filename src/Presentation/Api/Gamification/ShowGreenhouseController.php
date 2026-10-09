<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\ShowGreenhouse\ShowGreenhouseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/greenhouse', name: 'api_greenhouse', methods: ['GET'], format: 'json')]
final class ShowGreenhouseController extends AbstractController
{
    public function __invoke(ShowGreenhouseHandler $showGreenhouse): JsonResponse
    {
        return $this->json($showGreenhouse());
    }
}
