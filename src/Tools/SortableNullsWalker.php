<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\ORM\Tools;

use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\AST\OrderByItem;
use Doctrine\ORM\Query\SqlWalker;

use function is_array;
use function is_string;
use function str_replace;

/**
 * The SortableNullsWalker is a TreeWalker that walks over a DQL AST and constructs
 * the corresponding SQL to allow ORDER BY x ASC NULLS FIRST|LAST.
 * @see https://gist.github.com/doctrinebot/ccd63ae93fb80415323d
 *
 * [use]
 * $query = $qb->getQuery();
 * $query->setHint(Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);
 * $query->setHint(SortableNullsWalker::FieldKey, [
 *		'p.firstname' => SortableNullsWalker::NullsLast,
 *	]);
 */
class SortableNullsWalker extends SqlWalker
{
	public const string FieldKey = 'sortableNulls.fields';
	public const string NullsFirst = 'NULLS FIRST';
	public const string NullsLast = 'NULLS LAST';

	/** @deprecated Use FieldKey instead */
	public const FIELDS_KEY = self::FieldKey;
	/** @deprecated Use NullsFirst instead */
	public const NULLS_FIRST = self::NullsFirst;
	/** @deprecated Use NullsLast instead */
	public const NULLS_LAST = self::NullsLast;


	/**
	 * @inheritDoc
	 */
	public function walkOrderByItem(OrderByItem $orderByItem): string
	{
		$hint = $this->getQuery()->getHint(self::FieldKey);
		$sql = parent::walkOrderByItem($orderByItem);

		if (empty($hint) || !is_array($hint)) {
			return $sql;
		}

		$expr = $orderByItem->expression;
		$type = strtoupper($orderByItem->type);

		if (!$expr instanceof PathExpression || $expr->type != PathExpression::TYPE_STATE_FIELD) {
			return $sql;
		}

		$index = $expr->identificationVariable.'.'.$expr->field;

		if (!isset($hint[$index]) || !is_string($hint[$index])) {
			return $sql;
		}

		$search = $this->walkPathExpression($expr).' '.$type;
		$sql = str_replace($search, $search.' '.$hint[$index], $sql);

		return $sql;
	}
}
