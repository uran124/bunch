<?php

use PHPUnit\Framework\TestCase;

final class CartCheckoutIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            Session::destroy();
        }

        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        Session::start();
        http_response_code(200);
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            Session::destroy();
        }

        $_SESSION = [];
        $_POST = [];
    }

    public function testCheckoutRejectsGuestBeforeAnyCartProcessing(): void
    {
        $controller = new CartCheckoutTestController(new CartCheckoutTestCart([]));

        ob_start();
        $controller->checkout();
        $output = ob_get_clean();

        $this->assertSame(401, http_response_code());
        $this->assertStringContainsString('Требуется авторизация', $output);
    }

    public function testCheckoutRejectsEmptyCartForAuthenticatedUser(): void
    {
        Auth::login(77, 'customer');
        $controller = new CartCheckoutTestController(new CartCheckoutTestCart([]));

        ob_start();
        $controller->checkout();
        $output = ob_get_clean();

        $this->assertSame(400, http_response_code());
        $this->assertStringContainsString('Корзина пуста', $output);
    }
}

final class CartCheckoutTestController extends CartController
{
    public function __construct(private Cart $cart)
    {
    }

    protected function makeCart(): Cart
    {
        return $this->cart;
    }
}

final class CartCheckoutTestCart extends Cart
{
    public function __construct(private array $items)
    {
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
