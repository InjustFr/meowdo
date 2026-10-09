<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\UpgradeFacility\UpgradeFacilityHandler;
use App\Domain\Gamification\Greenhouse\Facility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;

#[Route('/api/greenhouse/facilities/{facility}/upgrade', name: 'api_greenhouse_upgrade', requirements: ['facility' => new EnumRequirement(Facility::class)], methods: ['POST'], format: 'json')]
final class UpgradeFacilityController extends AbstractController
{
    public function __invoke(Facility $facility, UpgradeFacilityHandler $upgradeFacility): Response
    {
        $upgradeFacility($facility);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
