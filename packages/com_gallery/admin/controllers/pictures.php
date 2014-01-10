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
 * Pictures list controller class.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class GalleryControllerPictures extends JControllerAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var     string
	 * @since   3.2
	 */
	protected $text_prefix = 'COM_GALLERY_PICTURES';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy
	 *
	 * @since   3.2
	 */
	public function getModel($name = 'Picture', $prefix = 'GalleryModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to get pictures using ajax.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function getPicturesAjax()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		// Get the application.
		$app = JFactory::getApplication();

		// Get data from request.
		$albumId = $this->input->getInt('album_id');

		// Get an instance of the generic pictures model.
		$model = JModelLegacy::getInstance('Pictures', 'GalleryModel', array('ignore_request' => true));
		$model->setState('list.select', 'a.id, a.title, a.description, a.filename, a.size, a.state, a.ordering');
		$model->setState('list.ordering', 'a.ordering');
		$model->setState('list.direction', 'desc');
		$model->setState('list.limit', 0);
		$model->setState('filter.album_id', $albumId);

		$items = $model->getItems();

		$results = new JObject;
		$results->files = (array) $items;

		// Output a JSON object
		echo json_encode($results);

		// Close the application.
		$app->close();
	}
}
