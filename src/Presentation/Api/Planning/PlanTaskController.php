<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\PlanTask\PlanTask;
use App\Application\Planning\PlanTask\PlanTaskHandler;
use App\Presentation\Api\DayParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/tasks/{id}/plan', name: 'api_tasks_plan', requirements: ['id' => Requirement::ULID], methods: ['POST'], format: 'json')]
final class PlanTaskController extends AbstractController
{
    public function __invoke(Ulid $id, #[MapRequestPayload] PlanPayload $payload, PlanTaskHandler $planTask): JsonResponse
    {
        return $this->json($planTask(new PlanTask($id, $payload->when, DayParameter::of($payload->date))));
    }
}
