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
 * "floor" "(" Column ")"
 * @link www.doctrine-project.org
 */
final class Floor extends FunctionNode
{
	public Node|string $column;


	public function parse(Parser $parser): void
	{
		$parser->match(TokenType::T_IDENTIFIER); // (2)
		$parser->match(TokenType::T_OPEN_PARENTHESIS); // (3)

		$this->column = $parser->SimpleArithmeticExpression(); // (4)

		$parser->match(TokenType::T_CLOSE_PARENTHESIS); // (3)
	}


	public function getSql(SqlWalker $sqlWalker): string
	{
		$column = $sqlWalker->walkSimpleArithmeticExpression($this->column);

		return 'floor('.$column.')';
	}
}
