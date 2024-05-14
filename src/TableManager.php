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
	 * @throws DriverException
	 */
	private function execute(string $query): mixed
	{
		return $this->connection->query($query);
	}
}
