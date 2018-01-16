<?php
defined('ABSPATH') or die('Restricted Access!');

wp_enqueue_style('jquery-minicolors-styles', plugin_dir_url(dirname(__FILE__)).'bulletedmanager/css/jquery.minicolors.css');
wp_enqueue_style('wpse-bootstrap', plugin_dir_url(dirname(__FILE__)).'bulletedmanager/css/bootstrap.css');
wp_enqueue_style('wpse_btn_styles', plugin_dir_url(__FILE__).'css/style.css');

wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');
wp_enqueue_script('tinymce', '//cdn.tinymce.com/4/tinymce.min.js');
wp_enqueue_script('jquery-minicolors', plugin_dir_url(dirname(__FILE__)).'bulletedmanager/jquery.minicolors.min.js');
wp_enqueue_script('wpsebutton-js', plugin_dir_url(__FILE__).'button.js');

$data_array = array(
    'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
    'confirmText' => __('Do you really want to delete this button?', 'wp-smart-editor'),
    'updateText' => __('Update', 'wp-smart-editor')
);
wp_localize_script('wpsebutton-js', 'myButtons', $data_array);

$buttons_saved = get_option('wpse_button_styles');
?>
<div class="preload-img"></div>
<div class="container" style="display: none;">
    <div class="row">
        <div class="col-sm-5" id="accordion">
            <h3><?php _e('Text / Color', 'wp-smart-editor') ?></h3>
            <div id="font-tab">
                <div class="control-group">
                    <div class="control-label">
                        <label for="button_label"><?php _e('Text', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="button_label" data-css-prop="text" class="mini-input observeBtnChanges" id="button_label" value="<?php _e('Button text', 'wp-smart-editor') ?>" />
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="button_fontsize"><?php _e('Font size', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="button_fontsize" data-css-prop="font-size" class="mini-input observeBtnChanges" id="button_fontsize" value="14" />
                        <button class="btn incr" type="button" id="button_fontsize_incr">+</button>
                        <button class="btn decr" type="button" id="button_fontsize_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="text_color"><?php _e('Color', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input name="text_color" data-css-prop="color" id="text_color"
                               class="minicolors minicolors-input observeBtnChanges"
                               data-position="left" data-control="hue"
                               type="text" value="#fff" size="7" />
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="back_color"><?php _e('Background color', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input name="back_color" data-css-prop="background" id="back_color"
                               class="minicolors minicolors-input observeBtnChanges"
                               data-position="left" data-control="hue"
                               type="text" value="#0e83cd" size="7" />
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label" style="margin-bottom: 10px">
                        <label for="link_target"><?php _e('Link', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="link_target" class="input" id="link_target" placeholder="http://" />
                    </div>
                </div>
            </div>

            <h3><?php _e('Box / Border', 'wp-smart-editor') ?></h3>
            <div id="border-tab">
                <div class="control-group">
                    <div class="control-label">
                        <label for="button_padding"><?php _e('Padding', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls" style="display: block">
                        <div style="position: relative; border: 1px solid #ccc; height: 150px; width: 200px; margin: 20px auto">
                            <div style="margin: 40px;border: 1px dashed #ccc;line-height: 70px;text-align: center;">Lorem ipsum</div>
                            <div style="position: absolute; top: 65px; left: 5px">
                                <input style="width: 30px" type="text" name="button_padding_left" data-css-prop="padding-left" class="mini-input observeBtnChanges" id="button_padding_left" value="12" />
                            </div>
                            <div style="position: absolute; top: 5px; left: 80px">
                                <input style="width: 30px" type="text" name="button_padding_top" data-css-prop="padding-top" class="mini-input observeBtnChanges" id="button_padding_top" value="6" />
                            </div>
                            <div style="position: absolute; top: 65px; right: 5px">
                                <input style="width: 30px" type="text" name="button_padding_right" data-css-prop="padding-right" class="mini-input observeBtnChanges" id="button_padding_right" value="12" />
                            </div>
                            <div style="position: absolute; bottom: 5px; left: 80px">
                                <input style="width: 30px" type="text" name="button_padding_bottom" data-css-prop="padding-bottom" class="mini-input observeBtnChanges" id="button_padding_bottom" value="6" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="button_radius"><?php _e('Border radius', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="button_radius" data-css-prop="border-radius" class="mini-input observeBtnChanges" id="button_radius" value="0" />
                        <button class="btn incr" type="button" id="button_radius_incr">+</button>
                        <button class="btn decr" type="button" id="button_radius_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="border_color"><?php _e('Border color', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input name="border_color" data-css-prop="border-color" id="border_color"
                               class="minicolors minicolors-input observeBtnChanges"
                               data-position="left" data-control="hue"
                               type="text" value="#357ebd" size="7" />
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="button_border_width"><?php _e('Border width', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="button_border_width" data-css-prop="border-width" class="mini-input observeBtnChanges" id="button_border_width" value="1" />
                        <button class="btn incr" type="button" id="button_border_width_incr">+</button>
                        <button class="btn decr" type="button" id="button_border_width_decr">-</button>
                    </div>
                </div>
            </div>

            <h3><?php _e('Hover', 'wp-smart-editor') ?></h3>
            <div id="hover-tab">
                <div class="control-group">
                    <div class="control-label">
                        <label for="text_hover_color"><?php _e('Text color', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input name="text_hover_color" data-css-prop="color-hover" id="text_hover_color"
                               class="minicolors minicolors-input observeBtnChanges"
                               data-position="left" data-control="hue"
                               type="text" value="#ffffff" size="7" />
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="back_hover_color"><?php _e('Background color', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input name="back_hover_color" data-css-prop="background-hover" id="back_hover_color"
                               class="minicolors minicolors-input observeBtnChanges"
                               data-position="left" data-control="hue"
                               type="text" value="#1990f9" size="7" />
                    </div>
                </div>

                <h4 style="margin: 5px;">Box Shadow</h4>

                <div class="control-group">
                    <div class="control-label">
                        <label for="boxshadow_hover_color"><?php _e('Color', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input name="boxshadow_hover_color" data-css-prop="box-shadow-color-hover" id="boxshadow_hover_color"
                               class="minicolors minicolors-input observeBtnChanges"
                               data-position="left" data-control="hue"
                               type="text" value="#bababa" size="7" />
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="boxshadow_horz"><?php _e('H offset', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="boxshadow_horz" data-css-prop="box-shadow-horz-hover" class="mini-input observeBtnChanges" id="boxshadow_horz" value="3" />
                        <button class="btn incr" type="button" id="boxshadow_horz_incr">+</button>
                        <button class="btn decr" type="button" id="boxshadow_horz_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="boxshadow_vert"><?php _e('V offset', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="boxshadow_vert" data-css-prop="box-shadow-vert-hover" class="mini-input observeBtnChanges" id="boxshadow_vert" value="3" />
                        <button class="btn incr" type="button" id="boxshadow_vert_incr">+</button>
                        <button class="btn decr" type="button" id="boxshadow_vert_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="boxshadow_blur"><?php _e('Blur', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="boxshadow_blur" data-css-prop="box-shadow-blur-hover" class="mini-input observeBtnChanges" id="boxshadow_blur" value="3" />
                        <button class="btn incr" type="button" id="boxshadow_blur_incr">+</button>
                        <button class="btn decr" type="button" id="boxshadow_blur_decr">-</button>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <label for="boxshadow_spread"><?php _e('Spread', 'wp-smart-editor') ?>:</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="boxshadow_spread" data-css-prop="box-shadow-spread-hover" class="mini-input observeBtnChanges" id="boxshadow_spread" value="0" />
                        <button class="btn incr" type="button" id="boxshadow_spread_incr">+</button>
                        <button class="btn decr" type="button" id="boxshadow_spread_decr">-</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-7">
            <div id="preview-block">
                <label for="preview"><?php _e('Preview', 'wp-smart-editor') ?>:</label>
                <div id="preview">
                    <a id="previewBtn" class="wpsebtn"><?php _e('Button text', 'wp-smart-editor') ?></a>
                </div>
            </div>
            <p style="text-align: right; color: #aaa; margin: 0; min-height: 30px"><span id="save-notice" style="display: none"><?php _e('All modifications were saved!', 'wp-smart-editor') ?></span></p>

            <div class="submit-block" style="text-align: center; margin-top: 20px;">
                <button id="btn_add" class="btn btn-success">
                    <?php _e('Insert', 'wp-smart-editor') ?>
                </button>
                <button id="btn_save" class="btn btn-blue">
                    <span id="btn_save_text"><?php _e('Save', 'wp-smart-editor') ?></span>
                </button>
            </div>

            <div class="saved-btns">
                <label for="saved-btns"><?php _e('Saved buttons', 'wp-smart-editor') ?>:</label>
                <div id="saved-btns-block">
                    <ul class="saved-btns-list clearfix">
                        <?php if (count($buttons_saved)) : ?>
                            <?php foreach ($buttons_saved as $button) : ?>
                                <li class="btns-item" data-id-button="<?php echo $button['id'] ?>">
                                    <a id="btn-<?php echo $button['id'] ?>" data-id-button="<?php echo $button['id'] ?>" class="wpsebtn"><?php echo $button['text'] ?></a>
                                    <br />
                                    <div>
                                        <a class="insert" title="<?php _e('Insert', 'wp-smart-editor') ?>"><i class="dashicons dashicons-plus-alt"></i></a>
                                        <a class="edit" title="<?php _e('Edit', 'wp-smart-editor') ?>"><i class="dashicons dashicons-edit"></i></a>
                                        <a class="trash" title="<?php _e('Delete', 'wp-smart-editor') ?>"><i class="dashicons dashicons-trash"></i></a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p><?php _e('No buttons saved', 'wp-smart-editor') ?></p>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style id="dynamic-styles" ></style>

<style id="btn-styles" ></style>

<script type="text/javascript">
    var mySavedButtons = {};
<?php if (count($buttons_saved)) : ?>
    <?php foreach ($buttons_saved as $btn_saved) : ?>
        mySavedButtons[<?php echo $btn_saved['id']?>] = {};
        mySavedButtons[<?php echo $btn_saved['id']?>]['font-size'] = '<?php echo esc_html($btn_saved['font-size']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['color'] = '<?php echo esc_html($btn_saved['color']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['background'] = '<?php echo esc_html($btn_saved['background']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['padding-left'] = '<?php echo esc_html($btn_saved['padding-left']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['padding-top'] = '<?php echo esc_html($btn_saved['padding-top']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['padding-right'] = '<?php echo esc_html($btn_saved['padding-right']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['padding-bottom'] = '<?php echo esc_html($btn_saved['padding-bottom']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['border-width'] = '<?php echo esc_html($btn_saved['border-width']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['border-radius'] = '<?php echo esc_html($btn_saved['border-radius']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['border-color'] = '<?php echo esc_html($btn_saved['border-color']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['color-hover'] = '<?php echo esc_html($btn_saved['color-hover']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['background-hover'] = '<?php echo esc_html($btn_saved['background-hover']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['box-shadow-color-hover'] = '<?php echo esc_html($btn_saved['box-shadow-color-hover']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['box-shadow-horz-hover'] = '<?php echo esc_html($btn_saved['box-shadow-horz-hover']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['box-shadow-vert-hover'] = '<?php echo esc_html($btn_saved['box-shadow-vert-hover']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['box-shadow-blur-hover'] = '<?php echo esc_html($btn_saved['box-shadow-blur-hover']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['box-shadow-spread-hover'] = '<?php echo esc_html($btn_saved['box-shadow-spread-hover']) ?>';
        mySavedButtons[<?php echo $btn_saved['id']?>]['text'] = '<?php echo esc_html($btn_saved['text']) ?>';
        <?php if(isset($btn_saved['btn-link'])) {?>
            mySavedButtons[<?php echo $btn_saved['id']?>]['btn-link'] = '<?php echo esc_html($btn_saved['btn-link']) ?>';
        <?php }?>
    <?php endforeach; ?>
<?php endif; ?>
</script>