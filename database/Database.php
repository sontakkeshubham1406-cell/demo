<?php
namespace App\Database;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static int $transactionDepth = 0;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $driver = DB_DRIVER;

            try {
                if ($driver === 'sqlite') {
                    $dbFile = DB_DATABASE;
                    
                    // On serverless platforms like Vercel, copy SQLite DB to writable /tmp
                    if (getenv('VERCEL') || isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
                        $tmpDb = sys_get_temp_dir() . '/ecommerce.sqlite';
                        if (!file_exists($tmpDb)) {
                            $sourceDb = file_exists(DB_DATABASE) ? DB_DATABASE : __DIR__ . '/ecommerce.sqlite';
                            if (file_exists($sourceDb)) {
                                @copy($sourceDb, $tmpDb);
                            }
                        }
                        $dbFile = $tmpDb;
                    }

                    $dbDir = dirname($dbFile);
                    if (!is_dir($dbDir)) {
                        @mkdir($dbDir, 0777, true);
                    }
                    $isNew = !file_exists($dbFile);
                    
                    self::$instance = new PDO("sqlite:" . $dbFile, null, null, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    
                    // Enable SQLite foreign key constraints
                    self::$instance->exec("PRAGMA foreign_keys = ON;");

                    if ($isNew) {
                        self::initializeSchema(self::$instance);
                    }
                } else {
                    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE . ";charset=utf8mb4";
                    self::$instance = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                }
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function initializeSchema(PDO $pdo): void
    {
        $schemaFile = __DIR__ . '/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
            
            // Run seeder automatically for new databases
            $seederFile = __DIR__ . '/seed.php';
            if (file_exists($seederFile)) {
                require_once $seederFile;
            }
        }
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function insert(string $table, array $data): int
    {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($data);

        return (int) self::getConnection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): bool
    {
        $setClauses = [];
        $params = [];

        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :set_{$key}";
            $params["set_{$key}"] = $value;
        }

        $setString = implode(', ', $setClauses);
        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";

        $mergedParams = array_merge($params, $whereParams);
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($mergedParams);
    }

    public static function beginTransaction(): bool
    {
        if (self::$transactionDepth === 0) {
            self::$transactionDepth++;
            return self::getConnection()->beginTransaction();
        }
        self::$transactionDepth++;
        return true;
    }

    public static function commit(): bool
    {
        if (self::$transactionDepth === 1) {
            self::$transactionDepth = 0;
            return self::getConnection()->commit();
        }
        if (self::$transactionDepth > 1) {
            self::$transactionDepth--;
            return true;
        }
        return false;
    }

    public static function rollBack(): bool
    {
        if (self::$transactionDepth > 0) {
            self::$transactionDepth = 0;
            return self::getConnection()->rollBack();
        }
        return false;
    }
}
