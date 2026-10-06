<?php

declare(strict_types=1);

namespace App\Presentation\Api\Planning;

use App\Application\Planning\ShowMatrix\ShowMatrixHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/matrix', name: 'api_matrix', methods: ['GET'], format: 'json')]
final class ShowMatrixController extends AbstractController
{
    public function __invoke(ShowMatrixHandler $showMatrix): JsonResponse
    {
        return $this->json($showMatrix());
    }
}
