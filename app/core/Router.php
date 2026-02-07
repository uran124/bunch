<?php
// app/core/Router.php

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $page, array $handler): void
    {
        $this->routes['GET'][$page] = $handler;
    }

    public function post(string $page, array $handler): void
    {
        $this->routes['POST'][$page] = $handler;
    }

    public function dispatch(string $page, string $method = 'GET')
    {
        $method = strtoupper($method);
        $handler = $this->routes[$method][$page] ?? null;

        if (!$handler) {
            $this->renderNotFound();
            return;
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
