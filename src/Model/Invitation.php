<?php


namespace App\Model;


class Invitation
{
    protected $id;
    protected $name;
    protected $description;
    protected $inviter;
    protected $invitee;
    protected $response;
    protected $createdAt;
    protected $modifiedAt;

    public function __construct(int $id, string $name, ?string $description, User $inviter, User $invitee, ?bool $response, \DateTime $createdAt, \DateTime $modifiedAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->inviter = $inviter;
        $this->invitee = $invitee;
        $this->response = $response;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return User
     */
    public function getInviter(): User
    {
        return $this->inviter;
    }

    /**
     * @return User
     */
    public function getInvitee(): User
    {
        return $this->invitee;
    }

    /**
     * @return bool|null
     */
    public function getResponse(): ?bool
    {
        return $this->response;
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
            'name' => $this->name,
            'description' => $this->description,
            'inviter' => $this->inviter->toArray(),
            'invitee' => $this->invitee->toArray(),
            'response' => $this->response,
            'createdAt' => $this->createdAt,
            'modifiedAt' => $this->modifiedAt
        ];
    }

}