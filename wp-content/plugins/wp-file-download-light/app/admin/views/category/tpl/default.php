<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

// No direct access.
defined( 'ABSPATH' ) || die();
global $wp_roles;

$options=array();

foreach ($wp_roles->roles as $k => $role){
    $selected = '';
    if(in_array(strtolower($k), $this->category->roles)){
        $selected = 'checked="checked"';
    }
    $options[] = '<input type="checkbox" name="params[roles][]" value="'. strtolower($k).'" '.$selected.'/>'.$role['name'].'<br/>';
}

$ordering_options = array(
    'ordering' => __('Ordering','wp-smart-editor'),
    'ext' => __('Type','wp-smart-editor'),
    'title' => __('Title','wp-smart-editor'),
    'description' => __('Description','wp-smart-editor'),
    'size' => __('Filesize','wp-smart-editor'),
    'created_time' => __('Date added','wp-smart-editor'),
    'modified_time' => __('Date modified','wp-smart-editor'),
    'version' => __('Version','wp-smart-editor'),
    'hits' => __('Hits','wp-smart-editor'),
);

if($this->mainConfig['catparameters']=='0'): ?>
<style type="text/css">
    #category-theme-params{display: none;}
</style>
<?php endif; ?>

<div class="wpfdparams">
    <form id="category_params">

        <div class="control-group <?php if ($this->mainConfig['catparameters'] == 0) echo 'hidden'?>">
            <label class="control-label" for="wpfd-theme"><?php _e('Theme','wp-smart-editor'); ?></label>
            <div class="controls">
                <select name="params[theme]" id="wpfd-theme" >
                    <?php foreach($this->themes as $theme) {
                        if($this->category->params['theme']==$theme) {
                            $selected = 'selected="selected"';
                        }else {
                            $selected = '';
                        }
                        echo '<option value="'.$theme.'" '.$selected. ' >'.$theme.'</option>';
                    }?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="visibility"><?php _e('Visibility','wp-smart-editor'); ?></label>
            <div class="controls">
                <select name="params[visibility]" id="visibility">
                    <option value="0" <?php if( $this->category->access==0){echo 'selected="selected"';} ?>><?php _e('Public','wp-smart-editor'); ?></option>
                    <option value="1" <?php if( $this->category->access==1){echo 'selected="selected"';} ?>><?php _e('Private','wp-smart-editor'); ?></option>
                </select>
            </div>
            <div id="visibilitywrap">
                <?php echo implode('',$options); ?>
            </div>
        </div>
        <?php if (defined('WPFDA_VERSION')) { ?>
            <div class="control-group">
                <label class="control-label" for="wpfd-social-locker"><?php _e('Lock content by socials','wp-smart-editor'); ?></label>
                <div class="controls">
                    <select name="params[social]" id="wpfd-social-locker" >
                        <option value="0" <?php if( $this->category->params['social']==0){echo 'selected="selected"';} ?>><?php _e('No','wp-smart-editor'); ?></option>
                        <option value="1" <?php if( $this->category->params['social']==1){echo 'selected="selected"';} ?>><?php _e('Yes','wp-smart-editor'); ?></option>
                    </select>
                </div>
            </div>
        <?php } ?>

        <div class="control-group">
            <label for="ordering" class="control-label"><?php _e('Ordering','wp-smart-editor'); ?></label>
            <div class="controls">
                <select name="params[ordering]" id="ordering" >
                    <?php foreach ($ordering_options as $order_key => $order_text) { ?>
                        <option value="<?php echo $order_key;?>" <?php selected($this->category->ordering, $order_key);?>><?php echo $order_text; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="orderingdir" class="control-label"><?php _e('Ordering direction','wp-smart-editor'); ?></label>
            <div class="controls">
                <select name="params[orderingdir]" id="orderingdir">
                    <option value="asc" <?php selected($this->category->orderingdir, 'asc');?>><?php _e('Ascending','wp-smart-editor'); ?></option>
                    <option value="desc" <?php selected($this->category->orderingdir, 'desc');?>><?php _e('Descending','wp-smart-editor'); ?></option>
                </select>
            </div>
        </div>

        <div id="category-theme-params">
            <?php
            if (wpfdBase::checkExistTheme($this->category->params['theme'])) {
                echo $this->loadTemplate('theme-'. $this->category->params['theme']);
            } else {
                $dir = wp_upload_dir();
                $this->setPath($dir['basedir'] . '/wpfd-themes/wpfd-'. $this->category->params['theme'].'/');
                echo $this->loadTemplate('theme-'. $this->category->params['theme']);
            }
            ?>
        </div>
        <?php if ($this->mainConfig['shortcodecat'] == 1) { ?>

            <div class="control-group">
                <label title="" class="control-label" for="shortcodecat"><?php echo __('Category shortcode', 'wp-smart-editor');?></label>
                <div class="controls">
                    <input type="text" id="shortcodecat" name="shortcodecat" readonly="true" value="[wpfd_category id='<?php echo $this->category->term_id;?>']" class="inputbox">
                </div>
            </div>
        <?php } ?>

    <button class="button button-primary" type="submit"><?php _e('Save','wp-smart-editor'); ?></button>
   </form>
</div>