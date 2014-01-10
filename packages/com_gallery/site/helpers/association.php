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

// Register dependent classes.
JLoader::register('GalleryHelper', JPATH_ADMINISTRATOR . '/components/com_gallery/helpers/gallery.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');

/**
 * Gallery Component Association Helper.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
abstract class GalleryHelperAssociation extends CategoryHelperAssociation
{
	/**
	 * Method to get the associations for a given item.
	 *
	 * @param   integer  $id    Id of the item.
	 * @param   string   $view  Name of the view.
	 *
	 * @return  array  Array of associations for the item.
	 *
	 * @since   3.2
	 */
	public static function getAssociations($id = 0, $view = null)
	{
		// Load route helper.
		jimport('helper.route', JPATH_COMPONENT_SITE);

		// Initialiase variables.
		$app  = JFactory::getApplication();
		$view = is_null($view) ? $app->input->get('view') : $view;
		$id   = empty($id) ? $app->input->getInt('id') : $id;

		if ($view == 'album')
		{
			if ($id)
			{
				$associations = JLanguageAssociations::getAssociations('com_gallery', '#__gallery_albums', 'com_gallery.item', $id);
				$return       = array();

				foreach ($associations as $tag => $item)
				{
					$return[$tag] = GalleryHelperRoute::getAlbumRoute($item->id, $item->catid, $item->language);
				}

				return $return;
			}
		}

		if ($view == 'category' || $view == 'categories')
		{
			return self::getCategoryAssociations($id, 'com_gallery');
		}

		return array();
	}
}
