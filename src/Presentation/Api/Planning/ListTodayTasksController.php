<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ListTodayTasks\ListTodayTasksHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks/today', name: 'api_tasks_today', methods: ['GET'], format: 'json')]
final class ListTodayTasksController extends AbstractController
{
    public function __invoke(ListTodayTasksHandler $listToday): JsonResponse
    {
        return $this->json($listToday());
    }
}
