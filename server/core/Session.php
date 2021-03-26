<?php

class Session {
    protected static array $flash = [];

    public static function start(): void {
        // Start PHP session
        session_name(config('session.cookie_name') . '_short');
        session_start();

        // Clean flashed values
        static::$flash = [];
        foreach (static::get('_flash', []) as $key) {
            static::$flash[$key] = static::get($key);
            unset($_SESSION[$key]);
        }
        static::set('_flash', []);

        // Create CSRF token if it not exists
        if (Session::get('_csrf_token') == null) {
            Session::set('_csrf_token', bin2hex(random_bytes(16)));
        }
    }

    public static function get(string $key, $default = null) {
        if (isset(static::$flash[$key])) {
            return static::$flash[$key];
        } elseif (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return $default;
        }
    }

    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public static function flash(string $key, $value): void {
        $_SESSION[$key] = $value;
        $_SESSION['_flash'][] = $key;
    }

    public static function remove(string $key): void {
        unset(static::$flash[$key]);
        unset($_SESSION[$key]);
        $_SESSION['_flash'] = array_diff($_SESSION['_flash'], [ $key ]);
    }
}

function session(string $key, $default = null) {
    return Session::get($key, $default);
}

function request(string $key, $default = null) {
    return $_REQUEST[$key] ?? $default;
}

function old(string $key, $default = null) {
    return session('_old_' . $key, $default);
}

function hasErrors(string $key): bool {
    return isset(session('_errors')[$key]);
}

function errors(string $key): array {
    return session('_errors')[$key] ?? [];
}
