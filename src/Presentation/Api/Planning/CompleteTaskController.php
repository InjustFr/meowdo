<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\CompleteTask\CompleteTaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/tasks/{id}/complete', name: 'api_tasks_complete', requirements: ['id' => Requirement::ULID], methods: ['POST'], format: 'json')]
final class CompleteTaskController extends AbstractController
{
    public function __invoke(Ulid $id, CompleteTaskHandler $completeTask): JsonResponse
    {
        return $this->json($completeTask($id));
    }
}
