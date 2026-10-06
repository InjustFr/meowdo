<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ReopenTask\ReopenTaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/tasks/{id}/reopen', name: 'api_tasks_reopen', requirements: ['id' => Requirement::ULID], methods: ['POST'], format: 'json')]
final class ReopenTaskController extends AbstractController
{
    public function __invoke(Ulid $id, ReopenTaskHandler $reopenTask): JsonResponse
    {
        return $this->json($reopenTask($id));
    }
}
