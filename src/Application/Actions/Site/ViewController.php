<?php

declare(strict_types=1);

namespace App\Application\Actions\Site;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Odan\Session\SessionInterface;
use Slim\Views\PhpRenderer;

class ViewController
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $this->session->set('authorized', true);
        $renderer = new PhpRenderer('../templates');
        return $renderer->render($response, "index.php");
    }
}
