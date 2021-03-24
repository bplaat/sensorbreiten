<?php

class Auth {
    protected static ?object $user = null;

    public static function createSession($userId): string {
        static::$user = null;

        $session = Sessions::generateSession();

        $ip = getIP();
        $ipInfo = json_decode(file_get_contents('https://ipinfo.io/' . $ip . '/json'));
        $ipLocation = explode(',', $ipInfo->loc ?? '');
        $user_agent = parse_user_agent();

        Sessions::insert([
            'user_id' => $userId,
            'session' => $session,
            'ip' => $ip,
            'ip_country' => $ipInfo->country ?? '?',
            'ip_city' => $ipInfo->city ?? '?',
            'ip_lat' => $ipLocation[0] ?? 0,
            'ip_lng' => $ipLocation[1] ?? 0,
            'platform' => $user_agent['platform'] ?? '?',
            'browser' => $user_agent['browser'] ?? '?',
            'browser_version' =>  $user_agent['version'] ?? '?',
            'expires_at' => date('Y-m-d H:i:s', time() + config('session.duration'))
        ]);

        $_COOKIE[config('session.cookie_name')] = $session;
        setcookie(config('session.cookie_name'), $session, [
            'expires' => time() + config('session.duration'),
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true
        ]);

        return $session;
    }

    public static function updateSession(?string $session = null): void {
        if ($session == null) {
            $session = static::session();
        }

        $ip = getIP();
        $ipInfo = json_decode(file_get_contents('https://ipinfo.io/' . $ip . '/json'));
        $ipLocation = explode(',', $ipInfo->loc ?? '');
        $user_agent = parse_user_agent();

        Sessions::update([
            'session' => $session
        ], [
            'ip' => $ip,
            'ip_country' => $ipInfo->country ?? '?',
            'ip_city' => $ipInfo->city ?? '?',
            'ip_lat' => $ipLocation[0] ?? 0,
            'ip_lng' => $ipLocation[1] ?? 0,
            'platform' => $user_agent['platform'] ?? '?',
            'browser' => $user_agent['browser'] ?? '?',
            'browser_version' =>  $user_agent['version'] ?? '?',

            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function revokeSession(?string $session = null): void {
        if ($session == null) {
            $session = static::session();
        }

        Sessions::update([
            'session' => $session
        ], [
            'expires_at' => date('Y-m-d H:i:s')
        ]);

        if ($session == static::session()) {
            static::$user = null;

            unset($_COOKIE[config('session.cookie_name')]);
            setcookie(config('session.cookie_name'), $session, [
                'expires' => time() - 60 * 60,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true
            ]);
        }
    }

    public static function login(string $email, string $password): bool {
        $userQuery = Users::select([ 'email' => $email ]);
        if ($userQuery->rowCount() == 1) {
            $user = $userQuery->fetch();
            if (password_verify($password, $user->password)) {
                static::createSession($user->id);
                return true;
            }
        }
        return false;
    }

    public static function logout(): void {
        static::revokeSession();
    }

    public static function session(): ?string {
        return $_COOKIE[config('session.cookie_name')] ?? null;
    }

    public static function user(): ?object {
        if (static::$user == null && static::session() != null) {
            $sessionQuery = Sessions::select([ 'session' => static::session() ]);
            if ($sessionQuery->rowCount() == 1) {
                $session = $sessionQuery->fetch();

                if (time() < strtotime($session->expires_at)) {
                    static::$user = Users::first($session->user_id);

                    if (time() >= strtotime($session->updated_at) + config('session.update_duration')) {
                        static::updateSession();
                    }
                } else {
                    static::revokeSession();
                }
            }
        }
        return static::$user;
    }

    public static function check(): bool {
        return static::user() != null;
    }

    public static function id(): int {
        return static::user()->id;
    }
}
