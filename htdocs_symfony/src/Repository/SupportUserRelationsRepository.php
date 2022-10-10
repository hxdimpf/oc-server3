<?php

declare(strict_types=1);

namespace Oc\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Oc\Entity\SupportUserRelationsEntity;
use Oc\Repository\Exception\RecordAlreadyExistsException;
use Oc\Repository\Exception\RecordNotFoundException;
use Oc\Repository\Exception\RecordNotPersistedException;
use Oc\Repository\Exception\RecordsNotFoundException;

class SupportUserRelationsRepository
{
    private const TABLE = 'support_user_relations';

    private Connection $connection;

    private NodesRepository $nodesRepository;

    private UserRepository $userRepository;

    public function __construct(Connection $connection, NodesRepository $nodesRepository, UserRepository $userRepository)
    {
        $this->connection = $connection;
        $this->nodesRepository = $nodesRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws Exception
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
     * @throws Exception
     * @throws \Exception
     */
    public function fetchOneBy(array $where = []): SupportUserRelationsEntity
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
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws Exception
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
     * @throws RecordAlreadyExistsException
     * @throws Exception
     */
    public function create(SupportUserRelationsEntity $entity): SupportUserRelationsEntity
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
     * @throws RecordNotPersistedException
     * @throws Exception
     */
    public function update(SupportUserRelationsEntity $entity): SupportUserRelationsEntity
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
     * @throws RecordNotPersistedException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function remove(SupportUserRelationsEntity $entity): SupportUserRelationsEntity
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

    public function getDatabaseArrayFromEntity(SupportUserRelationsEntity $entity): array
    {
        return [
                'id' => $entity->id,
                'oc_user_id' => $entity->ocUserId,
                'node_id' => $entity->nodeId,
                'node_user_id' => $entity->nodeUserId,
                'node_username' => $entity->nodeUsername,
        ];
    }

    /**
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function getEntityFromDatabaseArray(array $data): SupportUserRelationsEntity
    {
        $entity = new SupportUserRelationsEntity();
        $entity->id = (int)$data['id'];
        $entity->ocUserId = (int)$data['oc_user_id'];
        $entity->nodeId = (int)$data['node_id'];
        $entity->nodeUserId = (string)$data['node_user_id'];
        $entity->nodeUsername = (string)$data['node_username'];
        $entity->node = $this->nodesRepository->fetchOneBy(['id' => $entity->nodeId]);
        $entity->user = $this->userRepository->fetchOneById($entity->ocUserId);

        return $entity;
    }
}
