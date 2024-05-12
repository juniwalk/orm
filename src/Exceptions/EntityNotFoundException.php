<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Exceptions;

final class EntityNotFoundException extends \RuntimeException
{
	public static function fromEntity(object $entity): static
	{
		return static::fromClass($entity::class);
	}


	/**
	 * @param class-string|null $entityName
	 */
	public static function fromClass(?string $entityName): static
	{
		return new static('Entity "'.($entityName ?? 'undefined').'" was not found.');
	}
}
