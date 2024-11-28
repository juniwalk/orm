<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\ORM\Entity\Interfaces\Identified;	// ! Used for @phpstan
use JuniWalk\ORM\Exceptions\EntityNotPersistedException;

/**
 * @phpstan-require-implements Identified
 */
trait Identifier
{
	#[ORM\Column(type: 'integer', unique: true, nullable: false)]
	#[ORM\GeneratedValue(strategy: 'IDENTITY')]
	#[ORM\Id]
	protected int $id;	// ! Cannot be readonly | See doctrine/orm #9538 & #9863


	/**
	 * @return ($require is true ? int : ?int)
	 * @throws EntityNotPersistedException
	 */
	public function getId(bool $require = false): ?int
	{
		if ($require && $this->isNotPersisted()) {
			throw EntityNotPersistedException::fromEntity($this);
		}

		return $this->id ?? null;
	}


	public function __clone(): void
	{
		unset($this->id);
	}


	public function isNotPersisted(): bool
	{
		return !isset($this->id);
	}
}
