<?php


namespace App\Service;


use Dotenv\Dotenv;

class Config
{
    private $env;

    public function __construct()
    {
        $this->env = Dotenv::createImmutable(dirname(dirname(__DIR__)));
        $this->env->load();
    }

    public function get(string $name)
    {
        return $_ENV[$name] ?? null;
    }

    public function mode(){
        return $_ENV['APP_MODE'] ?? 'production';
    }
}