<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * "search" "(" Column, Query, Lang ")"
 */
final class Search extends FunctionNode
{
	public Node $column;
	public Node $query;
	public Node $lang;


	public function parse(Parser $parser): void
	{
		$parser->match(Lexer::T_IDENTIFIER); // (2)
		$parser->match(Lexer::T_OPEN_PARENTHESIS); // (3)

		$this->column = $parser->StringPrimary(); // (4)

		$parser->match(Lexer::T_COMMA); // (5)

		$this->query = $parser->InstanceOfParameter(); // (6)

		$parser->match(Lexer::T_COMMA); // (7)

		$this->lang = $parser->StringPrimary(); // (8)

		$parser->match(Lexer::T_CLOSE_PARENTHESIS); // (9)
	}


	public function getSql(SqlWalker $sqlWalker): string
	{
		$column = $sqlWalker->walkSimpleArithmeticExpression($this->column);
		$query = $sqlWalker->walkSimpleArithmeticExpression($this->query);
		$lang = $sqlWalker->walkSimpleArithmeticExpression($this->lang);

		return "{$column} @@ websearch_to_tsquery({$lang}, {$query})";
	}
}
