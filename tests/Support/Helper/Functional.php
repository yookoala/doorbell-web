<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\TestInterface;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Functional extends \Codeception\Module
{
    public function _before(TestInterface $test)
    {
        // Initialize the test database
        $testDbPath = $this->getModule('Db')->_getConfig('dsn');
        if (!$testDbPath) {
            throw new \RuntimeException('Test database path is not configured.');
        }
        $testDbPath = str_replace('sqlite:', '', $testDbPath);
        $testDbPath = trim($testDbPath);
        if (!file_exists($testDbPath)) {
            throw new \RuntimeException("Test database file not found at: $testDbPath");
        }
        // Ensure the database directory exists
        $dbDir = dirname($testDbPath);
        if (!is_dir($dbDir)) {
            if (!mkdir($dbDir, 0777, true) && !is_dir($dbDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dbDir));
            }
        }
        // Ensure the database file is writable
        if (!is_writable($testDbPath)) {
            throw new \RuntimeException("Test database file is not writable: $testDbPath");
        }
        
        // backup the current database file
        $originalDbPath = 'var/db/doorbell.db';
        $backupDbPath = 'var/db/doorbell_backup.db';
        if (file_exists($originalDbPath)) {
            copy($originalDbPath, $backupDbPath);
        }

        // Replace the database with a test database
        if (file_exists($testDbPath)) {
            copy($testDbPath, $originalDbPath);
        } else {
            throw new \RuntimeException("Test database file not found at: $testDbPath");
        }
    }

    public function _after(TestInterface $test)
    {
        // Restore the original database file after the test
        $originalDbPath = 'var/db/doorbell.db';
        $backupDbPath = 'var/db/doorbell_backup.db';
        if (file_exists($backupDbPath)) {
            copy($backupDbPath, $originalDbPath);
            unlink($backupDbPath); // Remove the backup file
        } else {
            throw new \RuntimeException("Backup database file not found: $backupDbPath");
        }
    }
}
