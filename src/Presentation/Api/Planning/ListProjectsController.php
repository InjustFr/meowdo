<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ListProjects\ListProjectsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/projects', name: 'api_projects_list', methods: ['GET'], format: 'json')]
final class ListProjectsController extends AbstractController
{
    public function __invoke(ListProjectsHandler $listProjects): JsonResponse
    {
        return $this->json($listProjects());
    }
}
