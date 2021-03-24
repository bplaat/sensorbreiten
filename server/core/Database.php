<?php

class Database {
    protected static ?PDO $pdo = null;

    protected static int $queryCount;

    public static function connect(string $dsn, string $user, string $password): void {
        static::$pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        static::$queryCount = 0;
    }

    public static function disconnect(): void {
        static::$pdo = null;
    }

    public static function queryCount(): int {
        return static::$queryCount;
    }

    public static function lastInsertId(): int {
        return static::$pdo->lastInsertId();
    }

    public static function query(string $query, ...$parameters): PDOStatement {
        $statement = static::$pdo->prepare($query);
        $statement->execute($parameters);
        static::$queryCount++;
        return $statement;
    }
}
