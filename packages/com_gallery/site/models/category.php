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
 * This models supports retrieving a category, the albums associated with the category,
 * sibling, child and parent categories.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno@atomtech.com.br>
 * @since       3.2
 */
class GalleryModelCategory extends JModelList
{
	/**
	 * Category items data.
	 *
	 * @access  protected
	 * @var     JCategoryNode
	 */
	protected $_item = null;

	/**
	 * Array of Items.
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $_albums = null;

	/**
	 * Array of Children.
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $_children = null;

	/**
	 * Parent Category object.
	 *
	 * @access  protected
	 * @var     JCategoryNode
	 */
	protected $_parent = null;

	/**
	 * Model context string.
	 *
	 * @access  protected
	 * @var     string
	 */
	protected $_context = 'com_gallery.category';

	/**
	 * Pagination object
	 *
	 * @access  protected
	 * @var     object
	 */
	protected $_pagination = null;

	/**
	 * Array of Right Sibling.
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $_rightsibling = null;

	/**
	 * Array of Right Sibling.
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $_leftsibling = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'modified', 'a.modified',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'author', 'a.author',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialiase variables.
		$app = JFactory::getApplication('site');
		$pk  = $app->input->getInt('id');

		$this->setState('category.id', $pk);

		// Load the parameters. Merge Global and Menu Item params into new object.
		$params = $app->getParams();
		$menuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->params);
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);

		// Load the parameters.
		$this->setState('params', $mergedParams);

		// Get the current user object.
		$user = JFactory::getUser();

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		if ((!$user->authorise('core.edit.state', 'com_gallery')) &&  (!$user->authorise('core.edit', 'com_gallery')))
		{
			// Limit to published for people who can not edit or edit.state.
			$this->setState('filter.state', 1);

			// Filter by start and end dates.
			$nullDate = $db->quote($db->getNullDate());
			$nowDate = $db->quote(JFactory::getDate()->toSQL());

			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
				->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}
		else
		{
			$this->setState('filter.state', array(0, 1, 2));
		}

		// Process show_noauth parameter.
		if (!$params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}

		// Optional filter text.
		$this->setState('list.filter', $app->input->getString('filter-search'));

		$itemid = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol = $app->getUserStateFromRequest('com_gallery.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->getUserStateFromRequest('com_gallery.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

		// Set limit for query. If list, use parameter. If blog, add blog parameters for limit.
		if (($app->input->get('layout') == 'blog') || $params->get('layout_type') == 'blog')
		{
			$limit = $params->get('num_leading_albums') + $params->get('num_intro_albums') + $params->get('num_links');
			$this->setState('list.links', $params->get('num_links'));
		}
		else
		{
			$limit = $app->getUserStateFromRequest('com_gallery.category.list.' . $itemid . '.limit', 'limit', $params->get('display_num'), 'uint');
		}

		$this->setState('list.limit', $limit);

		// Set the depth of the category query based on parameter.
		$showSubcategories = $params->get('show_subcategory_album', '0');

		if ($showSubcategories)
		{
			$this->setState('filter.max_category_levels', $params->get('show_subcategory_album', '1'));
			$this->setState('filter.subcategories', true);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());

		$this->setState('layout', $app->input->getString('layout'));
	}

	/**
	 * Get the albums in the category
	 *
	 * @return  mixed  An array of albums or false if an error occurs.
	 *
	 * @since   3.2
	 */
	function getItems()
	{
		// Initialiase variables.
		$limit = $this->getState('list.limit');

		if ($this->_albums === null && $category = $this->getCategory())
		{
			$model = JModelLegacy::getInstance('Albums', 'GalleryModel', array('ignore_request' => true));
			$model->setState('params', JFactory::getApplication()->getParams());
			$model->setState('filter.category_id', $category->id);
			$model->setState('filter.state', $this->getState('filter.state'));
			$model->setState('filter.access', $this->getState('filter.access'));
			$model->setState('filter.language', $this->getState('filter.language'));
			$model->setState('list.ordering', $this->_buildAlbumOrderBy());
			$model->setState('list.start', $this->getState('list.start'));
			$model->setState('list.limit', $limit);
			$model->setState('list.direction', $this->getState('list.direction'));
			$model->setState('list.filter', $this->getState('list.filter'));

			// The filter.subcategories indicates whether to include albums from subcategories in the list or blog.
			$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
			$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
			$model->setState('list.links', $this->getState('list.links'));

			if ($limit >= 0)
			{
				$this->_albums = $model->getItems();

				if ($this->_albums === false)
				{
					$this->setError($model->getError());
				}
			}
			else
			{
				$this->_albums = array();
			}

			$this->_pagination = $model->getPagination();
		}

		return $this->_albums;
	}

	/**
	 * Build the orderby for the query
	 *
	 * @return  string  $orderby portion of query.
	 *
	 * @since   3.2
	 */
	protected function _buildAlbumOrderBy()
	{
		// Initialiase variables.
		$app       = JFactory::getApplication('site');
		$db        = $this->getDbo();
		$params    = $this->state->params;
		$itemid    = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol  = $app->getUserStateFromRequest('com_gallery.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		$orderDirn = $app->getUserStateFromRequest('com_gallery.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		$orderby   = ' ';

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = null;
		}

		if (!in_array(strtoupper($orderDirn), array('ASC', 'DESC', '')))
		{
			$orderDirn = 'ASC';
		}

		if ($orderCol && $orderDirn)
		{
			$orderby .= $db->escape($orderCol) . ' ' . $db->escape($orderDirn) . ', ';
		}

		$albumOrderby      = $params->get('orderby_sec', 'rdate');
		$albumOrderDate    = $params->get('order_date');
		$categoryOrderby = $params->def('orderby_pri', '');
		$secondary       = GalleryHelperQuery::orderbySecondary($albumOrderby, $albumOrderDate) . ', ';
		$primary         = GalleryHelperQuery::orderbyPrimary($categoryOrderby);

		$orderby .= $primary . ' ' . $secondary . ' a.created ';

		return $orderby;
	}

	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  JPagination  A JPagination object for the data set.
	 *
	 * @since   3.2
	 */
	public function getPagination()
	{
		if (empty($this->_pagination))
		{
			return null;
		}

		return $this->_pagination;
	}

	/**
	 * Method to get category data for the current category.
	 *
	 * @return  object
	 *
	 * @since   3.2
	 */
	public function getCategory()
	{
		if (!is_object($this->_item))
		{
			if (isset($this->state->params))
			{
				$params  = $this->state->params;
				$options = array();
				$options['countItems'] = $params->get('show_cat_num_albums', 1) || !$params->get('show_empty_categories_cat', 0);
			}
			else
			{
				$options['countItems'] = 0;
			}

			$categories  = JCategories::getInstance('Gallery', $options);
			$this->_item = $categories->get($this->getState('category.id', 'root'));

			// Compute selected asset permissions.
			if (is_object($this->_item))
			{
				// Get the current user object.
				$user  = JFactory::getUser();

				$asset = 'com_gallery.category.' . $this->_item->id;

				// Check general create permission.
				if ($user->authorise('core.create', $asset))
				{
					$this->_item->getParams()->set('access-create', true);
				}

				// TODO: Why are not we lazy loading the children and siblings?
				$this->_children = $this->_item->getChildren();
				$this->_parent   = false;

				if ($this->_item->getParent())
				{
					$this->_parent = $this->_item->getParent();
				}

				$this->_rightsibling = $this->_item->getSibling();
				$this->_leftsibling  = $this->_item->getSibling(false);
			}
			else
			{
				$this->_children = false;
				$this->_parent   = false;
			}
		}

		return $this->_item;
	}

	/**
	 * Get the parent category.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 *
	 * @since   3.2
	 */
	public function getParent()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		return $this->_parent;
	}

	/**
	 * Get the left sibling (adjacent) categories.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 *
	 * @since   3.2
	 */
	function &getLeftSibling()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		return $this->_leftsibling;
	}

	/**
	 * Get the right sibling (adjacent) categories.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 *
	 * @since   3.2
	 */
	function &getRightSibling()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		return $this->_rightsibling;
	}

	/**
	 * Get the child categories.
	 *
	 * @return  mixed  An array of categories or false if an error occurs.
	 *
	 * @since   3.2
	 */
	function &getChildren()
	{
		if (!is_object($this->_item))
		{
			$this->getCategory();
		}

		// Order subcategories.
		if (count($this->_children))
		{
			$params = $this->getState()->get('params');

			if ($params->get('orderby_pri') == 'alpha' || $params->get('orderby_pri') == 'ralpha')
			{
				jimport('joomla.utilities.arrayhelper');

				JArrayHelper::sortObjects($this->_children, 'title', ($params->get('orderby_pri') == 'alpha') ? 1 : - 1);
			}
		}

		return $this->_children;
	}

	/**
	 * Increment the hit counter for the category.
	 *
	 * @param   int  $pk  Optional primary key of the category to increment.
	 *
	 * @return  boolean True if successful; false otherwise and internal error set.
	 *
	 * @since   3.2
	 */
	public function hit($pk = 0)
	{
		// Initialise variables.
		$input    = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

			$table = JTable::getInstance('Category', 'JTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
