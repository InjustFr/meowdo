<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ShowStatistics\ShowStatisticsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stats', name: 'api_stats', methods: ['GET'], format: 'json')]
final class ShowStatisticsController extends AbstractController
{
    public function __invoke(ShowStatisticsHandler $showStatistics): JsonResponse
    {
        return $this->json($showStatistics());
    }
}
