<?php

use PHPUnit\Framework\TestCase;

final class SessionAuthTest extends TestCase
{
    protected function setUp(): void
    {
        $this->resetSession();
    }

    protected function tearDown(): void
    {
        $this->resetSession();
    }

    private function resetSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            Session::destroy();
        }

        // Ensure clean superglobal for deterministic assertions
        $_SESSION = [];
        $_POST = [];
        Session::start();
    }

    public function testSetAndGetWithDefault(): void
    {
        Session::set('foo', 'bar');

        $this->assertSame('bar', Session::get('foo'));
        $this->assertSame('fallback', Session::get('missing', 'fallback'));
    }

    public function testRemoveClearsKey(): void
    {
        Session::set('temp', 123);
        Session::remove('temp');

        $this->assertNull(Session::get('temp'));
    }

    public function testDestroyResetsSessionState(): void
    {
        Session::set('key', 'value');
        Session::destroy();

        $this->assertSame(PHP_SESSION_NONE, session_status());
        $this->assertSame([], $_SESSION);
    }

    public function testAuthLoginAndLogoutWrapSession(): void
    {
        Auth::login(42, 'admin');

        $this->assertTrue(Auth::check());
        $this->assertSame(42, Auth::userId());
        $this->assertSame('admin', Auth::role());
        $this->assertTrue(Auth::hasRole('admin'));

        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::userId());
        $this->assertSame('customer', Auth::role());
    }

    public function testCsrfTokenIsStableWithinSessionAndValidated(): void
    {
        $token = Csrf::token();

        $this->assertNotSame('', $token);
        $this->assertSame($token, Csrf::token());

        $_POST['_csrf'] = $token;

        $this->assertTrue(Csrf::isValidRequest());

        $_POST['_csrf'] = 'invalid';

        $this->assertFalse(Csrf::isValidRequest());
    }
}
