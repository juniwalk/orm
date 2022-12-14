<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{
	#[ORM\Column(type: 'datetimetz', options: ['default' => 'CURRENT_TIMESTAMP'])]
	private DateTime $created;

	#[ORM\Column(type: 'datetimetz', nullable: true)]
	private ?DateTime $modified = null;


	public function getCreated(): DateTime
	{
		return clone $this->created;
	}


	public function setModified(?DateTimeInterface $modified): void
	{
		$this->modified = $modified ? clone $modified : new DateTime;
	}


	public function getModified(): ?DateTimeInterface
	{
		if (!$this->modified) {
			return null;
		}

		return clone $this->modified;
	}


	public function getTimestamp(): DateTimeInterface
	{
		return clone ($this->modified ?: $this->created);
	}


	#[ORM\PreUpdate]
	public function onUpdate(): void
	{
		$this->modified = new DateTime;
	}


	#[ORM\PrePersist]
	public function onPersist(): void
	{
		$this->created = new DateTime;
	}
}
