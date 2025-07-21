<?php

declare(strict_types=1);

namespace App\Application\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use PDO;

class ListApiKeysCommand extends Command
{
    protected static $defaultName = 'app:api-key:list';
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
            ->setDescription('Lists all API keys.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stmt = $this->db->query('SELECT * FROM api_keys');
        $keys = $stmt->fetchAll();

        if (empty($keys)) {
            $output->writeln('No API keys found.');
            return Command::SUCCESS;
        }

        foreach ($keys as $key) {
            $output->writeln('ID: ' . $key['id']);
            $output->writeln('Name: ' . $key['name']);
            $output->writeln('API Key: ' . $key['api_key']);
            $output->writeln('Remark: ' . $key['remark']);
            $output->writeln('Created At: ' . date('Y-m-d H:i:s', $key['created_at']));
            $output->writeln('Revoked At: ' . ($key['revoked_at'] ? date('Y-m-d H:i:s', $key['revoked_at']) : 'Not revoked'));
            $output->writeln('---');
        }

        return Command::SUCCESS;
    }
}
