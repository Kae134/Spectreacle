<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

final class AppConfig
{
    private static ?self $instance = null;
    private array $config = [];
    
    private function __construct()
    {
        // Charger la config depuis les fichiers
        $appConfig = require __DIR__ . '/../../../config/app.php';
        $dbConfig = require __DIR__ . '/../../../config/database.php';
        
        $this->config = array_merge($appConfig, ['database' => $dbConfig]);
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config = $value;
    }
    
    public function all(): array
    {
        return $this->config;
    }
}