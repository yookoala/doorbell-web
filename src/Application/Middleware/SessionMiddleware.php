<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($this->session instanceof SessionManagerInterface) {
            if (PHP_SAPI !== 'cli' && !$this->session->isStarted()) {
                $this->session->start();
            }
        }

        return $handler->handle($request);
    }
}
