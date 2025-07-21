<?php

declare(strict_types=1);

namespace App\Application\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use PDO;

class UpdateApiKeyCommand extends Command
{
    protected static $defaultName = 'app:api-key:update';
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
            ->setDescription('Updates an API key.')
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the API key to update.')
            ->addArgument('name', InputArgument::OPTIONAL, 'The new name for the API key.')
            ->addArgument('remark', InputArgument::OPTIONAL, 'The new remark for the API key.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $name = $input->getArgument('name');
        $remark = $input->getArgument('remark');

        if (!$name && !$remark) {
            $output->writeln('You must provide a new name or remark.');
            return Command::INVALID;
        }

        $fields = [];
        $params = ['id' => $id];
        if ($name) {
            $fields[] = 'name = :name';
            $params['name'] = $name;
        }
        if ($remark) {
            $fields[] = 'remark = :remark';
            $params['remark'] = $remark;
        }

        $sql = 'UPDATE api_keys SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $output->writeln('API Key updated successfully.');
        } else {
            $output->writeln('API Key not found.');
        }

        return Command::SUCCESS;
    }
}
