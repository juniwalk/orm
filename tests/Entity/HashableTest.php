<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

use JuniWalk\ORM\Entity\Traits\Hashable;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class HashableTest extends TestCase
{
	public function testGetHash_String(): void
	{
		$entity = new class {
			use Hashable;

			protected function createHashParams(): mixed
			{
				return 'my-custom-string';
			}
		};

		Assert::same('ddfbbcb5', $entity->getHash());
	}


	public function testGetHash_Array(): void
	{
		$entity = new class {
			use Hashable;

			protected function createHashParams(): mixed
			{
				return ['my', 'custom', 'array'];
			}
		};

		Assert::same('16cc1569', $entity->getHash());
	}
}

(new HashableTest)->run();
