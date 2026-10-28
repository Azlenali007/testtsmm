<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewareAliases = [];
    private array $groupStack = [];

    public function __construct()
    {
        $this->middlewareAliases = [
            'auth' => \App\Middleware\AuthMiddleware::class,
            'admin' => \App\Middleware\AdminMiddleware::class,
            'guest' => \App\Middleware\GuestMiddleware::class,
            'csrf' => \App\Middleware\CsrfMiddleware::class,
        ];
    }

    public function get(string $uri, array|callable $action, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, array|callable $action, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, array|callable $action, array $middleware = []): self
    {
        return $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function delete(string $uri, array|callable $action, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $uri, array|callable $action, array $middleware = []): self
    {
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $m = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $groupMiddleware = array_merge($groupMiddleware, $m);
            }
        }

        $fullUri = $prefix . '/' . trim($uri, '/');
        $fullUri = '/' . trim($fullUri, '/');
        if ($fullUri === '//') {
            $fullUri = '/';
        }

        $allMiddleware = array_merge($groupMiddleware, $middleware);

        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $fullUri,
            'action' => $action,
            'middleware' => $allMiddleware,
            'regex' => $this->compileRegex($fullUri),
        ];

        return $this;
    }

    private function compileRegex(string $uri): string
    {
        // Replace {param} with regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                $params = array_filter($matches, '\is_string', ARRAY_FILTER_USE_KEY);
                $params = array_map(function($val) {
                    return (is_string($val) && ctype_digit($val)) ? (int)$val : $val;
                }, $params);

                // Run Middleware Stack
                foreach ($route['middleware'] as $mwName) {
                    $mwClass = $this->middlewareAliases[$mwName] ?? $mwName;
                    if (class_exists($mwClass)) {
                        $mwInstance = new $mwClass();
                        if (method_exists($mwInstance, 'handle')) {
                            $mwInstance->handle($request);
                        }
                    }
                }

                // Execute Action
                $action = $route['action'];
                if (is_callable($action)) {
                    call_user_func_array($action, array_values($params));
                    return;
                }

                if (is_array($action) && count($action) === 2) {
                    [$class, $methodName] = $action;
                    if (class_exists($class)) {
                        $controller = new $class();
                        if (method_exists($controller, $methodName)) {
                            call_user_func_array([$controller, $methodName], array_values($params));
                            return;
                        }
                    }
                }

                throw new \RuntimeException("Invalid route action defined for [{$uri}]");
            }
        }

        // 404 Not Found
        http_response_code(404);
        if ($request->isAjax() || str_starts_with($uri, '/api/')) {
            Response::json(['success' => false, 'message' => 'API endpoint not found (404)'], 404);
        } else {
            View::render('errors/404', ['title' => 'Page Not Found'], 'main');
        }
    }
}
