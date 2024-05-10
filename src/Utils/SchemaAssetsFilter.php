<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Utils;

use Doctrine\DBAL\Schema\AbstractAsset;
use JuniWalk\Utils\Strings;

final class SchemaAssetsFilter
{
	/** @var string[] */
	private array $assets;

	public function __construct(string ...$assets)
	{
		$this->assets = $assets;
	}


	public function __invoke(string|AbstractAsset $assetName): bool
	{
		if (empty($this->assets)) {
			return true;
		}

		if ($assetName instanceof AbstractAsset) {
			$assetName = $assetName->getName();
		}

		if (in_array($assetName, $this->assets)) {
			return false;
		}

		return !Strings::match($assetName, '/'.implode('|', $this->assets).'/i');
	}
}
