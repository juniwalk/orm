<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity as Identity;

trait Ownerable
{
	#[ORM\ManyToOne(targetEntity: Identity::class)]
	protected ?Identity $owner = null;


	public function setOwner(?Identity $owner): void
	{
		$this->owner = $owner;
	}


	public function getOwner(): ?Identity
	{
		return $this->owner;
	}


	public function isOwner(?Identity $owner): bool
	{
		return $this->owner?->getId() === $owner?->getId();
	}
}
