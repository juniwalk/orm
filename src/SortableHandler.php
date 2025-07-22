<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM;

use Closure;
use Contributte\Datagrid\Datagrid;
use JuniWalk\ORM\Entity\Interfaces\Sortable;
use JuniWalk\ORM\Exceptions\EntityNotValidException;

class SortableHandler
{
	private readonly ?string $order;

	/**
	 * @param Closure(string $column, ?string $sort): Sortable[] $callback
	 */
	public function __construct(
		private readonly string $column,
		Datagrid $grid,
		private readonly Closure $callback,
	) {
		$grid->findSessionValues();
		$grid->findDefaultSort();

		$this->order = $grid->sort[$column] ?? null;
	}


	/**
	 * @throws EntityNotValidException
	 */
	public function sort(?int $itemId, ?int $prevId, ?int $nextId): void
	{
		[$prevId, $nextId] = match ($this->order) {
			'ASC'	=> [$nextId, $prevId],
			default	=> [$prevId, $nextId],
		};

		$items = ($this->callback)($this->column, $this->order);
		$order = sizeof($items) - 1;

		if (!isset($items[$itemId])) {
			throw new EntityNotValidException('Item '.$itemId.' is not in the items list.');
		}

		$moveDown = $items[$itemId]->getOrder() >= (int) ($items[$nextId] ?? null)?->getOrder();
		$moveUp = $items[$itemId]->getOrder() <= (int) ($items[$nextId] ?? null)?->getOrder();

		foreach ($items as $id => $item) {
			if (!$item instanceof Sortable) {	// @phpstan-ignore instanceof.alwaysTrue (I'd like to keep the check)
				throw new EntityNotValidException($item::class.' has to implement '.Sortable::class);
			}

			if ($id === $itemId && ($prevId || $nextId)) {
				continue;
			}

			if ($id === $nextId && $moveUp) {
				$items[$itemId]->setOrder($order--);
			}

			$item->setOrder($order--);

			if ($id === $prevId && $moveDown) {
				$items[$itemId]->setOrder($order--);
			}
		}
	}
}
