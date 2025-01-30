<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

use JuniWalk\ORM\Entity\Traits\ParamsSimplified;
use JuniWalk\ORM\Entity\Traits\ParamsStructured;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class ParamsTest extends TestCase
{
	public function testParams_Simplified(): void
	{
		$params = [
			'contract.number' => 7534129651,
			'contract.expire' => '2024-01-01T00:00:00+00:00',
		];

		$entity = new class { use ParamsSimplified; };
		$entity->setParam('contract.number', $params['contract.number']);
		$entity->setParam('contract.expire', DateTime::createFromFormat(DateTime::ATOM, $params['contract.expire']));
		$entity->setParam('contract.extend', null);

		Assert::same('2024-01-01T00:00:00+00:00', $entity->getParam('contract.expire'));
		Assert::same($params, $entity->getParams());
	}


	public function testParams_Structured(): void
	{
		$params = [
			'contract'	 => [
				'number' => 7534129651,
				'expire' => '2024-01-01T00:00:00+00:00',
			],
		];

		$entity = new class { use ParamsStructured; };
		$entity->setParam('contract.number', $params['contract']['number']);
		$entity->setParam('contract.expire', DateTime::createFromFormat(DateTime::ATOM, $params['contract']['expire']));
		$entity->setParam('contract.extend', null);

		Assert::same('2024-01-01T00:00:00+00:00', $entity->getParam('contract.expire'));
		Assert::same($params, $entity->getParams());
	}
}

(new ParamsTest)->run();
