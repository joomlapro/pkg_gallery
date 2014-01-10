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
defined('JPATH_BASE') or die;

/**
 * Supports a modal album picker.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @since       3.2
 */
class JFormFieldModal_Album extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.2
	 */
	protected $type = 'Modal_Album';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		// Initialiase variables.
		$allowEdit  = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language.
		JFactory::getLanguage()->load('com_gallery', JPATH_ADMINISTRATOR);

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script.
		$script = array();

		// Select button script.
		$script[] = 'function jSelectAlbum_' . $this->id . '(id, title, catid, object) {';
		$script[] = '	document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '	document.getElementById("' . $this->id . '_name").value = title;';

		if ($allowEdit)
		{
			$script[] = '	jQuery("#' . $this->id . '_edit").removeClass("hidden");';
		}

		if ($allowClear)
		{
			$script[] = '	jQuery("#' . $this->id . '_clear").removeClass("hidden");';
		}

		$script[] = '	SqueezeBox.close();';
		$script[] = '}';

		// Clear button script.
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			$script[] = 'function jClearAlbum(id) {';
			$script[] = '	document.getElementById(id + "_id").value = "";';
			$script[] = '	document.getElementById(id + "_name").value = "' . htmlspecialchars(JText::_('COM_GALLERY_SELECT_AN_ALBUM', true), ENT_COMPAT, 'UTF-8') . '";';
			$script[] = '	jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '	if (document.getElementById(id + "_edit")) {';
			$script[] = '		jQuery("#"+id + "_edit").addClass("hidden");';
			$script[] = '	}';
			$script[] = '	return false;';
			$script[] = '}';
		}

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();
		$link = 'index.php?option=com_gallery&amp;view=albums&amp;layout=modal&amp;tmpl=component&amp;function=jSelectAlbum_' . $this->id;

		if (isset($this->element['language']))
		{
			$link .= '&amp;forcedLanguage=' . $this->element['language'];
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('title')
			->from($db->quoteName('#__gallery_albums'))
			->where($db->quoteName('id') . ' = ' . $db->quote((int) $this->value));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$title = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		if (empty($title))
		{
			$title = JText::_('COM_GALLERY_SELECT_AN_ALBUM');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The active album id field.
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// The current album display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
		$html[] = '<a class="modal btn hasTooltip" title="' . JHtml::tooltipText('COM_GALLERY_CHANGE_ALBUM') . '" href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('JSELECT') . '</a>';

		// Edit album button.
		if ($allowEdit)
		{
			$html[] = '<a class="btn hasTooltip' . ($value ? '' : ' hidden') . '" href="index.php?option=com_gallery&layout=modal&tmpl=component&task=album.edit&id=' . $value . '" target="_blank" title="' . JHtml::tooltipText('COM_GALLERY_EDIT_ALBUM') . '" ><span class="icon-edit"></span> ' . JText::_('JACTION_EDIT') . '</a>';
		}

		// Clear album button.
		if ($allowClear)
		{
			$html[] = '<button id="' . $this->id . '_clear" class="btn' . ($value ? '' : ' hidden') . '" onclick="return jClearAlbum(\'' . $this->id . '\')"><span class="icon-remove"></span> ' . JText::_('JCLEAR') . '</button>';
		}

		$html[] = '</span>';

		// Set class='required' for client side validation.
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
