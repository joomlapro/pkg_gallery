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

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Create shortcuts to some parameters.
$params  = $this->item->params;
$canEdit = $params->get('access-edit');
$user    = JFactory::getUser();

// Load the tooltip behavior script.
JHtml::_('behavior.caption');

// Create a unique id to grouping images.
$id = uniqid();
?>
<div class="gallery album-item<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)): ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
				<?php if ($title = $this->item->title): ?>
					/ <?php echo $this->escape($this->item->title); ?>
				<?php endif; ?>
			</h1>
		</div>
	<?php else: ?>
		<div class="page-header">
			<h2>
				<?php echo $this->escape($this->item->title); ?>
			</h2>
		</div>
	<?php endif; ?>

	<?php if ($this->pictures): ?>
		<?php foreach (array_chunk($this->pictures, 4) as $row): ?>
			<div class="row">
				<?php foreach ($row as $picture): ?>
					<div class="col-md-3">
						<figure>
							<a href="<?php echo $picture->image; ?>" rel="prettyPhoto['<?php echo $id; ?>']">
								<img src="<?php echo $picture->thumbnail; ?>" alt="<?php echo $picture->title; ?>">
							</a>
						</figure>
						<?php if ($picture->title): ?>
							<article>
								<h3><?php echo $this->escape($picture->title); ?></h3>
							</article>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="alert alert-warning alert-dismissable">
			<strong><?php echo JText::_('WARNING') ?></strong> <?php echo JText::_('COM_GALLERY_MESSAGE_NOT_HAVE_PICTURES'); ?>
		</div>
	<?php endif; ?>
</div>
