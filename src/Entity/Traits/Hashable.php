<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Hashable
{
	#[ORM\Column(type: 'string', length: 8, nullable: true)]
	protected ?string $hash = null;


	final public function getHash(): string
	{
		return $this->hash ?? $this->createHash();
	}


	#[ORM\PreFlush]
	final public function createHash(): string
	{
		$hash = serialize($this->createHashParams());
		return $this->hash = substr(sha1($hash), 0, 8);
	}


	abstract protected function createHashParams(): mixed;
}
