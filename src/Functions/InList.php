<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;


/**
 * "IN_LIST" "(" Column, PARAM [, ...] ")"
 */
final class InList extends FunctionNode
{
	public Node|string $column;

	/** @var Node[] */
	public array $values;


	public function parse(Parser $parser): void
	{
		$lexer = $parser->getLexer();

		$parser->match(TokenType::T_IDENTIFIER);
		$parser->match(TokenType::T_OPEN_PARENTHESIS);

		$this->column = $parser->ArithmeticPrimary();

		do {
			$parser->match(TokenType::T_COMMA);

			$this->values[] = $parser->InParameter();

		} while ($lexer->lookahead?->type !== TokenType::T_CLOSE_PARENTHESIS);

		$parser->match(TokenType::T_CLOSE_PARENTHESIS);
	}


	public function getSql(SqlWalker $sqlWalker): string
	{
		$column = $sqlWalker->walkArithmeticPrimary($this->column);
		$values = implode(', ', array_map($sqlWalker->walkInParameter(...), $this->values));

		return "string_to_array({$column}, ',') && ARRAY[{$values}]";
	}
}
