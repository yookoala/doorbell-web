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
        $is_authorized = false;
        if ($this->session->get('authorized') === true) {
            $is_authorized = true;
        }

        if (!$is_authorized) {
            $apiKey = null;
            // Check for API key in header
            $authHeader = $request->getHeaderLine('Authorization');
            if (preg_match('/^Basic\s+(.*)$/i', $authHeader, $matches)) {
                $apiKey = $matches[1];
            }

            // Check for API key in query parameter if not in header
            if (!$apiKey) {
                $queryParams = $request->getQueryParams();
                $apiKey = $queryParams['key'] ?? null;
            }

            if ($apiKey) {
                $stmt = $this->db->prepare('SELECT * FROM api_keys WHERE api_key = :api_key AND revoked_at IS NULL');
                $stmt->execute(['api_key' => $apiKey]);
                if ($stmt->fetch()) {
                    $is_authorized = true;
                }
            }
        }

        if (!$is_authorized) {
            $response->getBody()->write(json_encode(['code' => 403, 'error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        $stmt = $this->db->prepare('INSERT INTO doorbell_rings (ring_time) VALUES (:time)');
        $stmt->execute(['time' => time()]);
        
        $response->getBody()->write(json_encode(['code' => 200, 'result' => 'Message sent']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
