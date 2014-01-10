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
 * Script file of Gallery Component.
 *
 * @package     Gallery
 * @subpackage  com_gallery
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @since       3.2
 */
class Com_GalleryInstallerScript
{
	/**
	 * Called before any type of action.
	 *
	 * @param   string            $route    Which action is happening (install|uninstall|discover_install).
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function preflight($route, JAdapterInstance $adapter)
	{

	}

	/**
	 * Called after any type of action.
	 *
	 * @param   string            $route    Which action is happening (install|uninstall|discover_install).
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function postflight($route, JAdapterInstance $adapter)
	{
		// Adding content type for albums.
		$table = JTable::getInstance('Contenttype', 'JTable');

		if (!$table->load(array('type_alias' => 'com_gallery.album')))
		{
			// Table column.
			$special = new stdClass;
			$special->dbtable = '#__gallery_albums';
			$special->key     = 'id';
			$special->type    = 'Album';
			$special->prefix  = 'GalleryTable';
			$special->config  = 'array()';

			$common = new stdClass;
			$common->dbtable  = '#__ucm_content';
			$common->key      = 'ucm_id';
			$common->type     = 'Corecontent';
			$common->prefix   = 'JTable';
			$common->config   = 'array()';

			$table_object = new stdClass;
			$table_object->special = $special;
			$table_object->common  = $common;

			// Field mappings column.
			$common = new stdClass;
			$common->core_content_item_id = 'id';
			$common->core_title           = 'title';
			$common->core_state           = 'state';
			$common->core_alias           = 'alias';
			$common->core_created_time    = 'created';
			$common->core_modified_time   = 'modified';
			$common->core_body            = 'description';
			$common->core_hits            = 'hits';
			$common->core_publish_up      = 'publish_up';
			$common->core_publish_down    = 'publish_down';
			$common->core_access          = 'access';
			$common->core_params          = 'params';
			$common->core_featured        = 'featured';
			$common->core_metadata        = 'metadata';
			$common->core_language        = 'language';
			$common->core_images          = null;
			$common->core_urls            = null;
			$common->core_version         = 'version';
			$common->core_ordering        = 'ordering';
			$common->core_metakey         = 'metakey';
			$common->core_metadesc        = 'metadesc';
			$common->core_catid           = 'catid';
			$common->core_xreference      = 'xreference';
			$common->asset_id             = 'asset_id';

			$field_mappings = new stdClass;
			$field_mappings->common  = $common;
			$field_mappings->special = new stdClass;

			// Content history options column.
			$hideFields = array(
				'asset_id',
				'checked_out',
				'checked_out_time',
				'version'
			);

			$ignoreChanges = array(
				'modified_by',
				'modified',
				'checked_out',
				'checked_out_time',
				'version',
				'hits'
			);

			$convertToInt = array(
				'publish_up',
				'publish_down',
				'featured',
				'ordering'
			);

			$displayLookup = array(
				array(
					'sourceColumn' => 'catid',
					'targetTable' => '#__categories',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				),
				array(
					'sourceColumn' => 'created_by',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				),
				array(
					'sourceColumn' => 'access',
					'targetTable' => '#__viewlevels',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				),
				array(
					'sourceColumn' => 'modified_by',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				)
			);

			$content_history_options = new stdClass;
			$content_history_options->formFile      = 'administrator/components/com_gallery/models/forms/album.xml';
			$content_history_options->hideFields    = $hideFields;
			$content_history_options->ignoreChanges = $ignoreChanges;
			$content_history_options->convertToInt  = $convertToInt;
			$content_history_options->displayLookup = $displayLookup;

			$content_types['type_title']              = 'Album';
			$content_types['type_alias']              = 'com_gallery.album';
			$content_types['table']                   = json_encode($table_object);
			$content_types['rules']                   = '';
			$content_types['field_mappings']          = json_encode($field_mappings);
			$content_types['router']                  = 'GalleryHelperRoute::getAlbumRoute';
			$content_types['content_history_options'] = json_encode($content_history_options);

			$table->save($content_types);
		}

		// Adding content type for gallery category.
		$table = JTable::getInstance('Contenttype', 'JTable');

		if (!$table->load(array('type_alias' => 'com_gallery.category')))
		{
			// Table column.
			$special = new stdClass;
			$special->dbtable = '#__categories';
			$special->key     = 'id';
			$special->type    = 'Category';
			$special->prefix  = 'JTable';
			$special->config  = 'array()';

			$common = new stdClass;
			$common->dbtable  = '#__ucm_content';
			$common->key      = 'ucm_id';
			$common->type     = 'Corecontent';
			$common->prefix   = 'JTable';
			$common->config   = 'array()';

			$table_object = new stdClass;
			$table_object->special = $special;
			$table_object->common  = $common;

			// Field mappings column.
			$common = new stdClass;
			$common->core_content_item_id = 'id';
			$common->core_title           = 'title';
			$common->core_state           = 'published';
			$common->core_alias           = 'alias';
			$common->core_created_time    = 'created_time';
			$common->core_modified_time   = 'modified_time';
			$common->core_body            = 'description';
			$common->core_hits            = 'hits';
			$common->core_publish_up      = null;
			$common->core_publish_down    = null;
			$common->core_access          = 'access';
			$common->core_params          = 'params';
			$common->core_featured        = null;
			$common->core_metadata        = 'metadata';
			$common->core_language        = 'language';
			$common->core_images          = null;
			$common->core_urls            = null;
			$common->core_version         = 'version';
			$common->core_ordering        = null;
			$common->core_metakey         = 'metakey';
			$common->core_metadesc        = 'metadesc';
			$common->core_catid           = 'parent_id';
			$common->core_xreference      = null;
			$common->asset_id             = 'asset_id';

			$special = new stdClass;
			$special->parent_id = 'parent_id';
			$special->lft       = 'lft';
			$special->rgt       = 'rgt';
			$special->level     = 'level';
			$special->path      = 'path';
			$special->extension = 'extension';
			$special->note      = 'note';

			$field_mappings = new stdClass;
			$field_mappings->common  = $common;
			$field_mappings->special = $special;

			// Content history options column.
			$hideFields = array(
				'asset_id',
				'checked_out',
				'checked_out_time',
				'version',
				'lft',
				'rgt',
				'level',
				'path',
				'extension'
			);

			$ignoreChanges = array(
				'modified_user_id',
				'modified_time',
				'checked_out',
				'checked_out_time',
				'version',
				'hits',
				'path'
			);

			$convertToInt = array(
				'publish_up',
				'publish_down'
			);

			$displayLookup = array(
				array(
					'sourceColumn' => 'created_user_id',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				),
				array(
					'sourceColumn' => 'access',
					'targetTable' => '#__viewlevels',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				),
				array(
					'sourceColumn' => 'modified_user_id',
					'targetTable' => '#__users',
					'targetColumn' => 'id',
					'displayColumn' => 'name'
				),
				array(
					'sourceColumn' => 'parent_id',
					'targetTable' => '#__categories',
					'targetColumn' => 'id',
					'displayColumn' => 'title'
				)
			);

			$content_history_options = new stdClass;
			$content_history_options->formFile      = 'administrator/components/com_categories/models/forms/category.xml';
			$content_history_options->hideFields    = $hideFields;
			$content_history_options->ignoreChanges = $ignoreChanges;
			$content_history_options->convertToInt  = $convertToInt;
			$content_history_options->displayLookup = $displayLookup;

			$content_types['type_title']              = 'Album Category';
			$content_types['type_alias']              = 'com_gallery.category';
			$content_types['table']                   = json_encode($table_object);
			$content_types['rules']                   = '';
			$content_types['field_mappings']          = json_encode($field_mappings);
			$content_types['router']                  = 'GalleryHelperRoute::getCategoryRoute';
			$content_types['content_history_options'] = json_encode($content_history_options);

			$table->save($content_types);
		}

		// Adding Category "uncategorized" if installing or discovering.
		if ($route != 'update')
		{
			$this->_addCategory();
		}

		// Adding default params if installing or discovering.
		if ($route != 'update')
		{
			// Get an instance of the table table.
			$table = JTable::getInstance('Extension', 'JTable');
			$table->load(array('name' => 'com_gallery'));
			$table->params = '{"max_size":"1280x720","thumb_size":"280x280","album_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","info_block_position":"0","show_category":"1","link_category":"1","show_parent_category":"1","link_parent_category":"1","show_author":"1","link_author":"0","show_create_date":"1","show_modify_date":"1","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_tags":"1","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","show_publishing_options":"1","show_album_options":"1","save_history":"1","history_limit":10,"float_intro":"right","float_fulltext":"right","category_layout":"_:default","show_category_heading_title_text":"1","show_category_title":"1","show_description":"1","show_description_image":"0","maxLevel":"-1","show_empty_categories":"0","show_no_albums":"1","show_subcat_desc":"1","show_cat_num_albums":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_albums_cat":"1","num_leading_albums":"1","num_intro_albums":"4","num_columns":"2","num_links":"4","multi_column_order":"0","show_subcategory_content":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","orderby_pri":"none","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0","feed_show_readmore":"0"}';
			$table->store();
		}
	}

	/**
	 * Called on installation.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function install(JAdapterInstance $adapter)
	{
		// Set the redirect location.
		$adapter->getParent()->setRedirectURL('index.php?option=com_gallery');
	}

	/**
	 * Called on update.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function update(JAdapterInstance $adapter)
	{

	}

	/**
	 * Called on uninstallation.
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		echo '<p>' . JText::_('COM_GALLERY_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * Method to add a default category "uncategorised".
	 *
	 * @return  integer  Id of the created category.
	 *
	 * @since   3.2
	 */
	protected function _addCategory()
	{
		// Include dependancies.
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/models', 'CategoriesModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');

		// Get an instance of the generic category model.
		$model = JModelLegacy::getInstance('Category', 'CategoriesModel', array('ignore_request' => true));

		// Attempt to save the category.
		$data  = array(
			'id'          => 0,
			'parent_id'   => 0,
			'level'       => 1,
			'path'        => 'uncategorised',
			'extension'   => 'com_gallery',
			'title'       => 'Uncategorised',
			'alias'       => 'uncategorised',
			'description' => '',
			'published'   => 1,
			'params'      => '{"target":"","image":""}',
			'metadata'    => '{"page_title":"","author":"","robots":""}',
			'language'    => '*'
		);

		// Save the data.
		$model->save($data);

		// Initialiase variables.
		$id    = $model->getItem()->id;
		$db    = JFactory::getDbo();

		// Updating all gallery without category to have this new one.
		$query = $db->getQuery(true)
			->update($db->quoteName('#__gallery_albums'))
			->set($db->quoteName('catid') . ' = ' . $db->quote((int) $id))
			->where($db->quoteName('catid') . ' = ' . $db->quote(0));

		$db->setQuery($query)
			->query();

		return;
	}
}
