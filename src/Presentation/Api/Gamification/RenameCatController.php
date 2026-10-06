<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\RenameCat\RenameCatHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cat/name', name: 'api_cat_name', methods: ['PUT'], format: 'json')]
final class RenameCatController extends AbstractController
{
    public function __invoke(#[MapRequestPayload] CatNamePayload $payload, RenameCatHandler $rename): Response
    {
        $rename($payload->name);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
