<?php

declare(strict_types=1);

namespace Oc\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Oc\Entity\SupportUserCommentsEntity;
use Oc\Repository\Exception\RecordAlreadyExistsException;
use Oc\Repository\Exception\RecordNotFoundException;
use Oc\Repository\Exception\RecordNotPersistedException;
use Oc\Repository\Exception\RecordsNotFoundException;

class SupportUserCommentsRepository
{
    private const TABLE = 'support_user_comments';

    private Connection $connection;

    private UserRepository $userRepository;

    public function __construct(Connection $connection, UserRepository $userRepository)
    {
        $this->connection = $connection;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws RecordsNotFoundException
     * @throws \Exception
     */
    public function fetchAll(): array
    {
        $statement = $this->connection->createQueryBuilder()
                ->select('*')
                ->from(self::TABLE)
                ->executeQuery();

        $result = $statement->fetchAllAssociative();

        if ($statement->rowCount() === 0) {
            throw new RecordsNotFoundException('No records found');
        }

        $records = [];

        foreach ($result as $item) {
            $records[] = $this->getEntityFromDatabaseArray($item);
        }

        return $records;
    }

    /**
     * @throws RecordNotFoundException
     * @throws \Exception
     */
    public function fetchOneBy(array $where = []): SupportUserCommentsEntity
    {
        $queryBuilder = $this->connection->createQueryBuilder()
                ->select('*')
                ->from(self::TABLE)
                ->setMaxResults(1);

        if (count($where) > 0) {
            foreach ($where as $column => $value) {
                $queryBuilder->andWhere($column . ' = ' . $queryBuilder->createNamedParameter($value));
            }
        }

        $statement = $queryBuilder->executeQuery();

        $result = $statement->fetchAssociative();

        if ($statement->rowCount() === 0) {
            throw new RecordNotFoundException('Record with given where clause not found');
        }

        return $this->getEntityFromDatabaseArray($result);
    }

    /**
     * @throws RecordsNotFoundException
     * @throws \Exception
     */
    public function fetchBy(array $where = []): array
    {
        $queryBuilder = $this->connection->createQueryBuilder()
                ->select('*')
                ->from(self::TABLE);

        if (count($where) > 0) {
            foreach ($where as $column => $value) {
                $queryBuilder->andWhere($column . ' = ' . $queryBuilder->createNamedParameter($value));
            }
        }

        $statement = $queryBuilder->executeQuery();

        $result = $statement->fetchAllAssociative();

        if ($statement->rowCount() === 0) {
            throw new RecordsNotFoundException('No records with given where clause found');
        }

        $entities = [];

        foreach ($result as $item) {
            $entities[] = $this->getEntityFromDatabaseArray($item);
        }

        return $entities;
    }

    /**
     * @throws Exception
     * @throws RecordAlreadyExistsException
     */
    public function create(SupportUserCommentsEntity $entity): SupportUserCommentsEntity
    {
        if (!$entity->isNew()) {
            throw new RecordAlreadyExistsException('The entity does already exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->insert(
                self::TABLE,
                $databaseArray
        );

        $entity->id = (int)$this->connection->lastInsertId();

        return $entity;
    }

    /**
     * @throws Exception
     * @throws RecordNotPersistedException
     */
    public function update(SupportUserCommentsEntity $entity): SupportUserCommentsEntity
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->update(
                self::TABLE,
                $databaseArray,
                ['id' => $entity->id]
        );

        return $entity;
    }

    /**
     * @throws Exception
     * @throws RecordNotPersistedException
     */
    public function remove(SupportUserCommentsEntity $entity): SupportUserCommentsEntity
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $this->connection->delete(
                self::TABLE,
                ['id' => $entity->id]
        );

        $entity->id = 0;

        return $entity;
    }

    public function getDatabaseArrayFromEntity(SupportUserCommentsEntity $entity): array
    {
        return [
                'id' => $entity->id,
                'oc_user_id' => $entity->ocUserId,
                'comment' => $entity->comment,
                'comment_created' => $entity->commentCreated,
                'comment_last_modified' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * @throws Exception
     * @throws RecordNotFoundException
     */
    public function getEntityFromDatabaseArray(array $data): SupportUserCommentsEntity
    {
        $entity = new SupportUserCommentsEntity(0);
        $entity->id = (int)$data['id'];
        $entity->ocUserId = (int)$data['oc_user_id'];
        $entity->user = $this->userRepository->fetchOneById($entity->ocUserId);
        $entity->comment = (string)$data['comment'];
        $entity->commentCreated = (string)$data['comment_created'];
        $entity->commentLastModified = date('Y-m-d H:i:s');

        return $entity;
    }
}
