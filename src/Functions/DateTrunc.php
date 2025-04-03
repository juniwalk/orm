<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * "date_trunc" "(" Interval ", " Column ")"
 */
final class DateTrunc extends FunctionNode
{
	public Node $interval;
	public Node $column;


	public function parse(Parser $parser): void
	{
		$parser->match(TokenType::T_IDENTIFIER); // (2)
		$parser->match(TokenType::T_OPEN_PARENTHESIS); // (3)

		$this->interval = $parser->StringPrimary(); // (4)

        $parser->match(TokenType::T_COMMA); // (5)

		$this->column = $parser->StringPrimary(); // (6)

		$parser->match(TokenType::T_CLOSE_PARENTHESIS); // (7)
	}


	public function getSql(SqlWalker $sqlWalker): string
	{
		$interval = $sqlWalker->walkSimpleArithmeticExpression($this->interval);
		$column = $sqlWalker->walkSimpleArithmeticExpression($this->column);

		return 'date_trunc('.$interval.', '.$column.')';
	}
}
