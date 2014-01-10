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

// Load StyleSheet.
JHtml::stylesheet('com_gallery/jquery.fileupload.css', false, true, false);
JHtml::stylesheet('com_gallery/jquery.fileupload-ui.css', false, true, false);

// Add JavaScript Frameworks.
JHtml::_('jquery.framework');

// Load JavaScript.
JHtml::script('com_gallery/jquery.ui.widget.js', false, true);
JHtml::script('com_gallery/tmpl.min.js', false, true);
JHtml::script('com_gallery/load-image.min.js', false, true);
JHtml::script('com_gallery/canvas-to-blob.min.js', false, true);
JHtml::script('com_gallery/jquery.iframe-transport.js', false, true);
JHtml::script('com_gallery/jquery.fileupload.js', false, true);
JHtml::script('com_gallery/jquery.fileupload-process.js', false, true);
JHtml::script('com_gallery/jquery.fileupload-image.js', false, true);
JHtml::script('com_gallery/jquery.fileupload-validate.js', false, true);
JHtml::script('com_gallery/jquery.fileupload-ui.js', false, true);

$saveOrderingUrl = 'index.php?option=com_gallery&task=pictures.saveOrderAjax&tmpl=component';
JHtml::_('sortablelist.sortable', 'pictureList', 'item-form', 'asc', $saveOrderingUrl);

$imageUrl  = COM_GALLERY_BASEURL . '/' . $this->item->id;
$deleteUrl = 'index.php?option=com_gallery&task=picture.deletePictureAjax&album_id=' . $this->item->id . '&' . JSession::getFormToken() . '=1&format=json';
?>
<noscript>
	<link rel="stylesheet" href="<?php echo JHtml::stylesheet('com_gallery/jquery.fileupload-noscript.css', false, true, true); ?>">
	<link rel="stylesheet" href="<?php echo JHtml::stylesheet('com_gallery/jquery.fileupload-ui-noscript.css', false, true, true); ?>">
	<input type="hidden" name="redirect" value="http://blueimp.github.io/jQuery-File-Upload/">
</noscript>
<!--[if (gte IE 8)&(lt IE 10)]>
	<script src="<?php echo JHtml::script('com_gallery/jquery.xdr-transport.js', false, true, true); ?>"></script>
<![endif]-->
<script type="text/javascript">
	jQuery(document).ready(function($) {
		// Initialize the jQuery File Upload widget:
		$('#item-form').fileupload({
			disableImageResize: false,
			// Uncomment the following to send cross-domain cookies:
			//xhrFields: {withCredentials: true},
			// url: 'server/php/'
			url: 'index.php?option=com_gallery&task=picture.upload&format=json'
		}).bind('fileuploadsubmit', function(e, data) {
			var inputs = data.context.find(':input');

			if (inputs.filter('[required][value=""]').first().focus().length) {
				return false;
			}

			data.formData = inputs.serializeArray();
		});

		// Enable iframe cross-domain access via redirect option:
		$('#item-form').fileupload(
			'option',
			'redirect',
			window.location.href.replace(
				/\/[^\/]*$/,
				'/cors/result.html?%s'
			)
		);

		// Load existing files:
		$('#item-form').addClass('fileupload-processing');

		$.ajax({
			// Uncomment the following to send cross-domain cookies:
			//xhrFields: {withCredentials: true},
			// url: $('#item-form').fileupload('option', 'url'),
			url: 'index.php?option=com_gallery&task=pictures.getPicturesAjax',
			dataType: 'json',
			data: {
				album_id: <?php echo $this->item->id; ?>,
				'<?php echo JSession::getFormToken(); ?>': 1
			},
			context: $('#item-form')[0]
		}).always(function() {
			$(this).removeClass('fileupload-processing');
		}).done(function(result) {
			$(this).fileupload('option', 'done')
				.call(this, $.Event('done'), {
					result: result
				});

			var sortableList = new $.JSortableList('#pictureList tbody', 'item-form', 'asc', 'index.php?option=com_gallery&task=pictures.saveOrderAjax&tmpl=component', '', '');
		});
	});
</script>
<div class="row-fluid fileupload-buttonbar">
	<div class="span7">
		<span class="btn btn-success fileinput-button">
			<i class="icon-plus"></i> <?php echo JText::_('COM_GALLERY_ADD_FILES'); ?>
			<input type="file" name="files[]" multiple>
		</span>
		<button type="submit" class="btn btn-primary start">
			<i class="icon-upload"></i> <?php echo JText::_('JTOOLBAR_UPLOAD'); ?>
		</button>
		<button type="reset" class="btn btn-warning cancel">
			<i class="icon-ban-circle"></i> <?php echo JText::_('JTOOLBAR_CANCEL'); ?>
		</button>
		<button type="button" class="btn btn-danger delete">
			<i class="icon-trash"></i> <?php echo JText::_('JTOOLBAR_DELETE'); ?>
		</button>
		<input type="checkbox" class="toggle">
		<span class="fileupload-process"></span>
	</div>
	<div class="span5 fileupload-progress fade">
		<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
			<div class="progress-bar progress-bar-success" style="width: 0%;"></div>
		</div>
		<div class="progress-extended">&nbsp;</div>
	</div>
</div>
<table class="table table-striped table-hover" id="pictureList" role="presentation">
	<thead>
		<tr>
			<th width="1%" class="nowrap center hidden-phone">
				<i class="icon-menu-2"></i>
			</th>
			<th width="1%" class="hidden-phone"></th>
			<th width="5%" class="nowrap">
				<?php echo JText::_('COM_GALLERY_HEADING_PREVIEW'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('COM_GALLERY_HEADING_TITLE'); ?>
			</th>
			<th width="5%" class="nowrap hidden-phone">
				<?php echo JText::_('COM_GALLERY_HEADING_SIZE'); ?>
			</th>
			<th width="5%" class="nowrap">
				<?php echo JText::_('COM_GALLERY_HEADING_ACTION'); ?>
			</th>
			<th width="1%" class="nowrap center hidden-phone">
				<?php echo JText::_('JGRID_HEADING_ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody class="files"></tbody>
</table>
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<tr class="row{%=i % 2%} template-upload fade">
		<td class="order nowrap center hidden-phone">
			<span class="sortable-handler inactive">
				<i class="icon-menu"></i>
			</span>
		</td>
		<td class="center hidden-phone"></td>
		<td>
			<span class="preview"></span>
		</td>
		<td class="nowrap">
			<p class="name">
				<input type="text" name="picture[title]" value="{%=file.name%}" class="span6" placeholder="<?php echo JText::_('COM_GALLERY_FIELD_TITLE_LABEL'); ?>">
			</p>
			<p class="description">
				<textarea name="picture[description]" cols="30" rows="3" class="span6" placeholder="<?php echo JText::_('COM_GALLERY_FIELD_DESCRIPTION_LABEL') ?>"></textarea>
			</p>
			<div>
				<input type="hidden" name="picture[album_id]" value="<?php echo $this->item->id; ?>">
				<?php echo JHtml::_('form.token'); ?>
			</div>
			<strong class="error text-danger"></strong>
		</td>
		<td class="hidden-phone">
			<p class="size"><?php echo JText::_('COM_GALLERY_PROCESSING'); ?></p>
			<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
		</td>
		<td class="nowrap">
			{% if (!i && !o.options.autoUpload) { %}
				<button class="btn btn-primary start" disabled>
					<i class="icon-upload"></i> <?php echo JText::_('JTOOLBAR_UPLOAD'); ?>
				</button>
			{% } %}
			{% if (!i) { %}
				<button class="btn btn-warning cancel">
					<i class="icon-ban-circle"></i> <?php echo JText::_('JTOOLBAR_CANCEL'); ?>
				</button>
			{% } %}
		</td>
		<td class="center hidden-phone"></td>
	</tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
	<tr class="row{%=i % 2%} template-download fade">
		<td class="order nowrap center hidden-phone">
			<span class="sortable-handler hasTooltip" title="">
				<i class="icon-menu"></i>
			</span>
			<input type="checkbox" style="display:none" name="cid[]" value="{%=file.id%}" />
			<input type="text" style="display:none" name="order[]" value="{%=file.ordering%}" />
		</td>
		<td class="center hidden-phone">
			<input type="checkbox" name="delete" value="1" class="toggle">
		</td>
		<td>
			<span class="preview">
				<a href="<?php echo $imageUrl; ?>/{%=file.filename%}" title="{%=file.title%}" download="{%=file.filename%}">
					<img src="<?php echo $imageUrl; ?>/thumbnails/{%=file.filename%}" style="max-width: 160px;">
				</a>
			</span>
		</td>
		<td class="nowrap">
			<p class="name">
				<input type="text" name="pictures[{%=file.id%}][title]" value="{%=file.title%}" class="span6" placeholder="<?php echo JText::_('COM_GALLERY_FIELD_TITLE_LABEL'); ?>">
			</p>
			<p class="description">
				<textarea name="pictures[{%=file.id%}][description]" cols="30" rows="3" class="span6" placeholder="<?php echo JText::_('COM_GALLERY_FIELD_DESCRIPTION_LABEL') ?>">{%=file.description%}</textarea>
			</p>
			{% if (file.error) { %}
				<div><span class="label label-danger"><?php echo JText::_('MESSAGE'); ?></span> {%=file.error%}</div>
			{% } %}
		</td>
		<td class="hidden-phone">
			{% if (file.size) { %}
				<span class="size">{%=o.formatFileSize(Number(file.size))%}</span>
			{% } %}
		</td>
		<td class="nowrap">
			{% if (file.id) { %}
				<button class="btn btn-danger delete" data-type="DELETE" data-url="<?php echo $deleteUrl; ?>&filename={%=file.filename%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
					<i class="icon-trash"></i> <?php echo JText::_('JTOOLBAR_DELETE'); ?>
				</button>
			{% } else { %}
				<button class="btn btn-warning cancel">
					<i class="icon-ban-circle"></i> <?php echo JText::_('JTOOLBAR_CANCEL'); ?>
				</button>
			{% } %}
		</td>
		<td class="center hidden-phone">
			{%=file.id%}
		</td>
	</tr>
{% } %}
</script>
