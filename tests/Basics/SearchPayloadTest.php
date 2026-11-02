<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\ORM\Tests\Basics;

use JuniWalk\ORM\Entity\Interfaces\HtmlOption;
use JuniWalk\ORM\Enums\Display;
use JuniWalk\ORM\SearchPayload;
use Nette\Utils\Html;
use Tester\Assert;
use Tester\TestCase;

require __DIR__.'/../bootstrap.php';

/**
 * @testCase
 */
final class SearchPayloadTest extends TestCase
{
	public function testPayloadWithGroups(): void
	{
		$payload = new SearchPayload;
		$payload->addItems([
			['id' => 1, 'text' => 'Apple', 'group' => 'Favorite Fruits'],
			['id' => 2, 'text' => 'Other'],
			['id' => 3, 'text' => 'Pear', 'group' => 'Favorite Fruits'],
		]);

		Assert::same([
			'results' => [
				[
					'text' => 'favorite-fruits',
					'children' => [
						['id' => 1, 'text' => 'Apple', 'content' => null, 'group' => 'Favorite Fruits', 'icon' => null, 'color' => null, 'disabled' => false],
						['id' => 3, 'text' => 'Pear', 'content' => null, 'group' => 'Favorite Fruits', 'icon' => null, 'color' => null, 'disabled' => false],
					],
				],
				['id' => 2, 'text' => 'Other', 'content' => null, 'group' => null, 'icon' => null, 'color' => null, 'disabled' => false],
			],
			'pagination' => ['more' => false, 'found' => 3, 'page' => 1],
		], $this->decodePayload($payload));
	}


	public function testPayloadWithoutGroups(): void
	{
		$payload = new SearchPayload;
		$payload->setGroupsAllowed(false);
		$payload->addItems([
			['id' => 1, 'text' => 'Apple', 'group' => 'Favorite Fruits'],
			['id' => 2, 'text' => 'Other'],
			['id' => 3, 'text' => 'Pear', 'group' => 'Favorite Fruits'],
		]);

		Assert::same([
			'results' => [
				['id' => 1, 'text' => 'Favorite Fruits - Apple', 'content' => null, 'group' => null, 'icon' => null, 'color' => null, 'disabled' => false],
				['id' => 2, 'text' => 'Other', 'content' => null, 'group' => null, 'icon' => null, 'color' => null, 'disabled' => false],
				['id' => 3, 'text' => 'Favorite Fruits - Pear', 'content' => null, 'group' => null, 'icon' => null, 'color' => null, 'disabled' => false],
			],
			'pagination' => ['more' => false, 'found' => 3, 'page' => 1],
		], $this->decodePayload($payload));
	}


	public function testDisplaySmall(): void
	{
		$payload = new SearchPayload;
		$payload->setDisplay(Display::Small);
		$payload->addItem($this->createEntity());

		Assert::same(Display::Small, $payload->getDisplay());
		Assert::same('small', $this->decodePayload($payload)['results'][0]['text']);
	}


	public function testDisplayLarge(): void
	{
		$payload = new SearchPayload;
		$payload->setDisplay(Display::Large);
		$payload->addItem($this->createEntity());

		Assert::same('large', $this->decodePayload($payload)['results'][0]['text']);
	}


	public function testDisplayDefault(): void
	{
		$payload = new SearchPayload;
		$payload->addItem($this->createEntity());

		Assert::null($payload->getDisplay());
		Assert::same('default', $this->decodePayload($payload)['results'][0]['text']);
	}


	private function createEntity(): HtmlOption
	{
		return new class implements HtmlOption {
			public function createOption(?Display $display = null): Html
			{
				return Html::el('option', ['value' => 1])->setText($display?->value ?? 'default');
			}
		};
	}


	private function decodePayload(SearchPayload $payload): mixed
	{
		$json = json_encode($payload);

		if ($json === false) {
			throw new \RuntimeException('Failed to encode search payload: ' . json_last_error_msg());
		}

		return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
	}
}

(new SearchPayloadTest)->run();
