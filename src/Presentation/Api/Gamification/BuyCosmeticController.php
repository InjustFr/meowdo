<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\BuyCosmetic\BuyCosmeticHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/shop/{slug}/buy', name: 'api_shop_buy', methods: ['POST'], format: 'json')]
final class BuyCosmeticController extends AbstractController
{
    public function __invoke(string $slug, BuyCosmeticHandler $buy): Response
    {
        $buy($slug);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
