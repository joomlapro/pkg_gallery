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

// Load dependent classes.
require_once __DIR__ . '/albums.php';

/**
 * Featured list controller class.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class GalleryControllerFeatured extends GalleryControllerAlbums
{
	/**
	 * Removes an item.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function delete()
	{
		// Check for request forgeries.
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Initialiase variables.
		$user = JFactory::getUser();
		$ids  = $this->input->get('cid', array(), 'array');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.delete', 'com_gallery.album.' . (int) $id))
			{
				// Prune items that you can not delete.
				unset($ids[$i]);

				JError::raiseNotice(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
			}
		}

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->featured($ids, 0))
			{
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_gallery&view=featured');
	}

	/**
	 * Method to publish a list of albums.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function publish()
	{
		parent::publish();

		$this->setRedirect('index.php?option=com_gallery&view=featured');
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   3.2
	 */
	public function getModel($name = 'Feature', $prefix = 'GalleryModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
