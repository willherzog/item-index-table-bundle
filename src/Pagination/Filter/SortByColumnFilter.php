<?php

namespace WHSymfony\WHItemIndexTableBundle\Pagination\Filter;

use Symfony\Component\HttpFoundation\Request;

use WHSymfony\WHItemPaginatorBundle\Filter\ExcludeFromActiveFiltersCount;
use WHSymfony\WHItemPaginatorBundle\Filter\{HasDefaultValue,HasRequestQuery};
use WHSymfony\WHItemPaginatorBundle\Filter\ItemFilter;
use WHSymfony\WHItemPaginatorBundle\Paginator\ItemPaginator;

use WHSymfony\WHItemIndexTableBundle\Config\SortDirection;
use WHSymfony\WHItemIndexTableBundle\Exception\InvalidItemTableOrColumnException;
use WHSymfony\WHItemIndexTableBundle\Exception\UnknownSortByColumnException;
use WHSymfony\WHItemIndexTableBundle\Pagination\SortByColumnPaginator;
use WHSymfony\WHItemIndexTableBundle\View\ItemTable;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class SortByColumnFilter implements ItemFilter, HasRequestQuery, HasDefaultValue, ExcludeFromActiveFiltersCount
{
	public const SORT_BY_REQUEST_QUERY = 'sortby';
	public const SORT_DIR_REQUEST_QUERY = 'sortdir';

	protected readonly array $sortByColumns;
	protected readonly string $defaultSortByColumn;

	private readonly string $sortByColumn;
	private readonly SortDirection $sortByDirection;

	public function __construct(ItemTable $tableView, protected readonly bool $throwForInvalidColumn = false)
	{
		$defaultSortByColumn = $tableView->getDefaultSortByColumn();
		$columnNames = [];

		foreach( $tableView->getColumns() as $column )  {
			if( !is_callable($column->sortByFunc) ) {
				continue;
			}

			if( !isset($this->defaultSortByColumn) && ($defaultSortByColumn === null || $column->slug === $defaultSortByColumn) ) {
				$this->defaultSortByColumn = $column->slug;
			}

			$columnNames[$column->slug] = $column->sortByFunc;
		}

		if( $columnNames === [] ) {
			throw new InvalidItemTableOrColumnException('The $tableView argument must include one or more sort-by columns (i.e. columns with a non-empty $sortByProperty).');
		}

		if( !isset($this->defaultSortByColumn) ) {
			throw new InvalidItemTableOrColumnException(sprintf('The specified default sort-by column ("%s") did not match any of the actual table columns.', $defaultSortByColumn));
		}

		$this->sortByColumns = $columnNames;
	}

	public function getRequestQueryName(): string
	{
		return self::SORT_BY_REQUEST_QUERY;
	}

	public function getDefaultValue(): mixed
	{
		return $this->defaultSortByColumn;
	}

	public function supports(ItemPaginator $paginator): bool
	{
		return $paginator instanceof SortByColumnPaginator;
	}

	public function isApplicable(Request $request): bool
	{
		if( $request->query->has(self::SORT_BY_REQUEST_QUERY) ) {
			if( ($columnNameFromRequest = $request->query->getString(self::SORT_BY_REQUEST_QUERY)) !== '' ) {
				if( $this->sortByColumns === [] || key_exists($columnNameFromRequest, $this->sortByColumns) ) {
					$this->sortByColumn = $columnNameFromRequest;
				} elseif( $this->sortByColumns !== [] && $this->throwForInvalidColumn ) {
					throw new UnknownSortByColumnException(sprintf('"%s" does not match the name of a valid sort-by column.', $columnNameFromRequest));
				}
			}
		}

		if( $request->query->has(self::SORT_DIR_REQUEST_QUERY) ) {
			$sortDirectionFromRequest = strtoupper($request->query->getString(self::SORT_DIR_REQUEST_QUERY));

			$this->sortByDirection = SortDirection::tryFrom($sortDirectionFromRequest) ?? SortDirection::Ascending;
		}

		return true;
	}

	public function getSortByColumn(): string {
		return $this->sortByColumn ?? $this->defaultSortByColumn;
	}

	public function apply(ItemPaginator $paginator): void
	{
		$this->sortByColumns[$this->getSortByColumn()]($paginator, $this->sortByDirection ?? null);
	}
}
