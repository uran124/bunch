<?php

use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        http_response_code(200);
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
}

class RouterTestStubController
{
    public function hello(): string
    {
        return 'ok:hello';
    }
}
