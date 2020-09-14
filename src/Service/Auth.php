<?php


namespace App\Service;

use App\Model\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;


class Auth
{
    protected $database;
    protected $key;

    public function __construct(\PDO $database, string $key)
    {
        $this->database = $database;
        $this->key = $key;
    }

    /**
     * @param string $username
     * @param string $password
     * @return string|null
     */
    public function login(string $username, string $password): ?string
    {
        $query = 'SELECT * FROM users where username=:username';
        $values = [':username' => $username];
        try {
            $resource = $this->database->prepare($query);
            $resource->execute($values);
            $count = $resource->rowCount();
            if ($count === 1) {
                $row = $resource->fetch(\PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password'])) {
                    return JWT::encode([
                        'iss' => 'ISSUER',
                        'aud' => 'AUDIENCE',
                        'iat' => time(),
                        'nbf' => time() + 10,
                        'exp' => time() + 3600,
                        'data' => [
                            'id' => $row['id'],
                            'username' => $row['username'],
                            'email' => $row['email']
                        ]
                    ], $this->key);
                } else return null;
            } else {
                return null;
            }
        } catch (\PDOException $exception) {
            return null;
        }
    }

    /**
     * @param string $token
     * @return bool
     */
    public function isValid(string $token): bool
    {
        $token = str_replace('Bearer ', '', $token);
        try {
            $jwt = JWT::decode($token, $this->key, ['HS256']);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $token
     * @return User|null
     */
    public function getUser(string $token): ?User {
        $token = str_replace('Bearer ', '', $token);
        try {
            $jwt = JWT::decode($token, $this->key, ['HS256']);
            $userRepository = new UserRepository($this->database);
            return $userRepository->findById($jwt->data->id);
        } catch (\Exception $exception) {
            return null;
        }
    }
}