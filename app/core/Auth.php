<?php
// app/core/Auth.php

class Auth
{
    private const SESSION_KEY = 'auth_user_id';

    public static function login(int $userId): void
    {
        Session::set(self::SESSION_KEY, $userId);
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
    }

    public static function userId(): ?int
    {
        $id = Session::get(self::SESSION_KEY);
        return $id !== null ? (int) $id : null;
    }

    public static function check(): bool
    {
        return self::userId() !== null;
    }
}
