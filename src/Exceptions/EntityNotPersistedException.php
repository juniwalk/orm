<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Exceptions;

final class EntityNotPersistedException extends \RuntimeException
{
	public static function fromEntity(object $entity): static
	{
		return static::fromClass($entity::class);
	}


	/**
	 * @param class-string $entityName
	 */
	public static function fromClass(string $entityName): static
	{
		return new static('Entity "'.$entityName.'" is not persisted and thus does not have Id.');
	}
}
