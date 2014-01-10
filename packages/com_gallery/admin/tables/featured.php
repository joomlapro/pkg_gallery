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
 * Featured Table class.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class GalleryTableFeatured extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  Driver A database connector object.
	 *
	 * @since   3.2
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__gallery_albums_frontpage', 'album_id', $db);
	}
}
