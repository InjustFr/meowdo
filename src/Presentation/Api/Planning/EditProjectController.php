<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\EditProject\EditProject;
use App\Application\Planning\EditProject\EditProjectHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/projects/{id}', name: 'api_projects_edit', requirements: ['id' => Requirement::ULID], methods: ['PATCH'], format: 'json')]
final class EditProjectController extends AbstractController
{
    public function __invoke(Ulid $id, #[MapRequestPayload] ProjectPayload $payload, EditProjectHandler $editProject): JsonResponse
    {
        return $this->json($editProject(new EditProject($id, $payload->name, $payload->color)));
    }
}
