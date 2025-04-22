<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use JuniWalk\ORM\Entity\Interfaces\Activated;	// ! Used for @phpstan
use Doctrine\ORM\Mapping as ORM;

/**
 * @phpstan-require-implements Activated
 */
trait Activable
{
	#[ORM\Column(type: 'boolean', options: ['default' => true])]
	protected bool $isActive = true;


	public function setActive(bool $active): void
	{
		$this->isActive = $active;
	}


	public function isActive(): bool
	{
		return $this->isActive;
	}
}
