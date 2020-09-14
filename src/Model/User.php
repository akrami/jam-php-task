<?php

namespace App\Model;

class User
{
    protected $id;
    protected $username;
    protected $email;
    protected $createdAt;
    protected $modifiedAt;

    public function __construct(int $id, string $username, string $email, \DateTime $createdAt, \DateTime $modifiedAt)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->createdAt = $createdAt;
        $this->modifiedAt = $modifiedAt;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getModifiedAt(): \DateTime
    {
        return $this->modifiedAt;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'createdAt' => $this->createdAt,
            'modifiedAt' => $this->modifiedAt
        ];
    }
}