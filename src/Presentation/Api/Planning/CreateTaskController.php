<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\CreateTask\CreateTask;
use App\Application\Planning\CreateTask\CreateTaskHandler;
use App\Presentation\Api\DayParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks', name: 'api_tasks_create', methods: ['POST'], format: 'json')]
final class CreateTaskController extends AbstractController
{
    public function __invoke(#[MapRequestPayload] CreateTaskPayload $payload, CreateTaskHandler $createTask): JsonResponse
    {
        return $this->json($createTask(new CreateTask(
            $payload->title,
            $payload->notes,
            $payload->projectId,
            $payload->plan,
            DayParameter::of($payload->planDate),
            DayParameter::of($payload->dueOn),
            $payload->quadrant,
        )), Response::HTTP_CREATED);
    }
}
