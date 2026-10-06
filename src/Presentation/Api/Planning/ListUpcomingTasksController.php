<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ListUpcomingTasks\ListUpcomingTasksHandler;
use App\Presentation\Api\DayParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks/upcoming', name: 'api_tasks_upcoming', methods: ['GET'], format: 'json')]
final class ListUpcomingTasksController extends AbstractController
{
    public function __invoke(ListUpcomingTasksHandler $listUpcoming, #[MapQueryParameter] ?string $from = null): JsonResponse
    {
        return $this->json($listUpcoming(DayParameter::of($from)));
    }
}
