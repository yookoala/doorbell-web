<?php

declare(strict_types=1);

use App\Application\Actions\SSE\ListenController;
use App\Application\Actions\SSE\TriggerController;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use App\Application\Middleware\SessionMiddleware;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\get;
use App\Application\Actions\Site\ViewController;
use App\Application\Commands\CreateApiKeyCommand;
use App\Application\Commands\ListApiKeysCommand;
use App\Application\Commands\SearchApiKeyCommand;
use App\Application\Commands\UpdateApiKeyCommand;
use App\Application\Commands\RevokeApiKeyCommand;
use Slim\Views\PhpRenderer;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        SessionInterface::class => function (ContainerInterface $c) {
            return new PhpSession();
        },
        SessionMiddleware::class => autowire()->constructor(get(SessionInterface::class)),
        PhpRenderer::class => function (ContainerInterface $c) {
            return new PhpRenderer(__DIR__ . '/../templates');
        },
        CreateApiKeyCommand::class => autowire()->constructor(get('doorbell_db')),
        ListApiKeysCommand::class => autowire()->constructor(get('doorbell_db')),
        SearchApiKeyCommand::class => autowire()->constructor(get('doorbell_db')),
        UpdateApiKeyCommand::class => autowire()->constructor(get('doorbell_db')),
        RevokeApiKeyCommand::class => autowire()->constructor(get('doorbell_db')),
        ViewController::class => autowire()->constructor(get(SessionInterface::class), get(PhpRenderer::class), get('basePath')),
        'doorbell_db' => function (ContainerInterface $c) {
            $dbFile = __DIR__ . '/../var/db/doorbell.db';
            $pdo = new PDO('sqlite:' . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('CREATE TABLE IF NOT EXISTS doorbell_rings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ring_time INTEGER NOT NULL
            )');
            $pdo->exec('CREATE TABLE IF NOT EXISTS api_keys (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                api_key TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                remark TEXT,
                created_at INTEGER NOT NULL,
                revoked_at INTEGER
            )');
            return $pdo;
        },
        ListenController::class => autowire()->constructor(get('doorbell_db')),
        TriggerController::class => autowire()->constructor(get('doorbell_db'), get(SessionInterface::class)),
    ]);
};
