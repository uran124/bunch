<?php

use PHPUnit\Framework\TestCase;

final class RouteConfigurationIntegrationTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            Session::destroy();
        }

        $_SESSION = [];
        $_POST = [];
        Session::start();

        $this->router = new Router();
        $routes = require __DIR__ . '/../app/routes.php';
        ($routes['register'])($this->router);
    }

    public function testPublicPagesRemainPublic(): void
    {
        $this->assertSame([], $this->routeMiddleware('home'));
        $this->assertSame([], $this->routeMiddleware('policy'));
        $this->assertSame([], $this->routeMiddleware('payment-result'));
        $this->assertSame([], $this->routeMiddleware('support-messages'));
    }

    public function testAuthPagesAreGuestOnly(): void
    {
        $this->assertSame(['guest'], $this->routeMiddleware('login'));
        $this->assertSame(['guest'], $this->routeMiddleware('register'));
        $this->assertSame(['guest'], $this->routeMiddleware('recover'));
        $this->assertSame(['guest'], $this->routeMiddleware('login', 'POST'));
    }

    public function testCustomerFlowsRequireAuthentication(): void
    {
        $this->assertSame(['auth'], $this->routeMiddleware('account'));
        $this->assertSame(['auth'], $this->routeMiddleware('cart'));
        $this->assertSame(['auth'], $this->routeMiddleware('cart-checkout', 'POST'));
        $this->assertSame(['auth'], $this->routeMiddleware('order-payment', 'POST'));
    }

    public function testAdminAndWholesaleRoutesCarryRoleMiddleware(): void
    {
        $this->assertSame(['role:admin'], $this->routeMiddleware('admin'));
        $this->assertSame(['role:admin'], $this->routeMiddleware('admin-user-role', 'POST'));
        $this->assertSame(['role:admin,wholesale'], $this->routeMiddleware('wholesale'));
        $this->assertSame(['forbid:wholesale'], $this->routeMiddleware('promo'));
    }

    public function testPaymentCallbackPostIsExplicitlyExcludedFromCsrf(): void
    {
        $this->assertSame(['csrf:off'], $this->routeMiddleware('payment-result', 'POST'));
    }

    private function routeMiddleware(string $page, string $method = 'GET'): array
    {
        $route = $this->router->getRoute($page, $method);

        $this->assertNotNull($route, sprintf('Route %s %s must be registered', strtoupper($method), $page));

        return $route['middleware'] ?? [];
    }
}
