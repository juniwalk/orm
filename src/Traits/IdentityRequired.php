<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity as Identity;

trait IdentityRequired
{
	#[ORM\ManyToOne(targetEntity: Identity::class)]
	#[ORM\JoinColumn(nullable: false)]
	protected Identity $user;


	public function setUser(Identity $user): void
	{
		$this->user = $user;
	}


	public function getUser(): Identity
	{
		return $this->user;
	}
}
