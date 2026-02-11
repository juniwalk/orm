<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Mapping\MappingException;

class TableManager
{
	protected readonly Connection $connection;

	public function __construct(
		protected readonly EntityManager $entityManager,
	) {
		$this->connection = $entityManager->getConnection();
	}


	/**
	 * @param  class-string $entityName
	 * @throws DriverException
	 */
	public function createPartition(string $entityName, DateTime $date, string $range): string
	{
		$tableName = $this->tableName($entityName);
		$partitionName = $tableName.'_'.$date->format('Ymd');

		$valueFrom = $date->format('Y-m-d');
		$valueTo = (clone $date)->modify($range)->format('Y-m-d');

		$this->execute('DROP TABLE IF EXISTS '.$partitionName.' CASCADE;');
		$this->execute('CREATE TABLE IF NOT EXISTS '.$partitionName.' PARTITION OF '.$tableName.' FOR VALUES FROM (\''.$valueFrom.'\') TO (\''.$valueTo.'\');');

		return $partitionName;
	}


	/**
	 * @param  class-string $entityName
	 * @throws DriverException
	 */
	public function truncate(string $entityName, bool $cascade = false): void
	{
		$this->execute('TRUNCATE TABLE '.$this->tableName($entityName).' RESTART IDENTITY'.($cascade == true ? ' CASCADE' : null));
	}


	/**
	 * @param  class-string $entityName
	 * @throws DriverException
	 * @throws MappingException
	 */
	public function reorder(string $entityName, string $fieldName): void
	{
		$metaData = $this->entityManager->getClassMetadata($entityName);

		if (!in_array($fieldName, $metaData->fieldNames)) {
            throw MappingException::mappingNotFound($entityName, $fieldName);
		}

		$columnName = $metaData->getColumnName($fieldName);
		$id = $metaData->getSingleIdentifierColumnName();
		$tableName = implode('.', array_filter([
			$metaData->getSchemaName(),
			$metaData->getTableName(),
		]));

		$this->execute(<<<SQL
			WITH cte AS (SELECT {$id}, ROW_NUMBER() OVER (ORDER BY "{$columnName}") AS rn FROM {$tableName})
			UPDATE {$tableName} SET "{$columnName}" = cte.rn FROM cte WHERE cte.{$id} = {$tableName}.{$id};
		SQL);
	}


	/**
	 * @param class-string $entityName
	 */
	public function tableName(string $entityName): string
	{
		$metaData = $this->entityManager->getClassMetadata($entityName);
		$tableName = $metaData->getTableName();

		if ($schemaName = $metaData->getSchemaName()) {
			$tableName = $schemaName.'.'.$tableName;
		}

		return $tableName;
	}


	/**
	 * @return int|numeric-string
	 * @throws DriverException
	 */
	private function execute(string $query): int|string
	{
		return $this->connection->executeStatement($query);
	}
}
