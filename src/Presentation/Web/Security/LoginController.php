<?php

declare(strict_types=1);

namespace App\Presentation\Web\Security;

use App\Presentation\Web\AuthPage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/login', name: 'login', methods: ['GET', 'POST'])]
final class LoginController extends AbstractController
{
    public function __construct(
        private readonly AuthPage $page,
        private readonly CsrfTokenManagerInterface $csrfTokens,
        private readonly TranslatorInterface $translator,
        private readonly Flashes $flashes,
    ) {
    }

    public function __invoke(AuthenticationUtils $authentication): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('app');
        }

        return $this->page->render('LoginPage', [
            'lastEmail' => $authentication->getLastUsername(),
            'error' => $this->loginError($authentication->getLastAuthenticationError()),
            'csrfToken' => $this->csrfTokens->getToken('authenticate')->getValue(),
            'notice' => $this->notice(),
        ]);
    }

    private function loginError(?AuthenticationException $error): ?string
    {
        $key = match (true) {
            null === $error => null,
            $error instanceof BadCredentialsException, $error instanceof UserNotFoundException => 'login.bad_credentials',
            $error instanceof TooManyLoginAttemptsAuthenticationException => 'login.too_many_attempts',
            $error instanceof InvalidCsrfTokenException => 'session.expired',
            default => 'login.failed',
        };

        return null === $key ? null : $this->translator->trans($key);
    }

    private function notice(): ?string
    {
        $key = $this->flashes->take('notice');

        return null === $key ? null : $this->translator->trans($key);
    }
}
