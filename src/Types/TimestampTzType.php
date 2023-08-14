<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Types;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Exception;

class TimestampTzType extends Type
{
	public const TYPE = 'timestamptz';
	private const FORMAT = 'Y-m-d H:i:s.uO';

	public function getName(): string
	{
		return self::TYPE;
	}


	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return 'TIMESTAMP(6) WITH TIME ZONE';
	}


	/**
	 * @throws ConversionException
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTime
	{
		if ($value === null || $value instanceof DateTimeInterface) {
			return $value;
		}

		try {
			$datetime = DateTime::createFromFormat(self::FORMAT, $value);

		} catch (Exception $e) {
			throw ConversionException::conversionFailedFormat($value, $this->getName(), self::FORMAT, $e);
		}

		if (!$datetime && !($datetime = new DateTime($value))) {
			throw ConversionException::conversionFailedFormat($value, $this->getName(), self::FORMAT);
		}

		return $datetime;
	}


	/**
	 * @throws ConversionException
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
	{
		if (null === $value) {
			return $value;
		}

		if ($value instanceof DateTimeInterface) {
			return $value->format(self::FORMAT);
		}

		throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTime']);
	}


	public function requiresSQLCommentHint(AbstractPlatform $platform): bool
	{
		return true;
	}
}
