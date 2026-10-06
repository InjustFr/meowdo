<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\EditTask\EditTask;
use App\Application\Planning\EditTask\EditTaskHandler;
use App\Presentation\Api\DayParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/tasks/{id}', name: 'api_tasks_edit', requirements: ['id' => Requirement::ULID], methods: ['PATCH'], format: 'json')]
final class EditTaskController extends AbstractController
{
    public function __invoke(Ulid $id, #[MapRequestPayload] EditTaskPayload $payload, EditTaskHandler $editTask): JsonResponse
    {
        return $this->json($editTask(new EditTask($id, $payload->title, $payload->notes, $payload->projectId, DayParameter::of($payload->dueOn))));
    }
}
