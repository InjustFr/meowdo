<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\CreateProject\CreateProject;
use App\Application\Planning\CreateProject\CreateProjectHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/projects', name: 'api_projects_create', methods: ['POST'], format: 'json')]
final class CreateProjectController extends AbstractController
{
    public function __invoke(#[MapRequestPayload] ProjectPayload $payload, CreateProjectHandler $createProject): JsonResponse
    {
        return $this->json($createProject(new CreateProject($payload->name, $payload->color)), Response::HTTP_CREATED);
    }
}
