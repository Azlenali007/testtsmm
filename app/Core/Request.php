<?php
declare(strict_types=1);

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $queryParams;
    private array $postParams;
    private array $serverParams;
    private array $headers;
    private ?array $jsonBody = null;

    public function __construct()
    {
        $this->serverParams = $_SERVER;
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $pos = strpos($uri, '?');
        $path = $pos !== false ? substr($uri, 0, $pos) : $uri;

        // Strip SCRIPT_NAME directory prefix if running in a subdirectory
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptDir !== '/' && $scriptDir !== '\\' && $scriptDir !== '.' && $scriptDir !== '') {
            $scriptDir = str_replace('\\', '/', $scriptDir);
            if (str_starts_with($path, $scriptDir)) {
                $path = substr($path, strlen($scriptDir));
            }
        }

        // Strip /index.php if directly prefixed in URI
        if (str_starts_with($path, '/index.php')) {
            $path = substr($path, strlen('/index.php'));
        }

        // Support explicit query parameter routing (e.g. ?route=/login or ?p=login)
        if (!empty($_GET['route']) && is_string($_GET['route'])) {
            $path = $_GET['route'];
        } elseif (!empty($_GET['page']) && is_string($_GET['page']) && ($path === '' || $path === '/')) {
            $path = '/' . ltrim($_GET['page'], '/');
        }

        // Strip .php or .html extension for standard pages (e.g. /login.html -> /login)
        if (preg_match('#^/([a-zA-Z0-9_\-/]+)\.(php|html)$#i', $path, $matches)) {
            $path = '/' . $matches[1];
        }

        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        $this->uri = $path;
        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->headers = $this->extractHeaders();
    }

    private function extractHeaders(): array
    {
        $headers = [];
        foreach ($this->serverParams as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $name = str_replace('_', '-', strtolower($key));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isAjax(): bool
    {
        return ($this->headers['x-requested-with'] ?? '') === 'XMLHttpRequest' ||
               str_contains($this->headers['accept'] ?? '', 'application/json');
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->postParams[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $json = $this->json();
        if ($json !== null && array_key_exists($key, $json)) {
            return $json[$key];
        }
        if (array_key_exists($key, $this->postParams)) {
            return $this->postParams[$key];
        }
        return $this->queryParams[$key] ?? $default;
    }

    public function all(): array
    {
        $json = $this->json();
        if ($json !== null) {
            return array_merge($this->queryParams, $this->postParams, $json);
        }
        return array_merge($this->queryParams, $this->postParams);
    }

    public function json(): ?array
    {
        if ($this->jsonBody !== null) {
            return $this->jsonBody;
        }

        $contentType = $this->headers['content-type'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->jsonBody = $decoded;
                return $this->jsonBody;
            }
        }

        return null;
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
}
