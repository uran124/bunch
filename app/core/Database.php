<?php
// app/core/Database.php

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                self::$instance->exec("SET time_zone = '+07:00'");
            } catch (PDOException $e) {
                error_log('DB connection error: ' . $e->getMessage());

                if (APP_ENV === 'dev') {
                    die('Ошибка подключения к базе данных');
                }

                http_response_code(500);
                die('Сервис временно недоступен');
            }
        }

        return self::$instance;
    }
}
