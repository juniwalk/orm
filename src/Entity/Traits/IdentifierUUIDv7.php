<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\ORM\Entity\Interfaces\Identified;	// ! Used for @phpstan
use Ramsey\Uuid\Doctrine\UuidV7Generator as UuidGenerator;
use Ramsey\Uuid\UuidInterface as Uuid;

/**
 * @phpstan-require-implements Identified
 */
trait IdentifierUUIDv7
{
	use IdentifierUUID;

	#[ORM\Id, ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class: UuidGenerator::class)]
	#[ORM\Column(type: 'uuid', unique: true, nullable: false)]
	protected Uuid $id;	// ! Cannot be readonly | See doctrine/orm #9538 & #9863
}
