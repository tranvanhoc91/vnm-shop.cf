<?php
/**
 * Plugin Name: WP Smart Editor
 * Plugin URI: https://www.joomunited.com/wordpress-products/wp-smart-editor
 * Description: Smart and powerful tool for editing.
 * Version: 1.3.0
 * Tested up to: 4.8.1
 * Author: JoomUnited
 * Author URI: https://www.joomunited.com
 * License: GPL2
 * Text Domain: wp-smart-editor
 * Domain Path: /languages
 */

/**
 * @copyright 2014  Joomunited  ( email : contact _at_ joomunited.com )
 *
 *  Original development of this plugin was kindly funded by Joomunited
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined('ABSPATH') or die('No direct access allowed!');

//Check plugin requirements
if (version_compare(PHP_VERSION, '5.3', '<')) {
    if( !function_exists('wpse_disable_plugin') ){
        function wpse_disable_plugin(){
            if ( current_user_can('activate_plugins') && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
                deactivate_plugins( __FILE__ );
                unset( $_GET['activate'] );
            }
        }
    }

    if( !function_exists('wpse_show_error') ){
        function wpse_show_error(){
            echo '<div class="error"><p><strong>WP Smart Editor</strong> need at least PHP 5.3 version, please update php before installing the plugin.</p></div>';
        }
    }

    //Add actions
    add_action( 'admin_init', 'wpse_disable_plugin' );
    add_action( 'admin_notices', 'wpse_show_error' );

    //Do not load anything more
    return;
}
if (!defined('WPSE_PLUGIN_FILE')) {
    define('WPSE_PLUGIN_FILE', __FILE__);
}
if (!defined('WPSE_LANG_DIR')) {
    define('WPSE_LANG_DIR', plugin_dir_path(__FILE__).'languages/');
}

require_once (plugin_dir_path(__FILE__).'install.php');
require_once (plugin_dir_path(__FILE__).'inc/wpse-main.php');
$wpse_main = new wpse_main();

// Load jutranslation helper
include_once('jutranslation' . DIRECTORY_SEPARATOR . 'jutranslation.php');
call_user_func( '\Joomunited\WPSE\Jutranslation\Jutranslation::init' , __FILE__, 'wp-smart-editor', 'WP Smart Editor', 'wp-smart-editor', 'languages' . DIRECTORY_SEPARATOR . 'wp-smart-editor-en_US.mo');