<?php

declare(strict_types=1);

// Configuration
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection function
function getDbConnection(): PDO {
    $host = $_ENV['DB_HOST'] ?? 'postgres';
    $port = $_ENV['DB_PORT'] ?? '5432';
    $dbname = $_ENV['DB_DATABASE'] ?? 'spectracle_db';
    $username = $_ENV['DB_USERNAME'] ?? 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? 'strongpassword123';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    try {
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }
}

// Simple router
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Routes
switch (true) {
    case $path === '/health':
        echo json_encode([
            'status' => 'OK',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
        break;
        
    case $path === '/api/test':
        try {
            $pdo = getDbConnection();
            echo json_encode([
                'message' => 'Database connection successful',
                'php_version' => PHP_VERSION,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;
        
    case $path === '/':
        echo json_encode([
            'message' => 'Welcome to Spectracle API',
            'endpoints' => [
                'GET /health' => 'Health check',
                'GET /api/test' => 'Test database connection'
            ]
        ]);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}