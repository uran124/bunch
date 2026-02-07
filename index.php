<?php
require_once __DIR__ . '/config.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/app/core/' . $class . '.php',
        __DIR__ . '/app/controllers/' . $class . '.php',
        __DIR__ . '/app/models/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$logDir = __DIR__ . '/storage/logs';
$logFile = $logDir . '/error.log';

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$requestId = str_replace('.', '', uniqid('req_', true));

$buildLogContext = static function () use ($requestId): array {
    $context = [
        'request_id' => $requestId,
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        'uri' => $_SERVER['REQUEST_URI'] ?? '',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ];

    if (class_exists('Auth') && Auth::check()) {
        $context['user_id'] = Auth::id();
    }

    return $context;
};

$logEvent = static function (string $level, string $context, ?Throwable $e = null, array $extra = []) use ($logFile, $buildLogContext): void {
    $payload = array_merge(
        $buildLogContext(),
        [
            'timestamp' => date('c'),
            'level' => $level,
            'context' => $context,
        ],
        $extra
    );

    if ($e !== null) {
        $payload['error'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }

    error_log(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, 3, $logFile);
};

set_exception_handler(static function (Throwable $e) use ($logEvent): void {
    $logEvent('error', 'Unhandled exception', $e);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo 'Ошибка: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        return;
    }

    if (class_exists('View')) {
        View::render('errors/maintenance', [
            'pageMeta' => [
                'title' => 'Сервис временно недоступен — Bunch flowers',
                'description' => 'Сервис временно недоступен. Попробуйте позже.',
            ],
        ]);
        return;
    }

    echo 'Сервис временно недоступен';
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(static function () use ($logEvent): void {
    $error = error_get_last();
    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($error['type'], $fatalTypes, true)) {
        return;
    }

    $logEvent('critical', 'Shutdown error', null, [
        'error' => [
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line'],
        ],
    ]);
});
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$isStaticPath = str_starts_with($path, 'static/');

$buildPageUrl = static function (string $page, array $query): string {
    $page = trim($page, '/');

    if ($page === '' || $page === 'home' || $page === 'index' || $page === 'index.php') {
        $path = '/';
    } elseif ($page === 'static' && !empty($query['slug'])) {
        $path = '/static/' . rawurlencode((string) $query['slug']);
        unset($query['slug']);
    } else {
        $path = '/' . $page;
    }

    if ($query) {
        $path .= '?' . http_build_query($query);
    }

    return $path;
};

if ($isStaticPath) {
    $slug = trim(substr($path, strlen('static/')), '/');
    if ($slug !== '') {
        $_GET['slug'] = urldecode($slug);
        $path = 'static';
    }
}

if ($path === 'static' && !$isStaticPath && !empty($_GET['slug']) && in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
    $redirectUrl = '/static/' . rawurlencode((string) $_GET['slug']);
    $remainingQuery = $_GET;
    unset($remainingQuery['slug'], $remainingQuery['page']);
    if ($remainingQuery) {
        $redirectUrl .= '?' . http_build_query($remainingQuery);
    }
    header('Location: ' . $redirectUrl, true, 301);
    exit;
}

if (str_starts_with($path, 'api/')) {
    require_once __DIR__ . '/api/index.php';
    exit;
}

Session::start();

$router = new Router();
$routesConfig = require __DIR__ . '/app/routes.php';
($routesConfig['register'])($router);

if (isset($_GET['page'])) {
    $requestedPage = (string) $_GET['page'];
    $queryParams = $_GET;
    unset($queryParams['page']);

    if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
        $targetUrl = $buildPageUrl($requestedPage, $queryParams);
        if ($targetUrl !== ($_SERVER['REQUEST_URI'] ?? '')) {
            header('Location: ' . $targetUrl, true, 301);
            exit;
        }
    }
}

$page = $_GET['page'] ?? $path;

if ($page === '' || $page === 'index' || $page === 'index.php') {
    $page = 'home';
}

$publicPages = $routesConfig['publicPages'];

if (!Auth::check() && !in_array($page, $publicPages, true)) {
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    $acceptHeader = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $isAjax = $requestedWith === 'xmlhttprequest' || str_contains($acceptHeader, 'application/json');

    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Требуется авторизация']);
        exit;
    }

    Session::set('auth_redirect', $_SERVER['REQUEST_URI'] ?? '/');
    header('Location: /login');
    exit;
}

if (Auth::check() && in_array($page, ['login', 'register', 'recover'], true)) {
    header('Location: /');
    exit;
}

$router->dispatch($page, $_SERVER['REQUEST_METHOD']);
