<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ChangeParent\ChangeParent;
use App\Application\Planning\ChangeParent\ChangeParentHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/tasks/{id}/parent', name: 'api_tasks_parent', requirements: ['id' => Requirement::ULID], methods: ['POST'], format: 'json')]
final class ChangeParentController extends AbstractController
{
    public function __invoke(Ulid $id, #[MapRequestPayload] ParentPayload $payload, ChangeParentHandler $changeParent): JsonResponse
    {
        return $this->json($changeParent(new ChangeParent($id, $payload->parentId)));
    }
}
