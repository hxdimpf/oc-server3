<?php

declare(strict_types=1);

namespace Oc\Repository;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Oc\Entity\GeoCacheReportsEntity;
use Oc\Repository\Exception\RecordAlreadyExistsException;
use Oc\Repository\Exception\RecordNotFoundException;
use Oc\Repository\Exception\RecordNotPersistedException;
use Oc\Repository\Exception\RecordsNotFoundException;

class CacheReportsRepository
{
    private const TABLE = 'cache_reports';

    private Connection $connection;

    private CachesRepository $cachesRepository;

    private UserRepository $userRepository;

    private CacheReportReasonsRepository $cacheReportReasonsRepository;

    private CacheReportStatusRepository $cacheReportStatusRepository;

    public function __construct(
            Connection $connection,
            CachesRepository $cachesRepository,
            UserRepository $userRepository,
            CacheReportReasonsRepository $cacheReportReasonsRepository,
            CacheReportStatusRepository $cacheReportStatusRepository
    ) {
        $this->connection = $connection;
        $this->cachesRepository = $cachesRepository;
        $this->userRepository = $userRepository;
        $this->cacheReportReasonsRepository = $cacheReportReasonsRepository;
        $this->cacheReportStatusRepository = $cacheReportStatusRepository;
    }

    /**
     * @throws Exception
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
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
     * @throws Exception
     * @throws RecordNotFoundException
     */
    public function fetchOneBy(array $where = []): GeoCacheReportsEntity
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
            //            throw new RecordNotFoundException('Record with given where clause not found');
        }

        return $this->getEntityFromDatabaseArray($result);
    }

    /**
     * @throws Exception
     * @throws RecordNotFoundException
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
            //            throw new RecordsNotFoundException('No records with given where clause found');
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
    public function create(GeoCacheReportsEntity $entity): GeoCacheReportsEntity
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
    public function update(GeoCacheReportsEntity $entity): GeoCacheReportsEntity
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
     */
    public function remove(GeoCacheReportsEntity $entity): GeoCacheReportsEntity
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $this->connection->delete(
                self::TABLE,
                ['id' => $entity->id]
        );

        $entity->cacheid = 0;

        return $entity;
    }

    public function getDatabaseArrayFromEntity(GeoCacheReportsEntity $entity): array
    {
        return [
                'id' => $entity->id,
                'date_created' => $entity->dateCreated->format('Y-m-d H:i:s'),
                'cacheid' => $entity->cacheid,
                'userid' => $entity->userid,
                'reason' => $entity->reason,
                'note' => $entity->note,
                'status' => $entity->status,
                'adminid' => $entity->adminid,
                'lastmodified' => $entity->lastmodified,
                'comment' => $entity->comment,
        ];
    }

    /**
     * @throws Exception
     * @throws RecordNotFoundException
     */
    public function getEntityFromDatabaseArray(array $data): GeoCacheReportsEntity
    {
        $entity = new GeoCacheReportsEntity();
        $entity->id = (int)$data['id'];
        $entity->dateCreated = new DateTime($data['date_created']);
        $entity->cacheid = (int)$data['cacheid'];
        $entity->userid = (int)$data['userid'];
        $entity->reason = (int)$data['reason'];
        $entity->note = (string)$data['note'];
        $entity->status = (int)$data['status'];
        $entity->adminid = (int)$data['adminid'];
        $entity->lastmodified = date('Y-m-d H:i:s');
        $entity->comment = (string)$data['comment'];
        $entity->cache = $this->cachesRepository->fetchOneBy(['cache_id' => $entity->cacheid]);
        $entity->user = $this->userRepository->fetchOneById($entity->userid);
        if ($entity->adminid) {
            $entity->admin = $this->userRepository->fetchOneById($entity->adminid);
        }
        $entity->reportReason = $this->cacheReportReasonsRepository->fetchOneBy(['id' => $entity->reason]);
        $entity->reportStatus = $this->cacheReportStatusRepository->fetchOneBy(['id' => $entity->status]);

        return $entity;
    }
}
