<?php
defined('ABSPATH') or die('No direct access!');

wp_enqueue_style('wpse-im-ex-css', plugin_dir_url(dirname(__FILE__)).'css/import_export.css');
wp_enqueue_script('wpse-im-ex-js', plugin_dir_url(dirname(__FILE__)).'js/import_export.js');
$view = isset($_GET['view']) ? $_GET['view'] : '';
?>
<?php if (isset($_GET['import-success'])) : ?>
<div class="notice notice-success">
    <p><?php _e('Your '.$_GET['import-success'].' have been successfully imported!', 'wp-smart-editor') ?></p>
    <script>setTimeout(function () {parent.window.location.reload()}, 500)</script>
</div>
<?php endif; ?>
<div class="container">
    <div id="import-block" class="action-block clearfix">
        <h3 class="action-title">
			<?php if ($view == 'profiles') {
				_e('Import profiles', 'wp-smart-editor');
			} elseif ($view == 'styles') {
				_e('Import custom styles', 'wp-smart-editor');
			} ?>
        </h3>
        <div class="action-content">
            <p class="action-desc">
				<?php _e('Choose your .json file that has been exported before to import into your site', 'wp-smart-editor') ?>
            </p>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form-actions" value="import" />
                <input type="file" name="import-file" id="import-file" style="width: 100%; font-size: small;" required />
				<?php wp_nonce_field('import_nonce', 'import_nonce') ?>
				<?php if ($view == 'styles') : ?>
                    <div class="controls">
                        <input type="checkbox" name="override-exist-styles" id="override-exist-styles" />
                        <label for="override-exist-styles" id="override-exist-styles-label"><?php _e('Override existing custom styles with same class-name', 'wp-smart-editor') ?></label>
                    </div>
				<?php endif; ?>
                <input type="submit" class="wpse-button" id="import-btn" value="<?php _e('Run Import', 'wp-smart-editor') ?>" />
            </form>
        </div>
    </div>

    <div id="export-block" class="action-block clearfix">
        <h3 class="action-title">
            <?php if ($view == 'profiles') {
                _e('Export profiles', 'wp-smart-editor');
            } elseif ($view == 'styles') {
	            _e('Export custom styles', 'wp-smart-editor');
            } ?>
        </h3>
        <div class="action-content">
            <p class="action-desc">
				<?php _e('Export to a .json file, that you can use it later for importing to other site', 'wp-smart-editor') ?>
            </p>
            <form action="" method="POST">
                <?php if ($view == 'profiles') : ?>
                <?php $profile_list = get_posts(array('post_type' => 'wpse_profiles', 'post_status' => 'publish')); ?>
                <div class="controls">
                    <input type="hidden" name="form-actions" value="export_profiles" id="export-profiles" checked required />
                </div>
                <div id="profiles-list" style="margin-left: 10px">
                    <?php if (count($profile_list)) : ?>
                        <div class="controls" style="padding: 3px 0; border-bottom: 1px solid #ddd">
                            <input type="checkbox" id="profile-check-all">
                            <label for="profile-check-all"><?php _e('Select all', 'wp-smart-editor') ?></label>
                        </div>
                        <?php foreach ($profile_list as $profile) : ?>
                            <div class="controls">
                                <input type="checkbox" name="profile-id[]" value="<?php echo $profile->ID ?>" id="profile-<?php echo $profile->ID ?>" />
                                <label for="profile-<?php echo $profile->ID ?>"><?php echo $profile->post_title ?></label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php elseif ($view == 'styles') : ?>
                <div class="controls">
                    <input type="hidden" name="form-actions" value="export_custom_styles" id="export-custom_styles" checked required />
                </div>
                <?php endif; ?>
				<?php wp_nonce_field('export_nonce', 'export_nonce') ?>
                <input type="submit" class="wpse-button" id="export-profile-btn" value="<?php _e('Run Export', 'wp-smart-editor') ?>" />
            </form>
        </div>
    </div>
</div>