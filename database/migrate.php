#!/usr/bin/env php
<?php
/**
 * Database Migration CLI Tool
 *
 * Usage:
 *   php migrate.php migrate          Run all pending migrations
 *   php migrate.php rollback         Rollback the last batch
 *   php migrate.php status           Show migration status
 *   php migrate.php create <name>    Create a new migration
 */

// Load configuration
$configFile = dirname(__DIR__) . '/src/config/database.php';
if (!file_exists($configFile)) {
    echo "Error: Database configuration not found. Run the installer first.\n";
    exit(1);
}

$config = require $configFile;

// Connect to database
try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $db = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    echo "Error: Could not connect to database: {$e->getMessage()}\n";
    exit(1);
}

// Load migration class
require_once __DIR__ . '/Migration.php';

$migration = new Migration($db);

// Parse command
$command = $argv[1] ?? 'status';

switch ($command) {
    case 'migrate':
        echo "Running migrations...\n\n";
        $results = $migration->migrate();

        if (empty($results)) {
            echo "Nothing to migrate.\n";
        } else {
            foreach ($results as $result) {
                $status = $result['status'] === 'success' ? '✓' : '✗';
                echo "[{$status}] {$result['migration']}: {$result['message']}\n";
            }
        }
        break;

    case 'rollback':
        echo "Rolling back last batch...\n\n";
        $results = $migration->rollback();

        if (empty($results)) {
            echo "Nothing to rollback.\n";
        } else {
            foreach ($results as $result) {
                $status = $result['status'] === 'success' ? '✓' : '✗';
                echo "[{$status}] {$result['migration']}: {$result['message']}\n";
            }
        }
        break;

    case 'status':
        echo "Migration Status:\n\n";
        $results = $migration->status();

        if (empty($results)) {
            echo "No migrations found.\n";
        } else {
            foreach ($results as $result) {
                $status = $result['status'] === 'executed' ? '✓ Executed' : '○ Pending ';
                echo "[{$status}] {$result['migration']}\n";
            }
        }
        break;

    case 'create':
        $name = $argv[2] ?? null;
        if (!$name) {
            echo "Error: Please provide a migration name.\n";
            echo "Usage: php migrate.php create <name>\n";
            exit(1);
        }

        // Sanitize name
        $name = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($name));
        $path = Migration::create($name);

        echo "Migration created: {$path}\n";
        break;

    default:
        echo "Unknown command: {$command}\n\n";
        echo "Available commands:\n";
        echo "  migrate   Run all pending migrations\n";
        echo "  rollback  Rollback the last batch\n";
        echo "  status    Show migration status\n";
        echo "  create    Create a new migration\n";
        exit(1);
}

echo "\nDone.\n";
