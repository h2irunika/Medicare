<?php
/*
 * The one database connection for the whole system.
 * Every page - patient, doctor and reception - uses this file.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'clinic_db';
$DB_USER = 'root';
$DB_PASS = 'AkilaMB2004#';          // set this if your MySQL root has a password
$DB_PORT = 3307;        // change if your MySQL runs on another port

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
