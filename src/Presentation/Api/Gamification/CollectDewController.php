<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\CollectDew\CollectDewHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/greenhouse/collect', name: 'api_greenhouse_collect', methods: ['POST'], format: 'json')]
final class CollectDewController extends AbstractController
{
    public function __invoke(CollectDewHandler $collectDew): JsonResponse
    {
        return $this->json($collectDew());
    }
}
