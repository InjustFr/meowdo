<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\ListHerbarium\ListHerbariumHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/herbarium', name: 'api_herbarium', methods: ['GET'], format: 'json')]
final class ListHerbariumController extends AbstractController
{
    public function __invoke(ListHerbariumHandler $listHerbarium): JsonResponse
    {
        return $this->json($listHerbarium());
    }
}
