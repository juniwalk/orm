<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity as Identity;

trait Finishable
{
	#[ORM\Column(type: 'boolean')]
	protected bool $isFinished = false;

	#[ORM\ManyToOne(targetEntity: Identity::class)]
	protected ?Identity $finishedBy = null;


	public function setFinished(bool $isFinished, ?Identity $by = null): void
	{
		$this->isFinished = $isFinished;
		$this->finishedBy = $by;
	}


	public function isFinished(): bool
	{
		return $this->isFinished;
	}


	public function setFinishedBy(?Identity $finishedBy = null): void
	{
		$this->finishedBy = $finishedBy;
	}


	public function getFinishedBy(): ?Identity
	{
		return $this->finishedBy;
	}
}
