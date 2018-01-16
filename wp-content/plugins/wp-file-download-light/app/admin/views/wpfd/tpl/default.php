<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0W
 */

use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Model;
use Joomunited\WPFramework\v1_0_2\Application;
// No direct access.
defined( 'ABSPATH' ) || die();

if (!current_user_can('wpfd_manage_file')) {
    wp_die(__('You don\'t have permission to view this page', 'wp-smart-editor'));
}

wp_localize_script('wpfd-main','l10n',array(
                'Drop files here to upload'=>__('Drop files here to upload','wp-smart-editor'),
                'Or use the button below'=>__('Or use the button below','wp-smart-editor'),
                'Add remote file'=>__('Add remote file','wp-smart-editor'),
                'Allowed extensions'=>__('Allowed extensions','wp-smart-editor'),
                'SEO URL'=>__('SEO URL','wp-smart-editor'),
                'Show files import'=>__('Show files import','wp-smart-editor'),
                'Max upload file size (Mb)'=>__('Max upload file size (Mb)','wp-smart-editor'),
                'Delete all files on uninstall'=>__('Delete all files on uninstall','wp-smart-editor'),
                'Close categories'=>__('Close categories','wp-smart-editor'),
                'Theme per categories'=>__('Theme per categories','wp-smart-editor'),
                'Default theme per category'=>__('Default theme per category','wp-smart-editor'),
                'Date format'=>__('Date format','wp-smart-editor'),
                'Use viewer'=>__('Use viewer','wp-smart-editor'),
                'Extensions to open with viewer'=>__('Extensions to open with viewer','wp-smart-editor'),
                'GA download tracking'=>__('GA download tracking','wp-smart-editor'),
                'Single user restriction'=>__('Single user restriction','wp-smart-editor'),
                'Use WYSIWYG editor'=>__('Use WYSIWYG editor','wp-smart-editor'),
                'Load the plugin on frontend'=>__('Load the plugin on frontend','wp-smart-editor'),
                'Category owner'=>__('Category owner','wp-smart-editor'),
                'Search page'=>__('Search page','wp-smart-editor'),
                'Plain text search'=>__('Plain text search','wp-smart-editor'),
                'Are you sure'=>__('Are you sure','wp-smart-editor'),
                'Delete'=>__('Delete','wp-smart-editor'),
                'Edit'=>__('Edit','wp-smart-editor'),
                'Your browser does not support HTML5 file uploads'=>__('Your browser does not support HTML5 file uploads','wp-smart-editor'),
                'Too many files'=>__('Too many files','wp-smart-editor'),
                'is too large'=>__('is too large','wp-smart-editor'),
                'Only images are allowed'=>__('Only images are allowed','wp-smart-editor'),
                'Do you want to delete &quot;'=>__('Do you want to delete &quot;','wp-smart-editor'),
                'Select files'=>__('Select files','wp-smart-editor'),
                'Image parameters'=>__('Image parameters','wp-smart-editor'),
                'Cancel'=>__('Cancel','wp-smart-editor'),
                'Ok'=>__('Ok','wp-smart-editor'),
                'Confirm'=>__('Confirm','wp-smart-editor'),
                'Save'=>__('Save','wp-smart-editor'),
                'close_categories' => 0,
                'show_file_import' => 0,
                'add_remote_file' => 0,
                'Are you sure restore file'=>__('Are you sure you want to restore the file: ','wp-smart-editor'),
                'Are you sure remove version'=>__('Are you sure you want to definitively remove this file version','wp-smart-editor'),
        ));

if (isset($_GET['noheader'])){
    global $hook_suffix;
    _wp_admin_html_begin();
    do_action( 'admin_enqueue_scripts', $hook_suffix );
    do_action( "admin_print_scripts-$hook_suffix" );
    do_action( 'admin_print_scripts' );
}

$alone = '';
?>
<script type="text/javascript">
    ajaxurl = "<?php echo Application::getInstance('wpfd')->getAjaxUrl(); ?>";
    dir = "<?php echo Application::getInstance('wpfd')->getBaseUrl(); ?>";
<?php if(Utilities::getInput('caninsert','GET','bool')): ?>
    gcaninsert=true;
    <?php $alone = 'wpfdalone wp-core-ui '; ?>
<?php else: ?>
    gcaninsert=false;
<?php endif; ?>

    var Wpfd = {}; Wpfd.maxfilesize = <?php echo apply_filters('wpfd_max_file_size', 300); ?>;
    if(typeof(addLoadEvent)==='undefined'){addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};};
</script>
<style>
    <?php if(Utilities::getInput('caninsert','GET','bool')): ?>
    html.wp-toolbar {padding-top: 0 !important}
    <?php endif; ?>
</style>
<div id="mybootstrap" class="<?php echo $alone; ?>">

        <div id="mycategories" class="wpfd-column" style="display: none">

            <div class="nested dd">
                <ol id="categorieslist" class="dd-list nav bs-docs-sidenav2 ">
                    <?php $content = '';
                    if(!empty($this->categories)){
                        $previouslevel = 1;
                        for ($index = 0; $index < count($this->categories); $index++) {
                            if($index+1!=count($this->categories)){
                                $nextlevel = $this->categories[$index+1]->level;
                            }else{
                                $nextlevel = 0;
                            }
                            $content .= openItem($this->categories[$index],$index);
                            if($nextlevel>$this->categories[$index]->level){
                                $content .= openlist($this->categories[$index]);
                            }elseif($nextlevel==$this->categories[$index]->level){
                                $content .= closeItem($this->categories[$index]);
                            }else{
                                $c = '';
                                $c .= closeItem($this->categories[$index]);
                                $c .= closeList($this->categories[$index]);
                                $content .= str_repeat($c,$this->categories[$index]->level-$nextlevel);
                            }
                            $previouslevel = $this->categories[$index]->level;
                        }
                    }
                    echo $content;
                    ?>
                </ol>
                <input type="hidden" id="categoryToken" name="" />
            </div>
        </div>

        <div id="pwrapper" class="wpfd-column">
            <div id="wpreview">
                <div class="wpfd-btn-toolbar" id="wpfd-toolbar">
                    <div class="btn-wrapper">
                        <button onclick="Wpfd.submitbutton('files.delete')" class="btn btn-small" id="wpfd-delete">
                            <span class="icon-trash"></span>
                            <?php _e('Delete files','wp-smart-editor'); ?></button>
                    </div>
                    <div class="btn-wrapper">
                        <button onclick="Wpfd.submitbutton('files.uncheck')" class="btn btn-small" id="wpfd-uncheck">
                            <span class="icon-remove"></span>
                            <?php _e('Uncheck','wp-smart-editor'); ?></button>
                    </div>
                </div>
                <div id="preview" class="<?php if (current_user_can('wpfd_edit_category') || current_user_can('wpfd_edit_own_category')) echo 'has-wpfd'; else echo 'no-wpfd';?>"></div>
            </div>
            <input type="hidden" name="id_category" value="" />

            <?php if (!isset($_COOKIE['WPFD_hide_upgrade'])) : ?>
                <div id="updateGroup">
                    <div id="updateInfo">
                        <p style="text-transform: uppercase; font-weight: bold; font-size: 16px"><?php _e('WP File Download Full Version', 'wp-smart-editor') ?></p>
                        <p id="updateDesc"><?php _e('Save time using WP File Download full version to manage files categories, file access, search engine, themes and more...', 'wp-smart-editor') ?></p>
                    </div>
                    <button type="button" class="updateHideBtn"><i class="dashicons dashicons-dismiss" ></i></button>
                    <a class="updateBtn" href="https://www.joomunited.com/wordpress-products/wp-file-download" target="_blank" title="<?php _e('WP File Download Full Version', 'wp-smart-editor') ?>"><?php _e('More Information','wp-smart-editor') ?></a>
                    <a class="updateBtn updateHideTxt" href="#" onclick="return false" title="<?php _e('Hide','wp-smart-editor') ?>"><?php _e('Close Notification','wp-smart-editor') ?></a>
                </div>
            <?php endif; ?>
        </div>


    <?php if (current_user_can('wpfd_edit_category') || current_user_can('wpfd_edit_own_category') || Utilities::getInput('caninsert', 'GET', 'bool')) { ?>
    <div id="rightcol" class="wpfd-column">



        <?php if(Utilities::getInput('caninsert', 'GET', 'bool')): ?>
            <a id="insertfile" class="button button-primary button-big" style="display: none;" href="#" onclick="if (window.parent) insertFile();"><?php _e('Insert this file','wp-smart-editor'); ?></a>
        <?php endif; ?>

        <div class="fileblock" style="display: none;">

            <div class="well">
                <h4><?php _e('Parameters','wp-smart-editor'); ?></h4>
                <div id="fileparams">

                </div>
            </div>
            <div id="fileversion">
                <div class="well">
                    <h4><?php _e('Send a new version','wp-smart-editor'); ?></h4>
                    <div id="versions_content"></div>
                        <div id="dropbox_version">
                            <div class="upload">
                                <span class="message"><?php _e('Drop files here to upload','wp-smart-editor'); ?></span>
                                <input class="hide" type="file" id="upload_input_version">
                                <a href="" id="upload_button_version" class="button button-primary button-big">
                                    <?php _e('Select files','wp-smart-editor'); ?>
                                </a>
                            </div>
                            <div class="progress progress-striped active hide">
                                <div class="bar" style="width: 0%;"></div>
                            </div>
                        </div>
                    <div class="clr"></div>
                </div>
            </div>
        </div>

    </div>
    <?php } ?>
</div>


<?php
function openItem($category,$key){
    $item = '<li class="dd-item dd3-item '.($key?'':'active').'" data-id-category="'.$category->term_id.'">
        <div class="dd-handle dd3-handle"><i class="material-icons wpfd-folder">folder</i></div>
        <div class="dd-content dd3-content">';
        $item .= '<a href="" class="t">'.apply_filters("wpfdAddonShowCategoryCloud", null, $category->term_id).
                apply_filters("wpfdAddonShowCategoryDropbox", null, $category->term_id).
            '<span class="title">'.$category->name.'</span> </a> </div>';
        return $item;
}

function closeItem($category){
    return '</li>';
}

function itemContent($category){
    $item = '<div class="dd-handle dd3-handle"><i class="material-icons wpfd-folder">folder</i></div>
    <div class="dd-content dd3-content"
        <i class="icon-chevron-right"></i>';
        if (current_user_can('wpfd_edit_category') || current_user_can('wpfd_edit_own_category')) {
            $item .= '<a class="edit"><i class="icon-edit"></i></a>';
        }
        $item .= '<a href="" class="t"> <span class="title">'.$category->name.'</span> </a>
    </div>';

    return $item;
}

function openlist($category){
    return '<ol class="dd-list">';
}

function closelist($category){
    return '</ol>';
}
?>