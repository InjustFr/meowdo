<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\RetintCritter\RetintCritterHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/critter/tint', name: 'api_critter_tint', methods: ['PUT'], format: 'json')]
final class RetintCritterController extends AbstractController
{
    public function __invoke(#[MapRequestPayload] TintPayload $payload, RetintCritterHandler $retint): Response
    {
        $retint($payload->tint);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
