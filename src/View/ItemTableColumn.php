<?php

namespace WHSymfony\WHItemIndexTableBundle\View;

use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator;

use WHSymfony\WHItemIndexTableBundle\Config\SortDirection;

/**
 * The view for an individual column of an item index table.
 *
 * @author Will Herzog <willherzog@gmail.com>
 */
readonly class ItemTableColumn
{
	public ?callable $sortByFunc;
	public ?SortDirection $defaultSortDirection;
	public string $htmlClass;

	final public function __construct(
		/** @param string Request query value (for sort-by columns) and (if $htmlClass is not set) the HTML class for this column */
		public string $slug,
		/** @param string The header label for this column */
		public string $label,
		/** @param string The template to include or render for table cells in this column */
		public string $view,
		/** @param string|callable (Optional) (Setting this makes this a "sort-by" column) The entity property / database column name for sort-by purposes OR a callback function which will recieve an `ItemPaginator` and a `SortDirection` (or `null`) as arguments */
		string|callable|null $sortByProperty = null,
		/** @param SortDirection (Optional) The default sort direction for this column (i.e. ascending or descending) */
		SortDirection $defaultSortDirection = SortDirection::Ascending,
		/** @param string (Optional) Value to use for HTML "class" attribute (the value for the $slug property is used if this is not set) */
		string|null $htmlClass = null
	) {
		if( $sortByProperty !== null ) {
			if( is_callable($sortByProperty) ) {
				$this->sortByFunc = $sortByProperty;
			} else {
				// Default callback implementation matching the logic previously found in SortByColumnFilter::apply()
				$this->sortByFunc = function(ItemPaginator $paginator, ?SortDirection $sortDirection = null) use ($sortByProperty): void {
					if( $sortDirection !== null ) {
						$paginator->setOrderBy($sortByProperty, $sortDirection === SortDirection::Ascending);
					} else {
						$paginator->setOrderBy($sortByProperty);
					}
				};
			}

			$this->defaultSortDirection = $defaultSortDirection;
		} else {
			$this->sortByFunc = null;
			$this->defaultSortDirection = null;
		}

		$this->htmlClass = $htmlClass ?? $slug;
	}
}
