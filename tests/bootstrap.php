<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Use a predictable session for isolation during CLI tests.
if (session_status() === PHP_SESSION_NONE) {
    session_id('phpunit-session');
}
