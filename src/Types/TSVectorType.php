<?php declare(strict_types=1);

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


	public function getSQLDeclaration(array $column, AbstractPlatform $platform)
	{
		return self::TYPE;
	}


	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return (null === $value) ? null : (string) $value;
	}


	public function requiresSQLCommentHint(AbstractPlatform $platform)
	{
		return true;
	}
}
