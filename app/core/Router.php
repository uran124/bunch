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

        echo <<<HTML
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница не найдена — Bunch flowers</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, sans-serif;
            display: grid;
            place-items: center;
            min-height: 100vh;
            background: radial-gradient(circle at 20% 20%, #ffe9f0 0, transparent 25%),
                        radial-gradient(circle at 80% 0%, #e6f3ff 0, transparent 30%),
                        #f8fafc;
            color: #0f172a;
            text-align: center;
        }
        h1 {
            margin: 0 0 12px;
            font-size: 1.5rem;
        }
        p {
            margin: 0 0 20px;
            line-height: 1.5;
        }
        a {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 12px;
            background: #111827;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.15);
        }
        a:hover {
            background: #0f172a;
            transform: translateY(-1px);
        }
        small {
            display: block;
            margin-top: 14px;
            color: #475569;
        }
    </style>
</head>
<body>
    <main>
        <h1>Страница не найдена</h1>
        <p>Похоже, ссылка не существует или была перемещена.<br>Перейдите на главную страницу Bunch flowers.</p>
        <a href="/?page=home">На главную</a>
        <small>Если проблема повторяется, напишите нам в поддержку.</small>
    </main>
</body>
</html>
HTML;
    }
}
