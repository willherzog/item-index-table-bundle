<?php

namespace WHSymfony\WHItemIndexTableBundle\View;

use Symfony\Component\String\Inflector\EnglishInflector;

use WHSymfony\WHItemIndexTableBundle\Exception\MultipleDefaultSortByColumnsException;

/**
 * The view for an item index table.
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
class ItemTable
{
	/**
	 * Contains both the singular and plural forms of the HTML class for this table's item type.
	 *
	 * @var string[] Always two keys: `sing` and `plur`
	 */
	final public readonly array $htmlClass;

	private array $columns = [];
	private bool $haveDefaultSortByColumn = false;

	/**
	 * @param string $itemClassSing Singular form of HTML class for the particular item type
	 * @param string|null $itemClassPlur (Optional) Plural form of HTML class for the particular item type (determined automatically if not set)
	 *
	 * @uses EnglishInflector to pluralize $itemClassSing if $itemClassPlur is not set
	 */
	final public function __construct(string $itemClassSing, ?string $itemClassPlur = null)
	{
		if( $itemClassPlur === null ) {
			$itemClassParts = explode('-', $itemClassSing);
			$last = array_key_last($itemClassParts);
			$itemClassParts[$last] = (new EnglishInflector())->pluralize($itemClassParts[$last])[0];
			$itemClassPlur = implode('-', $itemClassParts);
		}

		$this->htmlClass = [
			'sing' => $itemClassSing,
			'plur' => $itemClassPlur
		];
	}

	/**
	 * @throws MultipleDefaultSortByColumnsException if attempting to add more than one column with $isDefaultSortByColumn = TRUE
	 */
	final public function addColumn(ItemTableColumn $column): static
	{
		if( $column->isDefaultSortByColumn ) {
			if( $this->haveDefaultSortByColumn ) {
				throw new MultipleDefaultSortByColumnsException('This item index table already has a default sort-by column (there can be only one!).');
			} else {
				$this->haveDefaultSortByColumn = true;
			}
		}

		$this->columns[] = $column;

		return $this;
	}

	/**
	 * @return ItemTableColumn[]
	 */
	final public function getColumns(): array
	{
		return $this->columns;
	}
}
