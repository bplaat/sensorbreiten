<?php

class OutsideMeasurements extends Model {
    protected static ?string $table = 'outside_measurements';
    protected static array $dependencies = [ 'Stations' ];

    public static function createTable(): PDOStatement {
        return Database::query('CREATE TABLE `outside_measurements` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT,

            `station_id` BIGINT UNSIGNED NOT NULL,
            `temperature` FLOAT NOT NULL,
            `humidity` FLOAT NOT NULL,

            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (`id`),
            FOREIGN KEY (`station_id`) REFERENCES `stations`(`id`) ON DELETE CASCADE
        )');
    }
}
