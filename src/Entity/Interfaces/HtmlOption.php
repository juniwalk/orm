<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Interfaces;

use JuniWalk\ORM\Enums\Display;
use Nette\Utils\Html;

interface HtmlOption
{
	public function createOption(?Display $display = null): Html;
}
