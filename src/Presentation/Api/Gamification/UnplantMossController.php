<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\UnplantMoss\UnplantMossHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/api/greenhouse/pots/{pot}', name: 'api_greenhouse_unplant', requirements: ['pot' => Requirement::POSITIVE_INT], methods: ['DELETE'], format: 'json')]
final class UnplantMossController extends AbstractController
{
    public function __invoke(int $pot, UnplantMossHandler $unplantMoss): Response
    {
        $unplantMoss($pot);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
