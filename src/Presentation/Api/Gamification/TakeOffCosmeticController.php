<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\TakeOffCosmetic\TakeOffCosmeticHandler;
use App\Domain\Gamification\Cosmetic\Slot;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/critter/take-off/{slot}', name: 'api_critter_take_off', methods: ['POST'], format: 'json')]
final class TakeOffCosmeticController extends AbstractController
{
    public function __invoke(Slot $slot, TakeOffCosmeticHandler $takeOff): Response
    {
        $takeOff($slot);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
