<?php
/**
 * Database connection helper for Cit-E Cycling
 * Returns a MySQLi connection. Edit the constants below to match your server.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cycling');

function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log('Database connection failed: ' . $conn->connect_error);
        die('A database error occurred. Please try again later.');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
