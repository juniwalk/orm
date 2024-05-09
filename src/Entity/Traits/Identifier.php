<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\ORM\Entity\Interfaces\Identified;

/**
 * @phpstan-require-implements Identified
 */
trait Identifier
{
	#[ORM\Column(type: 'integer', unique: true, nullable: false)]
	#[ORM\GeneratedValue(strategy: 'IDENTITY')]
	#[ORM\Id]
	protected int $id;	// ! Cannot be readonly | See doctrine/orm #9538 & #9863


	public function getId(): ?int
	{
		return $this->id ?? null;
	}


	public function __clone()
	{
		unset($this->id);
	}


	public function isPersisted(): bool
	{
		return !isset($this->id);
	}


	/** @deprecated */
	public function isNewEntity(): bool
	{
		return $this->isPersisted();
	}
}
