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

?>
<div class="control-group">
    <label class="control-label" for="marginleft"><?php _e('Margin left','wp-smart-editor'); ?></label>
    <div class="controls"><input name="params[marginleft]" value="<?php echo $this->params["marginleft"]; ?>" class="inputbox input-block-level" type="text"></div>
</div>
<div class="control-group">
    <label class="control-label" for="marginright"><?php _e('Margin right','wp-smart-editor'); ?></label>
    <div class="controls"><input name="params[marginright]" value="<?php echo $this->params["marginright"]; ?>" class="inputbox input-block-level" type="text"></div>
</div>
<div class="control-group">
    <label class="control-label" for="margintop"><?php _e('Margin top','wp-smart-editor'); ?></label>
    <div class="controls"><input name="params[margintop]" value="<?php echo $this->params["margintop"]; ?>" class="inputbox input-block-level" type="text"></div>
</div>
<div class="control-group">
    <label class="control-label" for="marginbottom"><?php _e('Margin bottom','wp-smart-editor'); ?></label>
    <div class="controls"><input name="params[marginbottom]" value="<?php echo $this->params["marginbottom"]; ?>" class="inputbox input-block-level" type="text"></div>
</div>
<div class="control-group">
    <label class="control-label" for="showtitle"><?php _e('Show title','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showtitle]" class="inline">
            <option value="1" <?php if($this->params["showtitle"]=="1") { echo 'selected="selected"';}?> ><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showtitle"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="croptitle"><?php _e('Crop titles','wp-smart-editor'); ?></label>
    <div class="controls"><input name="params[croptitle]" value="<?php echo $this->params["croptitle"]; ?>" class="inputbox input-block-level" type="text"></div>
</div>

<div class="control-group">
    <label class="control-label" for="showsize"><?php _e('Show weight','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showsize]" class="inline">
            <option value="1" <?php if($this->params["showsize"]=="1") { echo 'selected="selected"';}?> ><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showsize"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="showversion"><?php _e('Show version','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showversion]" class="inline">
            <option value="1" <?php if($this->params["showversion"]=="1") { echo 'selected="selected"';}?> ><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showversion"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="showhits"><?php _e('Show hits','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showhits]" class="inline">
            <option value="1" <?php if($this->params["showhits"]=="1") { echo 'selected="selected"';}?> ><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showhits"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="showdownload"><?php _e('Show download link','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showdownload]" class="inline">
            <option value="1" <?php if($this->params["showdownload"]=="1") { echo 'selected="selected"';}?>><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showdownload"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="bgdownloadlink"><?php _e('Background download link','wp-smart-editor'); ?></label>
    <div class="controls">
        <input name="params[bgdownloadlink]" value="<?php echo $this->params["bgdownloadlink"]; ?>" class="inputbox input-block-level wp-color-field" type="text">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="colordownloadlink"><?php _e('Color download link','wp-smart-editor'); ?></label>
    <div class="controls">
        <input name="params[colordownloadlink]" value="<?php echo $this->params["colordownloadlink"]; ?>" class="inputbox input-block-level wp-color-field" type="text">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="showdateadd"><?php _e('Show date added','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showdateadd]" class="inline">
            <option value="1" <?php if($this->params["showdateadd"]=="1") { echo 'selected="selected"';}?> ><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showdateadd"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="showdatemodified"><?php _e('Show date modified','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showdatemodified]" class="inline">
            <option value="1" <?php if($this->params["showdatemodified"]=="1") { echo 'selected="selected"';}?>><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showdatemodified"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="showsubcategories"><?php _e('Show subcategories','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showsubcategories]" class="inline">
            <option value="1" <?php if($this->params["showsubcategories"]=="1") { echo 'selected="selected"';}?>><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showsubcategories"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="showbreadcrumb"><?php _e('Show Breadcrumb','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showbreadcrumb]" class="inline">
            <option value="1" <?php if($this->params["showbreadcrumb"]=="1") { echo 'selected="selected"';}?>><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showbreadcrumb"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="showfoldertree"><?php _e('Show folder tree','wp-smart-editor'); ?></label>
    <div class="controls">
        <select name="params[showfoldertree]" class="inline">
            <option value="1" <?php if($this->params["showfoldertree"]=="1") { echo 'selected="selected"';}?>><?php _e('Yes','wp-smart-editor'); ?></option>
            <option value="0" <?php if($this->params["showfoldertree"]=="0") { echo 'selected="selected"';}?> ><?php _e('No','wp-smart-editor'); ?></option>
        </select>
    </div>
</div>

