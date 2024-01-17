<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity as Identity;

trait Authorable
{
	#[ORM\ManyToOne(targetEntity: Identity::class)]
	private ?Identity $author = null;


	public function setAuthor(?Identity $author): void
	{
		$this->author = $author;
	}


	public function getAuthor(): ?Identity
	{
		return $this->author;
	}


	public function isAuthor(?Identity $author): bool
	{
		if (!$author XOR !$this->author) {
			return false;
		}

		if (!$author && !$this->author) {
			return true;
		}

		return $this->author->getId() === $author->getId();
	}
}
