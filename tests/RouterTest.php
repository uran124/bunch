<?php

use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        http_response_code(200);
        if (session_status() === PHP_SESSION_ACTIVE) {
            Session::destroy();
        }

        $_SESSION = [];
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        Session::start();
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            Session::destroy();
        }

        $_SESSION = [];
    }

    public function testDispatchReturnsControllerResponse(): void
    {
        $router = new Router();
        $router->get('hello', [RouterTestStubController::class, 'hello']);

        $result = $router->dispatch('hello');

        $this->assertSame('ok:hello', $result);
    }

    public function testDispatchRendersNotFound(): void
    {
        $router = new Router();

        ob_start();
        $router->dispatch('missing');
        $output = ob_get_clean();

        $this->assertSame(404, http_response_code());
        $this->assertStringContainsString('Страница не найдена', $output);
        $this->assertStringContainsString('На главную', $output);
    }

    public function testDispatchThrowsWhenControllerMissing(): void
    {
        $router = new Router();
        $router->get('broken', ['UnknownController', 'index']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Контроллер UnknownController не найден');

        $router->dispatch('broken');
    }

    public function testDispatchThrowsWhenActionMissing(): void
    {
        $router = new Router();
        $router->get('broken', [RouterTestStubController::class, 'missing']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Метод missing не найден в RouterTestStubController');

        $router->dispatch('broken');
    }

    public function testDispatchRedirectsGuestsFromAuthenticatedRoute(): void
    {
        $router = new Router();
        $router->get('account', [RouterTestStubController::class, 'hello'], ['auth']);
        $_SERVER['REQUEST_URI'] = '/account';

        $router->dispatch('account');

        $this->assertSame('/account', Session::get('auth_redirect'));
    }

    public function testDispatchBlocksAuthenticatedUserFromGuestRoute(): void
    {
        $router = new Router();
        $router->get('login', [RouterTestStubController::class, 'hello'], ['guest']);
        Auth::login(7, 'customer');

        $result = $router->dispatch('login');

        $this->assertNull($result);
    }

    public function testDispatchAllowsRequiredRole(): void
    {
        $router = new Router();
        $router->get('admin', [RouterTestStubController::class, 'hello'], ['role:admin,manager']);
        Auth::login(7, 'admin');

        $result = $router->dispatch('admin');

        $this->assertSame('ok:hello', $result);
    }

    public function testDispatchRejectsForbiddenRole(): void
    {
        $router = new Router();
        $router->get('promo', [RouterTestStubController::class, 'hello'], ['forbid:wholesale']);
        Auth::login(7, 'wholesale');

        $result = $router->dispatch('promo');

        $this->assertNull($result);
        $this->assertSame(403, http_response_code());
    }
}

class RouterTestStubController
{
    public function hello(): string
    {
        return 'ok:hello';
    }
}
