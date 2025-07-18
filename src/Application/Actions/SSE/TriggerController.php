<?php

declare(strict_types=1);

namespace App\Application\Actions\SSE;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class TriggerController
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $stmt = $this->db->prepare('INSERT INTO doorbell_rings (ring_time) VALUES (:time)');
        $stmt->execute(['time' => time()]);
        
        $response->getBody()->write('Message sent');
        return $response;
    }
}
