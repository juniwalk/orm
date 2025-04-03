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

final class Replace extends FunctionNode
{
	public Node $column;
	public Node $from;
	public Node $to;


	public function parse(Parser $parser): void
	{
		$parser->match(TokenType::T_IDENTIFIER);
		$parser->match(TokenType::T_OPEN_PARENTHESIS);

		$this->column  = $parser->StringPrimary();

		$parser->match(TokenType::T_COMMA);

		$this->from = $parser->StringPrimary();
	
		$parser->match(TokenType::T_COMMA);

		$this->to = $parser->StringPrimary();

		$parser->match(TokenType::T_CLOSE_PARENTHESIS);
	}


	public function getSql(SqlWalker $sqlWalker): string
	{
		return sprintf('REPLACE(%s, %s, %s)',
			$this->column->dispatch($sqlWalker),
			$this->from->dispatch($sqlWalker),
			$this->to->dispatch($sqlWalker)
		);
	}
}
