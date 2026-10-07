<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\AddSubtask\AddSubtask;
use App\Application\Planning\AddSubtask\AddSubtaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/tasks/{id}/subtasks', name: 'api_tasks_add_subtask', requirements: ['id' => Requirement::ULID], methods: ['POST'], format: 'json')]
final class AddSubtaskController extends AbstractController
{
    public function __invoke(Ulid $id, #[MapRequestPayload] SubtaskPayload $payload, AddSubtaskHandler $addSubtask): JsonResponse
    {
        return $this->json($addSubtask(new AddSubtask($id, $payload->title)), Response::HTTP_CREATED);
    }
}
