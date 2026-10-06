<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\DeleteProject\DeleteProjectHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/projects/{id}', name: 'api_projects_delete', requirements: ['id' => Requirement::ULID], methods: ['DELETE'], format: 'json')]
final class DeleteProjectController extends AbstractController
{
    public function __invoke(Ulid $id, DeleteProjectHandler $deleteProject): Response
    {
        $deleteProject($id);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
