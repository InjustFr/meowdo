<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\WearCosmetic\WearCosmeticHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cat/wear/{slug}', name: 'api_cat_wear', methods: ['POST'], format: 'json')]
final class WearCosmeticController extends AbstractController
{
    public function __invoke(string $slug, WearCosmeticHandler $wear): Response
    {
        $wear($slug);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
