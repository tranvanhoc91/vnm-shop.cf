<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Model;
use Joomunited\WPFramework\v1_0_2\Utilities;
defined( 'ABSPATH' ) || die();

$app = Application::getInstance('wpfd');

if (!defined('WPSE_LANG_DIR')) {
	define('WPSE_LANG_DIR', WP_PLUGIN_DIR . '/wp-smart-editor/languages/');
}
load_plugin_textdomain( 'wp-smart-editor', null, WPSE_LANG_DIR);

add_action('init', 'wpfd_session_start', 1);
function wpfd_session_start() {
    if ( ! session_id() ) {
        @session_start();
    }
}

add_action('wp_ajax_nopriv_wpfd', 'wpfd_ajax');
add_action('wp_ajax_wpfd', 'wpfd_ajax' );
add_action( 'init', 'wpfd_register_post_type' );
add_filter('woocommerce_prevent_admin_access', 'wpfd_disable_woo_login', 10, 1);
add_filter('posts_where', 'wpfd_files_query', 100, 2);
add_action( 'media_buttons_context', 'wpfd_button');

add_shortcode( 'wpfd_search', 'wpfd_search_shortcode' );
function wpfd_ajax(){
    $application = Application::getInstance('wpfd');
    require_once $application->getPath().DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'wpfdBase.php';
    $application->execute('file.download');
}

/**
 * Search query
 * @param $where
 * @param $ob
 * @return string
 */
function wpfd_files_query($where, $ob) {

    global $wpdb;
    if ($ob->get('post_type') == 'wpfd_file') {
        $where .= " AND ".$wpdb->prefix."posts.post_date <= '" . current_time( 'mysql')."'";
    }
    return $where;
}

/**
 * Register post type
 */
function wpfd_register_post_type() {
    $labels = array(
        'label' => __( 'WP File Download', 'wp-smart-editor' ),
        'rewrite' => array( 'slug' => 'wp-file-download' ),
        'menu_name'         => __( 'WP File Download', 'wp-smart-editor' ),
        'hierarchical' => true,
        'show_in_nav_menus' => true,
        'show_ui' => false
    );

    register_taxonomy('wpfd-category', 'wpfd_file',$labels);
    $labels = array(
        'name'                       => _x( 'Tags', 'wp-smart-editor' ),
        'singular_name'              => _x( 'Tag', 'wp-smart-editor' ),
        'search_items'               => __( 'Search Tags' , 'wp-smart-editor'),
        'popular_items'              => __( 'Popular Tags' , 'wp-smart-editor'),
        'all_items'                  => __( 'All Tags', 'wp-smart-editor' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Tag', 'wp-smart-editor' ),
        'update_item'                => __( 'Update Tag', 'wp-smart-editor' ),
        'add_new_item'               => __( 'Add New Tag', 'wp-smart-editor' ),
        'new_item_name'              => __( 'New Tag Name', 'wp-smart-editor' ),
        'separate_items_with_commas' => __( 'Separate tags with commas', 'wp-smart-editor' ),
        'add_or_remove_items'        => __( 'Add or remove tags', 'wp-smart-editor' ),
        'choose_from_most_used'      => __( 'Choose from the most used tags', 'wp-smart-editor' ),
        'not_found'                  => __( 'No tags found.', 'wp-smart-editor' ),
        'menu_name'                  => __( 'Tags', 'wp-smart-editor' ),
    );

    $args = array(
        'public' => false,
        'rewrite' => false,
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => false,
        'show_admin_column'     => false,
        'query_var'             => false,
    );

    register_taxonomy( 'wpfd-tag', 'wpfd_file', $args );
  register_post_type( 'wpfd_file',
    array(
      'labels' => array(
        'name' => __( 'Files','wp-smart-editor' ),
        'singular_name' => __( 'File','wp-smart-editor' )
      ),
      'public' => true,
        'show_ui'=> false,
        'show_in_nav_menu' => false,
        'exclude_from_search' => true,
      'taxonomies' => array('wpfd-category'),
      'has_archive' => false,
    )
  );
}

/**
 * Disable woocommerce login when downloading a file
 * @param $bool
 * @return bool
 */
function wpfd_disable_woo_login($bool) {
    return false;
}



/**
 * Display category
 */
function wpfd_detail_category() {

    $term = get_queried_object();
    if ($term->taxonomy != 'wpfd-category' ) return;

    wp_enqueue_style('wpfd-front', plugins_url( 'app/site/assets/css/front.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);

    $application = Application::getInstance('wpfd');
    require_once $application->getPath().DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'wpfdBase.php';

    $modelFiles = Model::getInstance('files');
    $modelCategories = Model::getInstance('categories');
    $modelCategory = Model::getInstance('category');

    $category = $modelCategory->getCategory($term->term_id);

    $ordering = Utilities::getInput('orderCol','GET','none') != null ? Utilities::getInput('orderCol','GET','none') : $category->ordering;
    $orderingdir = Utilities::getInput('orderDir','GET','none') != null ? Utilities::getInput('orderDir','GET','none') : $category->orderingdir;

    $files = $modelFiles->getFiles($term->term_id, $ordering, $orderingdir);
    $categories = $modelCategories->getCategories($term->term_id);

    $themename = $category->params['theme'];
    $params = $category->params ;


    $themefile = dirname(__FILE__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'wpfd-'.strtolower($themename).DIRECTORY_SEPARATOR.'theme.php';
    if(file_exists($themefile)){
        include_once $themefile;
    }

    $class = 'wpfdTheme'.ucfirst($themename);
    $theme = new $class();

    $options =  array('files' => $files,'category'=>$category,'categories'=>$categories,'params'=>$params);

    echo $theme->showCategory($options);
}

/**
 * View file
 */
function wpfd_file_viewer() {

    $post_type = get_query_var('post_type'); 
    if($post_type !='wpfd_file' ) return;

    wp_enqueue_style('wpfd-front', plugins_url( 'app/site/assets/css/front.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);

        $modelFile = Model::getInstance('file');
        $id = get_the_ID();
        $catid = Utilities::getInt('catid');             
        $ext  =   Utilities::getInput('ext','GET','string');             
        $mediaType =   Utilities::getInput('type','GET','string');  
        
        $app = Application::getInstance('wpfd') ;
        $downloadLink= $app->getAjaxUrl().'&task=file.download&wpfd_file_id='.$id.'&wpfd_category_id='.$catid.'&preview=1';                             
        $mineType = wpfdHelperFile::mime_type($ext);
        wp_enqueue_script('jquery');
        wp_enqueue_style('wpfd-mediaelementplayer', plugins_url( 'app/site/assets/css/mediaelementplayer.min.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_enqueue_script('wpfd-mediaelementplayer', plugins_url( 'app/site/assets/js/mediaelement-and-player.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
      
     
    $themefile = dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'frontviewer'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.'default.php';
    if(file_exists($themefile)){
        include_once $themefile;
    }

}

/**
 * search assets
 */
function wpfd_assets() {
       
    wp_enqueue_script('jquery');   
    wp_enqueue_style('jquery-ui-1.9.2',plugins_url('app/admin/assets/css/ui-lightness/jquery-ui-1.9.2.custom.min.css',WPFDL_PLUGIN_FILE));
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_script('jquery-ui-1.11.4',plugins_url('app/admin/assets/js/jquery-ui-1.11.4.custom.min.js',WPFDL_PLUGIN_FILE));
    wp_enqueue_script('wpfd-colorbox',plugins_url('app/site/assets/js/jquery.colorbox-min.js',WPFDL_PLUGIN_FILE));
    wp_enqueue_script('wpfd-colorbox-init', plugins_url( 'app/site/assets/js/colorbox.init.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
    wp_enqueue_script('wpfd-videojs', plugins_url( 'app/site/assets/js/video.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
    wp_localize_script('wpfd-colorbox','wpfdcolorbox',array('ajaxurl' => Application::getInstance('wpfd')->getAjaxUrl()));
    
    wp_enqueue_style('wpfd-videojs', plugins_url( 'app/site/assets/css/video-js.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
    wp_enqueue_style('wpfd-colorbox', plugins_url( 'app/site/assets/css/colorbox.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
    wp_enqueue_style('wpfd-viewer', plugins_url( 'app/site/assets/css/viewer.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);

}


/**
 * Search access
 */
function wpfd_assets_search() {
      
    wp_enqueue_style('wpfd-jquery-tagit',plugins_url('app/admin/assets/css/jquery.tagit.css',WPFDL_PLUGIN_FILE));
    wp_enqueue_style('wpfd-datetimepicker', plugins_url( 'app/site/assets/css/jquery.datetimepicker.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);

    wp_enqueue_script('wpfd-jquery-tagit',plugins_url('app/admin/assets/js/jquery.tagit.js',WPFDL_PLUGIN_FILE));
    wp_enqueue_script('wpfd-datetimepicker', plugins_url( 'app/site/assets/js/jquery.datetimepicker.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
}

/**
 * Display insert wpfd button
 * @param $context
 * @return string
 */
function wpfd_button($context){

    $application = Application::getInstance('wpfd'); 
    $modelConfig = Model::getInstance('config');
    $config = $modelConfig->getGlobalConfig();
    if ($config['enablewpfd'] == 1) {
        wp_enqueue_style('wpfd-modal',plugins_url('app/admin/assets/css/leanmodal.css',WPFDL_PLUGIN_FILE));
        wp_enqueue_script('wpfd-modal',plugins_url('app/admin/assets/js/jquery.leanModal.min.js',WPFDL_PLUGIN_FILE));
        wp_enqueue_script('wpfd-modal-init',plugins_url('app/site/assets/js/leanmodal.init.js',WPFDL_PLUGIN_FILE));
        wp_localize_script('wpfd-modal-init','wpfdmodalvars',array('adminurl' => admin_url()));
        wp_enqueue_style('wpfd-viewer', plugins_url( 'app/site/assets/css/viewer.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);

        $context .= "<a href='#wpfdmodal' class='button wpfdlaunch' id='wpfdlaunch' title='WP File Download'><span class='dashicons dashicons-download' style='line-height: inherit;'></span> ".__('WP File Download', 'wp-smart-editor')."</a>";

    }

    return $context;
}