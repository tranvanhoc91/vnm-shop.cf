<?php
defined('ABSPATH') or die('Restricted Access!');

wp_enqueue_style('wpse_htmltemplate_style', plugin_dir_url(__FILE__).'css/style.css');

wp_enqueue_script('wpse_htmltemplate_js', plugin_dir_url(__FILE__).'htmltemplate.js');
$data_array = array(
	'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
    'confirmText' => __('Do you really want to delete this template?', 'wp-smart-editor'),
	'emptyName' => __('Template name cannot be empty!', 'wp-smart-editor'),
);
wp_localize_script('wpse_htmltemplate_js', 'data', $data_array);

$saved_template = get_option('wpse_html_template');
?>
<div class="preload-img"></div>
<div class="container" id="htmlTemplate" style="display: none;">
	<div class="save-template-block">
		<label for="save-template-name"><?php _e('Save current layout as a template', 'wp-smart-editor') ?></label>
		<input type="text" id="save-template-name" placeholder="<?php _e('Template name', 'wp-smart-editor') ?>" />
		<button id="saveBtn" class="btns"><?php _e('Save template', 'wp-smart-editor') ?></button>
		<div id="save-desc" class="template-desc"><?php _e('Save your layout and reuse it on different sections of your website', 'wp-smart-editor') ?></div>
	</div>

	<div class="load-template-block">
		<label for="load-template-block"><?php _e('Load Template', 'wp-smart-editor') ?></label>
		<div id="load-desc" class="template-desc"><?php _e('Append previously saved template to the current layout', 'wp-smart-editor') ?></div>
		<div id="saved-template">
			<ul id="saved-template-list">
				<?php if (count($saved_template) > 0) : ?>
					<?php foreach ($saved_template as $template) : ?>
						<li class="saved-template-item" id="template-<?php echo $template['id'] ?>" data-id-template="<?php echo $template['id'] ?>">
							<a class="title"><span><?php echo esc_html($template['name']) ?></span></a>
							<a class="trash" title="<?php _e('Delete', 'wp-smart-editor') ?>">
								<i class="dashicons dashicons-trash"></i>
							</a>
                            <a class="edit" title="<?php _e('Edit name', 'wp-smart-editor') ?>">
                                <i class="dashicons dashicons-edit"></i>
                            </a>
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>