<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\ORM\Entity\Interfaces\Identified;	// ! Used for @phpstan
use JuniWalk\ORM\Exceptions\EntityNotPersistedException;
use Ramsey\Uuid\UuidInterface as Uuid;

/**
 * @phpstan-require-implements Identified
 */
trait IdentifierUUID
{
	#[ORM\Id]
	#[ORM\Column(type: 'uuid', unique: true, nullable: false)]
	protected Uuid $id;


	public function __clone(): void
	{
		unset($this->id);
	}


	/**
	 * @throws EntityNotPersistedException
	 */
	public function getId(): Uuid
	{
		if (!isset($this->id)) {
			throw EntityNotPersistedException::fromEntity($this);
		}

		return $this->id;
	}


	/**
	 * @throws EntityNotPersistedException
	 */
	public function getIdString(): string
	{
		return $this->getId()->toString();
	}


	public function isIdAvailable(): bool
	{
		throw new EntityNotPersistedException('Calling isIdAvailable is not reliable for UUID identifiers');
	}
}
