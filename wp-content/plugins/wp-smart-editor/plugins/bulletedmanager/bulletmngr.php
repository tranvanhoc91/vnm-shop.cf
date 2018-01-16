<?php
defined('ABSPATH') or die('Restricted Access!');

wp_enqueue_style('jquery-minicolors-styles', plugin_dir_url(__FILE__).'css/jquery.minicolors.css');
wp_enqueue_style('wpse-bootstrap', plugin_dir_url(__FILE__).'css/bootstrap.css');
wp_enqueue_style('wpse_bul_styles', plugin_dir_url(__FILE__).'css/style.css');

wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');
wp_enqueue_script('tinymce', '//cdn.tinymce.com/4/tinymce.min.js');
wp_enqueue_script('jquery-ddslick', plugin_dir_url(__FILE__).'jquery.ddslick.min.js');
wp_enqueue_script('jquery-minicolors', plugin_dir_url(__FILE__).'jquery.minicolors.min.js');
wp_enqueue_script('wpsebullet-js', plugin_dir_url(__FILE__).'bulleted.js');
$data_array = array(
    'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
    'confirmText' => __('Do you really want to delete this bullet?', 'wp-smart-editor'),
    'updateText' => __('Update', 'wp-smart-editor')
);
wp_localize_script('wpsebullet-js', 'myBul', $data_array);

$bullets_saved = get_option('wpse_bulleted_styles');
?>
<div class="preload-img"></div>

<div class="container" style="display: none;">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-inline">
                <div class="control-group" style="margin-bottom: 20px">
                    <div class="control-label">
                        <label for="bulleted_icon" id="bulleted_icon-lbl"><?php _e('Icon Selection', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <select name="bulleted_icon" id="bulleted_icon">
                            <option value="" data-icon=""><?php _e('None', 'wp-smart-editor') ?></option>
                            <option value="pushpin" data-icon="dashicons-admin-post"><?php _e('Pushpin', 'wp-smart-editor') ?></option>
                            <option value="cofg" data-icon="dashicons-admin-generic"><?php _e('Configuration', 'wp-smart-editor') ?></option>
                            <option value="flag" data-icon="dashicons-flag"><?php _e('Flag', 'wp-smart-editor') ?></option>
                            <option value="star" data-icon="dashicons-star-filled"><?php _e('Star', 'wp-smart-editor') ?></option>
                            <option value="checkmark" data-icon="dashicons-yes"><?php _e('Checkmark', 'wp-smart-editor') ?></option>
                            <option value="minus" data-icon="dashicons-minus"><?php _e('Minus', 'wp-smart-editor') ?></option>
                            <option value="plus" data-icon="dashicons-plus"><?php _e('Plus', 'wp-smart-editor') ?></option>
                            <option value="play" data-icon="dashicons-controls-play"><?php _e('Play', 'wp-smart-editor') ?></option>
                            <option value="arrow" data-icon="dashicons-arrow-right-alt"><?php _e('Arrow Right', 'wp-smart-editor') ?></option>
                            <option value="cross" data-icon="dashicons-dismiss"><?php _e('Cross', 'wp-smart-editor') ?></option>
                            <option value="warning" data-icon="dashicons-warning"><?php _e('Warning', 'wp-smart-editor') ?></option>
                            <option value="help" data-icon="dashicons-editor-help"><?php _e('Help', 'wp-smart-editor') ?></option>
                            <option value="info" data-icon="dashicons-info"><?php _e('Info', 'wp-smart-editor') ?></option>
                            <option value="circle" data-icon="dashicons-marker"><?php _e('Circle', 'wp-smart-editor') ?></option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="font_size"><?php _e('Icon size', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="font_size" data-css-prop="font-size" class="mini-input observeListChanges" id="font_size" value="14" />
                        <button class="btn incr" type="button" id="font_size_incr">+</button>
                        <button class="btn decr" type="button" id="font_size_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="color_icon"><?php _e('Icon color', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input name="color_icon" data-css-prop="color" id="color_icon"
                               class="minicolors minicolors-input observeListChanges"
                               data-position="left" data-control="hue"
                               type="text" value="#000000" size="7" />
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="text_fontsize"><?php _e('Font size', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="text_size" data-css-prop="text-size" class="mini-input observeListChanges" id="text_fontsize" value="14" />
                        <button class="btn incr" type="button" id="text_fontsize_incr">+</button>
                        <button class="btn decr" type="button" id="text_fontsize_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="line_height"><?php _e('Line height', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="line_height" data-css-prop="line-height" class="mini-input observeListChanges" id="line_height" value="15" />
                        <button class="btn incr" type="button" id="line_height_incr">+</button>
                        <button class="btn decr" type="button" id="line_height_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="bulleted_margin"><?php _e('Margin', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="bulleted_margin" data-css-prop="margin" class="mini-input observeListChanges" id="bulleted_margin" value="5" />
                        <button class="btn incr" type="button" id="bulleted_margin_incr">+</button>
                        <button class="btn decr" type="button" id="bulleted_margin_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="bulleted_padding"><?php _e('Padding', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="bulleted_padding" data-css-prop="padding" class="mini-input observeListChanges" id="bulleted_padding" value="5" />
                        <button class="btn incr" type="button" id="bulleted_padding_incr">+</button>
                        <button class="btn decr" type="button" id="bulleted_padding_decr">-</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="form-inline">
                <label for="preview"><?php _e('Preview', 'wp-smart-editor') ?></label>
                <div id="preview" style="padding:20px 0 25px 0;margin-top: 20px; border: 1px solid #ccc;">
                    <ul id="previewList">
                        <li><?php _e('List items', 'wp-smart-editor') ?></li>
                        <li><?php _e('List items', 'wp-smart-editor') ?></li>
                    </ul>
                </div>
            </div>
            <p style="text-align: right; color: #aaa; margin: 10px 0 0 0; min-height: 30px"><span id="save-notice" style="display: none"><?php _e('All modifications were saved!', 'wp-smart-editor') ?></span></p>

            <div class="form-inline" style="text-align: center; margin-top: 30px;">
                <button id="btn_add" class="btn btn-success">
                    <?php _e('Insert', 'wp-smart-editor') ?>
                </button>
                <button id="btn_save" class="btn btn-success">
                    <span id="btn_save_text"><?php _e('Save', 'wp-smart-editor') ?></span>
                </button>
            </div>

            <div class="saved-bullets">
                <label for="saved-bullets-block"><?php _e('Saved bullets', 'wp-smart-editor') ?></label>
                <div id="saved-bullets-block">
                    <ul id="saved-bullets-list" class="clearfix">
                        <?php if ($bullets_saved && count($bullets_saved)) : ?>
                            <?php foreach ($bullets_saved as $bullet) : ?>
                                <li class="bul-items" data-id-bullet="<?php echo $bullet['id'] ?>">
                                    <ul id="bul-<?php echo $bullet['id'] ?>" class="<?php echo $bullet['classes'] ?>">
                                        <li><?php _e('List items', 'wp-smart-editor') ?></li>
                                    </ul>
                                    <br />
                                    <div>
                                        <a class="insert" title="<?php _e('Insert', 'wp-smart-editor') ?>"><i class="dashicons dashicons-plus-alt"></i></a>
                                        <a class="edit" title="<?php _e('Edit', 'wp-smart-editor') ?>"><i class="dashicons dashicons-edit"></i></a>
                                        <a class="trash" title="<?php _e('Delete', 'wp-smart-editor') ?>"><i class="dashicons dashicons-trash"></i></a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p style="margin: 0"><?php _e('No bullets saved', 'wp-smart-editor') ?></p>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style id="dynamic-styles"></style>

<style id="bulleted-styles"></style>

<script type="text/javascript">
    var mySavedBullets = {};
<?php if ($bullets_saved && count($bullets_saved)) : ?>
    <?php foreach ($bullets_saved as $bullet) : ?>
        mySavedBullets[<?php echo $bullet['id']?>] = {};
        mySavedBullets[<?php echo $bullet['id']?>]['font-size'] = '<?php echo esc_html($bullet['font-size']) ?>';
        mySavedBullets[<?php echo $bullet['id']?>]['color'] = '<?php echo esc_html($bullet['color']) ?>';
        mySavedBullets[<?php echo $bullet['id']?>]['line-height'] = '<?php echo esc_html($bullet['line-height']) ?>';
        mySavedBullets[<?php echo $bullet['id']?>]['margin'] = '<?php echo esc_html($bullet['margin']) ?>';
        mySavedBullets[<?php echo $bullet['id']?>]['padding'] = '<?php echo esc_html($bullet['padding']) ?>';
        mySavedBullets[<?php echo $bullet['id']?>]['classes'] = '<?php echo esc_html($bullet['classes']) ?>';
        mySavedBullets[<?php echo $bullet['id']?>]['text-size'] = '<?php echo esc_html($bullet['text-size']) ?>';
    <?php endforeach; ?>
<?php endif; ?>
</script>