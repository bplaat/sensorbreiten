<?php

class Stations extends Model {
    public static function createTable(): PDOStatement {
        return Database::query('CREATE TABLE `stations` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT,

            `name` VARCHAR(191) NOT NULL,
            `key` CHAR(32) NOT NULL,
            `latitude` DECIMAL(10, 8) NOT NULL,
            `longitude` DECIMAL(11, 8) NOT NULL,

            PRIMARY KEY (`id`),
            UNIQUE (`key`)
        )');
    }

    // Genereate random API key
    public static function generateKey () {
        $session = bin2hex(random_bytes(16));
        if (static::count([ 'key' => $session ]) == 1) {
            return static::generateSession();
        }
        return $session;
    }

    // Get the latest measurement
    public static function latestMeasurement($where) {
        if (is_numeric($where)) $where = [ 'station_id' => $where ];
        if (is_object($where)) $where = [ 'station_id' => $where->{static::primaryKey()} ];
        foreach ($where as $key => $value) $wheres[] = '`' . $key . '` = ?';
        return Database::query('SELECT * FROM `measurements` WHERE ' . implode(' AND ', $wheres) . ' ORDER BY `created_at` DESC LIMIT 1', ...array_values($where));
    }

    // Get the measurements by day
    public static function measurementsByDay($where, $day) {
        if (is_numeric($where)) $where = [ 'station_id' => $where ];
        if (is_object($where)) $where = [ 'station_id' => $where->{static::primaryKey()} ];
        foreach ($where as $key => $value) $wheres[] = '`' . $key . '` = ?';
        return Database::query('SELECT * FROM `measurements` WHERE ' . implode(' AND ', $wheres) . ' AND `created_at` >= ? AND `created_at` < ? ORDER BY `created_at`', ...[ ...array_values($where), date('Y-m-d H:i:s', $day), date('Y-m-d H:i:s', $day + 24 * 60 * 60) ]);
    }

    // Get the latest outside measurement
    public static function latestOutsideMeasurement($where) {
        if (is_numeric($where)) $where = [ 'station_id' => $where ];
        if (is_object($where)) $where = [ 'station_id' => $where->{static::primaryKey()} ];
        foreach ($where as $key => $value) $wheres[] = '`' . $key . '` = ?';
        return Database::query('SELECT * FROM `outside_measurements` WHERE ' . implode(' AND ', $wheres) . ' ORDER BY `created_at` DESC LIMIT 1', ...array_values($where));
    }

    // Get the outside measurements by day
    public static function outsideMeasurementsByDay($where, $day) {
        if (is_numeric($where)) $where = [ 'station_id' => $where ];
        if (is_object($where)) $where = [ 'station_id' => $where->{static::primaryKey()} ];
        foreach ($where as $key => $value) $wheres[] = '`' . $key . '` = ?';
        return Database::query('SELECT * FROM `outside_measurements` WHERE ' . implode(' AND ', $wheres) . ' AND `created_at` >= ? AND `created_at` < ? ORDER BY `created_at`', ...[ ...array_values($where), date('Y-m-d H:i:s', $day), date('Y-m-d H:i:s', $day + 24 * 60 * 60) ]);
    }
}
