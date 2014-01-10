<?php
/**
 * @package     Gallery
 * @subpackage  com_gallery
 *
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @copyright   Copyright (C) 2014 CTIS IT Services. All rights reserved.
 * @license     Commercial License
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Gallery Component Query Helper.
 *
 * @static
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class GalleryHelperQuery
{
	/**
	 * Translate an order code to a field for primary category ordering.
	 *
	 * @param   string  $orderby  The ordering code.
	 *
	 * @return  string  The SQL field(s) to order by.
	 *
	 * @since   3.2
	 */
	public static function orderbyPrimary($orderby)
	{
		switch ($orderby)
		{
			case 'alpha' :
				$orderby = 'c.path, ';
				break;

			case 'ralpha' :
				$orderby = 'c.path DESC, ';
				break;

			case 'order' :
				$orderby = 'c.lft, ';
				break;

			default :
				$orderby = '';
				break;
		}

		return $orderby;
	}

	/**
	 * Translate an order code to a field for secondary category ordering.
	 *
	 * @param   string  $orderby    The ordering code.
	 * @param   string  $orderDate  The ordering code for the date.
	 *
	 * @return  string  The SQL field(s) to order by.
	 *
	 * @since   3.2
	 */
	public static function orderbySecondary($orderby, $orderDate = 'created')
	{
		$queryDate = self::getQueryDate($orderDate);

		switch ($orderby)
		{
			case 'date' :
				$orderby = $queryDate;
				break;

			case 'rdate' :
				$orderby = $queryDate . ' DESC ';
				break;

			case 'alpha' :
				$orderby = 'a.title';
				break;

			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;

			case 'hits' :
				$orderby = 'a.hits DESC';
				break;

			case 'rhits' :
				$orderby = 'a.hits';
				break;

			case 'order' :
				$orderby = 'a.ordering';
				break;

			case 'author' :
				$orderby = 'author';
				break;

			case 'rauthor' :
				$orderby = 'author DESC';
				break;

			case 'front' :
				$orderby = 'a.featured DESC, fp.ordering';
				break;

			default :
				$orderby = 'a.ordering';
				break;
		}

		return $orderby;
	}

	/**
	 * Translate an order code to a field for primary category ordering.
	 *
	 * @param   string  $orderDate  The ordering code.
	 *
	 * @return  string  The SQL field(s) to order by.
	 *
	 * @since   3.2
	 */
	public static function getQueryDate($orderDate)
	{
		$db = JFactory::getDbo();

		switch ($orderDate)
		{
			case 'modified' :
				$queryDate = ' CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END';
				break;

			// Use created if publish_up is not set.
			case 'published' :
				$queryDate = ' CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END ';
				break;

			case 'created' :
			default :
				$queryDate = ' a.created ';
				break;
		}

		return $queryDate;
	}

	/**
	 * Get join information for the voting query.
	 *
	 * @param   JRegistry  $params  An options object for the album.
	 *
	 * @return  array  A named array with "select" and "join" keys.
	 *
	 * @since   3.2
	 */
	public static function buildVotingQuery($params = null)
	{
		if (!$params)
		{
			$params = JComponentHelper::getParams('com_gallery');
		}

		$voting = $params->get('show_vote');

		if ($voting)
		{
			// Calculate voting count.
			$select = ' , ROUND(v.rating_sum / v.rating_count) AS rating, v.rating_count';
			$join   = ' LEFT JOIN #__gallery_rating AS v ON a.id = v.album_id';
		}
		else
		{
			$select = '';
			$join   = '';
		}

		$results = array ('select' => $select, 'join' => $join);

		return $results;
	}

	/**
	 * Method to order the intro albums array for ordering
	 * down the columns instead of across.
	 * The layout always lays the introtext albums out across columns.
	 * Array is reordered so that, when albums are displayed in index order
	 * across columns in the layout, the result is that the
	 * desired album ordering is achieved down the columns.
	 *
	 * @param   array    &$albums   Array of intro text albums.
	 * @param   integer  $numColumns  Number of columns in the layout.
	 *
	 * @return  array  Reordered array to achieve desired ordering down columns.
	 *
	 * @since   3.2
	 */
	public static function orderDownColumns(& $albums, $numColumns = 1)
	{
		$count = count($albums);

		// Just return the same array if there is nothing to change.
		if ($numColumns == 1 || !is_array($albums) || $count <= $numColumns)
		{
			$return = $albums;
		}
		// We need to re-order the intro albums array.
		else
		{
			// We need to preserve the original array keys.
			$keys     = array_keys($albums);

			$maxRows  = ceil($count / $numColumns);
			$numCells = $maxRows * $numColumns;
			$numEmpty = $numCells - $count;
			$index    = array();

			// Calculate number of empty cells in the array.

			// Fill in all cells of the array
			// put -1 in empty cells so we can skip later.

			for ($row = 1, $i = 1; $row <= $maxRows; $row++)
			{
				for ($col = 1; $col <= $numColumns; $col++)
				{
					if ($numEmpty > ($numCells - $i))
					{
						// Put -1 in empty cells.
						$index[$row][$col] = -1;
					}
					else
					{
						// Put in zero as placeholder.
						$index[$row][$col] = 0;
					}

					$i++;
				}
			}

			// Layout the albums in column order, skipping empty cells.
			$i = 0;

			for ($col = 1; ($col <= $numColumns) && ($i < $count); $col++)
			{
				for ($row = 1; ($row <= $maxRows) && ($i < $count); $row++)
				{
					if ($index[$row][$col] != - 1)
					{
						$index[$row][$col] = $keys[$i];

						$i++;
					}
				}
			}

			// Now read the $index back row by row to get albums in right row/col
			// so that they will actually be ordered down the columns (when read by row in the layout).
			$return = array();
			$i = 0;

			for ($row = 1; ($row <= $maxRows) && ($i < $count); $row++)
			{
				for ($col = 1; ($col <= $numColumns) && ($i < $count); $col++)
				{
					$return[$keys[$i]] = $albums[$index[$row][$col]];

					$i++;
				}
			}
		}

		return $return;
	}
}
