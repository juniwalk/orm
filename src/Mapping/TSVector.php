<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Mapping;

use Attribute;
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


	public function createDefinition(string $fieldType, ClassMetadata $metadata): string
	{
		$columns = [];

		foreach ($this->fields as $field) {
			$column = $metadata->getColumnName($field);
			$columns[$column] = "to_tsvector('{$this->language}'::regconfig, (COALESCE(\"{$column}\", ''::character varying))::text)";
		}

		$sql = array_shift($columns);

		foreach ($columns as $columnDefinition) {
			$sql = "({$sql} || {$columnDefinition})";
		}

		return $fieldType.' GENERATED ALWAYS AS ('.$sql.') STORED';
	}
}
