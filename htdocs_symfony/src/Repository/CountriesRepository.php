<?php

declare(strict_types=1);

namespace Oc\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Oc\Entity\CountriesEntity;
use Oc\Repository\Exception\RecordAlreadyExistsException;
use Oc\Repository\Exception\RecordNotFoundException;
use Oc\Repository\Exception\RecordNotPersistedException;
use Oc\Repository\Exception\RecordsNotFoundException;

class CountriesRepository
{
    private const TABLE = 'countries';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
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
     */
    public function fetchOneBy(array $where = []): CountriesEntity
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
    public function create(CountriesEntity $entity): CountriesEntity
    {
        if (!$entity->isNew()) {
            throw new RecordAlreadyExistsException('The entity does already exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->insert(
                self::TABLE,
                $databaseArray
        );

        $entity->short = (int)$this->connection->lastInsertId();

        return $entity;
    }

    /**
     * @throws Exception
     * @throws RecordNotPersistedException
     */
    public function update(CountriesEntity $entity): CountriesEntity
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->update(
                self::TABLE,
                $databaseArray,
                ['short' => $entity->short]
        );

        return $entity;
    }

    /**
     * @throws Exception
     * @throws RecordNotPersistedException
     * @throws InvalidArgumentException
     */
    public function remove(CountriesEntity $entity): CountriesEntity
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $this->connection->delete(
                self::TABLE,
                ['short' => $entity->short]
        );

        $entity->short = null;

        return $entity;
    }

    /**
     * fetch all countries from DB, sort them ascending
     *
     * @throws RecordsNotFoundException
     * @throws Exception
     */
    public function fetchCountryList(string $locale): array
    {
        $fetchedCountries = $this->fetchAll();
        $countryList = [];

        foreach ($fetchedCountries as $country) {
            if ($locale == 'de') {
                $countryList[$country->de] = $country->short;
            } else {
                $countryList[$country->en] = $country->short;
            }
        }

        ksort($countryList);

        return ($countryList);
    }

    public function getDatabaseArrayFromEntity(CountriesEntity $entity): array
    {
        return [
                'short' => $entity->short,
                'name' => $entity->name,
                'trans_id' => $entity->transId,
                'de' => $entity->de,
                'en' => $entity->en,
                'list_default_de' => $entity->listDefaultDe,
                'sort_de' => $entity->sortDe,
                'list_default_en' => $entity->listDefaultEn,
                'sort_en' => $entity->sortEn,
                'adm_display2' => $entity->admDisplay2,
                'adm_display3' => $entity->admDisplay3,
        ];
    }

    public function getEntityFromDatabaseArray(array $data): CountriesEntity
    {
        $entity = new CountriesEntity();
        $entity->short = (string)$data['short'];
        $entity->name = (string)$data['name'];
        $entity->transId = (int)$data['trans_id'];
        $entity->de = (string)$data['de'];
        $entity->en = (string)$data['en'];
        $entity->listDefaultDe = (int)$data['list_default_de'];
        $entity->sortDe = (string)$data['sort_de'];
        $entity->listDefaultEn = (int)$data['list_default_en'];
        $entity->sortEn = (string)$data['sort_en'];
        $entity->admDisplay2 = (int)$data['adm_display2'];
        $entity->admDisplay3 = (int)$data['adm_display3'];

        return $entity;
    }
}
