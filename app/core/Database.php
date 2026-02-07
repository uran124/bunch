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
                $timezone = new DateTimeZone(APP_TIMEZONE);
                $offsetSeconds = $timezone->getOffset(new DateTime('now', $timezone));
                $offsetSign = $offsetSeconds >= 0 ? '+' : '-';
                $offsetSeconds = abs($offsetSeconds);
                $offsetHours = (int) floor($offsetSeconds / 3600);
                $offsetMinutes = (int) floor(($offsetSeconds % 3600) / 60);
                $offset = sprintf('%s%02d:%02d', $offsetSign, $offsetHours, $offsetMinutes);
                self::$instance->exec("SET time_zone = '{$offset}'");
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
