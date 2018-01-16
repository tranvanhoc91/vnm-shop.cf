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
defined('ABSPATH') || die();
$app = Application::getInstance('wpfd');
?>
<?php if (!empty($this->file)): ?>
    <?php
    $bg_color = wpfdBase::loadValue($this->file_params, 'singlebg', '#444444');
    $hover_color = wpfdBase::loadValue($this->file_params, 'singlehover', '#888888');
    $font_color = wpfdBase::loadValue($this->file_params, 'singlefontcolor', '#ffffff');

    ?>
    <style>
        .wpfd-single-file .wpfd_previewlink {
            margin-top: 10px;
            display: block;
            font-weight: bold;
        }
        <?php if ($bg_color != '') {?>
        .wpfd-single-file .wpfd-file-link {
            background-color:<?php echo $bg_color;?> !important;
        }
        <?php } ?>
        <?php if ($font_color != '') {?>
        .wpfd-single-file .wpfd-file-link a{
            color:<?php echo $font_color;?> !important;
        }
        <?php } ?>
        <?php if ($hover_color != '') { ?>
        .wpfd-single-file .wpfd-file-link a:hover {
            background-color:<?php echo $hover_color;?> !important;
        }
        <?php } ?>
    </style>

    <div class="wpfd-file wpfd-single-file" data-file="<?php echo $this->file->ID; ?>">
        <div class="wpfd-file-link wpfd_downloadlink">
        <a class="noLightbox"
           href="<?php echo $this->file->linkdownload ?>"
           data-id="<?php echo $this->file->ID; ?>" title="<?php echo ($this->file->description)?$this->file->description:$this->file->title; ?>">
            <span class="droptitle"><?php echo $this->file->title; ?></span><br/>
                <span class="dropinfos"> 
                      <?php if (wpfdBase::loadValue($this->params, 'showsize', 1) == 1): ?>
                          <b><?php _e('Size', 'wp-smart-editor'); ?> : </b><?php echo wpfdHelperFile::bytesToSize($this->file->size); ?>
                      <?php endif; ?>
                    <b><?php _e('Format', 'wp-smart-editor'); ?> : </b><?php echo strtoupper($this->file->ext); ?>
                    </span>
        </a><br>
        <?php if (isset($this->file->openpdflink)) { ?>
            <a href="<?php echo $this->file->openpdflink; ?>" class="noLightbox" target="_blank">
                <?php _e('Preview','wp-smart-editor'); ?> </a>
        <?php } else if (isset($this->file->viewerlink)) { ?>
            <a data-id="<?php echo $this->file->ID;?>" data-catid="<?php echo $this->file->catid;?>" data-file-type="<?php echo $this->file->ext;?>" class="openlink wpfdlightbox wpfd_previewlink noLightbox" href="<?php echo $this->file->viewerlink;?>">
                <?php _e('Preview','wp-smart-editor'); ?></a>
        <?php } ?>
        </div>
    </div>
<?php endif; ?>
