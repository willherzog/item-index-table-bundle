<?php

namespace WHSymfony\WHItemIndexTableBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use WHSymfony\WHItemIndexTableBundle\Config\SortDirection;
use WHSymfony\WHItemIndexTableBundle\Exception\InvalidItemTableOrColumnException;
use WHSymfony\WHItemIndexTableBundle\Pagination\Filter\SortByColumnFilter;
use WHSymfony\WHItemIndexTableBundle\View\ItemTableColumn;
use WHSymfony\WHItemIndexTableBundle\View\UseSortByPropertyForRequests;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
class WHItemIndexTableExtension extends AbstractExtension
{
	public function __construct(
		private readonly bool $toggleDirectionForSameColumn,
		private readonly RequestStack $requestStack
	)
	{}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('sort_by_column_route_params', [$this, 'sortByColumnRouteParams'])
		];
	}

	public function sortByColumnRouteParams(ItemTableColumn $column, bool $isCurrentSortByColumn, ?SortDirection $directionToForce = null): array
	{
		if( !$column->sortByProperty ) {
			throw new InvalidItemTableOrColumnException(sprintf('Table column with slug "%s" is not a "sort-by" column (i.e. its sortByProperty is empty).', $column->slug));
		}

		$request = $this->requestStack->getCurrentRequest();

		// Filter out "sortby" and "sortdir"
		$requestQueries = array_filter(
			$request->query->all(),
			fn($queryName) => !in_array($queryName, [
				SortByColumnFilter::SORT_BY_REQUEST_QUERY,
				SortByColumnFilter::SORT_DIR_REQUEST_QUERY
			], true),
			ARRAY_FILTER_USE_KEY
		);

		if( $column instanceof UseSortByPropertyForRequests ) {
			$requestQueries[SortByColumnFilter::SORT_BY_REQUEST_QUERY] = $column->sortByProperty;
		} else {
			$requestQueries[SortByColumnFilter::SORT_BY_REQUEST_QUERY] = $column->slug;
		}

		if( $directionToForce !== null ) {
			$newSortDir = $directionToForce;
		} else {
			if( $isCurrentSortByColumn && $request->query->has(SortByColumnFilter::SORT_DIR_REQUEST_QUERY) ) {
				$currentSortDir = strtoupper($request->query->getString(SortByColumnFilter::SORT_DIR_REQUEST_QUERY));
				$currentSortDir = SortDirection::tryFrom($currentSortDir);
			}

			if( isset($currentSortDir) ) {
				$newSortDir = $this->toggleDirectionForSameColumn ? $currentSortDir->toggle() : $currentSortDir;
			} else {
				$newSortDir = $column->defaultSortDirection;
			}
		}

		$requestQueries[SortByColumnFilter::SORT_DIR_REQUEST_QUERY] = strtolower($newSortDir->value);

		return array_merge($request->attributes->get('_route_params'), $requestQueries);
	}
}
