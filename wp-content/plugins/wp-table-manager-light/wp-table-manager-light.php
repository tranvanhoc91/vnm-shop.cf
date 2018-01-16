<?php
/*
Plugin Name: WP Table Manager Light
Plugin URI: http://www.joomunited.com/wordpress-products/wp-table-manager
Description: WP Table Manager, a new way to manage tables in WordPress
Author: Joomunited
Version: 1.3.0
Tested up to: 4.8.1
Text Domain: wp-smart-editor
Domain Path: /app/languages
Author URI: http://www.joomunited.com
*/

// If we activated full version do not use this free version
if (defined('WPTM_PLUGIN_FILE')) {
	wp_die(__('Full version of WP Table Manager has been activated! Please deactivate it if you want to use free version!', 'wp-smart-editor'));
	return false;
};

//Check plugin requirements
if (version_compare(PHP_VERSION, '5.3', '<')) {
    if( !function_exists('wptm_disable_plugin') ){
        function wptm_disable_plugin(){
            if ( current_user_can('activate_plugins') && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
                deactivate_plugins( __FILE__ );
                unset( $_GET['activate'] );
            }
        }
    }

    if( !function_exists('wptm_show_error') ){
        function wptm_show_error(){
            echo '<div class="error"><p><strong>WP Table Manager</strong> need at least PHP 5.3 version, please update php before installing the plugin.</p></div>';
        }
    }

    //Add actions
    add_action( 'admin_init', 'wptm_disable_plugin' );
    add_action( 'admin_notices', 'wptm_show_error' );

    //Do not load anything more
    return;
}

use Joomunited\WPFramework\v1_0_2\Application;

//Check if the framework is installed
$frameworkInstalled = false;
if(function_exists('juLibrariesCheck')){
    $frameworkInstalled = juLibrariesCheck('1.0.2');
}else{
    if(!file_exists(WPMU_PLUGIN_DIR)){
	wp_mkdir_p(WPMU_PLUGIN_DIR);
    }
    copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'ju-libraries.php', WPMU_PLUGIN_DIR.DIRECTORY_SEPARATOR.'ju-libraries.php');
    $content = file_get_contents(WPMU_PLUGIN_DIR.DIRECTORY_SEPARATOR.'ju-libraries.php');
    $content = str_replace('JUPlugin Name', 'Plugin Name',$content,$count);
    if($count){
	file_put_contents(WPMU_PLUGIN_DIR.DIRECTORY_SEPARATOR.'ju-libraries.php', $content);
    }
  
}
if(!$frameworkInstalled){
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    WP_Filesystem();
    if(!file_exists(WPMU_PLUGIN_DIR.DIRECTORY_SEPARATOR.'ju-libraries'.DIRECTORY_SEPARATOR.'WPFramework' .DIRECTORY_SEPARATOR.'v1_0_2')){
	wp_mkdir_p(WPMU_PLUGIN_DIR.DIRECTORY_SEPARATOR.'ju-libraries'.DIRECTORY_SEPARATOR.'WPFramework' .DIRECTORY_SEPARATOR.'v1_0_2');
    }
    copy_dir(dirname(__FILE__).DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'ju-libraries'.DIRECTORY_SEPARATOR
        .'WPFramework'.DIRECTORY_SEPARATOR.'v1_0_2', WPMU_PLUGIN_DIR.DIRECTORY_SEPARATOR.'ju-libraries' .DIRECTORY_SEPARATOR.'WPFramework'.DIRECTORY_SEPARATOR.'v1_0_2'.DIRECTORY_SEPARATOR);
    include_once(WPMU_PLUGIN_DIR.DIRECTORY_SEPARATOR.'ju-libraries.php');
}

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
if (!defined('WPTML_PLUGIN_FILE')) {
    define('WPTML_PLUGIN_FILE', __FILE__);
}
if (!defined('WPTML_PLUGIN_DIR')) {
    define('WPTML_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
define( 'WP_TABLE_MANAGER_LIGHT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'WPTML_VERSION' ) ) {
	define( 'WPTML_VERSION', '1.3.0' );
}        
include_once('app'.DIRECTORY_SEPARATOR.'install.php');
include_once('app'.DIRECTORY_SEPARATOR.'autoload.php');

//Initialise the application        
$app = Application::getInstance('wptm',__FILE__);
$app->init();

if(is_admin()) {
    add_filter('mce_external_plugins', 'wptm_mce_external_plugins2' );
    if (!function_exists('wptm_mce_external_plugins2')) {
        function wptm_mce_external_plugins2($plugins) {
            $plugins['wpedittable'] = WP_TABLE_MANAGER_LIGHT_PLUGIN_URL . '/app/admin/assets/plugins/wpedittable/plugin.js';
            return $plugins;
        }
    }
    
    //config section        
    if(!defined('JU_BASE')){
        define( 'JU_BASE', 'https://www.joomunited.com/' );
    }
}
