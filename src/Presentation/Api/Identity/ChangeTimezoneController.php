<?php

declare(strict_types=1);

namespace App\Presentation\Api\Identity;

use App\Application\Identity\ChangeTimezone\ChangeTimezoneHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/me/timezone', name: 'api_me_timezone', methods: ['PUT'], format: 'json')]
final class ChangeTimezoneController extends AbstractController
{
    public function __invoke(#[MapRequestPayload] TimezonePayload $payload, ChangeTimezoneHandler $changeTimezone): Response
    {
        $changeTimezone($payload->timezone);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
