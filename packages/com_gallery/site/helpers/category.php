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
 * Gallery Component Category Tree.
 *
 * @static
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class GalleryCategories extends JCategories
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $options  Array of options.
	 *
	 * @since   3.2
	 */
	public function __construct($options = array())
	{
		$options['table']     = '#__gallery_albums';
		$options['extension'] = 'com_gallery';

		parent::__construct($options);
	}
}
