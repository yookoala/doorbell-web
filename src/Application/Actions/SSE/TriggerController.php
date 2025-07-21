<?php

declare(strict_types=1);

namespace App\Application\Actions\SSE;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Odan\Session\SessionInterface;

class TriggerController
{
    private $db;
    private SessionInterface $session;

    public function __construct(PDO $db, SessionInterface $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        if ($this->session->get('authorized') !== true) {
            $response->getBody()->write(json_encode(['code' => 403, 'error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $stmt = $this->db->prepare('INSERT INTO doorbell_rings (ring_time) VALUES (:time)');
        $stmt->execute(['time' => time()]);
        
        $response->getBody()->write(json_encode(['code' => 200, 'result' => 'Message sent']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
