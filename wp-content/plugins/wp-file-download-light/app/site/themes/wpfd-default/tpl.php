<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Application;
//-- No direct access
defined( 'ABSPATH' ) || die();
$app = Application::getInstance('wpfd');
?>
<?php if(wpfdBase::loadValue($this->params,'showsubcategories',1)==1): ?>
<script type="text/x-handlebars-template" id="wpfd-template-categories">
<div class="wpfd-categories">
<?php if(wpfdBase::loadValue($this->params,'showcategorytitle',1)==1): ?>
    {{#if category}}
        {{#with category}}
        <h2>{{name}}
            {{#if parent}}
            <a class="catlink wpfdcategory backcategory" href="#" data-idcat="{{parent}}"><i class="zmdi zmdi-chevron-left"></i><?php _e('Back','wp-smart-editor'); ?></a>
            {{/if}}
        </h2>
        {{/with}}
    {{/if}}
<?php endif; ?>

{{#if categories}}
    {{#each categories}}
        <a class="catlink wpfdcategory" style="<?php echo $this->style; ?>" href="#" data-idcat="{{term_id}}"><span>{{name}}</span><i class="zmdi zmdi-folder wpfd-folder"></i></a>
    {{/each}}
{{/if}}
</div>
</script>
<?php endif; ?>

<script type="text/x-handlebars-template" id="wpfd-template-files">
{{#if files}}
<div class="wpfd_list">
    {{#each files}}
                <div class="file" style="<?php echo $this->style; ?>">
                    <div class="filecontent">
                        <div class="ext {{ext}}"><span class="txt">{{ext}}</div>

                        <?php if(wpfdBase::loadValue($this->params,'showtitle',1)==1): ?>
                        <h3><a href="{{linkdownload}}" class="wpfd_downloadlink" title="{{post_title}}">{{crop_title}}</a></h3>
                        <?php endif; ?>
                        <div class="file-xinfo">
                            <div class="file-desc">{{{description}}}</div>
                            <?php if(wpfdBase::loadValue($this->params,'showversion',1)==1): ?>
                                {{#if versionNumber}}
                                <div class="file-version"><span><?php _e('Version :','wp-smart-editor'); ?></span> {{versionNumber}}&nbsp;</div>
                                {{/if}}
                            <?php endif; ?>
                            <?php if(wpfdBase::loadValue($this->params,'showsize',1)==1): ?>
                                <div class="file-size"><span><?php _e('Size :','wp-smart-editor'); ?></span> {{bytesToSize size}}</div>
                            <?php endif; ?>
                            <?php if(wpfdBase::loadValue($this->params,'showhits',1)==1): ?>
                                <div class="file-hits"><span><?php _e('Hits :','wp-smart-editor'); ?></span> {{hits}}</div>
                            <?php endif; ?>
                            <?php if(wpfdBase::loadValue($this->params,'showdateadd',0)==1): ?>
                                <div class="file-dated"><span><?php _e('Date added :','wp-smart-editor'); ?></span> {{created}}</div>
                            <?php endif; ?>
                            <?php if(wpfdBase::loadValue($this->params,'showdatemodified',0)==1): ?>
                                <div class="file-modifed"><span><?php _e('Date modified :','wp-smart-editor'); ?></span> {{modified}}</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="file-right">
                        <?php if(wpfdBase::loadValue($this->params,'showdownload',1)==1): ?>
                            <?php
                            $bg_download = wpfdBase::loadValue($this->params,'bgdownloadlink','');
                            $color_download = wpfdBase::loadValue($this->params,'colordownloadlink','');
                            $download_style = '';
                            if ($bg_download != '' ) {
                                $download_style .= 'background-color:'.$bg_download.';';
                            }
                            if ($color_download != '') {
                                $download_style .= 'color:'.$color_download.';';
                            }
                            if ($download_style != '') {
                                $download_style = 'style="'.$download_style.'"';
                            }
                            ?>

                            <a class="downloadlink wpfd_downloadlink" href="{{linkdownload}}" <?php echo $download_style;?>><?php _e('Download','wp-smart-editor'); ?><i class="zmdi zmdi-cloud-download wpfd-download"></i></a>
                        <?php endif; ?>
                        {{#if openpdflink}}
                            <a href="{{openpdflink}}" target="_blank"><?php _e('Preview','wp-smart-editor'); ?><i class="zmdi zmdi-filter-center-focus wpfd-preview"></i></a>
                        {{else}}
                            {{#if viewerlink}}
                        <?php
                        $viewer_attr = 'class="openlink wpfdlightbox wpfd_previewlink"';
                        if ($this->config['use_google_viewer'] == 'tab') {
                            $viewer_attr = 'class="openlink wpfd_previewlink" target="_blank"';
                        } ?>
                        <a href="{{viewerlink}}" <?php echo $viewer_attr;?> data-id="{{ID}}" data-catid="{{catid}}" data-file-type="{{ext}}"><?php _e('Preview','wp-smart-editor'); ?><i class="zmdi zmdi-filter-center-focus wpfd-preview"></i></a>
                            {{/if}}
                        {{/if}}

                        </span>
                </div>
        {{/each}}
</div>
{{/if}}
</script>
<?php if(!empty($this->category) ): ?>
<?php if(!empty($this->files) || !empty($this->categories)): ?>

<div class="wpfd-content wpfd-content-default wpfd-content-multi" data-category="<?php echo $this->category->term_id; ?>">
    <input type="hidden" id="current_category" value="<?php echo $this->category->term_id; ?>"/>
    <input type="hidden" id="current_category_slug" value="<?php echo $this->category->slug; ?>"/>
     <?php if(wpfdBase::loadValue($this->params,'showbreadcrumb',1)==1): ?>
    <ul class="breadcrumbs wpfd-breadcrumbs-default">
        <li class="active">
		<?php echo $this->category->name; ?> 
	</li>
    </ul>
 <?php endif; ?>
<div class="wpfd-container">
    <?php if(wpfdBase::loadValue($this->params,'showfoldertree',0)==1): ?>
    <div class="wpfd-foldertree wpfd-foldertree-default <?php echo wpfdBase::loadValue($this->params,'showsubcategories',1)==1 ? 'foldertree-hide' : '';?>">
        
    </div>
    <?php endif; ?>
    
    <div class="wpfd-container-default <?php if(wpfdBase::loadValue($this->params,'showfoldertree',0)==1) { echo " with_foldertree";}?>">
        <div class="wpfd-categories">
    <?php if(wpfdBase::loadValue($this->params,'showcategorytitle',1)==1): ?>
    <h2><?php echo $this->category->name; ?></h2>
    <?php endif; ?>
    
    <?php if(count($this->categories) && wpfdBase::loadValue($this->params,'showsubcategories',1)==1): ?>
        <?php foreach ($this->categories as $category): ?>
            <a class="wpfdcategory catlink" style="<?php echo $this->style; ?>" href="#" data-idcat="<?php echo $category->term_id; ?>" title="<?php echo $category->name; ?>">
                <span><?php echo $category->name; ?></span><i class="zmdi zmdi-folder wpfd-folder"></i></a>
        <?php  endforeach; ?>
    <?php endif; ?>
        </div>
    <?php if(!empty($this->files)): ?>
        <div class="wpfd_list">
        <?php foreach ($this->files as $file): ?>
            <div class="file" style="<?php echo $this->style; ?>">
                <div class="filecontent">
                    <div class="ext <?php echo $file->ext ; ?>"><span class="txt"><?php echo $file->ext ; ?></div>
                    <?php if(wpfdBase::loadValue($this->params,'showtitle',1)==1): ?>
                    <h3><a class="wpfd_downloadlink" href="<?php echo $file->linkdownload ?>" title="<?php echo $file->post_title ; ?>"  ><?php echo $file->crop_title ; ?></a></h3>
                    <?php endif; ?>
                    <div class="file-xinfo">
                    <div class="file-desc"><?php if(!empty($file->description)) {echo $file->description ;} else { echo '';} ?></div>
                    <?php if(wpfdBase::loadValue($this->params,'showversion',1)==1 && trim($file->versionNumber!='')): ?>
                        <div class="file-version"><span><?php _e('Version :','wp-smart-editor'); ?></span> <?php echo $file->versionNumber; ?>&nbsp;</div>
                    <?php endif; ?>
                    <?php if(wpfdBase::loadValue($this->params,'showsize',1)==1): ?>
                        <div class="file-size"><span><?php _e('Size :','wp-smart-editor'); ?></span> <?php echo ($file->size == 'n/a') ? $file->size : wpfdHelperFile::bytesToSize($file->size); ?></div>
                    <?php endif; ?>
                    <?php if(wpfdBase::loadValue($this->params,'showhits',1)==1): ?>
                        <div class="file-hits"><span><?php _e('Hits :','wp-smart-editor'); ?></span> <?php echo $file->hits; ?></div>
                    <?php endif; ?>
                    <?php if(wpfdBase::loadValue($this->params,'showdateadd',0)==1): ?>
                        <div class="file-dated"><span><?php _e('Date added :','wp-smart-editor'); ?></span> <?php echo $file->created; ?></div>
                    <?php endif; ?>
                    <?php if(wpfdBase::loadValue($this->params,'showdatemodified',0)==1): ?>
                        <div class="file-modified"><span><?php _e('Date modified :','wp-smart-editor'); ?></span> <?php echo $file->modified; ?></div>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="file-right">
                    <?php if(wpfdBase::loadValue($this->params,'showdownload',1)==1): ?>
                        <?php
                        $bg_download = wpfdBase::loadValue($this->params,'bgdownloadlink','');
                        $color_download = wpfdBase::loadValue($this->params,'colordownloadlink','');
                        $download_style = '';
                        if ($bg_download != '' ) {
                            $download_style .= 'background-color:'.$bg_download.';';
                        }
                        if ($color_download != '') {
                            $download_style .= 'color:'.$color_download.';';
                        }
                        if ($download_style != '') {
                            $download_style = 'style="'.$download_style.'"';
                        }
                        ?>
                        <a class="downloadlink wpfd_downloadlink" href="<?php echo $file->linkdownload ?>" <?php echo $download_style;?>>
                        <?php _e('Download','wp-smart-editor'); ?> <i class="zmdi zmdi-cloud-download wpfd-download"></i></a>
                    <?php endif; ?>
                    <?php if (isset($file->openpdflink)) { ?>
                        <a href="<?php echo $file->openpdflink; ?>" target="_blank">
                            <?php _e('Preview','wp-smart-editor'); ?> <i class="zmdi zmdi-filter-center-focus wpfd-preview"></i></a>
                    <?php } else { ?>
                        <?php if (isset($file->viewerlink)) { ?>
                            <?php
                            $viewer_attr = 'class="openlink wpfdlightbox wpfd_previewlink"';
                            if ($file->viewer_type == 'tab') {
                                $viewer_attr = 'class="openlink wpfd_previewlink" target="_blank"';
                            } ?>
                            <a href="<?php echo $file->viewerlink; ?>" <?php echo $viewer_attr;?> data-id="<?php echo $file->ID ; ?>" data-catid="<?php echo $file->catid ; ?>" data-file-type="<?php echo $file->ext ;?>">
                                <?php _e('Preview','wp-smart-editor'); ?> <i class="zmdi zmdi-filter-center-focus wpfd-preview"></i></a>
                        <?php }  ?>
                    <?php } ?>
                    </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div>
</div>
</div>
<?php endif; ?>
<?php endif; ?>