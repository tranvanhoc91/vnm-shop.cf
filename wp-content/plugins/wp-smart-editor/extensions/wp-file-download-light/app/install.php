<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Filesystem;
use Joomunited\WPFramework\v1_0_2\Model;

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

register_activation_hook( WPFDL_PLUGIN_FILE, 'wpfd_install' );

register_uninstall_hook( WPFDL_PLUGIN_FILE, 'wpfd_uninstall');

if (!function_exists('wpfd_install')) {
    function wpfd_install() {
        add_option( 'wpfdl_version', '1.3.0' );

        // Set permissions for editors and admins so they can do stuff with WPFD
        $wpfd_roles = array( 'editor', 'administrator' );
        foreach ( $wpfd_roles as $role_name ) {
            $role = get_role( $role_name );
            if($role) {
                $role->add_cap('wpfd_create_category');
                $role->add_cap('wpfd_edit_category');
                $role->add_cap('wpfd_edit_own_category');
                $role->add_cap('wpfd_delete_category');
                $role->add_cap('wpfd_manage_file');
            }
        }
        wpfd_create_page();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta(wpfd_get_schema());

        if (function_exists('wpfd_deactivate_light_ver')) {
	        wpfd_deactivate_light_ver();
        }
    }
}

/**
 * Create a search page for search shortcode code
 * @return bool
 */
if (!function_exists('wpfd_create_page')) {
    function wpfd_create_page()
    {

        $option_search_page = '_wpfd_search_page_id';
        $search_page_id = get_option($option_search_page);

        if ($search_page_id > 0) {
            $page_object = get_post($search_page_id);

            if ('page' === $page_object->post_type && $page_object->ID) {
                return true;
            }
        }

        $page_data = array(
            'post_status' => 'publish',
            'post_type' => 'page',
//        'post_author'    => 1,
            'post_name' => 'wp-file-download-search',
            'post_title' => 'WP File download search',
            'post_content' => '[wpfd_search]',
            'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
        if ($page_id) {
            update_option($option_search_page, $page_id);
        }

    }
}

/**
 * Uninstall
 */
if (!function_exists('wpfd_uninstall')) {
    function wpfd_uninstall()
    {
        $app = Application::getInstance('wpfd', WPFDL_PLUGIN_FILE);
        $app->init();
        require_once $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'wpfdBase.php';
        require_once $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'config.php';
        $modelConfig = new wpfdModelConfig;
        $params = $modelConfig->getConfig();

        if (wpfdBase::loadValue($params, 'deletefiles', 0)) {

            require_once $app->getPath() . DIRECTORY_SEPARATOR . $app->getType() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'wpfdTool.php';
            $wpfdTool = new wpfdTool;
            $wpfdTool->deleteAllData();

            WP_Filesystem_Base::rmdir(wpfdBase::getFilesPath(), true);

            delete_option('wpfdl_version');
        }
    }
}

/**
 * Get Table schema.
 * @return string
 */
if (!function_exists('wpfd_get_schema')) {
    function wpfd_get_schema()
    {
        global $wpdb;

        $collate = '';

        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if (!empty($wpdb->collate)) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        return "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpfd_statistics (
          id int(11) NOT NULL AUTO_INCREMENT,
          related_id varchar(200) NOT NULL,
          type varchar(200) NOT NULL,
          date date NOT NULL DEFAULT '0000-00-00',
          count int(11) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) $collate ;
		";
    }
}