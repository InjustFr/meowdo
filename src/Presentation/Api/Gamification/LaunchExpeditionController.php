<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\LaunchExpedition\LaunchExpeditionHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/greenhouse/expeditions', name: 'api_greenhouse_expedition', methods: ['POST'], format: 'json')]
final class LaunchExpeditionController extends AbstractController
{
    public function __invoke(LaunchExpeditionHandler $launchExpedition): JsonResponse
    {
        return $this->json($launchExpedition(), Response::HTTP_CREATED);
    }
}
