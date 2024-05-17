<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

use JuniWalk\ORM\SearchQuery;
use Tester\Assert;
use Tester\TestCase;

require '../bootstrap.php';

/**
 * @testCase
 */
final class SearchQueryTest extends TestCase
{
	public function setUp() {}
	public function tearDown() {}


	/**
	 * @dataProvider argumentMatrix
	 */
	public function testQueryFormat(string $query, string $expect): void
	{
		Assert::same((new SearchQuery($query))->format(), $expect);
	}


	/**
	 * @return iterable<array{query: string, expect: string}>
	 */
	public function argumentMatrix(): iterable
	{
		static $matrix = [
			'This AND That'	=> 'this & that*',
			'This or That'	=> 'this | that*',
			'"This That"'	=> 'this <-> that',

			// Detected issues from production
			'this:that & '	=> 'this & that*',	// 2024-03-27 -	K2moto (mixed with shit)
			'a this'		=> 'this*',			// 2024-04-11 - K2moto (starts with [a])
			'this (them)'	=> 'this & them*',	// 2024-04-16 - Elvis (use of parenthesis)
		];

		foreach ($matrix as $query => $expect) {
			yield ['query' => $query, 'expect' => $expect];
		}
	}
}

(new SearchQueryTest)->run();
