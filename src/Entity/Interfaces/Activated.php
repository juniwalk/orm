<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Interfaces;

interface Activated
{
	public function setActive(bool $active): void;
	public function isActive(): bool;
}
