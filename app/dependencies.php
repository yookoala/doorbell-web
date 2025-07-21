<?php

declare(strict_types=1);

use App\Application\Actions\SSE\ListenController;
use App\Application\Actions\SSE\TriggerController;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\get;

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
        TriggerController::class => autowire()->constructor(get('doorbell_db')),
    ]);
};
