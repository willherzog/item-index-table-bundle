<?php

namespace WHSymfony\WHItemIndexTableBundle\Config;

/**
 * @author Will Herzog <willherzog@gmail.com>
 */
enum SortDirection: string
{
	case Ascending = 'ASC';
	case Descending = 'DESC';

	/**
	 * Returns the opposite SortDirection.
	 */
	public function toggle(): self
	{
		return match($this) {
			self::Ascending => self::Descending,
			self::Descending => self::Ascending
		};
	}
}
