<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\ShowPlayer\ShowPlayerHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/player', name: 'api_player', methods: ['GET'], format: 'json')]
final class ShowPlayerController extends AbstractController
{
    public function __invoke(ShowPlayerHandler $showPlayer): JsonResponse
    {
        return $this->json($showPlayer());
    }
}
