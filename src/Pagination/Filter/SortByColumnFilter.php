<?php

namespace WHSymfony\WHItemIndexTableBundle\Pagination\Filter;

use Symfony\Component\HttpFoundation\Request;

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
class SortByColumnFilter implements ItemFilter, HasRequestQuery, HasDefaultValue
{
	public const SORT_BY_REQUEST_QUERY = 'sortby';
	public const SORT_DIR_REQUEST_QUERY = 'sortdir';

	protected readonly array $columnNames;
	protected readonly string $defaultSortByColumn;

	private readonly string $sortByColumn;
	private readonly SortDirection $sortByDirection;

	public function __construct(ItemTable $tableView, protected readonly bool $throwForInvalidColumn = false, bool $useColumnSortByProperty = false)
	{
		$columnNames = [];

		foreach( $tableView->getColumns() as $tableColumn )  {
			if( !$tableColumn->sortByProperty ) {
				continue;
			}

			$columnName = $useColumnSortByProperty ? $tableColumn->sortByProperty : $tableColumn->slug;

			if( $tableColumn->isDefaultSortByColumn ) {
				$this->defaultSortByColumn = $columnName;
			}

			$columnNames[$columnName] = $tableColumn->sortByProperty;
		}

		if( !isset($this->defaultSortByColumn) ) {
			throw new InvalidItemTableOrColumnException('The $tableView argument must include a default sort-by column.');
		}

		$this->columnNames = $columnNames;
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
				if( $this->columnNames === [] || key_exists($columnNameFromRequest, $this->columnNames) ) {
					$this->sortByColumn = $columnNameFromRequest;
				} elseif( $this->columnNames !== [] && $this->throwForInvalidColumn ) {
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
		$sortByProperty = $this->getSortByColumn();

		if( $this->columnNames !== [] ) {
			$sortByProperty = $this->columnNames[$sortByProperty];
		}

		if( isset($this->sortByDirection) ) {
			$paginator->setOrderBy($sortByProperty, $this->sortByDirection === SortDirection::Ascending);
		} else {
			$paginator->setOrderBy($sortByProperty);
		}
	}
}
