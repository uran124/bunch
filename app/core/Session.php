<?php
// app/core/Session.php

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
}

class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    private const FORM_FIELD = '_csrf';
    private const HEADER_NAME = 'HTTP_X_CSRF_TOKEN';

    public static function token(): string
    {
        Session::start();

        $token = Session::get(self::SESSION_KEY);
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        Session::set(self::SESSION_KEY, $token);

        return $token;
    }

    public static function fieldName(): string
    {
        return self::FORM_FIELD;
    }

    public static function requestToken(): ?string
    {
        $headerToken = $_SERVER[self::HEADER_NAME] ?? null;
        if (is_string($headerToken) && $headerToken !== '') {
            return $headerToken;
        }

        $postToken = $_POST[self::FORM_FIELD] ?? null;
        if (is_string($postToken) && $postToken !== '') {
            return $postToken;
        }

        return null;
    }

    public static function isValidRequest(): bool
    {
        $requestToken = self::requestToken();
        if (!is_string($requestToken) || $requestToken === '') {
            return false;
        }

        return hash_equals(self::token(), $requestToken);
    }

    public static function shouldProtectMethod(?string $method = null): bool
    {
        $method = strtoupper($method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        return !in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
    }
}
