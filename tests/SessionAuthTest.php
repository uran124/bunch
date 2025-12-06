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
        Auth::login(42);

        $this->assertTrue(Auth::check());
        $this->assertSame(42, Auth::userId());

        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::userId());
    }
}
