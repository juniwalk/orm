<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity as Identity;

/**
 * @template T of Identity
 */
trait IdentityOptional
{
	/** @var ?T */
	#[ORM\ManyToOne(targetEntity: Identity::class)]
	protected ?Identity $user = null;


	/**
	 * @param ?T $user
	 */
	public function setUser(?Identity $user): void
	{
		$this->user = $user;
	}


	/**
	 * @return ?T
	 */
	public function getUser(): ?Identity
	{
		return $this->user;
	}
}
