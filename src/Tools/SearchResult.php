<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\ORM\Tools;

use JsonSerializable;
use Ramsey\Uuid\UuidInterface as Uuid;

class SearchResult implements JsonSerializable
{
	public Uuid|string|int $id;
	public string $text;
	public ?string $content = null;
	public ?string $group = null;
	public ?string $icon = null;
	public ?string $color = null;
	public bool $disabled = false;


	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array
	{
		return [
			'id'		=> $this->id,
			'text'		=> $this->text,
			'content'	=> $this->content,
			'group'		=> $this->group,
			'icon'		=> $this->icon,
			'color'		=> $this->color,
			'disabled'	=> $this->disabled,
		];
	}


	public function prependWithGroupName(): void
	{
		if ($this->group === null) {
			return;
		}

		$this->text = $this->group . ' - ' . $this->text;
		$this->group = null;
	}
}
