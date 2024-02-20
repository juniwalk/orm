<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Interfaces;

interface Sortable
{
	public function setOrder(int $order): void;
	public function getOrder(): int;
}
