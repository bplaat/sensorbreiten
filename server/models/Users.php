<?php

class Users extends Model {
    const ROLE_NORMAL = 0;
    const ROLE_MODERATOR = 1;
    const ROLE_ADMIN = 2;

    public static function createTable(): PDOStatement {
        return Database::query('CREATE TABLE `users` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT,

            `firstname` VARCHAR(191) NOT NULL,
            `lastname` VARCHAR(191) NOT NULL,
            `email` VARCHAR(191) NOT NULL,
            `password` VARCHAR(191) NOT NULL,
            `role` TINYINT NOT NULL DEFAULT ' . static::ROLE_NORMAL . ',

            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (`id`),
            UNIQUE (`email`)
        )');
    }

    public static function seedTable(): void {
        static::insert([
            'firstname' => 'Bastiaan',
            'lastname' => 'van der Plaat',
            'email' => 'bastiaan.v.d.plaat@gmail.com',
            'password' => password_hash('nederland', PASSWORD_DEFAULT),
            'role' => static::ROLE_ADMIN
        ]);
    }

    // Get all active sessions of a user
    public static function activeSessions($where): PDOStatement {
        if (is_numeric($where)) $where = [ 'user_id' => $where ];
        if (is_object($where)) $where = [ 'user_id' => $where->{static::primaryKey()} ];
        foreach ($where as $key => $value) $wheres[] = '`' . $key . '` = ?';
        return Database::query('SELECT * FROM `sessions` WHERE ' . implode(' AND ', $wheres) . ' AND `expires_at` > ? ORDER BY `updated_at` DESC', ...[ ...array_values($where), date('Y-m-d H:i:s') ]);
    }
}
