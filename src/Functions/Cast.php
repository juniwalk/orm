<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Literal;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * "cast" "(" Column, Type ")")
 */
final class Cast extends FunctionNode
{
	public Node|string $column;
	public Node|string $type;


	public function parse(Parser $parser): void
	{
		$parser->match(TokenType::T_IDENTIFIER); // (2)
		$parser->match(TokenType::T_OPEN_PARENTHESIS); // (3)
		$lexer = $parser->getLexer();

		$this->column = $parser->StringPrimary(); // (4)

		switch (true) {
			case $lexer->isNextToken(TokenType::T_COMMA):
				$parser->match(TokenType::T_COMMA); // (5)
				break;
			case $lexer->isNextToken(TokenType::T_AS):
				$parser->match(TokenType::T_AS); // (5)
				break;
		}

		switch (true) {
			case $lexer->isNextToken(TokenType::T_STRING):
				$this->type = $parser->StringPrimary(); // (6)
				break;
			case $lexer->isNextToken(TokenType::T_IDENTIFIER):
				$parser->match(TokenType::T_IDENTIFIER);
				$this->type = new Literal(Literal::STRING, $lexer->token->value ?? 'varchar'); // (6)
				break;
		}

		$parser->match(TokenType::T_CLOSE_PARENTHESIS); // (7)
	}


	/**
	 * @see https://github.com/oroinc/doctrine-extensions/blob/master/src/Oro/ORM/Query/AST/Platform/Functions/Postgresql/Cast.php
	 */
	public function getSql(SqlWalker $sqlWalker): string
	{
		$column = $sqlWalker->walkSimpleArithmeticExpression($this->column);
		$type = $sqlWalker->walkSimpleArithmeticExpression($this->type);

		$type = trim(strtolower($type), '"\'');
		if ($type === 'datetime') {
			return '"timestamp"(' . $column . ')';
		}

		if ($type === 'bool') {
			$type = 'boolean';
		}

		if ($type === 'binary') {
			$type = 'bytea';
		}

		/**
		 * The notations varchar(n) and char(n) are aliases for character varying(n) and character(n), respectively.
		 * character without length specifier is equivalent to character(1). If character varying is used
		 * without length specifier, the type accepts strings of any size. The latter is a PostgreSQL extension.
		 * http://www.postgresql.org/docs/9.2/static/datatype-character.html
		 */
		if ($type === 'string') {
			$type = 'varchar';
		}

		return 'cast(' . $column . ' AS ' . $type . ')';
	}
}
