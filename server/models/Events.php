<?php

class Events extends Model {
    protected static array $dependencies = [ 'Stations' ];

    const TYPE_LED = 0;
    const TYPE_BEEPER = 1;

    public static function createTable(): PDOStatement {
        return Database::query('CREATE TABLE `events` (
            `id` INT UNSIGNED AUTO_INCREMENT,

            `station_id` BIGINT UNSIGNED NOT NULL,
            `name` VARCHAR(191) NOT NULL,
            `trigger` TEXT NOT NULL,
            `type` INT UNSIGNED NOT NULL DEFAULT ' . static::TYPE_LED . ',
            `frequency` INT UNSIGNED NOT NULL,
            `duration` INT UNSIGNED NOT NULL,
            `active` BOOLEAN NOT NULL DEFAULT 0,

            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY (`id`),
            FOREIGN KEY (`station_id`) REFERENCES `stations`(`id`) ON DELETE CASCADE
        )');
    }

    // Validate trigger
    public static function validateTrigger($key, $niceKey, $value, $fail) {
        ob_start();
        try {
            $_trigger = $value;
            $absolute_time = 0;
            $time = 0;
            $temperature = 0;
            $humidity = 0;
            $light = 0;
            $outside_temperature = 0;
            $outside_humidity = 0;
            eval('unset($_trigger); return ' . $_trigger . ';');
        } catch (Error $error) {
            $fail('The ' . $niceKey . ' field contains syntax errors');
        }
        $output = ob_get_contents();
        ob_end_clean();

        if ($output != '') {
            $fail('The ' . $niceKey . ' field creates echo output');
        }
    }
}
