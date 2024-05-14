<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity as Identity;

/**
 * @template T of Identity
 */
trait Authorable
{
	/** @var T */
	#[ORM\ManyToOne(targetEntity: Identity::class)]
	#[ORM\JoinColumn(nullable: false)]
	protected Identity $author;


	/**
	 * @param T $author
	 */
	public function setAuthor(Identity $author): void
	{
		$this->author = $author;
	}


	/**
	 * @return T
	 */
	public function getAuthor(): Identity
	{
		return $this->author;
	}


	/**
	 * @param T $author
	 */
	public function isAuthor(Identity $author): bool
	{
		return $this->author->getId() === $author->getId();
	}
}
