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
    private PhpRenderer $renderer;
    private string $basePath;

    public function __construct(SessionInterface $session, PhpRenderer $renderer, string $basePath)
    {
        $this->session = $session;
        $this->renderer = $renderer;
        $this->basePath = $basePath;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $this->session->set('authorized', true);
        return $this->renderer->render($response, "index.php", ['basePath' => $this->basePath]);
    }
}
