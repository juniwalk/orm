<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\ORM;

use JsonSerializable;
use JuniWalk\ORM\Entity\Interfaces\HtmlOption;
use JuniWalk\ORM\Tools\SearchGroup;
use JuniWalk\ORM\Tools\SearchResult;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Throwable;

use function array_values;
use function array_slice;
use function is_null;

class SearchPayload implements JsonSerializable
{
	private bool $isGroupsAllowed = true;
	private ?int $maxResults = null;
	private int $pageCount = 0;
	private int $page;

	private Processor $processor;

	/** @var array<string|int, SearchResult|SearchGroup> */
	private array $items = [];


	public function __construct(?int $page = null, ?int $maxResults = null)
	{
		$this->processor = new Processor;
		$this->maxResults = $maxResults;
		$this->page = $page ?? 1;
		$this->pageCount = 0;
	}


	public function setPage(int $page): void
	{
		$this->page = $page;
	}


	public function getPage(): int
	{
		return $this->page;
	}


	public function setMaxResults(?int $maxResults): void
	{
		$this->maxResults = $maxResults;
	}


	public function getMaxResults(): ?int
	{
		if (is_null($this->maxResults)) {
			return null;
		}

		// ? Add one to maxResults to check if there are more results available
		return $this->maxResults + 1;
	}


	public function getFirstResult(): ?int
	{
		if (is_null($this->maxResults)) {
			return null;
		}

		return ($this->page -1) * $this->maxResults;
	}


	public function setGroupsAllowed(bool $groupsAllowed): void
	{
		$this->isGroupsAllowed = $groupsAllowed;
	}


	public function isGroupsAllowed(): bool
	{
		return $this->isGroupsAllowed;
	}


	/**
	 * @param mixed[] $items
	 */
	public function addItems(iterable $items): static
	{
		foreach ($items as $item) {
			$this->addItem($item);
		}

		return $this;
	}


	public function addItem(mixed $item): void
	{
		$item = $this->checkStructure($item);
		$key = (string) $item->id;

		if ($group = $this->createGroup($item)) {
			$group->children[$key] = $item;

		} else {
			$this->items[$key] = $item;
		}

		$this->pageCount += 1;
	}


	/**
	 * @return array{results: list<SearchGroup|SearchResult>, pagination: array{more: bool, found: int, page: int}}
	 */
	public function getPayload(): array
	{
		$results = array_values($this->items);
		$results = array_slice($results, 0, $this->maxResults);

		return [
			'results'		=> $results,
			'pagination'	=> [
				'more'		=> $this->maxResults && $this->pageCount > $this->maxResults,
				'found'		=> $this->pageCount,
				'page'		=> $this->page,
			],
		];
	}


	public function jsonSerialize(): mixed
	{
		return $this->getPayload();
	}


	/**
	 * @throws Throwable
	 */
	protected function createGroup(SearchResult $result): ?SearchGroup
	{
		if (!$groupName = $result->group) {
			return null;
		}

		$groupName = Strings::webalize($groupName);
		$group = $this->items[$groupName] ?? null;

		if ($group instanceof SearchGroup) {
			return $group;
		}

		if ($group instanceof SearchResult) {
			throw new \RuntimeException('A SearchResult with the same group name already exists.');
		}

		return $this->items[$groupName] = new SearchGroup($groupName);
	}


	/**
	 * @throws Throwable
	 */
	protected function checkStructure(mixed $data): SearchResult
	{
		if ($data instanceof HtmlOption) {
			$data = $data->createOption();
		}

		if ($data instanceof Html && $data->getName() == 'option') {
			$data = [
				'id'		=> $data->getValue(),
				'text'		=> $data->getText(),
				'content'	=> $data->getAttribute('data-content'),
				'group'		=> $data->getAttribute('data-group'),
				'icon'		=> $data->getAttribute('data-icon'),
				'color'		=> $data->getAttribute('data-color'),
				'disabled'	=> $data->getDisabled() ?? false,
			];
		}

		try {
			$item = $this->processor->process(Expect::from(new SearchResult), $data);

			if (!$item instanceof SearchResult) {
				throw new \RuntimeException('Processed item is not an instance of SearchResult.');
			}

		} catch (Throwable $e) {
			throw $e;
		}

		if ($this->isGroupsAllowed === false) {
			$item->prependWithGroupName();
		}

		return $item;
	}
}
