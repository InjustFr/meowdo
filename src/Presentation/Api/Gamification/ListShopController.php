<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\ListShop\ListShopHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/shop', name: 'api_shop', methods: ['GET'], format: 'json')]
final class ListShopController extends AbstractController
{
    public function __invoke(ListShopHandler $listShop): JsonResponse
    {
        return $this->json($listShop());
    }
}
