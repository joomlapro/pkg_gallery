<?php
/**
 * @package     Gallery
 * @subpackage  com_gallery
 *
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @copyright   Copyright (C) 2013 AtomTech, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Albums helper.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class AlbumsHelper extends JHelperContent
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $categoryId  The category ID.
	 * @param   integer  $id          The item ID.
	 * @param   string   $assetName   The asset name.
	 *
	 * @return  JObject  A JObject containing the allowed actions.
	 *
	 * @since   3.2
	 */
	public static function getActions($categoryId = 0, $id = 0, $assetName = '')
	{
		// Initialiase variables.
		$user   = JFactory::getUser();
		$result = new JObject;
		$path   = JPATH_ADMINISTRATOR . '/components/' . $assetName . '/access.xml';

		if (empty($id) && empty($categoryId))
		{
			$section = 'component';
		}
		elseif (empty($id))
		{
			$section = 'category';
			$assetName .= '.category.' . (int) $categoryId;
		}
		else
		{
			$section = 'album';
			$assetName .= '.album.' . (int) $id;
		}

		$actions = JAccess::getActionsFromFile($path, "/access/section[@name='" . $section . "']/");

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Method to get first picture from album.
	 *
	 * @param   integer  $id  The id of album.
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function getFirstImage($id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('filename')
			->from($db->quoteName('#__gallery_albums_pictures'))
			->where($db->quoteName('album_id') . ' = ' . $db->quote($id))
			->order($db->quoteName('ordering') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query, 0, 1);

		try
		{
			$picture = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		if (!$picture)
		{
			return JUri::root(true) . '/media/com_gallery/images/no-image.png';
		}

		return COM_GALLERY_BASEURL . '/' . $id . '/thumbnails/' . $picture;
	}

	/**
	 * Method to get pictures from album.
	 *
	 * @param   integer  $id  The album id.
	 *
	 * @return  object
	 *
	 * @since   3.2
	 */
	public static function getPictures($id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('title, filename')
			->from($db->quoteName('#__gallery_albums_pictures'))
			->where($db->quoteName('state') . ' = ' . $db->quote('1'))
			->where($db->quoteName('album_id') . ' = ' . $db->quote($id))
			->order($db->quoteName('ordering') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$pictures = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		foreach ($pictures as $picture)
		{
			$picture->image = COM_GALLERY_BASEURL . '/' . $id . '/' . $picture->filename;
			$picture->thumbnail = COM_GALLERY_BASEURL . '/' . $id . '/thumbnails/' . $picture->filename;
		}

		return $pictures;
	}
}
