<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JuniWalk\Utils\Arrays;
use JuniWalk\Utils\Format;

trait ParamsStructured
{
	/** @var mixed[] */
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

		$params = Arrays::flatten($this->params);

		if (!$overwrite && isset($params[$key])) {
			return;
		}

		$params[$key] = $value;

		if (is_null($value)) {
			unset($params[$key]);
		}

		$this->params = Arrays::unflatten($params);
	}


	public function getParam(string $key): mixed
	{
		$params = Arrays::flatten($this->params);
		return $params[$key] ?? null;
	}


	/**
	 * @return mixed[]
	 */
	public function getParams(): array
	{
		return $this->params;
	}
}
