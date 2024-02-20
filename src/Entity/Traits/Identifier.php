<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Identifier
{
	#[ORM\Column(type: 'integer', unique: true, nullable: false)]
	#[ORM\GeneratedValue(strategy: 'IDENTITY')]
	#[ORM\Id]
	// ! Cannot be readonly as Doctrine modifies it on remove
	// ! See doctrine/orm issues #9538 & #9863
	protected /*readonly*/ int $id;


	public function getId(): ?int
	{
		return $this->id ?? null;
	}


	public function __clone()
	{
		unset($this->id);
	}


	public function isNewEntity(): bool
	{
		return !isset($this->id);
	}
}
