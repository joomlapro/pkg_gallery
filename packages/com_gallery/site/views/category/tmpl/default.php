<?php
/**
 * @package     Gallery
 * @subpackage  com_gallery
 *
 * @author      Bruno Batista <bruno.batista@ctis.com.br>
 * @copyright   Copyright (C) 2014 CTIS IT Services. All rights reserved.
 * @license     Commercial License
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

// Load the caption behavior script.
JHtml::_('behavior.caption');
?>
<div class="category-list<?php echo $this->pageclass_sfx; ?>">
	<?php
	$this->subtemplatename = 'items';

	echo JLayoutHelper::render('joomla.content.category_default', $this);
	?>
</div>
