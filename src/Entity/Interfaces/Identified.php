<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Interfaces;

use Ramsey\Uuid\UuidInterface as Uuid;

interface Identified
{
	public function getId(): int|string|Uuid|null;
	public function isPersisted(): bool;
}
