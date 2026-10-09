<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\PlantMoss\PlantMoss;
use App\Application\Gamification\PlantMoss\PlantMossHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/api/greenhouse/pots/{pot}', name: 'api_greenhouse_plant', requirements: ['pot' => Requirement::POSITIVE_INT], methods: ['PUT'], format: 'json')]
final class PlantMossController extends AbstractController
{
    public function __invoke(int $pot, #[MapRequestPayload] PlantPayload $payload, PlantMossHandler $plantMoss): Response
    {
        $plantMoss(new PlantMoss($pot, $payload->species));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
