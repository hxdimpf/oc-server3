<?php

declare(strict_types=1);

namespace Oc\User;

use Doctrine\DBAL\Exception;
use Oc\Entity\UserEntity;
use Oc\Repository\Exception\RecordNotFoundException;
use Oc\Repository\Exception\RecordsNotFoundException;
use Oc\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Fetches all users.
     *
     * @throws Exception
     */
    public function fetchAll(): array
    {
        try {
            $result = $this->userRepository->fetchAll();
        } catch (RecordsNotFoundException $e) {
            $result = [];
        }

        return $result;
    }

    /**
     * Fetches a user by its id.
     *
     * @throws Exception
     */
    public function fetchOneById(int $id): ?UserEntity
    {
        try {
            $result = $this->userRepository->fetchOneById($id);
        } catch (RecordNotFoundException $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * Creates a user in the database.
     */
    public function create(UserEntity $entity): UserEntity
    {
        return $this->userRepository->create($entity);
    }

    /**
     * Update a user in the database.
     */
    public function update(UserEntity $entity): UserEntity
    {
        return $this->userRepository->update($entity);
    }

    /**
     * Removes a user from the database.
     */
    public function remove(UserEntity $entity): UserEntity
    {
        return $this->userRepository->remove($entity);
    }
}
