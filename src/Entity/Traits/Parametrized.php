<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JuniWalk\Utils\Format;

trait Parametrized
{
	#[ORM\Column(type: 'json', options: ['jsonb' => true, 'default' => '[]'])]
	protected array $params = [];


	/**
	 * @throws InvalidArgumentException
	 */
	public function setParam(string $key, mixed $value, bool $overwrite = true): void
	{
		if ($value && !$value = Format::scalarize($value)) {
			throw new InvalidArgumentException('Value '.gettype($value).' cannot be scalarized');
		}

		if (!$overwrite && $this->hasParam($key)) {
			return;
		}

		$this->params[$key] = $value;

		if (is_null($value)) {
			unset($this->params[$key]);
		}
	}


	public function getParam(string $key): mixed
	{
		return $this->params[$key] ?? null;
	}


	public function getParams(): array
	{
		return $this->params;
	}


	public function hasParam(string $key): bool
	{
		return isset($this->params[$key]);
	}
}
