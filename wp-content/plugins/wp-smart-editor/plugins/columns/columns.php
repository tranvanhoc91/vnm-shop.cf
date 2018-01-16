<?php
defined('ABSPATH') or die('Restricted Access!');

wp_enqueue_style('dashicons');
wp_enqueue_style('wpse-col-style', plugin_dir_url(__FILE__). 'css/admin.css');

wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');
wp_enqueue_script('tinymce', '//cdn.tinymce.com/4/tinymce.min.js');
wp_enqueue_script('jquery-ddslick', plugin_dir_url(__FILE__).'jquery.ddslick.min.js');
wp_enqueue_script('wpse-columns', plugin_dir_url(__FILE__).'columns.js');
wp_localize_script('wpse-columns', 'myCol', array(
    'url' => plugin_dir_url(__FILE__),
    'confirm' => __('All existing columns will be lost, do you want to continue?', 'wp-smart-editor'),
    'noSpace' => __('Not enough spaces reserved. Resize your columns to add new one!', 'wp-smart-editor'),
    'chooseLine' => __('Please choose a position to insert columns!', 'wp-smart-editor'),
    'confirmRemove' => __('Are you sure to delete this columns? This action cannot be undone!', 'wp-smart-editor')
))
?>
<div class="preload-img"></div>
<div class="container" style="display: none;">
    <div class="dry-header-wrapper clearfix">
        <div class="action-buttons">
            <div id="columnAdd" class="btns btns-add" title="<?php _e('Add a column at right side of the last column', 'wp-smart-editor') ?>"><?php _e('Add a column', 'wp-smart-editor') ?></div>
        </div>
        <div class="plugin-details">
            <b><?php _e('Column manager', 'wp-smart-editor') ?></b>
            <p><?php _e('Use a column pre-defined structure or create your own, up to 5 columns layout. Then resize them as you want and define content padding (inner space).', 'wp-smart-editor') ?></p>
        </div>
        <select id="columnStructure" class="wrapper-dropdown">
            <option value="0"><?php _e('Empty', 'wp-smart-editor') ?></option>
            <option value="2"><?php _e('2 Columns', 'wp-smart-editor') ?></option>
            <option value="3"><?php _e('3 Columns', 'wp-smart-editor') ?></option>
            <option value="4"><?php _e('4 Columns', 'wp-smart-editor') ?></option>
            <option value="5"><?php _e('5 Columns', 'wp-smart-editor') ?></option>
            <option value="-1"><?php _e('Existing Structure', 'wp-smart-editor') ?></option>
        </select>
        <div class="control-group">
            <div class="control-label">
                <label for="col_padding" style="font-size: 13px"><?php _e('Padding', 'wp-smart-editor') ?>:</label>
            </div>
            <div class="controls" style="font-size: 13px">
                <input type="text" name="col_padding" class="mini-input observeBtnChanges" id="col_padding" value="5" /> px
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <label for="col_responsive" title="<?php _e('Enable the columns to be responsive on mobile devices', 'wp-smart-editor') ?>" style="font-size: 13px"><?php _e('Responsive columns', 'wp-smart-editor') ?>:</label>
            </div>
            <div class="controls" style="vertical-align: middle;">
                <label class="switch">
                    <input type="checkbox" name="col_responsive" value="1" class="observeBtnChanges" id="col_responsive" checked />
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
        <input type="hidden" id="activeColumnStructure" value=""/>
    </div>
    <div id="dry-wrapper" class="wp-core-ui clearfix">
        <form>
            <div class="sliderContainer">
                <div id="editSlider" class="column-slider">
                    <div class="column-slider-container ui-droppable">
                        <div class="column-slider-innerBar" id="droppable"></div>
                        <div class="column-sliders"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="contentHolder" style="font-size: 13px"></div>

    <div class="dry_row">
        <button id="btn_save" class="btns"><?php _e('Insert Columns','wp-smart-editor') ?></button>
    </div>

</div>