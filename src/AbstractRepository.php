<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM;

use DateTime;
use Doctrine\DBAL\Driver\Exception as DriverException;

/**
 * @template T of object
 * @extends Repository<T>
 * @deprecated
 */
abstract class AbstractRepository extends Repository
{
	protected ?TableManager $tableManager = null;

	/**
	 * @param  class-string|null $entityName
	 * @throws DriverException
	 * @deprecated
	 */
	public function createPartition(DateTime $date, string $range, ?string $entityName = null): string
	{
		return $this->getManager()->createPartition($entityName ?: $this->entityName, $date, $range);
	}


	/**
	 * @param  class-string|null $entityName
	 * @throws DriverException
	 * @deprecated
	 */
	public function truncateTable(bool $cascade = false, ?string $entityName = null): void
	{
		$this->getManager()->truncate($entityName ?: $this->entityName, $cascade);
	}


	/**
	 * @param  class-string|null $entityName
	 * @deprecated
	 */
	public function getTableName(?string $entityName = null): string
	{
		return $this->getManager()->tableName($entityName ?: $this->entityName);
	}


	private function getManager(): TableManager
	{
		return $this->tableManager ??= new TableManager($this->entityManager);
	}
}
