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
 * HTML Albums View class for the Gallery component.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class GalleryViewAlbums extends JViewLegacy
{
	/**
	 * Model state object.
	 *
	 * @var     object
	 */
	protected $state = null;

	/**
	 * List of update items.
	 *
	 * @var     array
	 */
	protected $items = null;

	/**
	 * List pagination.
	 *
	 * @var     JPagination
	 */
	protected $pagination = null;

	protected $lead_items = array();

	protected $intro_items = array();

	protected $link_items = array();

	protected $columns = 1;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  The template file to include.
	 *
	 * @return  mixed  False on error, null otherwise.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		// Get the current user object.
		$user = JFactory::getUser();

		// Get some data from the models.
		$state      = $this->get('State');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$params = &$state->params;

		// Get the metrics for the structural page layout.
		$numLeading = (int) $params->def('num_leading_albums', 1);
		$numIntro   = (int) $params->def('num_intro_albums', 4);
		$numLinks   = (int) $params->def('num_links', 4);

		// Compute the album slugs and prepare description (runs content plugins).
		foreach ($items as &$item)
		{
			$item->slug        = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$item->catslug     = ($item->category_alias) ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;
			$item->link        = JRoute::_(GalleryHelperRoute::getAlbumRoute($item->slug, $item->catslug));

			// No link for ROOT category.
			if ($item->parent_alias == 'root')
			{
				$item->parent_slug = null;
			}

			$item->event = new stdClass;

			// Get the event dispatcher.
			$dispatcher = JEventDispatcher::getInstance();

			// Old plugins: Ensure that text property is available.
			if (!isset($item->text))
			{
				$item->text = $item->description;
			}

			// Load the content plugin group.
			JPluginHelper::importPlugin('content');

			$dispatcher->trigger('onContentPrepare', array ('com_gallery.albums', &$item, &$this->params, 0));

			// Old plugins: Use processed text as description.
			$item->description = $item->text;

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_gallery.albums', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_gallery.albums', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_gallery.albums', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		// Preprocess the breakdown of leading, intro and linked albums.
		// This makes it much easier for the designer to just interogate the arrays.
		$max = count($items);

		// The first group is the leading albums.
		$limit = $numLeading;

		for ($i = 0; $i < $limit && $i < $max; $i++)
		{
			$this->lead_items[$i] = &$items[$i];
		}

		// The second group is the intro albums.
		$limit = $numLeading + $numIntro;

		// Order albums across, then down (or single column mode).
		for ($i = $numLeading; $i < $limit && $i < $max; $i++)
		{
			$this->intro_items[$i] = &$items[$i];
		}

		$this->columns = max(1, $params->def('num_columns', 1));
		$order = $params->def('multi_column_order', 1);

		if ($order == 0 && $this->columns > 1)
		{
			// Call order down helper.
			$this->intro_items = GalleryHelperQuery::orderDownColumns($this->intro_items, $this->columns);
		}

		// The remainder are the links.
		for ($i = $numLeading + $numIntro; $i < $max; $i++)
		{
			$this->link_items[$i] = &$items[$i];
		}

		// Escape strings for HTML output.
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->params     = &$params;
		$this->state      = &$state;
		$this->items      = &$items;
		$this->pagination = &$pagination;
		$this->user       = &$user;

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function _prepareDocument()
	{
		// Initialiase variables.
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself.
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_GALLERY_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		// Configure the document meta-description.
		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		// Configure the document meta-keywords.
		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		// Configure the document robots.
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Add feed links.
		if ($this->params->get('show_feed_link', 1))
		{
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);

			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
