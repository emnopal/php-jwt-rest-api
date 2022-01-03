<?php

namespace BadHabit\LoginManagement\Repository;

use BadHabit\LoginManagement\Domain\User;

class UserRepository
{

    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(User $user): User
    {
        $sql = "INSERT INTO users(username, password, fullName, email, role) VALUES (?,?,?,?,?)";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            $user->username,
            $user->password,
            $user->full_name,
            $user->email,
            $user->role
        ]);
        return $user;


    }

    public function findById(string $id): ?User
    {
        $sql = "SELECT username, password, fullName, email, role FROM users WHERE username = ?";
        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute([$id]);
            if ($row = $statement->fetch()) {
                $user = new User();
                $user->username = $row['username'];
                $user->password = $row['password'];
                $user->full_name = $row['fullName'];
                $user->email = $row['email'];
                $user->role = $row['role'];
                return $user;
            }
            return null;
        } finally {
            $statement->closeCursor();
        }
    }

    // Only for testing
    public function deleteAll(): void
    {
        $this->connection->exec("DELETE FROM users");
    }

    public function update(User $user): User
    {
        $sql = "UPDATE users SET fullName = ?, password = ?, email = ? WHERE username = ?";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            $user->full_name,
            $user->password,
            $user->email,
            $user->username,
        ]);
        return $user;
    }

    public function changeId(string $id, string $newId): void
    {
        $sql = "UPDATE users SET username = ? WHERE username = ?";
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            $newId,
            $id,
        ]);
    }

}