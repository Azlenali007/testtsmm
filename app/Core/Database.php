<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $driver = $config['default'] ?? 'mysql';

        try {
            if ($driver === 'sqlite') {
                $dbPath = $config['connections']['sqlite']['database'];
                $dir = dirname($dbPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                self::$instance = new PDO("sqlite:" . $dbPath, null, null, $config['connections']['sqlite']['options']);
                self::$instance->exec('PRAGMA foreign_keys = ON;');
            } else {
                $db = $config['connections']['mysql'];
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $db['host'], $db['port'], $db['database'], $db['charset']);
                self::$instance = new PDO($dsn, $db['username'], $db['password'], $db['options']);
            }
        } catch (PDOException $e) {
            // Attempt socket fallback if localhost fails
            if ($driver === 'mysql' && str_contains($e->getMessage(), 'Connection refused')) {
                try {
                    $db = $config['connections']['mysql'];
                    $dsn = sprintf('mysql:unix_socket=/run/mysqld/mysqld.sock;dbname=%s;charset=%s', $db['database'], $db['charset']);
                    self::$instance = new PDO($dsn, $db['username'], $db['password'], $db['options']);
                    return self::$instance;
                } catch (PDOException $e2) {
                    throw new RuntimeException("Database Connection Error: " . $e2->getMessage(), (int)$e2->getCode(), $e2);
                }
            }
            throw new RuntimeException("Database Connection Error: " . $e->getMessage(), (int)$e->getCode(), $e);
        }

        return self::$instance;
    }

    public static function pdo(): PDO
    {
        return self::connect();
    }

    public static function beginTransaction(): bool
    {
        return self::connect()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::connect()->commit();
    }

    public static function rollBack(): bool
    {
        if (self::connect()->inTransaction()) {
            return self::connect()->rollBack();
        }
        return false;
    }

    /**
     * Executes callback inside a secure database transaction with automatic rollback (nested-transaction safe)
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connect();
        $isNested = $pdo->inTransaction();

        if (!$isNested) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback($pdo);
            if (!$isNested) {
                $pdo->commit();
            }
            return $result;
        } catch (\Throwable $e) {
            if (!$isNested && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
