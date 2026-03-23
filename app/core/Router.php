<?php
// app/core/Router.php

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $page, array $handler, array $middleware = []): void
    {
        $this->routes['GET'][$page] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function post(string $page, array $handler, array $middleware = []): void
    {
        $this->routes['POST'][$page] = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $page, string $method = 'GET')
    {
        $method = strtoupper($method);
        $route = $this->routes[$method][$page] ?? null;

        if (!$route) {
            $this->renderNotFound();
            return;
        }

        $handler = $route['handler'] ?? null;
        $middleware = $route['middleware'] ?? [];

        $accessMiddleware = new RouteAccessMiddleware();
        if (!$accessMiddleware->handle($middleware)) {
            return null;
        }

        $csrfMiddleware = new RouteCsrfMiddleware();
        if (!$csrfMiddleware->handle($middleware, $method)) {
            return null;
        }

        [$controllerClass, $action] = $handler;

        if (!class_exists($controllerClass)) {
            throw new RuntimeException("Контроллер {$controllerClass} не найден");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            throw new RuntimeException("Метод {$action} не найден в {$controllerClass}");
        }

        return $controller->{$action}();
    }

    public function getRoute(string $page, string $method = 'GET'): ?array
    {
        $method = strtoupper($method);

        return $this->routes[$method][$page] ?? null;
    }

    private function renderNotFound(): void
    {
        http_response_code(404);

        View::render('errors/404', [
            'pageMeta' => [
                'title' => 'Страница не найдена — Bunch flowers',
                'description' => 'Запрашиваемая страница не найдена.',
            ],
        ]);
    }
}

class RouteCsrfMiddleware
{
    public function handle(array $middleware, string $method): bool
    {
        if (!Csrf::shouldProtectMethod($method)) {
            return true;
        }

        if (in_array('csrf:off', $middleware, true)) {
            return true;
        }

        if (Csrf::isValidRequest()) {
            return true;
        }

        return $this->denyInvalidToken();
    }

    private function denyInvalidToken(): bool
    {
        if ($this->isAjaxRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Недействительный CSRF-токен']);
            return false;
        }

        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');
        echo 'Сессия формы устарела. Обновите страницу и повторите действие.';
        return false;
    }

    private function isAjaxRequest(): bool
    {
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $acceptHeader = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));

        return $requestedWith === 'xmlhttprequest' || str_contains($acceptHeader, 'application/json');
    }
}

class RouteAccessMiddleware
{
    public function handle(array $middleware): bool
    {
        foreach ($middleware as $rule) {
            if ($rule === 'guest' && !$this->allowGuestOnly()) {
                return false;
            }

            if ($rule === 'auth' && !$this->requireAuthenticatedUser()) {
                return false;
            }

            if (str_starts_with($rule, 'role:') && !$this->requireRole($this->parseRoles($rule))) {
                return false;
            }

            if (str_starts_with($rule, 'forbid:') && !$this->forbidRole($this->parseRoles($rule))) {
                return false;
            }
        }

        return true;
    }

    private function allowGuestOnly(): bool
    {
        if (!Auth::check()) {
            return true;
        }

        header('Location: /');
        return false;
    }

    private function requireAuthenticatedUser(): bool
    {
        if (Auth::check()) {
            return true;
        }

        if ($this->isAjaxRequest()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Требуется авторизация']);
            return false;
        }

        Session::set('auth_redirect', $_SERVER['REQUEST_URI'] ?? '/');
        header('Location: /login');
        return false;
    }

    private function requireRole(array $roles): bool
    {
        if (!$this->requireAuthenticatedUser()) {
            return false;
        }

        if (Auth::hasRole(...$roles)) {
            return true;
        }

        return $this->denyForbidden();
    }

    private function forbidRole(array $roles): bool
    {
        if (!Auth::check()) {
            return true;
        }

        if (!Auth::hasRole(...$roles)) {
            return true;
        }

        return $this->denyForbidden();
    }

    private function denyForbidden(): bool
    {
        if ($this->isAjaxRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Недостаточно прав']);
            return false;
        }

        header('Location: /', true, 403);
        return false;
    }

    private function parseRoles(string $rule): array
    {
        [, $rolesRaw] = explode(':', $rule, 2);
        $roles = array_map(
            static fn (string $role): string => trim($role),
            explode(',', $rolesRaw)
        );

        return array_values(array_filter($roles, static fn (string $role): bool => $role !== ''));
    }

    private function isAjaxRequest(): bool
    {
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $acceptHeader = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));

        return $requestedWith === 'xmlhttprequest' || str_contains($acceptHeader, 'application/json');
    }
}
