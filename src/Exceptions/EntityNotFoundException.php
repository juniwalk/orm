<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Exceptions;

use JuniWalk\ORM\Entity\Interfaces\Identified;
use Throwable;

final class EntityNotFoundException extends \RuntimeException
{
	private string $entityName;
	private mixed $id;


	public static function fromField(string $field, mixed $id, ?Throwable $previous = null): self
	{
		$self = new self('Entity in form field "'.$field.'" with id "'.$id.'" was not found.', previous: $previous);	// @phpstan-ignore binaryOp.invalid
		$self->entityName = $field;
		$self->id = $id;

		return $self;
	}


	public static function fromEntity(object $entity, mixed $id = null): static
	{
		if ($entity instanceof Identified) {
			$id = $entity->getId();
		}

		return static::fromClass($entity::class, $id);
	}


	/**
	 * @param class-string|null $entityName
	 */
	public static function fromClass(?string $entityName, mixed $id = null): static
	{
		$entityName ??= 'unknown';

		$self = new static('Entity "'.$entityName.'" was not found.');
		$self->entityName = $entityName;
		$self->id = $id;

		return $self;
	}


	/**
	 * @return string|class-string
	 */
	public function getEntityName(): string
	{
		return $this->entityName;
	}


	public function getId(): mixed
	{
		return $this->id;
	}
}
