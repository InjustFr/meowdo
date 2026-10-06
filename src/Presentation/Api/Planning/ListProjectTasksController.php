<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ListProjectTasks\ListProjectTasksHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/projects/{id}/tasks', name: 'api_project_tasks', requirements: ['id' => Requirement::ULID], methods: ['GET'], format: 'json')]
final class ListProjectTasksController extends AbstractController
{
    public function __invoke(Ulid $id, ListProjectTasksHandler $listProjectTasks): JsonResponse
    {
        return $this->json($listProjectTasks($id));
    }
}
