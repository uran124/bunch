<?php
// app/core/Auth.php

class Auth
{
    private const SESSION_KEY = 'auth_user_id';
    private const SESSION_ROLE_KEY = 'auth_user_role';

    public static function login(int $userId, ?string $role = null): void
    {
        Session::set(self::SESSION_KEY, $userId);
        if ($role !== null && $role !== '') {
            Session::set(self::SESSION_ROLE_KEY, $role);
        } else {
            Session::remove(self::SESSION_ROLE_KEY);
        }
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
        Session::remove(self::SESSION_ROLE_KEY);
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

    public static function role(): string
    {
        if (!self::check()) {
            return 'customer';
        }

        $cachedRole = Session::get(self::SESSION_ROLE_KEY);
        if (is_string($cachedRole) && $cachedRole !== '') {
            return $cachedRole;
        }

        $userId = self::userId();
        if ($userId === null) {
            return 'customer';
        }

        $userModel = new User();
        $user = $userModel->findById($userId);
        $role = (string) ($user['role'] ?? 'customer');

        Session::set(self::SESSION_ROLE_KEY, $role);

        return $role;
    }

    public static function hasRole(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }
}
