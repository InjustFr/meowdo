<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\RenameCritter\RenameCritterHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/critter/name', name: 'api_critter_name', methods: ['PUT'], format: 'json')]
final class RenameCritterController extends AbstractController
{
    public function __invoke(#[MapRequestPayload] CritterNamePayload $payload, RenameCritterHandler $rename): Response
    {
        $rename($payload->name);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
