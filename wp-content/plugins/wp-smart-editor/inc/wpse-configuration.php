<?php
defined('ABSPATH') or die('Restricted Access!');

$save_config = get_option('wpse_config');

$custom_styles_saved = get_option('wpse_custom_styles', $this::$default_custom_styles);
?>

<h1><?php _e('WP Smart Editor Configuration', 'wp-smart-editor') ?></h1>
<?php if (isset($_GET['save'])) : ?>
    <div id="wpse-config-success">
        <?php _e('Settings saved successfully', 'wp-smart-editor') ?>
        <i class="dashicons dashicons-dismiss" id="wpse-config-close"></i>
    </div>
<?php endif; ?>
<div class="config-container" style="margin-right: 20px">
    <ul class="tabs cyan z-depth-1">
        <li class="tab"><a href="#tabs-1" class="link-tab white-text waves-effect waves-light"><?php _e('Configuration', 'wp-smart-editor') ?></a></li>
        <li class="tab" id="custom-styles-tab"><a href="#tabs-2" class="link-tab white-text waves-effect waves-light"><?php _e('Custom Styles', 'wp-smart-editor') ?></a></li>
        <li class="tab"><a href="#tabs-3" class="link-tab white-text waves-effect waves-light"><?php _e('Translation Tools', 'wp-smart-editor') ?></a></li>
    </ul>

    <div id="tabs-1" class="tabs-content">
        <div class="config-block">
            <form method="post">
                <?php wp_nonce_field('wpse_config_nonce', 'wpse_config_nonce_field'); ?>
                <ul class="config-list">
                    <li>
                        <label for="disable_autop" class="config-label wpse_qtip" style="vertical-align: middle;" alt="<?php _e('Display (p) and (br) tags in editor HTML view','wp-smart-editor') ?>"><?php _e('Display (p) tags', 'wp-smart-editor'); ?></label>
                        <div class="switch-btn">
                            <label class="switch">
                                <input type="checkbox" class="config-input" id="disable_autop" name="disable_autop" value="<?php echo $save_config['disable_autop']; ?>" <?php if ($save_config['disable_autop'] == 1){echo 'checked';} ?>>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li>
                    <li>
                        <label for="active_post_editor" class="config-label wpse_qtip" style="vertical-align: middle;" alt="<?php _e('Enable colored code for Post and Page HTML view','wp-smart-editor') ?>"><?php _e('Colored HTML code', 'wp-smart-editor'); ?></label>
                        <div class="switch-btn">
                            <label class="switch">
                                <input type="checkbox" class="config-input" id="active_post_editor" name="active_post_editor" value="<?php echo $save_config['active_post_editor']; ?>" <?php if ($save_config['active_post_editor'] == 1){echo 'checked';} ?>>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li>
                    <li>
                        <label for="active_file_editor" class="config-label wpse_qtip" style="vertical-align: middle;" alt="<?php _e('Enable colored code for Themes and Plugins editor','wp-smart-editor') ?>"><?php _e('Colored Code', 'wp-smart-editor'); ?></label>
                        <div class="switch-btn">
                            <label class="switch">
                                <input type="checkbox" class="config-input" id="active_file_editor" name="active_file_editor" value="<?php echo $save_config['active_file_editor']; ?>" <?php if ($save_config['active_file_editor'] == 1){echo 'checked';} ?>>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li>
                </ul>
                <span class="save-btns save-config waves-effect waves-light waves-input-wrapper">
                    <input class="cyan" type="submit" id="save_config" name="save_config" value="<?php _e('Save settings', 'wp-smart-editor') ?>" />
                </span>
            </form>
        </div>
    </div>

    <!--Custom styles tab-->
    <div id="tabs-2"  class="tabs-content clearfix">
        <h3 style="margin: 0; padding: 5px 0 10px 0;">
            <?php _e('Custom styles', 'wp-smart-editor') ?>
            <button type="button" class="im_ex_wpse_settings" onclick="tb_show('Import/Export', 'admin.php?page=wpse-import-export&view=styles&noheader=1TB_iframe=1&width=550&height=350')"><?php _e('Import/Export', 'wp-smart-editor') ?></button>
        </h3>

        <div class="col-sm-2" id="wpse-customstyles-list">
            <div id="mybootstrap">
                <div class="wpse-customstyles-list-title" style="font-weight: bold; margin-bottom: 10px;"><?php _e('CSS Class names', 'wp-smart-editor') ?>:</div>
                <ul class="wpse-customstyles-list">
                    <?php
                    $content = '';
                    foreach ($custom_styles_saved as $customStyles) {
                        $content .= '<li class="wpse-customstyles-items" data-id-customstyle="'.$customStyles['id'].'">';
                        $content .= '<a><i class="wpseicon-quill"></i><span class="wpse-customstyles-items-title">'.esc_html($customStyles['title']).'</span></a>';
                        $content .= '<a class="copy"><i class="wpseicon-copy"></i></a>';
                        $content .= '<a class="trash"><i class="wpseicon-trash"></i></a>';
                        $content .= '<ul style="margin-left: 30px"><li class="wpse-customstyles-items-class">('.esc_html($customStyles['name']).')</li></ul>';
                        $content .= '</li>';
                    }
                    $content .= '<li><a class="wpse-customstyles-new"><i class="wpseicon-plus"></i>'.__("Add new class", 'wp-smart-editor').'</a></li>';

                    echo $content;
                    ?>
                </ul>
                <span id="savedInfo" style="display:none;"><?php _e('All modifications were saved!', 'wp-smart-editor') ?></span>
            </div>
        </div>

        <div class="col-sm-5" id="wpse-customstyles-info">
            <div class="wpse-customstyles-title">
                <label for="wpse-customstyles-title"><?php _e('Style title', 'wp-smart-editor') ?></label>
                <input type="text" name="customstyles-title" id="wpse-customstyles-title" value="" />
            </div>
            <div class="wpse-customstyles-classname">
                <label for="wpse-customstyles-classname"><?php _e('Style class', 'wp-smart-editor') ?></label>
                <input type="text" name="customstyles-classname" id="wpse-customstyles-classname" value="" />
            </div>
            <div class="wpse-customstyles-css">
                <label for="wpse-customstyles-css"><?php _e('Custom CSS', 'wp-smart-editor') ?></label>
                <textarea name="customstyles-css" id="wpse-customstyles-css"></textarea>
            </div>
            <div id="css-tips" style="border-top: 1px solid #ccc">
                <small><?php _e('Hint: Use "Ctrl + Space" for auto completion', 'wp-smart-editor') ?></small>
            </div>
            <div class="col-sm-12" style="text-align: center">
                <form method="POST">
                    <?php wp_nonce_field('wpse_cstyles_nonce', 'wpse_cstyles_nonce_field'); ?>
                    <span class="save-btns save-custom-styles waves-effect waves-light waves-input-wrapper" style="margin: 10px auto">
                        <input class="cyan" type="submit" id="save_custom_styles" name="save_custom_styles" value="<?php _e('Save styles', 'wp-smart-editor') ?>" />
                    </span>
                </form>
            </div>
        </div>
        <div class="col-sm-5" id="wpse-customstyles-preview">
            <p class="previous-block" style="margin-bottom: 20px"><?php _e('Previous Paragraph Previous Paragraph Previous Paragraph Previous Paragraph Previous Paragraph', 'wp-smart-editor') ?></p>
            <div class="wpse-customstyles-target"><?php _e('Example of text', 'wp-smart-editor') ?></div>
            <p class="follow-block"><?php _e('Following Paragraph Following Paragraph  Following Paragraph Following Paragraph Following Paragraph', 'wp-smart-editor') ?></p>
        </div>
    </div> <!--end of Custom styles tab-->

    <div id="tabs-3" class="tabs-content">
        <?php echo \Joomunited\WPSE\Jutranslation\Jutranslation::getInput(); ?>
    </div>
</div>
<?php
