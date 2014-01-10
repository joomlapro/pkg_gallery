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
 * View class for a list of featured albums.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class GalleryViewFeatured extends JViewLegacy
{
	/**
	 * List of update items.
	 *
	 * @var     array
	 */
	protected $items;

	/**
	 * List pagination.
	 *
	 * @var     JPagination
	 */
	protected $pagination;

	/**
	 * The model state.
	 *
	 * @var     JObject
	 */
	protected $state;

	/**
	 * List of authors.
	 *
	 * @var     array
	 */
	protected $authors;

	/**
	 * The form filter.
	 *
	 * @var     JForm
	 */
	public $filterForm;

	/**
	 * List of active filters.
	 *
	 * @var     array
	 */
	public $activeFilters;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		try
		{
			// Initialise variables.
			$this->items         = $this->get('Items');
			$this->pagination    = $this->get('Pagination');
			$this->state         = $this->get('State');
			$this->authors       = $this->get('Authors');
			$this->filterForm    = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');
		}
		catch (Exception $e)
		{
			JErrorPage::render($e);

			return false;
		}

		// Levels filter.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', JText::_('J1'));
		$options[] = JHtml::_('select.option', '2', JText::_('J2'));
		$options[] = JHtml::_('select.option', '3', JText::_('J3'));
		$options[] = JHtml::_('select.option', '4', JText::_('J4'));
		$options[] = JHtml::_('select.option', '5', JText::_('J5'));
		$options[] = JHtml::_('select.option', '6', JText::_('J6'));
		$options[] = JHtml::_('select.option', '7', JText::_('J7'));
		$options[] = JHtml::_('select.option', '8', JText::_('J8'));
		$options[] = JHtml::_('select.option', '9', JText::_('J9'));
		$options[] = JHtml::_('select.option', '10', JText::_('J10'));

		$this->f_levels = $options;

		// We do not need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			// Load the submenu.
			GalleryHelper::addSubmenu('featured');

			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		// Initialise variables.
		$state = $this->get('State');
		$canDo = AlbumsHelper::getActions($this->state->get('filter.category_id'), 0, 'com_gallery');
		$user  = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_GALLERY_MANAGER_FEATURED_TITLE'), 'star featured');

		if ($canDo->get('core.create') && count($user->getAuthorisedCategories('com_gallery', 'core.create')) > 0)
		{
			JToolbarHelper::addNew('album.add');
		}
		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own')))
		{
			JToolbarHelper::editList('album.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('albums.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('albums.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::custom('featured.delete', 'remove.png', 'remove_f2.png', 'JTOOLBAR_REMOVE', true);
			JToolbarHelper::archiveList('albums.archive');
			JToolbarHelper::checkin('albums.checkin');
		}

		if ($state->get('filter.state') == - 2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'albums.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('albums.trash');
		}

		if ($user->authorise('core.admin', 'com_gallery'))
		{
			JToolbarHelper::preferences('com_gallery');
		}

		JToolBarHelper::help('featured', $com = true);
	}
}
