<?php

class Sessions extends Model {
    protected static array $dependencies = [ 'Users' ];

    public static function createTable(): PDOStatement {
        return Database::query('CREATE TABLE `sessions` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT,

            `user_id` BIGINT UNSIGNED NOT NULL,
            `session` VARCHAR(32) NOT NULL,
            `ip` VARCHAR(32) NOT NULL,
            `ip_country` VARCHAR(2) NOT NULL,
            `ip_city` VARCHAR(191) NOT NULL,
            `ip_lat` DECIMAL(10, 8) NOT NULL,
            `ip_lng` DECIMAL(11, 8) NOT NULL,
            `platform` VARCHAR(32) NOT NULL,
            `browser` VARCHAR(32) NOT NULL,
            `browser_version` VARCHAR(16) NOT NULL,
            `expires_at` DATETIME NOT NULL,

            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (`id`),
            UNIQUE (`session`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        )');
    }

    public static function generateSession(): string {
        $session = bin2hex(random_bytes(16));
        if (static::count([ 'session' => $session ]) == 1) {
            return static::generateSession();
        }
        return $session;
    }
}
