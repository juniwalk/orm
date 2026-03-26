<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{
	#[ORM\Column(type: 'datetimetz_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
	protected readonly DateTimeImmutable $created;

	#[ORM\Column(type: 'datetimetz_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
	protected DateTimeImmutable $modified;


	public function setCreated(DateTimeInterface $created): void
	{
		$this->created ??= DateTimeImmutable::createFromInterface($created);
	}


	public function getCreated(): DateTimeImmutable
	{
		return $this->created;
	}


	public function setModified(DateTimeInterface $modified): void
	{
		$this->modified = DateTimeImmutable::createFromInterface($modified);
	}


	public function getModified(): DateTimeImmutable
	{
		return $this->modified;
	}


	#[ORM\PrePersist]
	public function onCreated(): void
	{
		$this->created ??= new DateTimeImmutable;
		$this->modified = new DateTimeImmutable;
	}


	#[ORM\PreUpdate]
	public function onModified(): void
	{
		$this->modified = new DateTimeImmutable;
	}
}
