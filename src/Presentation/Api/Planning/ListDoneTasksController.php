<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ListDoneTasks\ListDoneTasksHandler;
use App\Presentation\Api\DayParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks/done', name: 'api_tasks_done', methods: ['GET'], format: 'json')]
final class ListDoneTasksController extends AbstractController
{
    public function __invoke(ListDoneTasksHandler $listDone, #[MapQueryParameter] ?string $before = null): JsonResponse
    {
        return $this->json($listDone(DayParameter::of($before)));
    }
}
