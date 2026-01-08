<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Interfaces;

interface Identified
{
	public function getId(): mixed;
	public function isIdAvailable(): bool;

	/**
	 * @deprecated Use isIdAvailable instead
	 */
	public function isNotPersisted(): bool;
}
