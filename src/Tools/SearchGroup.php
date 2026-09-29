<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\ORM\Tools;

use JsonSerializable;

use function array_values;

class SearchGroup implements JsonSerializable
{
	public string $text;

	/**
	 * @var SearchResult[]
	 */
	public array $children = [];


	public function __construct(string $text)
	{
		$this->text = $text;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array
	{
		return [
			'text'		=> $this->text,
			'children'	=> array_values($this->children),
		];
	}
}
