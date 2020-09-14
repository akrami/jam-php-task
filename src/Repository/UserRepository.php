<?php

namespace App\Repository;

use App\Model\User;

class UserRepository
{
    protected $database;

    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @return User|null
     */
    public function insert(string $username, string $password, string $email): ?User
    {
        $username = trim($username);
        $password = trim($password);
        $password = password_hash($password, PASSWORD_DEFAULT);

        $query = 'INSERT INTO users (username, password, email) VALUES(:username, :password, :email)';
        $values = [':username' => $username, ':password' => $password, ':email' => $email];

        try {
            $resource = $this->database->prepare($query);
            if (!$resource->execute($values)) {
                if ($resource->errorInfo()[1]==1062){
                    throw new \PDOException('duplicate value for username or email', $resource->errorInfo()[1]);
                }
                throw new \PDOException($resource->errorInfo()[2], $resource->errorInfo()[1]);
            }
        } catch (\PDOException $exception) {
            throw $exception;
        }

        return $this->findById($this->database->lastInsertId());
    }

    /**
     * @param int $id
     * @return User|null
     * @throws \Exception
     */
    public function findById(int $id): ?User
    {
        $query = 'SELECT id, username, email, created_at, modified_at FROM users WHERE id=:id';
        $values = [':id' => $id];
        try {
            $resource = $this->database->prepare($query);
            $resource->execute($values);
            if ($resource->rowCount() < 1) return null;
            $row = $resource->fetch(\PDO::FETCH_ASSOC);
            return new User($id, $row['username'], $row['email'], new \DateTime($row['created_at']), new \DateTime($row['modified_at']));
        } catch (\PDOException $exception) {
            throw $exception;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function findAll(): array {
        $query = 'SELECT id, username, email, created_at, modified_at FROM users';
        try {
            $resource = $this->database->prepare($query);
            $resource->execute();
            $result = [];
            while($row = $resource->fetch(\PDO::FETCH_ASSOC)){
                $temp = new User($row['id'], $row['username'], $row['email'], new \DateTime($row['created_at']), new \DateTime($row['modified_at']));
                array_push($result, $temp);
            }
            return $result;
        }catch (\PDOException $exception){
            return [];
        }
    }
}