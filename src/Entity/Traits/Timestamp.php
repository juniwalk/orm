<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{
	#[ORM\Column(type: 'datetimetz', options: ['default' => 'CURRENT_TIMESTAMP'])]
	protected readonly DateTime $created;

	#[ORM\Column(type: 'datetimetz', nullable: true)]
	protected ?DateTime $modified = null;


	public function setCreated(DateTimeInterface $created): void
	{
		$this->created ??= DateTime::createFromInterface($created);
	}


	public function getCreated(): DateTime
	{
		return clone $this->created;
	}


	public function setModified(?DateTimeInterface $modified): void
	{
		if (!is_null($modified)) {
			$modified = DateTime::createFromInterface($modified);
		}

		$this->modified = $modified ?? new DateTime;
	}


	public function getModified(): ?DateTime
	{
		if (!$this->modified) {
			return null;
		}

		return clone $this->modified;
	}


	public function getTimestamp(): DateTime
	{
		return clone ($this->modified ?: $this->created);
	}


	#[ORM\PrePersist]
	public function onCreated(): void
	{
		$this->created ??= new DateTime;
	}


	#[ORM\PreUpdate]
	public function onModified(): void
	{
		$this->modified = new DateTime;
	}
}
