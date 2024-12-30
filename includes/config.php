<?php
// includes/config.php

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

$host = "localhost";
$dbname = "greenclean";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    error_log("Database connection established successfully");
    
    // Verify connection
    $test_query = $pdo->query("SELECT 1");
    error_log("Database test query successful");
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    throw new Exception("Database connection failed");
}