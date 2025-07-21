<?php

declare(strict_types=1);

namespace App\Application\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use PDO;

class RevokeApiKeyCommand extends Command
{
    protected static $defaultName = 'app:api-key:revoke';
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
            ->setDescription('Revokes an API key.')
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the API key to revoke.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $revokedAt = time();

        $stmt = $this->db->prepare('UPDATE api_keys SET revoked_at = :revoked_at WHERE id = :id');
        $stmt->execute(['revoked_at' => $revokedAt, 'id' => $id]);

        if ($stmt->rowCount() > 0) {
            $output->writeln('API Key revoked successfully.');
        } else {
            $output->writeln('API Key not found.');
        }

        return Command::SUCCESS;
    }
}
