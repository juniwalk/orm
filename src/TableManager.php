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
use Doctrine\ORM\Mapping\QuoteStrategy;

use function in_array;

class TableManager
{
	protected readonly Connection $connection;
	protected readonly QuoteStrategy $quoteStrategy;

	public function __construct(
		protected readonly EntityManager $entityManager,
	) {
		$this->connection = $entityManager->getConnection();
		$this->quoteStrategy = $entityManager->getConfiguration()->getQuoteStrategy();
	}


	/**
	 * @param  class-string $entityName
	 * @throws DriverException
	 */
	public function createPartition(string $entityName, DateTime $date, string $range): string
	{
		$partitionName = $this->tableName($entityName, '_'.$date->format('Ymd'));
		$tableName = $this->tableName($entityName);

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

		$id = $metaData->getSingleIdentifierColumnName();
		$columnName = $metaData->getColumnName($fieldName);
		$tableName = $this->tableName($entityName);

		$this->execute(<<<SQL
			WITH cte AS (SELECT {$id}, ROW_NUMBER() OVER (ORDER BY "{$columnName}") AS rn FROM {$tableName})
			UPDATE {$tableName} SET "{$columnName}" = cte.rn FROM cte WHERE cte.{$id} = {$tableName}.{$id};
		SQL);
	}


	/**
	 * @param class-string $entityName
	 */
	public function tableName(string $entityName, string $suffix = ''): string
	{
		$metaData = $this->entityManager->getClassMetadata($entityName);

		if ($suffix !== '') {
			$metaData = clone $metaData;
			$metaData->table['name'] .= $suffix;
		}

		return $this->quoteStrategy->getTableName($metaData, $this->connection->getDatabasePlatform());
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
