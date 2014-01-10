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

// Load the backend helper.
require_once JPATH_ADMINISTRATOR . '/components/com_gallery/helpers/albums.php';
?>
<div class="gallery album-list<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')): ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>

	<?php foreach (array_chunk($this->items, 4) as $row): ?>
		<div class="row">
			<?php foreach ($row as $item): ?>
				<div class="col-md-3">
					<figure>
						<a href="<?php echo $item->link; ?>"><img src="<?php echo AlbumsHelper::getFirstImage($item->id); ?>" alt="<?php echo $item->title; ?>" class="img-thumbnail"></a>
					</figure>
					<article>
						<h3><a href="<?php echo $item->link; ?>"><i class="icon-picture"></i> <?php echo $this->escape($item->title); ?></a></h3>
					</article>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<?php if ($this->params->get('show_pagination', 1)): ?>
		<div class="pagination">
			<?php if ($this->params->def('show_pagination_results', 1)): ?>
				<p class="counter">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</p>
			<?php endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
</div>
