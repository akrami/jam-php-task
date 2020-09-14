<?php


namespace App\Repository;


use App\Model\Invitation;
use App\Model\User;

class InvitationRepository
{
    protected $database;

    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }

    /**
     * @param string $name
     * @param string|null $description
     * @param User $inviter
     * @param User $invitee
     * @return Invitation|null
     * @throws \Exception
     */
    public function insert(string $name, ?string $description, User $inviter, User $invitee): ?Invitation
    {
        $query = 'INSERT INTO invitations (name, description, inviter, invitee) VALUES(:name, :description , :inviter, :invitee)';
        $values = [
            ':name' => $name,
            ':description' => $description,
            ':inviter' => $inviter->getId(),
            ':invitee' => $invitee->getId()
        ];

        try {
            $resource = $this->database->prepare($query);
            $resource->execute($values);
        } catch (\PDOException $exception) {
            throw $exception;
        }

        return $this->findById($this->database->lastInsertId());
    }

    /**
     * @param int $id
     * @return Invitation|null
     * @throws \Exception
     */
    public function findById(int $id): ?Invitation
    {
        $query = 'SELECT id, name, description, inviter, invitee, response, created_at, modified_at FROM invitations WHERE id=:id';
        $values = [':id' => $id];
        try {
            $resource = $this->database->prepare($query);
            $resource->execute($values);
            if ($resource->rowCount() < 1) return null;
            $row = $resource->fetch(\PDO::FETCH_ASSOC);
            $userRepository = new UserRepository($this->database);
            return new Invitation(
                $id,
                $row['name'],
                $row['description'],
                $userRepository->findById($row['inviter']),
                $userRepository->findById($row['invitee']),
                $row['response'],
                new \DateTime($row['created_at']),
                new \DateTime($row['modified_at'])
            );
        } catch (\PDOException $exception) {
            throw $exception;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function findAll(): array
    {
        $query = 'SELECT id, name, description, inviter, invitee, response, created_at, modified_at FROM invitations';
        try {
            $resource = $this->database->prepare($query);
            $resource->execute();
            $result = [];
            $userRepository = new UserRepository($this->database);
            while ($row = $resource->fetch(\PDO::FETCH_ASSOC)) {
                $temp = new Invitation(
                    $row['id'],
                    $row['name'],
                    $row['description'],
                    $userRepository->findById($row['inviter']),
                    $userRepository->findById($row['invitee']),
                    $row['response'],
                    new \DateTime($row['created_at']),
                    new \DateTime($row['modified_at'])
                );
                array_push($result, $temp);
            }
            return $result;
        } catch (\PDOException $exception) {
            return [];
        }
    }

    /**
     * @param User $me
     * @return array
     * @throws \Exception
     */
    public function findMine(User $me): array
    {
        $query = 'SELECT id, name, description, inviter, invitee, response, created_at, modified_at FROM invitations WHERE inviter=:inviter';
        $values = [':inviter' => $me->getId()];
        try {
            $resource = $this->database->prepare($query);
            $resource->execute($values);
            $result = [];
            $userRepository = new UserRepository($this->database);
            while ($row = $resource->fetch(\PDO::FETCH_ASSOC)) {
                $temp = new Invitation(
                    $row['id'],
                    $row['name'],
                    $row['description'],
                    $userRepository->findById($row['inviter']),
                    $userRepository->findById($row['invitee']),
                    $row['response'],
                    new \DateTime($row['created_at']),
                    new \DateTime($row['modified_at'])
                );
                array_push($result, $temp);
            }
            return $result;
        } catch (\PDOException $exception) {
            return [];
        }
    }

    /**
     * @param User $me
     * @return array
     * @throws \Exception
     */
    public function findPending(User $me): array
    {
        $query = 'SELECT id, name, description, inviter, invitee, response, created_at, modified_at FROM invitations WHERE invitee=:invitee';
        $values = [':invitee' => $me->getId()];
        try {
            $resource = $this->database->prepare($query);
            $resource->execute($values);
            $result = [];
            $userRepository = new UserRepository($this->database);
            while ($row = $resource->fetch(\PDO::FETCH_ASSOC)) {
                $temp = new Invitation(
                    $row['id'],
                    $row['name'],
                    $row['description'],
                    $userRepository->findById($row['inviter']),
                    $userRepository->findById($row['invitee']),
                    $row['response'],
                    new \DateTime($row['created_at']),
                    new \DateTime($row['modified_at'])
                );
                array_push($result, $temp);
            }
            return $result;
        } catch (\PDOException $exception) {
            return [];
        }
    }

    /**
     * @param int $id
     * @param User $me
     * @return bool
     */
    public function delete(int $id, User $me): bool
    {
        $query = 'DELETE FROM invitations WHERE id=:id and inviter=:inviter';
        $values = [':id' => $id, ':inviter' => $me->getId()];
        try {
            $resource = $this->database->prepare($query);
            $resource->execute($values);
            return $resource->rowCount()>=1;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    /**
     * @param int $id
     * @param User $me
     * @param bool $response
     * @return bool
     */
    public function respond(int $id, User $me, bool $response): bool
    {
        $query = 'UPDATE invitations SET response=:response, modified_at=CURRENT_TIMESTAMP WHERE id=:id AND invitee=:invitee';
        $values = [
            ':id' => $id,
            ':invitee' => $me->getId(),
            ':response' => $response?1:0
        ];

        try {
            $resource = $this->database->prepare($query);
            $resource->execute($values);
            return $resource->rowCount()>=1;
        } catch (\PDOException $exception) {
            return false;
        }

    }
}