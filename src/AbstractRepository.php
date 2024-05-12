<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NoResultException;
use JuniWalk\ORM\Entity\Interfaces\HtmlOption;
use JuniWalk\ORM\Enums\Display;
use JuniWalk\ORM\Exceptions\EntityNotFoundException;
use JuniWalk\Utils\Arrays;
use JuniWalk\Utils\Strings;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Ramsey\Uuid\UuidInterface as Uuid;

abstract class AbstractRepository
{
	public const DefaultAlias = 'e';
	public const DefaultIdentifier = 'e.id';
	public const DefaultIndexBy = self::DefaultIdentifier;

	protected readonly EntityManager $entityManager;
	protected readonly Connection $connection;

	/** @var class-string */
	protected string $entityName;

	/**
	 * @throws EntityNotFoundException
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->connection = $entityManager->getConnection();
		$this->entityManager = $entityManager;

		if (!isset($this->entityName) || !class_exists($this->entityName)) {
			throw EntityNotFoundException::fromClass($this->entityName ?? null);
		}
	}


	/**
	 * @return object[]
	 * @throws NoResultException
	 */
	public function getBy(
		callable $where,
		?int $maxResults = null,
		?string $indexBy = self::DefaultIndexBy,
	): array {
		$qb = $this->createQueryBuilder(self::DefaultAlias, $indexBy, $where);
		$qb->setMaxResults($qb->getMaxResults() ?? $maxResults);

		if (!$result = $qb->getQuery()->getResult()) {
			throw new NoResultException;
		}

		/** @var object[] */
		return $result;
	}


	/**
	 * @throws NoResultException
	 */
	public function getOneBy(callable $where, ?string $indexBy = self::DefaultIndexBy): object
	{
		$qb = $this->createQueryBuilder(self::DefaultAlias, $indexBy, $where);
		$qb->setMaxResults(1);

		/** @var object */
		return $qb->getQuery()->getSingleResult();
	}


	/**
	 * @return object[]
	 */
	public function findBy(
		callable $where,
		?int $maxResults = null,
		?string $indexBy = self::DefaultIndexBy,
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
	 * @param  int|string|Uuid|null $id
	 * @throws NoResultException
	 */
	public function getById(mixed $id, ?string $indexBy = self::DefaultIndexBy): object
	{
		$where = fn($qb) => $qb->where(self::DefaultIdentifier.' = :id')->setParameter('id', $id);
		return $this->getOneBy($where, $indexBy);
	}


	/**
	 * @param int|string|Uuid|null $id
	 */
	public function findById(mixed $id, ?string $indexBy = self::DefaultIndexBy): ?object
	{
		try {
			return $this->getById($id, $indexBy);

		} catch (NoResultException) {
			return null;
		}
	}


	/**
	 * @return Html[]
	 */
	public function createOptions(
		?callable $where = null,
		?int $maxResults = null,
		?string $indexBy = self::DefaultIndexBy,
		?Display $display = null,
	): array {
		$result = $this->findBy($where ?? fn($qb) => $qb, $maxResults, $indexBy);
		$display ??= Display::Large;
		$items = [];

		foreach ($result as $id => $item) {
			if (!$item instanceof HtmlOption) {
				continue;
			}

			$items[$id] = $item->createOption($display);
		}

		return $items;
	}


	/**
	 * @return int|float|array<int|float>
	 */
	public function countBy(
		callable $where,
		?int $maxResults = null,
		?string $indexBy = self::DefaultIndexBy,
	): int|float|array {
		$qb = $this->createQueryBuilder(self::DefaultAlias, $indexBy, function($qb) use ($where) {
			$qb->select('COUNT('.self::DefaultIdentifier.')');
			return $where($qb) ?? $qb;
		});

		$qb->setMaxResults($qb->getMaxResults() ?? $maxResults);
		$query = $qb->getQuery();

		if ($qb->getMaxResults() === 1) {
			/** @var int|float */
			return $query->getSingleScalarResult();
		}

		/** @var array<int|float> */
		return $query->getScalarResult();
	}


	/**
	 * @param object|object[] $result
	 * @param array<string, string> $columns
	 */
	public function fetchAssociations(object|array $result, array $columns): void
	{
		if (!is_array($result)) {
			$result = [$result];
		}

		if (empty($result) || empty($columns)) {
			return;
		}

		$idPartial = Strings::replace(self::DefaultIdentifier, '/^([a-z]+)\.(\w+)$/i', '$1.{$2}');
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


	public function createQueryBuilder(string $alias, ?string $indexBy = null, ?callable $where = null): QueryBuilder
	{
		$qb = $this->entityManager->createQueryBuilder()->select($alias)
			->from($this->entityName, $alias, $indexBy);

		if ($where) {
			$qb = $where($qb) ?? $qb;
		}

		return $qb;
	}


	public function createQuery(string $dql): Query
	{
		return $this->entityManager->createQuery($dql);
	}


	/**
	 * @param int|string|Uuid|null $id
	 * @param class-string|null $entityName
	 */
	public function getReference(mixed $id, ?string $entityName = null): ?object
	{
		if (is_null($id)) {
			return null;
		}

		return $this->entityManager->getReference($entityName ?: $this->entityName, $id);
	}


	/**
	 * @throws \Exception
	 */
	public function getFormReference(string $field, Form $form, bool $fetchEagerly = true): ?object
	{
		if (str_ends_with($field, '[]')) {
			throw new \Exception('Call getFormReferences to get list of them.');
		}

		/** @var string|null */
		$data = $form->getHttpData(Form::DataLine, $field) ?: null;
		$callback = match ($fetchEagerly) {
			false => $this->getReference(...),
			default => $this->findById(...),
		};

		return $callback($data);
	}


	/**
	 * @return object[]|null
	 */
	public function getFormReferences(string $field, Form $form, bool $fetchEagerly = true): array|null
	{
		if (!str_ends_with($field, '[]')) {
			$field .= '[]';
		}

		/** @var mixed[]|null */
		$data = $form->getHttpData(Form::DataLine, $field) ?: null;
		$callback = match ($fetchEagerly) {
			false => $this->getReference(...),
			default => $this->findById(...),
		};

		return Arrays::walk($data ?? [], fn($id) => yield $id => $callback($id));
	}


	/**
	 * @param  class-string|null $entityName
	 * @throws DriverException
	 */
	public function createPartition(DateTime $date, string $range, ?string $entityName = null): string
	{
		$tableName = $this->getTableName($entityName);
		$partitionName = $tableName.'_'.$date->format('Ymd');

		$valueFrom = $date->format('Y-m-d');
		$valueTo = (clone $date)->modify($range)->format('Y-m-d');

		$this->query('DROP TABLE IF EXISTS '.$partitionName.' CASCADE;');
		$this->query('CREATE TABLE IF NOT EXISTS '.$partitionName.' PARTITION OF '.$tableName.' FOR VALUES FROM (\''.$valueFrom.'\') TO (\''.$valueTo.'\');');

		return $partitionName;
	}


	/**
	 * @param  class-string|null $entityName
	 * @throws DriverException
	 */
	public function truncateTable(bool $cascade = false, ?string $entityName = null): void
	{
		$this->query('TRUNCATE TABLE '.$this->getTableName($entityName).' RESTART IDENTITY'.($cascade == true ? ' CASCADE' : null));
	}


	/**
	 * @param  class-string|null $entityName
	 */
	public function getTableName(?string $entityName = null): string
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
	 * @throws DriverException
	 */
	private function query(string $query): mixed
	{
		return $this->connection->query($query);
	}
}
