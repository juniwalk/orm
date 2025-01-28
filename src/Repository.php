<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM;

use BadMethodCallException;
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
use Throwable;

/**
 * @template T of object
 */
abstract class Repository
{
	public const DefaultIdentifier = 'e.id';
	public const DefaultIndexBy = self::DefaultIdentifier;
	public const DefaultAlias = 'e';

	/** @var class-string<T> */
	protected string $entityName;
	protected readonly EntityManager $entityManager;

	/**
	 * @throws EntityNotFoundException
	 */
	public function __construct(EntityManager $entityManager)
	{
		if (!isset($this->entityName) || !class_exists($this->entityName)) {
			throw EntityNotFoundException::fromClass($this->entityName ?? null);
		}

		$this->entityManager = $entityManager;
	}


	/**
	 * @return T[]
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

		/** @var T[] */
		return $result;
	}


	/**
	 * @return T
	 * @throws NoResultException
	 */
	public function getOneBy(callable $where, ?string $indexBy = self::DefaultIndexBy): object
	{
		$qb = $this->createQueryBuilder(self::DefaultAlias, $indexBy, $where);
		$qb->setMaxResults(1);

		/** @var T */
		return $qb->getQuery()->getSingleResult();
	}


	/**
	 * @return T[]
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


	/**
	 * @return ?T
	 */
	public function findOneBy(callable $where, ?string $indexBy = self::DefaultIndexBy): ?object
	{
		try {
			return $this->getOneBy($where, $indexBy);

		} catch (NoResultException) {
			return null;
		}
	}


	/**
	 * @return T
	 * @throws NoResultException
	 */
	public function getById(mixed $id, ?string $indexBy = self::DefaultIndexBy): object
	{
		// TODO: Might need to allow Uuid for example
		if (empty($id) || !is_scalar($id)) {
			throw new NoResultException;
		}

		$where = fn($qb) => $qb->where(self::DefaultIdentifier.' = :id')->setParameter('id', $id);

		try {
			return $this->getOneBy($where, $indexBy);

		// TODO: Do not catch whole Throwable !!!
		} catch (Throwable) {
			// TODO: This is kind of sus, I want to add $previous exception
			throw new NoResultException;
		}
	}


	/**
	 * @return ?T
	 */
	public function findById(mixed $id, ?string $indexBy = self::DefaultIndexBy): ?object
	{
		try {
			return $this->getById($id, $indexBy);

		// TODO: Do not catch whole Throwable !!!
		} catch (Throwable) {
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
	 * return ($maxResults is 1 ? mixed : mixed[])
	 * @return mixed|mixed[]
	 */
	public function countBy(
		callable $where,
		?int $maxResults = null,
		?string $indexBy = self::DefaultIndexBy,
	): mixed {
		$qb = $this->createQueryBuilder(self::DefaultAlias, $indexBy, function($qb) use ($where) {
			$qb->select('COUNT('.self::DefaultIdentifier.')');
			return $where($qb) ?? $qb;
		});

		$qb->setMaxResults($qb->getMaxResults() ?? $maxResults);
		$query = $qb->getQuery();

		if ($qb->getMaxResults() === 1) {
			return $query->getSingleScalarResult();
		}

		return $query->getScalarResult();
	}


	/**
	 * @param T|T[] $result
	 * @param array<string, string> $columns
	 */
	public function fetchAssociations(object|array|null $result, array $columns): void
	{
		if (!is_array($result)) {
			$result = [$result];
		}

		if (empty($result) || empty($columns)) {
			return;
		}

		$idPartial = Strings::replace(self::DefaultIdentifier, '/^([a-z]+)\.(\w+)$/i', '$1.{$2}');
		$qb = $this->createQueryBuilder(self::DefaultAlias, self::DefaultIndexBy)
			->select('partial '.$idPartial)->where(self::DefaultAlias.' IN (:items)');

		foreach ($columns as $alias => $column) {
			$qb->leftJoin($column, $alias)->addSelect($alias);
		}

		try {
			$qb->getQuery()
				->setParameter('items', $result)
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
	 * @return ($id is null ? null : T)
	 */
	public function getReference(mixed $id): ?object
	{
		if (empty($id)) {
			return null;
		}

		return $this->entityManager->getReference($this->entityName, $id);
	}


	/**
	 * @return T|null
	 * @throws BadMethodCallException
	 */
	public function findFormReference(string $field, Form $form): ?object
	{
		if (str_ends_with($field, '[]')) {
			throw new BadMethodCallException('Call getFormReferences to get list of references.');
		}

		/** @var string|null */
		$id = $form->getHttpData(Form::DataLine, $field) ?: null;

		return $this->findById($id);
	}


	/**
	 * @return T
	 * @throws BadMethodCallException
	 * @throws EntityNotFoundException
	 */
	public function getFormReference(string $field, Form $form): object
	{
		if (str_ends_with($field, '[]')) {
			throw new BadMethodCallException('Call getFormReferences to get list of references.');
		}

		/** @var string|null */
		$id = $form->getHttpData(Form::DataLine, $field) ?: null;

		try {
			return $this->getById($id);

		} catch (NoResultException $e) {
			throw EntityNotFoundException::fromField($field, $id, $e);
		}
	}


	/**
	 * @return T[]
	 */
	public function getFormReferences(string $field, Form $form): array
	{
		if (!str_ends_with($field, '[]')) {
			$field .= '[]';
		}

		/** @var mixed[] */
		$data = $form->getHttpData(Form::DataLine, $field) ?? [];

		return Arrays::walk($data, fn($id) => yield $id => $this->getById($id));
	}
}
