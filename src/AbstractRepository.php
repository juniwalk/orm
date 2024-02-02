<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use JuniWalk\ORM\Exceptions\EntityNotFoundException;
use JuniWalk\ORM\Interfaces\HtmlOption;
use JuniWalk\Utils\Arrays;
use JuniWalk\Utils\Strings;
use Nette\Application\UI\Form;

abstract class AbstractRepository
{
	public const DefaultAlias = 'e';
	public const DefaultIdentifier = 'e.id';
	public const DefaultIndexBy = self::DefaultIdentifier;

	protected readonly EntityManager $entityManager;
	protected readonly Connection $connection;
	protected string $entityName;

	/**
	 * @throws EntityNotFoundException
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->connection = $entityManager->getConnection();
		$this->entityManager = $entityManager;

		if (!isset($this->entityName) || !class_exists($this->entityName)) {
			throw EntityNotFoundException::fromClass($this->entityName ?? 'undefined');
		}
	}



	/**
	 * @throws NoResultException
	 */
	public function getBy(
		callable $where,
		?int $maxResults = null,
		?string $indexBy = self::DefaultIndexBy,
	): array {
		$qb = $this->createQueryBuilder(self::DefaultAlias, $indexBy, $where);
		$qb->setMaxResults($qb->getMaxResults() ?? $maxResults);

		$result = $qb->getQuery()
			->getResult();

		if (!$result) {
			throw new NoResultException;
		}

		return $result;
	}


	/**
	 * @throws NoResultException
	 */
	public function getOneBy(callable $where, ?string $indexBy = self::DefaultIndexBy): object
	{
		$qb = $this->createQueryBuilder(self::DefaultAlias, $indexBy, $where);
		$qb->setMaxResults(1);

		return $qb->getQuery()
			->getSingleResult();
	}


	public function findBy(
		callable $where,
		?int $maxResults = null,
		string $indexBy = self::DefaultIndexBy,
	): array {
		try {
			return $this->getBy($where, $maxResults, $indexBy);

		} catch (NoResultException) {
			return [];
		}
	}


	public function findOneBy(callable $where, ?string $indexBy = self::DefaultIndexBy): ?object
	{
		try {
			return $this->getOneBy($where, $indexBy);

		} catch (NoResultException) {
			return null;
		}
	}


	/**
	 * @throws NoResultException
	 */
	public function getById(mixed $id, ?string $indexBy = self::DefaultIndexBy): object
	{
		$where = fn($qb) => $qb->where(self::DefaultIdentifier.' = :id')
			->setParameter('id', (int) $id);

		return $this->getOneBy($where, $indexBy);
	}


	public function findById(mixed $id, ?string $indexBy = self::DefaultIndexBy): ?object
	{
		try {
			return $this->getById($id, $indexBy);

		} catch (NoResultException) {
			return null;
		}
	}


	public function createOptions(
		callable $where = null,
		?int $maxResults = null,
		?string $indexBy = self::DefaultIndexBy,
	): array {
		$result = $this->findBy($where ?? fn($qb) => $qb, $maxResults, $indexBy);
		$items = [];

		foreach ($result as $id => $item) {
			if (!$item instanceof HtmlOption) {
				continue;
			}

			$items[$id] = $item->createOption();
		}

		return $items;
	}


	public function countBy(
		callable $where,
		?int $maxResults = null,
		?string $indexBy = self::DefaultIndexBy,
	): int|float|array {
		$where = fn($qb) => ($where($qb) ?? $qb)->select('COUNT('.self::DefaultIdentifier.')');
		$qb = $this->createQueryBuilder(self::DefaultAlias, $indexBy, $where);
		$qb->setMaxResults($qb->getMaxResults() ?? $maxResults);

		$query = $qb->getQuery();

		if ($qb->getMaxResults() === 1) {
			return $query->getSingleScalarResult();
		}

		return $query->getScalarResult();
	}


	/**
	 * @internal
	 */
	public function fetchAssociations(array|object $result, array $columns): void
	{
		if (!is_array($result)) {
			$result = [$result];
		}

		$idPartial = Strings::replace(self::DefaultIdentifier, '/([a-z]+)\.(\w+)/i', '$1.{$2}');
		$qb = $this->createQueryBuilder(self::DefaultAlias, self::DefaultIndexBy)
			->select('partial '.$idPartial)->where(self::DefaultAlias.' IN (:rows)');

		foreach ($columns as $alias => $column) {
			$qb->leftJoin($column, $alias)->addSelect($alias);
		}

		try {
			$qb->getQuery()
				->setParameter('rows', $result)
				->getResult();

		} catch (NoResultException) {
		}
	}


	public function createQueryBuilder(string $alias, string $indexBy = null, callable $where = null): QueryBuilder
	{
		$qb = $this->entityManager->createQueryBuilder()->select($alias)
			->from($this->entityName, $alias, $indexBy);

		if ($where) {
			$qb = $where($qb) ?: $qb;
		}

		return $qb;
	}


	public function createQuery(string $dql = null): Query
	{
		return $this->entityManager->createQuery($dql);
	}


	public function getReference(mixed $id, string $entityName = null): ?object
	{
		if (!$id || empty($id) || !is_numeric($id)) {
			return null;
		}

		return $this->entityManager->getReference($entityName ?: $this->entityName, (int) $id);
	}


	public function getFormReference(string $field, Form $form, bool $findEagerly = true): mixed
	{
		$data = $form->getHttpData(Form::DataLine, $field) ?: null;
		$callback = $this->getReference(...);

		if ($findEagerly) {
			$callback = $this->findById(...);
		}

		if (!Strings::endsWith($field, '[]')) {
			return $callback($data);
		}

		return Arrays::walk($data ?? [], fn($id) => yield $id => $callback($id));
	}


	/**
	 * @throws DBALException
	 */
	public function createPartition(DateTime $date, string $range, string $entityName = null): string
	{
		$tableName = $this->getTableName($entityName);
		$partitionName = $tableName.'_'.$date->format('Ymd');

		$valueFrom = $date->format('Y-m-d');
		$valueTo = (clone $date)->modify($range)->format('Y-m-d');

		$this->query('DROP TABLE IF EXISTS '.$partitionName.' CASCADE;');
		$this->query('CREATE TABLE IF NOT EXISTS '.$partitionName.' PARTITION OF '.$tableName.' FOR VALUES FROM (\''.$valueFrom.'\') TO (\''.$valueTo.'\');');

		return $partitionName;
	}


	public function truncateTable(bool $cascade = false, string $entityName = null): void
	{
		$this->query('TRUNCATE TABLE '.$this->getTableName($entityName).' RESTART IDENTITY'.($cascade == true ? ' CASCADE' : null));
	}


	public function getTableName(string $entityName = null): string
	{
		$entityName = $entityName ?: $this->entityName;
		$metaData = $this->entityManager->getClassMetadata($entityName);
		$tableName = $metaData->getTableName();

		if ($schemaName = $metaData->getSchemaName()) {
			$tableName = $schemaName.'.'.$tableName;
		}

		return $tableName;
	}


	/**
	 * @throws DBALException
	 */
	private function query(string $query): mixed
	{
		return $this->connection->query($query);
	}
}
