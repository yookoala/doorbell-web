<?php

declare(strict_types=1);

namespace App\Application\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use PDO;

class CreateApiKeyCommand extends Command
{
    protected static $defaultName = 'app:api-key:create';
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Creates a new API key.')
            ->setHelp('This command allows you to create a new API key.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the API key.')
            ->addArgument('remark', InputArgument::OPTIONAL, 'A remark for the API key.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $remark = $input->getArgument('remark');
        $apiKey = bin2hex(random_bytes(32));
        $createdAt = time();

        $stmt = $this->db->prepare('INSERT INTO api_keys (api_key, name, remark, created_at) VALUES (:api_key, :name, :remark, :created_at)');
        $stmt->execute([
            'api_key' => $apiKey,
            'name' => $name,
            'remark' => $remark,
            'created_at' => $createdAt,
        ]);

        $output->writeln('API Key created successfully:');
        $output->writeln('API Key: ' . $apiKey);

        return Command::SUCCESS;
    }
}
