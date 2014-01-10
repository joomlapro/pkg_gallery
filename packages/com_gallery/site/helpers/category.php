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
 * Gallery Component Category Tree.
 *
 * @static
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno@atomtech.com.br>
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
