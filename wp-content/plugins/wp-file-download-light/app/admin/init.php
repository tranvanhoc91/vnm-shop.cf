<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Model;

defined('ABSPATH') || die();

if (!defined('WPSE_LANG_DIR')) {
	define('WPSE_LANG_DIR', WP_PLUGIN_DIR . '/wp-smart-editor/languages/');
}

$app = Application::getInstance('wpfd');
require_once $app->getPath() . DIRECTORY_SEPARATOR . $app->getType() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'wpfdBase.php';

if (!get_option('_wpfd_import_notice_flag', false)) {
    require_once $app->getPath() . DIRECTORY_SEPARATOR . $app->getType() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'wpfdTool.php';
    $wpfdTool = new wpfdTool;
    add_action('admin_notices', array($wpfdTool, 'wpfd_import_notice'), 3);
}

add_action('admin_menu', 'wpfd_menu');
add_action('admin_head', 'wpfd_menu_highlight');
add_action('wp_ajax_wpfd_import', array('wpfdTool', 'wpfd_import_categories'));
add_action('wp_ajax_wpfd', 'wpfd_ajax');
add_action('media_buttons_context', 'wpfd_button');
add_action('delete_term', 'wpfd_delete_term', 10, 4);

add_action('init', 'wpfd_register_post_type');
/**
 * Register post type
 */
function wpfd_register_post_type()
{

    $labels = array(
        'label' => __('WP File Download', 'wp-smart-editor'),
        'rewrite' => array('slug' => 'wp-file-download'),
        'menu_name' => __('WP File Download', 'wp-smart-editor'),
        'hierarchical' => true,
        'show_in_nav_menus' => true,
        'show_ui' => false
    );

    register_taxonomy('wpfd-category', 'wpfd_file', $labels);

    $labels = array(
        'name' => _x('Tags', 'wp-smart-editor'),
        'singular_name' => _x('Tag', 'wp-smart-editor'),
        'search_items' => __('Search Tags', 'wp-smart-editor'),
        'popular_items' => __('Popular Tags', 'wp-smart-editor'),
        'all_items' => __('All Tags', 'wp-smart-editor'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit Tag', 'wp-smart-editor'),
        'update_item' => __('Update Tag', 'wp-smart-editor'),
        'add_new_item' => __('Add New Tag', 'wp-smart-editor'),
        'new_item_name' => __('New Tag Name', 'wp-smart-editor'),
        'separate_items_with_commas' => __('Separate tags with commas', 'wp-smart-editor'),
        'add_or_remove_items' => __('Add or remove tags', 'wp-smart-editor'),
        'choose_from_most_used' => __('Choose from the most used tags', 'wp-smart-editor'),
        'not_found' => __('No tags found.', 'wp-smart-editor'),
        'menu_name' => __('Tags', 'wp-smart-editor'),
    );

    $args = array(
        'public' => false,
        'rewrite' => false,
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => false,
    );

    register_taxonomy('wpfd-tag', 'wpfd_file', $args);

    register_post_type('wpfd_file',
        array(
            'labels' => array(
                'name' => __('Files', 'wp-smart-editor'),
                'singular_name' => __('File', 'wp-smart-editor')
            ),
            'public' => true,
            'exclude_from_search'=> true,
            'publicly_queryable'=> false,
            'show_in_nav_menus'=> false,
            'show_ui'=> false,
            'taxonomies' => array('wpfd-category', 'wpfd-tag'),
            'has_archive' => false,
            'show_in_menu' => false,
            'capability_type' => 'wpfd_file',
            'map_meta_cap' => false,
            'capabilities' => array(
                'wpfd_create_category' => __('Create category', 'wp-smart-editor'),
                'wpfd_edit_category' => __('Edit category', 'wp-smart-editor'),
                'wpfd_edit_own_category' => __('Edit own category', 'wp-smart-editor'),
                'wpfd_delete_category' => __('Delete category', 'wp-smart-editor'),
                'wpfd_manage_file' => __('Access WP File Download', 'wp-smart-editor'),
            ),
        )
    );

    //force the WPFD menu box alway show on screen
    $hidden_nav_boxes = (array)get_user_option('metaboxhidden_nav-menus');

    $post_type = 'wpfd-category'; //Can also be a taxonomy slug
    $post_type_nav_box = 'add-' . $post_type;

    if (is_array($hidden_nav_boxes) && in_array($post_type_nav_box, $hidden_nav_boxes)):
        foreach ($hidden_nav_boxes as $i => $nav_box):
            if ($nav_box == $post_type_nav_box)
                unset($hidden_nav_boxes[$i]);
        endforeach;
        update_user_option(get_current_user_id(), 'metaboxhidden_nav-menus', $hidden_nav_boxes);
    endif;
    //Ensure the $wp_rewrite global is loaded
    global $wp_rewrite;
    //Call flush_rules() as a method of the $wp_rewrite object
    $wp_rewrite->flush_rules(false);

}


add_action('wp_update_nav_menu_item', 'wpfd_update_custom_nav_fields', 10, 3);
/**
 * update custom menu item
 * @param $menu_id
 * @param $menu_item_db_id
 * @param $args
 */
function wpfd_update_custom_nav_fields($menu_id, $menu_item_db_id, $args)
{

    // Check if element is properly sent
    if ($args['menu-item-db-id'] == "0" && $args['menu-item-object'] == 'wpfd-category') {

        $my_post = array(
            'ID' => $menu_item_db_id,
            'post_content' => '',
        );

        // Update the post into the database
        wp_update_post($my_post);
    }
}

/**
 * Add menus for WP File Download
 */
function wpfd_menu()
{
    $app = Application::getInstance('wpfd');
    add_menu_page('WP File Download', __('WP File Download', 'wp-smart-editor'), 'wpfd_manage_file', 'wpfd', 'wpfd_call', 'dashicons-category');
}

/**
 * Call ajax
 */
function wpfd_ajax()
{
    define('WPFD_AJAX', 'true');
    wpfd_call();
}

/**
 * Call task for controller
 * @param null $ref
 * @param string $default_task
 */
function wpfd_call($ref = null, $default_task = 'wpfd.display')
{

    $application = Application::getInstance('wpfd');

    wpfd_init();

    $application->execute($default_task);
}

/**
 * Init for plugin
 */
function wpfd_init()
{

    $application = Application::getInstance('wpfd');
    load_plugin_textdomain('wp-smart-editor', null, WPSE_LANG_DIR);

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-migrate');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-resizable');
    wp_enqueue_style('dashicons');
    wp_enqueue_script('jquery-ui-1.11.4', plugins_url('assets/js/jquery-ui-1.11.4.custom.min.js', __FILE__));
    $page = Utilities::getInput('page', 'GET', 'string');

    if ($page != 'wpfd-config') {
        wp_enqueue_script('wpfd-bootstrap', plugins_url('assets/js/bootstrap.min.js', __FILE__));
    }

    wp_enqueue_style('wpfd-bootstrap', plugins_url('assets/css/bootstrap.min.css', __FILE__));

    wp_enqueue_script('wpfd-bootstrap', plugins_url('assets/js/jquery.ui.touch-punch.min.js', __FILE__));

    wp_enqueue_style('buttons');
    wp_enqueue_style('wp-admin');
    wp_enqueue_style('colors-fresh');
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
    wp_enqueue_style('wpfd-upload', plugins_url('assets/css/upload.min.css', __FILE__));
    wp_enqueue_style('wpfd-style', plugins_url('assets/css/style.css', __FILE__));
    wp_enqueue_script('l10n');

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('jquery-filedrop', plugins_url('assets/js/jquery.filedrop.min.js', __FILE__));
    wp_enqueue_script('jquery-textselect', plugins_url('assets/js/jquery.textselect.min.js', __FILE__));
    wp_enqueue_script('jquery-nestable', plugins_url('assets/js/jquery.nestable.js', __FILE__));
    wp_enqueue_style('jquery.restable', plugins_url('assets/css/jquery.restable.css', __FILE__));
    wp_enqueue_script('jquery.restable', plugins_url('assets/js/jquery.restable.js', __FILE__));
    wp_enqueue_style('jquery-jaofiletree', plugins_url('assets/css/jaofiletree.css', __FILE__));
    wp_enqueue_script('jquery-jaofiletree', plugins_url('assets/js/jaofiletree.js', __FILE__));
    wp_enqueue_style('jquery-ui-1.9.2', plugins_url('assets/css/ui-lightness/jquery-ui-1.9.2.custom.min.css', __FILE__));

    wp_enqueue_style('jquery-tagit', plugins_url('assets/css/jquery.tagit.css', __FILE__));
    wp_enqueue_script('jquery-tagit', plugins_url('assets/js/jquery.tagit.js', __FILE__));
    wp_enqueue_script('jquery-bootbox', plugins_url('assets/js/bootbox.js', __FILE__));
    wp_enqueue_style('wpfd-gritter', plugins_url('assets/css/jquery.gritter.css', __FILE__));
    wp_enqueue_script('wpfd-gritter', plugins_url('assets/js/jquery.gritter.min.js', __FILE__));
    wp_enqueue_style('wpfd-calendar', plugins_url('assets/css/calendar-jos.css', __FILE__));
    wp_enqueue_script('wpfd-calendar', plugins_url('assets/js/calendar.js', __FILE__));
    wp_enqueue_script('wpfd-calendar-setup', plugins_url('assets/js/calendar-setup.js', __FILE__));
    wp_enqueue_script('wpfd-cookie', plugins_url('assets/js/jquery.cookie.js', __FILE__));
    wp_enqueue_script('wpfd-base64js', plugins_url('assets/js/encodingHelper.js', __FILE__));
    wp_enqueue_script('wpfd-TextEncoderLite', plugins_url('assets/js/TextEncoderLite.js', __FILE__));
    wp_enqueue_script('wpfd-main', plugins_url('assets/js/wpfd.js', __FILE__));
    wp_localize_script('wpfd-main', 'wpfd_permissions', array(
        'can_create_category' => current_user_can('wpfd_create_category'),
        'can_edit_category' => (current_user_can('wpfd_edit_category') || current_user_can('wpfd_edit_own_category')) ? true : false,
        'can_delete_category' => (current_user_can('wpfd_delete_category') || current_user_can('wpfd_edit_own_category')) ? true : false,
        'translate' => array(
            'wpfd_create_category' => __('You don\'t have permission to create new category', 'wp-smart-editor'),
            'wpfd_edit_category' => __('You don\'t have permission to edit category', 'wp-smart-editor')
        ),
    ));
    wp_localize_script('wpfd-main', 'wpfd_var', array(
        'adminurl' => admin_url('admin.php'),
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));

    if(isset($_COOKIE['wpfd_show_columns']) && is_string($_COOKIE['wpfd_show_columns'])){
        $listColumns = explode(',',$_COOKIE['wpfd_show_columns']);
    }else{
        $listColumns = array();
    }

    wp_localize_script('wpfd-main', 'wpfd_admin', array(
        'allowed' => '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,flac,m3u,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,rm,swf,vob,wmv,css,img',
        'msg_remove_file' => __('Files removed with success!','wp-smart-editor'),
        'msg_remove_files' => __('File(s) removed with success!','wp-smart-editor'),
        'msg_move_file' => __('Files moved with success!','wp-smart-editor'),
        'msg_move_files' => __('File(s) moved with success!','wp-smart-editor'),
        'msg_copy_file' => __('Files copied with success!','wp-smart-editor'),
        'msg_copy_files' => __('File(s) copied with success!','wp-smart-editor'),
        'msg_add_category' => __('Category created with success!','wp-smart-editor'),
        'msg_remove_category' => __('Category removed with success!','wp-smart-editor'),
        'msg_move_category' => __('New category order saved!','wp-smart-editor'),
        'msg_edit_category' => __('Category renamed with success!','wp-smart-editor'),
        'msg_save_category' => __('Category config saved with success!','wp-smart-editor'),
        'msg_save_file' => __('File config saved with success!','wp-smart-editor'),
        'msg_ordering_file' => __('File ordering with success!','wp-smart-editor'),
        'msg_ordering_file2' => __('File order saved with success!','wp-smart-editor'),
        'msg_upload_file' => __('New File(s) uploaded with success!','wp-smart-editor'),
        'msg_ask_delete_file' => __('Are you sure you want to delete this file?','wp-smart-editor'),
        'msg_ask_delete_files' => __('Are you sure you want to delete the files you have selected?','wp-smart-editor'),
        'listColumns' => $listColumns
    ));
    wp_enqueue_style('buttons');

    if (Utilities::getInput('noheader', 'GET', 'bool')) {
        //remove script loaded in bottom of page
        wp_dequeue_script('sitepress-scripts');
        wp_dequeue_script('wpml-tm-scripts');
    }
    wp_enqueue_style('wpfd-google-icon', '//fonts.googleapis.com/icon?family=Material+Icons');

}

/**
 * add vars for calendar
 * @return bool|string
 */
function wpfd_calendartranslation()
{
    static $jsscript = 0;

    // Guard clause, avoids unnecessary nesting
    if ($jsscript) {
        return false;
    }

    $jsscript = 1;

    $weekdays_full = array(
        __('Sunday', 'wp-smart-editor'), __('Monday', 'wp-smart-editor'), __('Tuesday', 'wp-smart-editor'), __('Wednesday', 'wp-smart-editor'), __('Thursday', 'wp-smart-editor'), __('Friday', 'wp-smart-editor'), __('Saturday', 'wp-smart-editor'), __('Sunday', 'wp-smart-editor')
    );
    $weekdays_short = array(
        __('Sun', 'wp-smart-editor'), __('Mon', 'wp-smart-editor'), __('Tue', 'wp-smart-editor'), __('Wed', 'wp-smart-editor'), __('Thu', 'wp-smart-editor'), __('Fri', 'wp-smart-editor'), __('Sat', 'wp-smart-editor'), __('Sun', 'wp-smart-editor')
    );

    $months_long = array(
        __('January', 'wp-smart-editor'), __('February', 'wp-smart-editor'), __('March', 'wp-smart-editor'), __('April', 'wp-smart-editor'), __('May', 'wp-smart-editor'), __('June', 'wp-smart-editor'),
        __('July', 'wp-smart-editor'), __('August', 'wp-smart-editor'), __('September', 'wp-smart-editor'), __('October', 'wp-smart-editor'), __('November', 'wp-smart-editor'), __('December', 'wp-smart-editor')
    );

    $months_short = array(
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    );

    // This will become an object in Javascript but define it first in PHP for readability
    $today = " " . __('Today', 'wp-smart-editor') . " ";
    $text = array(
        'INFO' => __('About the Calendar', 'wp-smart-editor'),
        'ABOUT' => "",
        'ABOUT_TIME' => "\n\n"
            . "Time selection:\n"
            . "- Click on any of the time parts to increase it\n"
            . "- or Shift-click to decrease it\n"
            . "- or click and drag for faster selection.",
        'PREV_YEAR' => __('Select to move to the previous year. Select and hold for a list of years.', 'wp-smart-editor'),
        'PREV_MONTH' => __('Select to move to the next month. Select and hold for a list of the months.', 'wp-smart-editor'),
        'GO_TODAY' => __('Go to today', 'wp-smart-editor'),
        'NEXT_MONTH' => __('Select to move to the next month. Select and hold for a list of the months.', 'wp-smart-editor'),
        'SEL_DATE' => __('Select a date.', 'wp-smart-editor'),
        'DRAG_TO_MOVE' => __('Drag to move.', 'wp-smart-editor'),
        'PART_TODAY' => $today,
        'DAY_FIRST' => __('Display %s first', 'wp-smart-editor'),
        'WEEKEND' => '0,6',
        'CLOSE' => __('Close', 'wp-smart-editor'),
        'TODAY' => __('Today', 'wp-smart-editor'),
        'TIME_PART' => __('(Shift-)Select or Drag to change the value.', 'wp-smart-editor'),
        'DEF_DATE_FORMAT' => "%Y-%m-%d",
        'TT_DATE_FORMAT' => __('%a, %b %e', 'wp-smart-editor'),
        'WK' => __('wk', 'wp-smart-editor'),
        'TIME' => __('Time:', 'wp-smart-editor')
    );


    return 'Calendar._DN = ' . json_encode($weekdays_full) . ';'
    . ' Calendar._SDN = ' . json_encode($weekdays_short) . ';'
    . ' Calendar._FD = 0;'
    . ' Calendar._MN = ' . json_encode($months_long) . ';'
    . ' Calendar._SMN = ' . json_encode($months_short) . ';'
    . ' Calendar._TT = ' . json_encode($text) . ';';
}

/**
 * Highlight tag menu
 */
function wpfd_menu_highlight()
{

    global $parent_file, $submenu_file, $post_type;
    if ($submenu_file == 'edit-tags.php?taxonomy=wpfd-tag') {
        $parent_file = 'wpfd';
    }

}

/**
 * add insert wpfd in editor
 * @param $context
 * @return string
 */
function wpfd_button($context)
{
    wp_enqueue_style('wpfd-modal', plugins_url('assets/css/leanmodal.css', __FILE__));
    wp_enqueue_script('wpfd-modal', plugins_url('assets/js/jquery.leanModal.min.js', __FILE__));
    wp_enqueue_script('wpfd-modal-init', plugins_url('assets/js/leanmodal.init.js', __FILE__));

    $context .= "<a href='#wpfdmodal' class='button wpfdlaunch' id='wpfdlaunch' title='WP File Download'><span class='dashicons dashicons-download' style='line-height: inherit;'></span> " . __('WP File Download', 'wp-smart-editor') . "</a>";

    return $context;
}

add_action('admin_enqueue_scripts', 'wpfd_heartbeat_enqueue');
add_filter('heartbeat_received', 'wpfd_heartbeat_received', 10, 2);
add_filter('vc_edit_form_enqueue_script', 'wpfd_init_vc_insert_button', 100, 1);
function wpfd_init_vc_insert_button($scripts)
{

    $scripts[] = plugins_url('assets/js/leanmodal.init.js', __FILE__);
    return $scripts;
}

// Load the heartbeat JS
function wpfd_heartbeat_enqueue($hook_suffix)
{
    // Make sure the JS part of the Heartbeat API is loaded.
    wp_enqueue_script('heartbeat');
    add_action('admin_print_footer_scripts', 'wpfd_heartbeat_footer_js');
}

// Inject our JS into the admin footer
function wpfd_heartbeat_footer_js()
{
    global $pagenow;
    ?>
    <script>
        (function ($) {
            // Hook into the heartbeat-send
            $(document).on('heartbeat-send', function (e, data) {
                data['wpfd_heartbeat'] = 'sync_process';
            });

            // Listen for the custom event "heartbeat-tick" on $(document).
            $(document).on('heartbeat-tick', function (e, data) {
                // Only proceed if our EDD data is present
                if (!data['wpfd_result'])
                    return;
            });
        }(jQuery));
    </script>
    <?php
}

// Modify the data that goes back with the heartbeat-tick
function wpfd_heartbeat_received($response, $data)
{

    // Make sure we only run our query if the edd_heartbeat key is present
    if (isset($data['wpfd_heartbeat']) && $data['wpfd_heartbeat'] == 'sync_process') {

        do_action("wpfdAddon_auto_sync");
        do_action("wpfdAddon_auto_sync_dropbox");
        // Send back the number of timestamp
        $response['wpfd_result'] = time();
    }
    return $response;
}

/**
 * action when delete a term
 * @param $term
 * @param $term_id
 * @param $taxonomy
 * @param $deleted_term
 */
function wpfd_delete_term($term, $term_id, $taxonomy, $deleted_term)
{

    if ($taxonomy == 'wpfd-tag') {
        $deleted_slug = $deleted_term->slug;

        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'wpfd_file',
            'post_status' => 'any',
        );

        $files = get_posts($args);
        if ($files) {
            foreach ($files as $file) {

                $metadata = get_post_meta($file->ID, '_wpfd_file_metadata', true);

                if (isset($metadata['file_tags'])) {

                    $tags = explode(',', $metadata['file_tags']);
                    if (in_array($deleted_slug, $tags)) {
                        $del_key = array_search($deleted_slug, $tags);
                        unset($tags[$del_key]);
                        $tags = array_values($tags);
                    }

                    $metadata['file_tags'] = implode(',', $tags);
                    update_post_meta($file->ID, '_wpfd_file_metadata', $metadata);
                }
            }

        }
    }

}

add_action('admin_print_footer_scripts', 'wpfd_calendar_enqueue', 100);
/**
 * Scripts for calendar
 */
function wpfd_calendar_enqueue()
{

    $page = Utilities::getInput('page', 'GET', 'string');
    if ($page == 'wpfd-statistics') {
        ?>
        <script type="text/javascript">
            <?php echo wpfd_calendartranslation();?>
        </script>
        <?php
    }

}

