<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Utils;

use Stringable;

class SearchQuery implements Stringable
{
	protected const CharFollow = '"';
	protected const CharPartial = '*';

	protected const ModifierPartial = ':*';

	protected const MethodFollow = '<->';
	protected const MethodAnd = '&';
	protected const MethodOr = '|';

	protected const TupleAnd = ['and', 'a', self::MethodAnd];
	protected const TupleOr = ['or', 'nebo', self::MethodOr];

	public function __construct(
		protected string $query,
		protected string $methodDefault = self::MethodAnd,
	) {
	}


	public function __toString(): string
	{
		return $this->format();
	}


	public function format(): string
	{
		$tokens = preg_split('/\s+/', $this->query);
		$lastKey = array_key_last($tokens);
		$method = $this->methodDefault;
		$output = '';

		foreach ($tokens as $key => $token) {
			$token = strtolower($token);

			if (in_array($token, self::TupleAnd)) {
				$output = substr($output, 0, -3).' '.self::MethodAnd.' ';
				$method = $methodNext = $this->methodDefault;
				continue;
			}

			if (in_array($token, self::TupleOr)) {
				$output = substr($output, 0, -3).' '.self::MethodOr.' ';
				$method = $methodNext = $this->methodDefault;
				continue;
			}

			if (str_starts_with($token, self::CharFollow)) {
				$token = substr($token, 1);
				$methodNext = $method = self::MethodFollow;
			}

			if (str_ends_with($token, self::CharFollow)) {
				$token = substr($token, 0, -1);
				$method = $this->methodDefault;
			}

			if (str_ends_with($token, self::CharPartial)) {
				$token = str_replace(self::CharPartial, self::ModifierPartial, $token);
			}

			$output .= $token;

			if ($key <> $lastKey) {
				$output .= ' '.$method.' ';
			}

			$methodNext ??= $method;
			$method = $methodNext;
		}

		return $output;
	}
}
