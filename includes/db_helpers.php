<?php

function dbDriver(PDO $db): string
{
    return $db->getAttribute(PDO::ATTR_DRIVER_NAME);
}

function dbSqlId(PDO $db): string
{
    return dbDriver($db) === 'mysql'
        ? 'id INT AUTO_INCREMENT PRIMARY KEY'
        : 'id INTEGER PRIMARY KEY AUTOINCREMENT';
}

function dbInsertIgnore(PDO $db, string $table, string $columns, string $placeholders, array $values): void
{
    $verb = dbDriver($db) === 'mysql' ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
    $db->prepare("{$verb} INTO {$table} ({$columns}) VALUES ({$placeholders})")->execute($values);
}

function dbReplaceSetting(PDO $db, string $key, string $value): void
{
    if (dbDriver($db) === 'mysql') {
        $db->prepare('REPLACE INTO cms_settings (`key`, value) VALUES (?, ?)')->execute([$key, $value]);
    } else {
        $db->prepare('INSERT OR REPLACE INTO cms_settings (key, value) VALUES (?, ?)')->execute([$key, $value]);
    }
}

function ensureColumn(PDO $db, string $table, string $column, string $definition): void
{
    try {
        $db->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
    } catch (PDOException $e) {
        // Column already exists on existing installations.
    }
}

function dbConnect(): PDO
{
    if (DB_DRIVER === 'mysql') {
        $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
        $name = defined('DB_NAME') ? DB_NAME : 'unity_cms';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';
        $db = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass);
    } else {
        $db_path = __DIR__ . '/../database.sqlite';
        $db = new PDO('sqlite:' . $db_path);
        $db->exec('PRAGMA foreign_keys = ON;');
    }

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $db;
}

function sqliteDatabasePath(): string
{
    return __DIR__ . '/../database.sqlite';
}

function isFreshDatabase(?PDO $db): bool
{
    if (DB_DRIVER === 'mysql') {
        if ($db === null) {
            return false;
        }
        try {
            $count = (int) $db->query('SELECT COUNT(*) FROM cms_settings')->fetchColumn();
            return $count === 0;
        } catch (PDOException $e) {
            return true;
        }
    }

    return !file_exists(sqliteDatabasePath());
}
