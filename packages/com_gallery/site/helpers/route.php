<?php
/**
 * @package     Gallery
 * @subpackage  com_gallery
 *
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @copyright   Copyright (C) 2014 AtomTech, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Gallery Component Route Helper.
 *
 * @static
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
abstract class GalleryHelperRoute
{
	/**
	 * The lookup array.
	 *
	 * @var     array
	 */
	protected static $lookup = array();

	/**
	 * The language lookup array.
	 *
	 * @var     array
	 */
	protected static $lang_lookup = array();

	/**
	 * Method to get a route configuration for the album view.
	 *
	 * @param   integer  $id        The route of the album item.
	 * @param   integer  $catid     The id of the category.
	 * @param   string   $language  The language code, default value of * means all.
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function getAlbumRoute($id, $catid = 0, $language = 0)
	{
		// Initialiase variables.
		$needles = array(
			'album' => array((int) $id)
		);

		// Create the link.
		$link = 'index.php?option=com_gallery&view=album&id=' . $id;

		if ((int) $catid > 1)
		{
			$categories = JCategories::getInstance('Gallery');
			$category = $categories->get((int) $catid);

			if ($category)
			{
				$needles['category']   = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];

				$link .= '&catid=' . $catid;
			}
		}

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];

				$needles['language'] = $language;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Method to get a route configuration for the category view.
	 *
	 * @param   integer  $catid     The id of the category.
	 * @param   string   $language  The language code, default value of * means all.
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id       = $catid->id;
			$category = $catid;
		}
		else
		{
			$id = (int) $catid;
			$category = JCategories::getInstance('Gallery')->get($id);
		}

		if ($id < 1 || !($category instanceof JCategoryNode))
		{
			$link = '';
		}
		else
		{
			$needles = array();

			// Create the link.
			$link   = 'index.php?option=com_gallery&view=category&id=' . $id;
			$catids = array_reverse($category->getPath());

			$needles['category']   = $catids;
			$needles['categories'] = $catids;

			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				self::buildLanguageLookup();

				if (isset(self::$lang_lookup[$language]))
				{
					$link .= '&lang=' . self::$lang_lookup[$language];

					$needles['language'] = $language;
				}
			}

			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}

		return $link;
	}

	/**
	 * Method to get a route configuration for the form view.
	 *
	 * @param   integer  $id      The id of the album.
	 * @param   string   $return  The return page variable.
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function getFormRoute($id, $return = null)
	{
		// Create the link.
		if ($id)
		{
			$link = 'index.php?option=com_gallery&task=album.edit&g_id=' . $id;
		}
		else
		{
			$link = 'index.php?option=com_gallery&task=album.edit&g_id=0';
		}

		if ($return)
		{
			$link .= '&return=' . $return;
		}

		return $link;
	}

	/**
	 * Builds the language lookup array.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected static function buildLanguageLookup()
	{
		if (count(self::$lang_lookup) == 0)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');

			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				self::$lang_lookup[$lang->lang_code] = $lang->sef;
			}
		}
	}

	/**
	 * Method to find the item.
	 *
	 * @param   array  $needles  The needles to find.
	 *
	 * @return  null
	 *
	 * @since   3.2
	 */
	protected static function _findItem($needles = null)
	{
		// Initialiase variables.
		$app      = JFactory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();

			$component  = JComponentHelper::getComponent('com_gallery');
			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[] = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}

					if (isset($item->query['id']))
					{
						/*
						Here it will become a bit tricky.
						language != * can override existing entries.
						language == * cannot override existing entries.
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		$active = $menus->getActive();

		if ($active && $active->component == 'com_gallery' && ($active->language == '*' || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link.
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}
}
