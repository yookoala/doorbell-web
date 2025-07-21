<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Actions\SSE\TriggerController;
use Codeception\Test\Unit;
use Odan\Session\SessionInterface;
use PDO;
use PDOStatement;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\Uri;
use Tests\Support\UnitTester;

class TriggerControllerTest extends Unit
{
    use ProphecyTrait;

    protected UnitTester $tester;

    public function testTriggerUnauthorized()
    {
        $request = $this->createRequest('GET', '/api/trigger');

        $session = $this->prophesize(SessionInterface::class);
        $session->get('authorized')->willReturn(false);

        $controller = new TriggerController($this->prophesize(PDO::class)->reveal(), $session->reveal());
        $response = $controller($request, new Response());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Unauthorized', (string) $response->getBody());
    }

    public function testTriggerAuthorizedBySession()
    {
        $request = $this->createRequest('GET', '/api/trigger');

        $session = $this->prophesize(SessionInterface::class);
        $session->get('authorized')->willReturn(true);

        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->execute(Argument::type('array'))->shouldBeCalled();

        $db = $this->prophesize(PDO::class);
        $db->prepare('INSERT INTO doorbell_rings (ring_time) VALUES (:ring_time)')->willReturn($stmt->reveal());
        $db->lastInsertId()->willReturn('1');

        $controller = new TriggerController($db->reveal(), $session->reveal());
        $response = $controller($request, new Response());

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $data['code']);
        $this->assertIsArray($data['result']);
        $this->assertIsInt($data['result']['ring_time']);
    }

    public function testTriggerAuthorizedByApiKeyInHeader()
    {
        $request = $this->createRequest('GET', '/api/trigger')
            ->withHeader('Authorization', 'Basic valid-api-key');

        $session = $this->prophesize(SessionInterface::class);
        $session->get('authorized')->willReturn(false);

        $stmtFetch = $this->prophesize(PDOStatement::class);
        $stmtFetch->execute(['api_key' => 'valid-api-key'])->shouldBeCalled();
        $stmtFetch->fetch()->willReturn(['api_key' => 'valid-api-key']);

        $stmtInsert = $this->prophesize(PDOStatement::class);
        $stmtInsert->execute(Argument::type('array'))->shouldBeCalled();

        $db = $this->prophesize(PDO::class);
        $db->prepare('SELECT * FROM api_keys WHERE api_key = :api_key AND revoked_at IS NULL')->willReturn($stmtFetch->reveal());
        $db->prepare('INSERT INTO doorbell_rings (ring_time) VALUES (:ring_time)')->willReturn($stmtInsert->reveal());
        $db->lastInsertId()->willReturn('2');

        $controller = new TriggerController($db->reveal(), $session->reveal());
        $response = $controller($request, new Response());

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $data['code']);
        $this->assertIsArray($data['result']);
        $this->assertIsInt($data['result']['ring_time']);
    }

    public function testTriggerAuthorizedByApiKeyInQuery()
    {
        $request = $this->createRequest(
            'GET',
            '/api/trigger?key=valid-api-key',
            ['HTTP_ACCEPT' => 'application/json'],
            [],
            ['QUERY_STRING' => 'key=valid-api-key']
        );

        $session = $this->prophesize(SessionInterface::class);
        $session->get('authorized')->willReturn(false);

        $stmtFetch = $this->prophesize(PDOStatement::class);
        $stmtFetch->execute(['api_key' => 'valid-api-key'])->shouldBeCalled();
        $stmtFetch->fetch()->willReturn(['api_key' => 'valid-api-key']);

        $stmtInsert = $this->prophesize(PDOStatement::class);
        $stmtInsert->execute(Argument::type('array'))->shouldBeCalled();

        $db = $this->prophesize(PDO::class);
        $db->prepare('SELECT * FROM api_keys WHERE api_key = :api_key AND revoked_at IS NULL')->willReturn($stmtFetch->reveal());
        $db->prepare('INSERT INTO doorbell_rings (ring_time) VALUES (:ring_time)')->willReturn($stmtInsert->reveal());
        $db->lastInsertId()->willReturn('3');

        $controller = new TriggerController($db->reveal(), $session->reveal());
        $response = $controller($request, new Response());

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals(200, $data['code']);
        $this->assertIsArray($data['result']);
        $this->assertIsInt($data['result']['ring_time']);
    }

    public function testTriggerUnauthorizedWithInvalidApiKey()
    {
        $request = $this->createRequest('GET', '/api/trigger?key=invalid-api-key');

        $session = $this->prophesize(SessionInterface::class);
        $session->get('authorized')->willReturn(false);

        $stmtFetch = $this->prophesize(PDOStatement::class);
        $stmtFetch->execute(['api_key' => 'invalid-api-key'])->shouldBeCalled();
        $stmtFetch->fetch()->willReturn(false);

        $db = $this->prophesize(PDO::class);
        $db->prepare('SELECT * FROM api_keys WHERE api_key = :api_key AND revoked_at IS NULL')->willReturn($stmtFetch->reveal());

        $controller = new TriggerController($db->reveal(), $session->reveal());
        $response = $controller($request, new Response());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Unauthorized', (string) $response->getBody());
    }

    private function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $path_parsed = parse_url($path);
        $uri = new Uri('', '', 80, $path_parsed['path'] ?? '', $path_parsed['query'] ?? '');
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new Request($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}
