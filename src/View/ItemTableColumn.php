<?php

namespace WHSymfony\WHItemIndexTableBundle\View;

use WHSymfony\WHItemIndexTableBundle\Config\SortDirection;

/**
 * The view for an individual column of an item index table.
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
readonly class ItemTableColumn
{
	final public ?SortDirection $defaultSortDirection;
	final public bool $isDefaultSortByColumn;

	final public function __construct(
		/** @var string Request query value (for sort-by columns) + HTML class for this column */
		public string $slug,
		/** @var string The header label for this column */
		public string $label,
		/** @var string The template to include or render for table cells in this column */
		public string $view,
		/** @var string (Optional) The entity property / database column name for sort-by purposes; setting this makes this a "sort-by" column */
		public ?string $sortByProperty = null,
		/** @var SortDirection (Optional) The default sort direction for this column (i.e. ascending or descending) */
		SortDirection $defaultSortDirection = SortDirection::Ascending,
		/** @var bool (Optional) Whether this column should be the *default* sort-by column */
		bool $isDefaultSortByColumn = false
	) {
		if( $this->sortByProperty ) {
			$this->defaultSortDirection = $defaultSortDirection;
			$this->isDefaultSortByColumn = $isDefaultSortByColumn;
		} else {
			$this->defaultSortDirection = null;
			$this->isDefaultSortByColumn = false;
		}
	}
}
