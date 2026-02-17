<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Types;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Throwable;

class TimestampTzType extends Type
{
	public const Type = 'timestamptz';
	public const Format = 'Y-m-d H:i:s.uO';

	public function getName(): string
	{
		return self::Type;
	}


	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return 'TIMESTAMP(0) WITH TIME ZONE';
	}


	/**
	 * @throws InvalidType
	 */
	public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
	{
		if (is_null($value) || $value instanceof DateTimeInterface) {
			return $value?->format(self::Format);
		}

        throw InvalidType::new($value, self::class, ['null', DateTimeInterface::class]);
	}


	/**
	 * @param  DateTimeInterface|non-empty-string|null $value
	 * @throws InvalidFormat
	 */
	public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DateTime
	{
		if (empty($value)) {
			return null;
		}

		if ($value instanceof DateTimeInterface) {
			return DateTime::createFromInterface($value);
		}

		try {
			return DateTime::createFromFormat(self::Format, $value) ?: new DateTime($value);

		} catch (Throwable $e) {
		}

		throw InvalidFormat::new($value, self::class, self::Format, $e);
	}


	public function requiresSQLCommentHint(AbstractPlatform $platform): bool
	{
		return true;
	}
}
