<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Interfaces;

use Nette\Utils\Html;

interface HtmlOption
{
	public function createOption(): Html;
}
