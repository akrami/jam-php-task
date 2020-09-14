<?php


namespace App\Service;


class Database
{
    protected $pdo;

    public function __construct(Config $config)
    {
        $connection = $config->get('DB_CONNECTION') ?? 'mysql:host=localhost;port=3306;dbname=invite';
        $username = $config->get('DB_USERNAME') ?? 'root';
        $password = $config->get('DB_PASSWORD') ?? '';
        $this->pdo = new \PDO($connection, $username, $password);
    }

    public function pdo()
    {
        return $this->pdo;
    }
}