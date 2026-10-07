<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ListSubtasks\ListSubtasksHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/tasks/{id}/subtasks', name: 'api_tasks_subtasks', requirements: ['id' => Requirement::ULID], methods: ['GET'], format: 'json')]
final class ListSubtasksController extends AbstractController
{
    public function __invoke(Ulid $id, ListSubtasksHandler $listSubtasks): JsonResponse
    {
        return $this->json($listSubtasks($id));
    }
}
