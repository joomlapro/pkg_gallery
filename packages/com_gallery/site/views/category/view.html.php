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
 * HTML Category View class for the Gallery component.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class GalleryViewCategory extends JViewCategory
{
	/**
	 * Array of leading items for blog display.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $lead_items = array();

	/**
	 * Array of intro (multicolumn display) items for blog display.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $intro_items = array();

	/**
	 * Array of links in blog display.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $link_items = array();

	/**
	 * Number of columns in a multi column display.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $columns = 1;

	/**
	 * The name of the extension for the category.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $extension = 'com_gallery';

	/**
	 * Default title to use for page title.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $defaultPageTitle = 'COM_GALLERY_DEFAULT_PAGE_TITLE';

	/**
	 * The name of the view to link individual items to.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $viewName = 'album';

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		parent::commonCategoryDisplay();

		// Prepare the data.
		// Get the metrics for the structural page layout.
		$params     = $this->params;
		$numLeading = $params->def('num_leading_albums', 1);
		$numIntro   = $params->def('num_intro_albums', 4);
		$numLinks   = $params->def('num_links', 4);

		// Compute the album slugs and prepare description (runs content plugins).
		foreach ($this->items as $item)
		{
			$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

			$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

			// No link for ROOT category.
			if ($item->parent_alias == 'root')
			{
				$item->parent_slug = null;
			}

			$item->catslug = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
			$item->event   = new stdClass;

			$dispatcher = JEventDispatcher::getInstance();

			// Old plugins: Ensure that text property is available.
			if (!isset($item->text))
			{
				$item->text = $item->description;
			}

			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array ('com_gallery.category', &$item, &$this->params, 0));

			// Old plugins: Use processed text as description.
			$item->description = $item->text;

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_gallery.category', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_gallery.category', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_gallery.category', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$app    = JFactory::getApplication();
		$active = $app->getMenu()->getActive();

		if ((!$active) || ((strpos($active->link, 'view=category') === false) || (strpos($active->link, '&id=' . (string) $this->category->id) === false)))
		{
			// Get the layout from the merged category params
			if ($layout = $this->category->params->get('category_layout'))
			{
				$this->setLayout($layout);
			}
		}
		// At this point, we are in a menu item, so we don't override the layout
		elseif (isset($active->query['layout']))
		{
			// We need to set the layout from the query in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		// For blog layouts, preprocess the breakdown of leading, intro and linked albums.
		// This makes it much easier for the designer to just interrogate the arrays.
		if (($params->get('layout_type') == 'blog') || ($this->getLayout() == 'blog'))
		{
			// $max = count($this->items);

			foreach ($this->items as $i => $item)
			{
				if ($i < $numLeading)
				{
					$this->lead_items[] = $item;
				}

				elseif ($i >= $numLeading && $i < $numLeading + $numIntro)
				{
					$this->intro_items[] = $item;
				}

				elseif ($i < $numLeading + $numIntro + $numLinks)
				{
					$this->link_items[] = $item;
				}
				else
				{
					continue;
				}
			}

			$this->columns = max(1, $params->def('num_columns', 1));

			$params->def('multi_column_order', 1);
		}

		return parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function prepareDocument()
	{
		parent::prepareDocument();

		// Initialise variables.
		$menu = $this->menu;
		$id   = (int) @$menu->query['id'];

		if ($menu && ($menu->query['option'] != 'com_gallery' || $menu->query['view'] == 'album' || $id != $this->category->id))
		{
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();

			while (($menu->query['option'] != 'com_gallery' || $menu->query['view'] == 'album' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => GalleryHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$this->pathway->addItem($item['title'], $item['link']);
			}
		}

		parent::addFeed();
	}
}
