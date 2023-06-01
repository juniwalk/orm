<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Mapping;

use Attribute;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadata;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class TSVector
{
	public function __construct(
		public array $fields,
		public string $language = 'simple',
		// public bool $generated = true,
		// public bool $stored = true,
	) {}


	public function createDefinition(string $fieldType, ClassMetadata $metadata, AbstractPlatform $platform): string
	{
		return $fieldType.' GENERATED ALWAYS AS ('.$this->createExpression($metadata, $platform).') STORED';
	}


	public function createExpression(ClassMetadata $metadata, AbstractPlatform $platform): string
	{
		$columns = [];

		foreach ($this->fields as $field) {
			$column = $metadata->getQuotedColumnName($field, $platform);
			$columns[$column] = "to_tsvector('{$this->language}'::regconfig, (COALESCE({$column}, ''::character varying))::text)";
		}

		$expression = array_shift($columns);

		foreach ($columns as $columnDefinition) {
			$expression = "({$expression} || {$columnDefinition})";
		}

		return $expression;
	}
}
