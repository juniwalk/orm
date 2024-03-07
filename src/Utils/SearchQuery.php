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
	protected const CharNot = '!';

	protected const ModifierPartial = ':*';

	protected const MethodFollow = '<->';
	protected const MethodAnd = '&';
	protected const MethodOr = '|';

	public function __construct(
		protected string $query,
		protected string $methodDefault = self::MethodAnd,
	) {
		$query = trim($query);

		if (str_ends_with($query, self::CharFollow)) {
			$query = rtrim($query, self::CharPartial).self::CharPartial;
		}

		$this->query = strtolower($query);
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
			$methodNext = null;

			if (in_array($token, ['and', 'a', self::MethodAnd])) {
				$output = substr($output, 0, -3);
				$methodNext = $method;
				$method = self::MethodAnd;
				$token = '';
			}

			if (in_array($token, ['or', 'nebo', self::MethodOr])) {
				$output = substr($output, 0, -3);
				$methodNext = $method;
				$method = self::MethodOr;
				$token = '';
			}

			if (str_starts_with($token, self::CharFollow)) {
				$token = substr($token, 1);
				$method = self::MethodFollow;
			}

			if (str_ends_with($token, self::CharFollow)) {
				$token = substr($token, 0, -1);
				$method = $this->methodDefault;
			}

			if (str_ends_with($token, self::CharNot)) {
				$token = substr($token, 0, -1);
			}

			if (str_ends_with($token, self::CharPartial)) {
				$token = str_replace(self::CharPartial, self::ModifierPartial, $token);
			}

			$output .= $token;

			if ($key <> $lastKey) {
				$output .= ' '.$method.' ';
			}

			if ($methodNext) {
				$method = $methodNext;
			}
		}

		return $output;
	}
}
