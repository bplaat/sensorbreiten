<?php

function guestMiddleware() {
    if (!Auth::check()) {
        return true;
    } else {
        return abort(404);
    }
}

function authMiddleware() {
    if (Auth::check()) {
        return true;
    } else {
        return Redirect::route('auth.login');
    }
}

function apiMiddleware() {
    define('IS_API', true);
    header('Access-Control-Allow-Origin: *');
    return true;
}

function moderatorMiddleware() {
    if (Auth::check() && Auth::user()->role >= Users::ROLE_MODERATOR) {
        return true;
    } else {
        return abort(403);
    }
}

function adminMiddleware() {
    if (Auth::check() && Auth::user()->role == Users::ROLE_ADMIN) {
        return true;
    } else {
        return abort(403);
    }
}

class Route {
    protected static array $routes = [];

    protected static array $routeMiddleware = [
        'guest' => 'guestMiddleware',
        'auth' => 'authMiddleware',
        'api' => 'apiMiddleware',
        'moderator' => 'moderatorMiddleware',
        'admin' => 'adminMiddleware'
    ];

    public static function match(array $methods, string $route, callable $callback): Route {
        $routeInstance = new Route();
        $routeInstance->methods = $methods;
        $routeInstance->route = $route;
        $routeInstance->callback = $callback;
        $routeInstance->name = null;
        $routeInstance->middleware = [];
        static::$routes[] = $routeInstance;
        return $routeInstance;
    }

    public static function get(string $route, callable $callback): Route {
        return static::match([ 'get' ], $route, $callback);
    }

    public static function post(string $route, callable $callback): Route {
        return static::match([ 'post' ], $route, $callback);
    }

    public static function any(string $route, callable $callback): Route {
        return static::match([ 'get', 'post' ], $route, $callback);
    }

    public static function view(string $route, string $view, array $data = []): Route {
        return static::get($route, function () use ($view, $data) {
            return view($view, $data);
        });
    }

    public function name(string $name): Route {
        $this->name = $name;
        return $this;
    }

    public function middleware($middleware): Route {
        $this->middleware = is_string($middleware) ? explode('|', $middleware) : $middleware;
        return $this;
    }

    protected static function handleResponse($response): void {
        if ($response instanceof Redirect) {
            $response->run();
        }

        if ($response instanceof View) {
            echo $response->render();
            exit;
        }

        if (is_string($response)) {
            echo $response;
            exit;
        }

        if (is_array($response) || is_object($response)) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }

    public static function run(): void {
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        $path = rtrim(preg_replace('#/+#', '/', strtok($_SERVER['REQUEST_URI'], '?')), '/');
        if ($path == '') $path = '/';

        foreach (static::$routes as $route) {
            if (
                in_array($method, $route->methods) &&
                preg_match('#^' . preg_replace('/{.*}/U', '([^/]*)', $route->route) . '$#', $path, $values)
            ) {
                array_shift($values);

                // Route model binding
                preg_match('/{(.*)}/U', $route->route, $names);
                array_shift($names);
                foreach ($names as $index => $name) {
                    if (class_exists($name)) {
                        $query = ($name . '::select')($values[$index]);
                        if ($query->rowCount() == 1) {
                            $values[$index] = $query->fetch();
                        } else {
                            continue;
                        }
                    }
                }

                // Run middleware
                foreach ($route->middleware as $middleware) {
                    if (is_string($middleware)) {
                        $middleware = static::$routeMiddleware[$middleware];
                    }

                    $response = $middleware(...$values);
                    if ($response === true) {
                        continue;
                    } else {
                        static::handleResponse($response);
                    }
                }

                // Run callback
                $response = call_user_func_array($route->callback, $values);
                static::handleResponse($response);
            }
        }

        // Return 404 error page
        static::handleResponse(abort(404));
    }

    public static function getRoute(string $name, ...$parameters): ?string {
        foreach (static::$routes as $route) {
            if (($route->name ?? '') == $name) {

                preg_match('/{(.*)}/U', $route->route, $names);
                array_shift($names);

                $values = [];
                for ($i = 0; $i < count($parameters); $i++) {
                    $values[] = $parameters[$i]->{($names[$i] . '::primaryKey')()};
                }

                $patterns = [];
                foreach ($names as $name) {
                    $patterns[] = '{' . $name . '}';
                }
                return str_replace($patterns, $values, $route->route);
            }
        }
        return null;
    }
}

function route(string $name, ...$parameters): ?string {
    return Route::getRoute($name, ...$parameters);
}
