<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\DeleteTask\DeleteTaskHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Ulid;

#[Route('/api/tasks/{id}', name: 'api_tasks_delete', requirements: ['id' => Requirement::ULID], methods: ['DELETE'], format: 'json')]
final class DeleteTaskController extends AbstractController
{
    public function __invoke(Ulid $id, DeleteTaskHandler $deleteTask): Response
    {
        $deleteTask($id);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
