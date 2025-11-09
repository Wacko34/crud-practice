<?php

class Database
{
    private static ?self $instance = null;
    private ?\PDO $connection = null;

    protected function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['dbname'],
            $config['charset']
        );

        $this->connection = new \PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
    }

    protected function __clone() {}

    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize singleton');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public static function connection(): \PDO
    {
        return self::getInstance()->connection;
    }

    public static function prepare(string $statement): \PDOStatement
    {
        return self::connection()->prepare($statement);
    }
}