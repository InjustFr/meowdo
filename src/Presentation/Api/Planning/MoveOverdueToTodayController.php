<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\MoveOverdueToToday\MoveOverdueToTodayHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks/overdue/plan-today', name: 'api_tasks_overdue_today', methods: ['POST'], format: 'json')]
final class MoveOverdueToTodayController extends AbstractController
{
    public function __invoke(MoveOverdueToTodayHandler $moveOverdue): JsonResponse
    {
        return $this->json(['moved' => $moveOverdue()]);
    }
}
