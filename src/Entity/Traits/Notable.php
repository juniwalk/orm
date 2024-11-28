<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Notable
{
	#[ORM\Column(type: 'text', nullable: true)]
	protected ?string $note = null;


	public function setNote(?string $note): void
	{
		if (!is_null($note)) {
			$note = html_entity_decode($note);
		}

		$this->note = $note ?: null;
	}


	public function getNote(): ?string
	{
		return $this->note;
	}
}
