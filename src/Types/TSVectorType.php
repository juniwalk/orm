<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class TSVectorType extends Type
{
	public const TYPE = 'tsvector';

	public function getName()
	{
		return self::TYPE;
	}


	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return self::TYPE;
	}


	public function convertToPHPValue($value, AbstractPlatform $platform): ?string
	{
		return (null === $value) ? null : (string) $value;
	}


	public function requiresSQLCommentHint(AbstractPlatform $platform): bool
	{
		return true;
	}
}
