<?php

class Measurements extends Model {
    protected static array $dependencies = [ 'Stations' ];

    public static function createTable(): PDOStatement {
        return Database::query('CREATE TABLE `measurements` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT,

            `station_id` BIGINT UNSIGNED NOT NULL,
            `temperature` FLOAT NOT NULL,
            `humidity` FLOAT NOT NULL,
            `light` FLOAT NOT NULL,

            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (`id`),
            FOREIGN KEY (`station_id`) REFERENCES `stations`(`id`) ON DELETE CASCADE
        )');
    }
}
