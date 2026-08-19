<?php
// SPDX-License-Identifier: GPL-3.0-or-later
// PVE-Importer Copyright (C) 2026 Robbie Loewen-Ross

namespace App\Security;

use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface {
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function start(Request $request, ?AuthenticationException $authException = null): Response {
        if ($request->attributes->get('_route') == 'login') {
            return new Response('Authentication Required.', Response::HTTP_UNAUTHORIZED);
        }

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }
}
?>