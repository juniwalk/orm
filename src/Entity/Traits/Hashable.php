<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use BadMethodCallException;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Stringable;

trait Hashable
{
	#[ORM\Column(type: 'string', length: 8, nullable: true)]
	protected ?string $hash = null;


	/**
	 * @throws BadMethodCallException
	 */
	final public function setHash(?string $hash): void
	{
		throw new BadMethodCallException('Setting hash is not allowed');
	}


	public function getHash(): string
	{
		return $this->hash ?? $this->createUniqueHash();
	}


	/**
	 * @throws BadMethodCallException
	 */
	#[ORM\PreFlush]
	public function createUniqueHash(): string
	{
		$hash = match (true) {
			$this instanceof JsonSerializable	=> json_encode($this);
			$this instanceof Stringable			=> strval($this),

			default => throw new BadMethodCallException(
				'Entity has to implement "Stringable|JsonSerializable" or use custom "'.__FUNCTION__.'" method'
			);
		}

		return $this->hash = substr(sha1($hash), 0, 8);
	}
}
