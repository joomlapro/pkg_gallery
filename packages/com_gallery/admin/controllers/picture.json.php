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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Picture JSON controller for Gallery Component.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class GalleryControllerPicture extends JControllerLegacy
{
	/**
	 * Upload a picture.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	function upload()
	{
		// Load the parameters.
		$params = JComponentHelper::getParams('com_media');

		$response = new JObject;

		// Check for request forgeries.
		if (!JSession::checkToken('request'))
		{
			$response->files[] = array(
				'status' => '0',
				'error' => JText::_('JINVALID_TOKEN')
			);

			echo json_encode($response);
			return;
		}

		// Get the user.
		$user = JFactory::getUser();

		JLog::addLogger(array('text_file' => 'upload.error.php'), JLog::ALL, array('upload'));

		// Get some data from the request.
		$files   = $this->input->files->get('files', '', 'array');
		$picture = $this->input->post->get('picture', '', 'array');

		foreach ($files as $file)
		{
			if ($_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024)
				|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('upload_max_filesize')) * 1024 * 1024
				|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('post_max_size')) * 1024 * 1024
				|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('memory_limit')) * 1024 * 1024)
			{
				$response->files[] = array(
					'status' => '0',
					'error' => JText::_('COM_GALLERY_ERROR_WARNFILETOOLARGE')
				);

				echo json_encode($response);
				return;
			}

			// Set FTP credentials, if given.
			JClientHelper::setCredentialsFromRequest('ftp');

			// Make the filename safe.
			$filename = uniqid() . '.' . JFile::getExt($file['name']);

			if (isset($file['name']))
			{
				// The request is valid.
				$err = null;

				$folder = COM_GALLERY_BASE . '/' . $picture['album_id'];

				if (!JFolder::exists($folder))
				{
					JFolder::create($folder);
				}

				if (!JFolder::exists($folder . '/thumbnails'))
				{
					JFolder::create($folder . '/thumbnails');
				}

				$filepath = JPath::clean($folder . '/' . $filename);

				$mediaHelper = new JHelperMedia;

				if (!$mediaHelper->canUpload($file, 'com_media'))
				{
					JLog::add('Invalid: ' . $filepath . ': ' . $err, JLog::INFO, 'upload');

					$response->files[] = array(
						'status' => '0',
						'error' => JText::_($err)
					);

					echo json_encode($response);
					return;
				}

				// Trigger the onContentBeforeSave event.
				JPluginHelper::importPlugin('content');

				$dispatcher = JEventDispatcher::getInstance();
				$object_file = new JObject($file);
				$object_file->filepath = $filepath;
				$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.file', &$object_file, true));

				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins.
					JLog::add('Errors before save: ' . $object_file->filepath . ' : ' . implode(', ', $object_file->getErrors()), JLog::INFO, 'upload');

					$response->files[] = array(
						'status' => '0',
						'error' => JText::plural('COM_GALLERY_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors))
					);

					echo json_encode($response);
					return;
				}

				if (JFile::exists($object_file->filepath))
				{
					// File exists.
					JLog::add('File exists: ' . $object_file->filepath . ' by user_id ' . $user->id, JLog::INFO, 'upload');

					$response->files[] = array(
						'status' => '0',
						'error' => JText::_('COM_GALLERY_ERROR_FILE_EXISTS')
					);

					echo json_encode($response);
					return;
				}
				elseif (!$user->authorise('core.create', 'com_media'))
				{
					// File does not exist and user is not authorised to create.
					JLog::add('Create not permitted: ' . $object_file->filepath . ' by user_id ' . $user->id, JLog::INFO, 'upload');

					$response->files[] = array(
						'status' => '0',
						'error' => JText::_('COM_GALLERY_ERROR_CREATE_NOT_PERMITTED')
					);

					echo json_encode($response);
					return;
				}

				if (!JFile::upload($object_file->tmp_name, $object_file->filepath))
				{
					// Error in upload.
					JLog::add('Error on upload: ' . $object_file->filepath, JLog::INFO, 'upload');

					$response->files[] = array(
						'status' => '0',
						'error' => JText::_('COM_GALLERY_ERROR_UNABLE_TO_UPLOAD_FILE')
					);

					echo json_encode($response);
					return;
				}
				else
				{
					// Load the parameters.
					$params = JComponentHelper::getParams('com_gallery');

					$max_size = explode('x', $params->get('max_size', '1280x720'));
					$thumb_size = explode('x', $params->get('thumb_size', '280x280'));

					$JImage = new JImage($object_file->filepath);

					try
					{
						$image = $JImage->cropResize($max_size[0], $max_size[1], false);
						$image->toFile($object_file->filepath);

						$thumbnail = $JImage->cropResize($thumb_size[0], $thumb_size[1], false);
						$thumbnail->toFile($folder . '/thumbnails/' . $filename);
					}
					catch (Exception $e)
					{
						$app->enqueueMessage($e->getMessage(), 'error');
					}

					// Trigger the onContentAfterSave event.
					$dispatcher->trigger('onContentAfterSave', array('com_media.file', &$object_file, true));

					JLog::add($picture['album_id'], JLog::INFO, 'upload');

					// Get an instance of the generic picture model.
					$model = JModelLegacy::getInstance('Picture', 'GalleryModel', array('ignore_request' => true));

					// Attempt to save the picture.
					$data = array(
						'id'          => 0,
						'album_id'    => $picture['album_id'],
						'title'       => JFile::stripExt($picture['title']),
						'description' => $picture['description'],
						'filename'    => $filename,
						'size'        => $file['size'],
						'type'        => $file['type'],
						'state'       => 1,
						'ordering'    => 0
					);

					// Save the data.
					$model->save($data);

					// Get the item.
					$item = $model->getItem();

					$response->files[] = array(
						'status'      => '1',
						'error'       => JText::sprintf('COM_GALLERY_UPLOAD_COMPLETE', substr($object_file->filepath, strlen(JPATH_ROOT))),
						'id'          => $item->id,
						'title'       => $item->title,
						'description' => $item->description,
						'filename'    => $item->filename,
						'size'        => $item->size,
						'ordering'    => $item->ordering
					);

					echo json_encode($response);
					return;
				}
			}
			else
			{
				$response->files[] = array(
					'status' => '0',
					'error' => JText::_('COM_GALLERY_ERROR_BAD_REQUEST')
				);

				echo json_encode($response);
				return;
			}
		}
	}

	/**
	 * Method to delete picture using ajax.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function deletePictureAjax()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		// Get the application.
		$app = JFactory::getApplication();

		// Get data from request.
		$albumId  = $app->input->getInt('album_id');
		$filename = $app->input->get('filename');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__gallery_albums_pictures'))
			->where($db->quoteName('filename') . ' = ' . $db->quote($filename));

		// Set the query and execute the delete.
		$db->setQuery($query);

		try
		{
			$result = $db->execute();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		try
		{
			JFile::delete(COM_GALLERY_BASE . '/' . $albumId . '/' . $filename);
			JFile::delete(COM_GALLERY_BASE . '/' . $albumId . '/thumbnails/' . $filename);
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		$result = array($filename => $result);

		// Output a JSON array.
		echo json_encode($result);

		// Close the application.
		$app->close();
	}
}
