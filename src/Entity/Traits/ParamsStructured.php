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
use LogicException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

trait ParamsStructured
{
	private PropertyAccessor $__accessor;

	/** @var array<string, mixed> */
	#[ORM\Column(type: 'json', options: ['jsonb' => true, 'default' => '[]'])]
	protected array $params = [];


	/**
	 * @throws InvalidArgumentException
	 */
	public function setParam(string $key, mixed $value): void
	{
		if ($value && !$value = Format::serializable($value)) {
			throw new InvalidArgumentException('Value '.gettype($value).' cannot be serialized');
		}

		$this->__accessor()->setValue(
			$this->params,	// @phpstan-ignore assign.propertyType (Don't know why it happens)
			$this->__path($key),
			$value,
		);

		if (is_null($value)) {
			$params = Arrays::flatten($this->params);
			unset($params[$key]);
			$this->params = Arrays::unflatten($params);
		}
	}


	public function getParam(string $key): mixed
	{
		return $this->__accessor()->getValue(
			$this->params,
			$this->__path($key),
		);
	}


	/**
	 * @return array<string, mixed>
	 */
	public function getParams(): array
	{
		return $this->params;
	}


	/**
	 * @throws LogicException
	 */
	private function __accessor(): PropertyAccessor
	{
		if (!isset($this->__accessor) && !class_exists(PropertyAccessor::class)) {
			throw new LogicException('Missing symfony/property-access package.');
		}

		return $this->__accessor ??= PropertyAccess::createPropertyAccessor();
	}


	private function __path(string $key): string
	{
		return '['.str_replace('.', '][', $key).']';
	}
}
