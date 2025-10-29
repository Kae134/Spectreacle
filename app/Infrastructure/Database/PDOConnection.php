<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOException;
use App\Infrastructure\Config\AppConfig;

print_r(PDO::getAvailableDrivers());

final class PDOConnection
{
    private static ?PDO $instance = null;
    
    private function __construct()
    {
    }
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = AppConfig::getInstance();
            
            $host = $config->get('database.host');
            $port = $config->get('database.port');
            $dbname = $config->get('database.name');
            $username = $config->get('database.username');
            $password = $config->get('database.password');
            $charset = $config->get('database.charset', 'utf8mb4');
            
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
            
            try {
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}