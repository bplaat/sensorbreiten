<?php

abstract class Model {
    protected static ?string $table = null;

    protected static string $primaryKey = 'id';

    protected static array $dependencies = [];

    public static function table(): string {
        return static::$table ?? strtolower(static::class);
    }

    public static function primaryKey(): string {
        return static::$primaryKey;
    }

    public static function dependencies(): array {
        return static::$dependencies;
    }

    protected static ?array $modelNames = null;

    protected static array $createdModels = [];

    protected static array $droppedModels = [];

    protected static array $seededModels = [];

    public static function modelNames(): array {
        if (static::$modelNames == null) {
            $models = [];

            $searchModels = function (string $folder) use (&$searchModels, &$models) {
                $files = glob($folder . '/*');
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        searchModels($file);
                    } else {
                        $models[] = basename($file, '.php');
                    }
                }
            };
            $searchModels(ROOT . '/models');

            static::$modelNames = $models;
        }
        return static::$modelNames;
    }

    public static function createTableSecure(): bool {
        if (!in_array(static::class, static::$createdModels)) {
            static::$createdModels[] = static::class;

            foreach (static::dependencies() as $dependency) {
                ($dependency . '::createTableSecure')();
            }

            echo 'Create model ' . static::table() . "\n";
            static::createTable();

            return true;
        }
        return false;
    }

    public abstract static function createTable(): PDOStatement;

    public static function dropTableSecure(): bool {
        if (!in_array(static::class, static::$droppedModels)) {
            static::$droppedModels[] = static::class;

            foreach (static::modelNames() as $model) {
                if (in_array(static::class, $model::dependencies())) {
                    ($model . '::dropTableSecure')();
                }
            }

            echo 'Drop model ' . static::table() . "\n";
            static::dropTable();

            return true;
        }
        return false;
    }

    public static function dropTable(): PDOStatement {
        return Database::query('DROP TABLE IF EXISTS `' . static::table() . '`');
    }

    public static function seedTableSecure(): bool {
        if (!in_array(static::class, static::$seededModels)) {
            static::$seededModels[] = static::class;

            foreach (static::dependencies() as $dependency) {
                ($dependency . '::seedTableSecure')();
            }

            if (method_exists(static::class, 'seedTable')) {
                echo 'Seed model ' . static::table() . "\n";
                static::seedTable();
            }

            return true;
        }
        return false;
    }

    public static function select($where = null): PDOStatement {
        if (is_null($where)) {
            return Database::query('SELECT * FROM `' . static::table() . '`');
        } else {
            if (is_numeric($where)) $where = [ static::primaryKey() => $where ];
            if (is_object($where)) $where = [ static::primaryKey() => $where->{static::primaryKey()} ];
            foreach ($where as $key => $value) $wheres[] = '`' . $key . '` = ?';
            return Database::query('SELECT * FROM `' . static::table() . '` WHERE ' . implode(' AND ', $wheres), ...array_values($where));
        }
    }

    public static function insert(array $values): PDOStatement {
        foreach ($values as $key => $value) $keys[] = '`' . $key . '`';
        return Database::query('INSERT INTO `' . static::table() . '` (' . implode(', ', $keys) . ') ' .
            'VALUES (' . implode(', ', array_fill(0, count($values), '?')) . ')', ...array_values($values));
    }

    public static function update($where, array $values): PDOStatement {
        if (is_numeric($where)) $where = [ static::primaryKey() => $where ];
        if (is_object($where)) $where = [ static::primaryKey() => $where->{static::primaryKey()} ];
        foreach ($values as $key => $value) $sets[] = '`' . $key . '` = ?';
        foreach ($where as $key => $value) $wheres[] = '`' . $key . '` = ?';
        return Database::query('UPDATE `' . static::table() . '` SET ' . implode(', ', $sets) . ' ' .
            'WHERE ' . implode(' AND ', $wheres), ...array_values($values), ...array_values($where));
    }

    public static function delete($where): PDOStatement {
        if (is_numeric($where)) $where = [ static::primaryKey() => $where ];
        if (is_object($where)) $where = [ static::primaryKey() => $where->{static::primaryKey()} ];
        foreach ($where as $key => $value) $wheres[] = '`' . $key . '` = ?';
        return Database::query('DELETE FROM `' . static::table() . '` WHERE ' . implode(' AND ', $wheres), ...array_values($where));
    }

    public static function all($where = null): array {
        return static::select($where)->fetchAll();
    }

    public static function first($where = null): object {
        return static::select($where)->fetch();
    }

    public static function count($where = null): int {
        return static::select($where)->rowCount();
    }

    public static function create(array $values): object {
        static::insert($values);
        return static::select(Database::lastInsertId());
    }
}
