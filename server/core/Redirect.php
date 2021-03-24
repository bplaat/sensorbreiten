<?php

class Redirect {
    public static function to(string $path): Redirect {
        $redirect = new Redirect();
        $redirect->path = $path;
        $redirect->flash = [];
        return $redirect;
    }

    public static function toRoute(string $route, ...$parameters): Redirect {
        return static::to(route($route, ...$parameters));
    }

    public static function back(): Redirect {
        return static::to($_SERVER['HTTP_REFERER']);
    }

    public function with(string $key, $value): Redirect {
        $this->flash[$key] = $value;
        return $this;
    }

    public function withInput(): Redirect {
        foreach ($_REQUEST as $key => $value) {
            $this->flash['_old_' . $key] = $value;
        }
        return $this;
    }

    public function withErrors(array $errors): Redirect {
        $this->flash['_errors'] = $errors;
        return $this;
    }

    public function run(): void {
        foreach ($this->flash as $key => $value) {
            Session::flash($key, $value);
        }

        header('Location: ' . $this->path);
        exit;
    }
}
