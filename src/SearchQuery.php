<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM;

use Stringable;

use function array_key_last;
use function in_array;
use function preg_split;
use function rtrim;
use function str_replace;
use function str_starts_with;
use function str_ends_with;
use function strtolower;
use function substr;

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
		$query = $this->cleanup($query);

		if (!str_ends_with($query, self::CharFollow)) {
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
		if (!$tokens = preg_split('/\s+/', $this->query)) {
			$tokens = [];
		}

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

			if (str_ends_with($token, self::CharPartial)) {
				$token = str_replace(self::CharPartial, self::ModifierPartial, $token);
			}

			$output .= $this->cleanup($token);

			if ($key <> $lastKey) {
				$output .= ' '.$method.' ';
			}

			if ($methodNext) {
				$method = $methodNext;
			}
		}

		return $this->cleanup($output, true);
	}


	private function cleanup(string $token, bool $trimOnly = false): string
	{
		if ($trimOnly === false) {
			$token = strtr($token, [
				self::ModifierPartial => self::CharPartial,
				'<' => null,
				'>' => null,
				'(' => null,
				')' => null,
				':' => ' ',
			]);
		}

		return trim($token, " \n\r\t\v\x00".implode('', [
			self::MethodAnd,
			self::MethodOr,
			self::CharNot,
		]));
	}
}
