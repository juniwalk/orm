<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * "search" "(" Column, Query, Lang ")"
 */
final class Search extends FunctionNode
{
	public Node $column;
	public InputParameter|string $query;
	public Node $lang;


	public function parse(Parser $parser): void
	{
		$parser->match(TokenType::T_IDENTIFIER); // (2)
		$parser->match(TokenType::T_OPEN_PARENTHESIS); // (3)

		$this->column = $parser->StringPrimary(); // (4)

		$parser->match(TokenType::T_COMMA); // (5)

		$this->query = $parser->InstanceOfParameter(); // (6)

		$parser->match(TokenType::T_COMMA); // (7)

		$this->lang = $parser->StringPrimary(); // (8)

		$parser->match(TokenType::T_CLOSE_PARENTHESIS); // (9)
	}


	public function getSql(SqlWalker $sqlWalker): string
	{
		$column = $sqlWalker->walkSimpleArithmeticExpression($this->column);
		$query = $sqlWalker->walkSimpleArithmeticExpression($this->query);
		$lang = $sqlWalker->walkSimpleArithmeticExpression($this->lang);

		return "{$column} @@ to_tsquery({$lang}, {$query})";
	}
}
