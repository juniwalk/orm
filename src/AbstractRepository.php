<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM;

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

		if (!$this->entityName) {
			throw EntityNotFoundException::fromClass($this->entityName);
		}
	}


	/**
	 * @throws NoResultException
	 */
	public function getBy(callable $where, ?int $maxResults = null): array
	{
		/** @see Hardcoded indexBy might cause issues with entities without $id */
		$qb = $this->createQueryBuilder('e', 'e.id', $where);
		$qb->setMaxResults($qb->getMaxResults() ?? $maxResults);

		return $qb->getQuery()
			->getResult();
	}


	/**
	 * @throws NoResultException
	 */
	public function getOneBy(callable $where): object
	{
		/** @see Hardcoded indexBy might cause issues with entities without $id */
		$qb = $this->createQueryBuilder('e', 'e.id', $where);
		$qb->setMaxResults(1);

		return $qb->getQuery()
			->getSingleResult();
	}


	public function findBy(callable $where, ?int $maxResults = null): array
	{
		try {
			return $this->getBy($where, $maxResults);

		} catch (NoResultException) {
			return [];
		}
	}


	public function findOneBy(callable $where): ?object
	{
		try {
			return $this->getOneBy($where);

		} catch (NoResultException) {
			return null;
		}
	}


	/**
	 * @throws NoResultException
	 */
	public function getById(int $id): object
	{
		return $this->getOneBy(function($qb) use ($id) {
			$qb->where('e.id = :id')->setParameter('id', $id);
		});
	}


	public function findById(int $id): ?object
	{
		try {
			return $this->getById($id);

		} catch (NoResultException) {
			return null;
		}
	}


	public function createOptions(callable $where = null, ?int $maxResults = null): array
	{
		$result = $this->findBy($where ?? fn($qb) => $qb, $maxResults);
		$items = [];

		foreach ($result as $id => $item) {
			if (!$item instanceof HtmlOption) {
				continue;
			}

			$items[$id] = $item->createOption();
		}

		return $items;
	}


	public function countBy(callable $where, ?int $maxResults = null): int|float|array
	{
		/** @see Hardcoded indexBy might cause issues with entities without $id */
		$qb = $this->createQueryBuilder('e', 'e.id', function($qb) use ($where) {
			$qb->select('count(e.id)');
			return ($where($qb) ?: $qb);
		});

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

		$qb = $this->createQueryBuilder('e', 'e.id')
			->select('partial e.{id}')->where('e IN (:rows)');

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


	public function getFormReference(string $field, Form $form): mixed
	{
		$data = $form->getHttpData(Form::DataLine, $field) ?: null;

		if (!Strings::endsWith($field, '[]')) {
			return $this->getReference($data);
		}

		return Arrays::walk($data ?? [], fn($id) => yield $id => $this->getReference($id));
	}


	public function truncateTable(bool $cascade = false, string $entityName = null): void
	{
		$this->query('TRUNCATE TABLE "'.$this->getTableName($entityName).'" RESTART IDENTITY'.($cascade == true ? ' CASCADE' : null));
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
