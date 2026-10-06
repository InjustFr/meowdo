<?php

declare(strict_types=1);

namespace App\Presentation\Api\Gamification;

use App\Application\Gamification\RecoatCat\RecoatCatHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/cat/coat', name: 'api_cat_coat', methods: ['PUT'], format: 'json')]
final class RecoatCatController extends AbstractController
{
    public function __invoke(#[MapRequestPayload] CoatPayload $payload, RecoatCatHandler $recoat): Response
    {
        $recoat($payload->coat);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
