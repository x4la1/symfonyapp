<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class UserRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }


    public function findById(int $id): ?User
    {
        return $this->entityManager->find(User::class, $id);
    }

    public function store(User $user): int
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user->getId();
    }

    public function findUsersByEmail(string $email): ?User
    {
        $query = $this->entityManager->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery();
        return $query->getOneOrNullResult();
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

}