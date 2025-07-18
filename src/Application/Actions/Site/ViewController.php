<?php

declare(strict_types=1);

namespace App\Application\Actions\Site;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

class ViewController
{
    public function __invoke(Request $request, Response $response): Response
    {
        $renderer = new PhpRenderer('../templates');
        return $renderer->render($response, "index.php");
    }
}
