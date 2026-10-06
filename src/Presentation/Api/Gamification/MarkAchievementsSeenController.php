<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\MarkAchievementsSeen\MarkAchievementsSeenHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/achievements/seen', name: 'api_achievements_seen', methods: ['POST'], format: 'json')]
final class MarkAchievementsSeenController extends AbstractController
{
    public function __invoke(MarkAchievementsSeenHandler $markSeen): Response
    {
        $markSeen();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
