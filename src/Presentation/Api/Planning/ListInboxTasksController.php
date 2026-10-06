<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ListInboxTasks\ListInboxTasksHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks/inbox', name: 'api_tasks_inbox', methods: ['GET'], format: 'json')]
final class ListInboxTasksController extends AbstractController
{
    public function __invoke(ListInboxTasksHandler $listInbox): JsonResponse
    {
        return $this->json($listInbox());
    }
}
