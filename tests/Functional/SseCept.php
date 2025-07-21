<?php 

declare(strict_types=1);

use Tests\Support\FunctionalTester;

$I = new FunctionalTester($scenario);
$I->wantTo('Test that SSE endpoint fires after trigger');

$apiKey = 'test-api-key';

// 1. Trigger the doorbell
$I->haveHttpHeader('Authorization', 'Basic ' . $apiKey);
$I->sendGet('/api/trigger');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson(['code' => 200]);
$response = json_decode($I->grabResponse(), true);
$ringTime = $response['result']['ring_time'];

// 2. Check the SSE endpoint
$I->sendGet('/api/sse', ['last_check_time' => $ringTime - 1]);
$I->seeResponseCodeIs(200);
$I->seeHttpHeader('Content-Type', 'text/event-stream;charset=UTF-8');
$I->seeResponseContains("data: {\"ring_time\":{$ringTime}}");
