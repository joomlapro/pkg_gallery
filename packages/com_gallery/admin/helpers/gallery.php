<?php
/**
 * @package     Gallery
 * @subpackage  com_gallery
 *
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @copyright   Copyright (C) 2013 CTIS IT Services. All rights reserved.
 * @license     Commercial License
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Gallery helper.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class GalleryHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_GALLERY_SUBMENU_ALBUMS'),
			'index.php?option=com_gallery&view=albums',
			$vName == 'albums'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_GALLERY_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_gallery',
			$vName == 'categories'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_GALLERY_SUBMENU_FEATURED'),
			'index.php?option=com_gallery&view=featured',
			$vName == 'featured'
		);
	}
}
