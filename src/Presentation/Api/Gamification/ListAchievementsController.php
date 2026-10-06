<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\ListAchievements\ListAchievementsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/achievements', name: 'api_achievements', methods: ['GET'], format: 'json')]
final class ListAchievementsController extends AbstractController
{
    public function __invoke(ListAchievementsHandler $listAchievements): JsonResponse
    {
        return $this->json($listAchievements());
    }
}
