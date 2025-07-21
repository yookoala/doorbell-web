<?php

declare(strict_types=1);

namespace App\Application\Actions\SSE;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class ListenController
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $last_check_time = (int)($request->getQueryParams()['last_check_time'] ?? 0);

        $response = $response->withHeader('Content-Type', 'text/event-stream')
                             ->withHeader('Cache-Control', 'no-cache');

        $stmt = $this->db->prepare('SELECT ring_time FROM doorbell_rings WHERE ring_time > :last_check_time ORDER BY ring_time ASC');
        $stmt->execute(['last_check_time' => $last_check_time]);
        $rings = $stmt->fetchAll();

        $body = $response->getBody();
        foreach ($rings as $ring) {
            $body->write("data: " . json_encode(['ring_time' => $ring['ring_time']]) . "\n\n");
        }

        // Send a comment to keep the connection alive if there is no message,
        // and to signal the client to close the connection and poll again.
        $body->write(": ping\n\n");
        
        return $response;
    }
}
