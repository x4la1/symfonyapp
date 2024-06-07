<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\User;
use App\Utils;
use http\Exception\RuntimeException;
use PDO;

class UserTable
{
    private const  MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(private \PDO $connection)
    {
    }

    public function findUserInDatabase(int $id): ?User
    {
        $query = <<<SQL
            SELECT user_id, first_name, last_name, middle_name, gender, birth_date, email, phone, avatar_path
            FROM user
            WHERE user_id = $id
            SQL;
        $statement = $this->connection->query($query); //делаем запрос в базу и сохраняем в $statement


        if ($row = $statement->fetch(\PDO::FETCH_ASSOC)) //возвращает массив, индексированный именами столбцов результирующего набора $row === [] => false
        {
            return $this->createUserFromRow($row);
        }
        return null;
    }

    private function createUserFromRow(array $row): User
    {
        return new User(
            (int)$row['user_id'],
            $row['first_name'],
            $row['last_name'],
            $row['middle_name'] ?? null,
            $row['gender'],
            Utils::parseDateTime($row['birth_date'], self::MYSQL_DATETIME_FORMAT),
            $row['email'],
            $row['phone'] ?? null,
            $row['avatar_path'] ?? null,
        );
    }

    public function saveUserToDatabase(User $user): int
    {
        $query = <<<SQL
            INSERT INTO user 
                (first_name, last_name, middle_name, gender, birth_date, email, phone, avatar_path) 
            VALUES (:firstName, :lastName, :middleName, :gender, :birthDate, :email, :phone, :avatarPath)
            SQL;
        $statement = $this->connection->prepare($query);
        try {
            $statement->execute([
                ':firstName' => $user->getFirstName(),
                ':lastName' => $user->getLastName(),
                ':middleName' => $user->getMiddleName(),
                ':gender' => $user->getGender(),
                ':birthDate' => Utils::convertDataTimeToString($user->getBirthDate()),
                ':email' => $user->getEmail(),
                ':phone' => $user->getPhone(),
                ':avatarPath' => $user->getAvatarPath(),
            ]);
            return (int)$this->connection->lastInsertId();
        } catch (\PDOException $exception) {
            throw new \RuntimeException($exception->getMessage(), (int)$exception->getCode());
        }
    }

    public function getAllUsersFromDB(): array
    {
        $query = <<<SQL
                SELECT * FROM user
             SQL;
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function findUserByEmail(string $email): void
    {
        $query = <<<SQL
                SELECT * FROM user WHERE email = :email
            SQL;
        $statement = $this->connection->prepare($query);
        try {
            $statement->execute([
                ':email' => $email
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }

        $row = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (count($row) > 1) {
            throw new \Exception('Email is already taken');
        }

    }

    public function findUserByPhone(string $phone): void
    {
        $query = <<<SQL
                SELECT * FROM user WHERE phone = :phone
            SQL;
        $statement = $this->connection->prepare($query);
        try {
            $statement->execute([
                ':phone' => $phone
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
        $row = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (count($row) > 1) {
            throw new \Exception('Phone is already taken');
        }

    }


    public function updateUserInDataBase(User $user, int $id): void
    {
        $query = <<<SQL
                UPDATE user
                SET first_name = :firstName,
                    last_name = :lastName, 
                    middle_name = :middleName, 
                    gender = :gender,
                    birth_date = :birthDate, 
                    email = :email, 
                    phone = :phone 
                WHERE user_id = :id
                SQL;
        $statement = $this->connection->prepare($query);

        try {
            $statement->execute([
                ':firstName' => $user->getFirstName(),
                ':lastName' => $user->getLastName(),
                ':middleName' => $user->getMiddleName(),
                ':gender' => $user->getGender(),
                ':birthDate' => Utils::convertDataTimeToString($user->getBirthDate()),
                ':email' => $user->getEmail(),
                ':phone' => $user->getPhone(),
                ':id' => $id
            ]);
        } catch (\PDOException $exception) {
            throw new \RuntimeException($exception->getMessage(), (int)$exception->getCode());
        }
    }

    public function deleteUserInDB(int $userId): void
    {

        $query = <<<SQL
                DELETE FROM user
                WHERE user_id = :userId
            SQL;
        $statement = $this->connection->prepare($query);

        try {
            $statement->execute([
                ':userId' => $userId
            ]);
        } catch (\PDOException $exception) {
            throw new \RuntimeException($exception->getMessage(), (int)$exception->getCode());
        }

    }


    public function addImagePathInDB(?string $imagePath, int $id): void
    {
        $query = <<<SQL
            UPDATE user
            SET avatar_path = :avatarPath
            WHERE user_id = $id
            SQL;
        $statement = $this->connection->prepare($query);
        try {
            $statement->execute([
                ':avatarPath' => $imagePath
            ]);
        } catch (\PDOException $exception) {
            throw new RuntimeException($exception->getMessage(), (int)$exception->getCode());
        }
    }
}