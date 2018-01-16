<?php
/**
 * WP Smart Editor
 *
 * @package WP Smart Editor
 * @author Joomunited
 * @version 1.0
 */
defined('ABSPATH') or die('No direct access allowed!');

register_activation_hook(WPSE_PLUGIN_FILE, 'wpse_plugin_activated');        // Register hook when plugin is activated
register_deactivation_hook(WPSE_PLUGIN_FILE, 'wpse_plugin_deactivated');    // Register deactivation hook
if (is_admin()) {
    add_action('init', 'wpse_custom_post_type');                                // Setup custom post type
    add_action('admin_init', 'add_wpse_caps');                                  // Add capabilities
    add_action('admin_init', 'wpse_plugin_activate_extra_plugins');             // Active extra plugin at 1st install
    add_filter('post_updated_messages', 'wpse_profiles_msg');                   // Change post update messages
    add_action('save_post_wpse_profiles', 'wpse_save_post_data');               // Save our profiles data
}

// Activation hook
function wpse_plugin_activated() {
    if(is_multisite()){
        $blogs = get_sites();
        foreach ($blogs as $blog){
            switch_to_blog($blog->blog_id);
                wpse_init_plugin();
            restore_current_blog();
        }
    }else{
        wpse_init_plugin();
    }
}

// Deactivation hook
function wpse_plugin_deactivated() {

}

// Active extra plugins
function wpse_plugin_activate_extra_plugins() {
    if(get_option('wpse_auto_active_extra_plugins') == 'yes') {
        wpse_active_plugin('wp-file-download');
        wpse_active_plugin('wp-table-manager');

        update_option('wpse_auto_active_extra_plugins', 'no') ;
    }
}

// Pre-active function
function wpse_init_plugin() {
    wpse_install_plugin('wp-file-download');
    wpse_install_plugin('wp-table-manager');

    // Set value when plugin install first time
    if (get_option('wpse_auto_active_extra_plugins') === false || get_option('wpse_auto_active_extra_plugins') == 'no') {
        update_option('wpse_auto_active_extra_plugins', 'yes');
    }

    // Set default config settings
    if (get_option('wpse_config') == false) {
        update_option('wpse_config', wpse_main::$default_config);
    }

    // Set default custom styles
    if (get_option('wpse_custom_styles') === false) {
        update_option('wpse_custom_styles', wpse_main::$default_custom_styles);
    }

    // Set default saved wpse-buttons data
    if (get_option('wpse_button_styles') === false) {
        update_option('wpse_button_styles', wpse_main::$default_wpse_buttons);
    }

    // Create default profile
    $args = array('post_type' => 'wpse_profiles');
    $loop = new WP_Query( $args );

    // Check if WPSE profiles already exist
    if ($loop->have_posts()) {
	    // Get all exist WPSE profiles
	    while ( $loop->have_posts() ) : $loop->the_post();
		    $postid        = get_the_ID();
		    $saved_buttons = get_post_meta( $postid, 'saved_buttons', true );
		    $no_htmltemplate  = true;
		    $no_tooltip    = true;
		    // Loop through them to find old profiles (do not have new buttons yet)
		    foreach ( $saved_buttons as $toolbar => $buttons ) {
			    if ( strpos( $buttons, 'htmltemplate' ) !== false) {
				    $no_htmltemplate = false;
			    }
			    if ( strpos( $buttons, 'wpsetooltips' ) !== false) {
				    $no_tooltip = false;
			    }
		    }
		    // If this is old profiles, we will add the new buttons at the end of second toolbar
		    if ( $no_htmltemplate && $no_tooltip ) {
			    $saved_buttons['toolbar2'] .= ' htmltemplate wpsetooltips';
			    update_post_meta( $postid, 'saved_buttons', $saved_buttons );

			    // Add new settings with default value
			    $post_types_list = get_post_types(array('public' => true));
			    unset($post_types_list['wpfd_file']);
			    update_post_meta($postid, 'post_types_active', $post_types_list);

			    $devices_list = array('desktop', 'tablet', 'mobile');
			    update_post_meta($postid, 'device_active', $devices_list);
		    } elseif (!$no_htmltemplate && $no_tooltip) {
			    $saved_buttons['toolbar2'] .= ' wpsetooltips';
			    update_post_meta( $postid, 'saved_buttons', $saved_buttons );

			    // Add new settings with default value
			    $post_types_list = get_post_types(array('public' => true));
			    unset($post_types_list['wpfd_file']);
			    update_post_meta($postid, 'post_types_active', $post_types_list);

			    $devices_list = array('desktop', 'tablet', 'mobile');
			    update_post_meta($postid, 'device_active', $devices_list);
		    }
	    endwhile;
    }
	// If WPSE profiles not exist we will create a default one
	if (!$loop->have_posts()) {
		$post_types_list = get_post_types(array('public' => true));
		unset($post_types_list['wpfd_file']);
		$devices_list = array('desktop', 'tablet', 'mobile');

        $post_arr = array(
            'post_title' => 'Default',
            'post_type' => 'wpse_profiles',
            'post_status' => 'publish',
            'meta_input' => array(
                'saved_buttons' => wpse_main::$default_buttons_array,
                'active_extra_btns' => wpse_main::$default_extra_btns,
                'roles_access' => wpse_main::$default_roles_access,
                'users_access' => array(),
	            'post_types_active' => $post_types_list,
	            'device_active' => $devices_list,
            )
        );
        wp_insert_post($post_arr, true);
    }
}

// Install extra plugins for the first time
function wpse_install_plugin($plugin) {
    require_once (ABSPATH.'wp-admin/includes/plugin.php');
    WP_Filesystem();

    $all_plugins = get_plugins();
    // Check if Full version is installed or not. if installed, activate it
    if (array_key_exists($plugin.'/'.$plugin.'.php', $all_plugins)) {
        if (!is_plugin_active($plugin.'/'.$plugin.'.php')) {
            activate_plugin($plugin.'/'.$plugin.'.php');
        }
    } else { // If not, installed light version
        if (!file_exists(plugin_dir_path(dirname(__FILE__)) . $plugin.'-light/'.$plugin.'-light.php')) {
            if (!file_exists(plugin_dir_path(dirname(__FILE__)) . $plugin.'-light/')) {
                if (!wp_mkdir_p(plugin_dir_path(dirname(__FILE__)) . $plugin.'-light/')) {
                    die(__('Something went wrong, plugin cannot activate correctly!', 'wp-smart-editor'));
                }
            }
            if (!copy_dir(plugin_dir_path(__FILE__) . 'extensions/'.$plugin.'-light/', plugin_dir_path(dirname(__FILE__)) . $plugin.'-light/')) {
                die(__('Something went wrong, plugin cannot activate correctly!', 'wp-smart-editor'));
            }

        } else { // If light version is installed, activate it
            if (!is_plugin_active($plugin.'-light/'.$plugin.'-light.php')) {
                activate_plugin($plugin.'-light/'.$plugin.'-light.php');
            }
        }
    }
}

// Activate extra plugins for the first-time
function wpse_active_plugin($plugin) {
    $all_plugins = get_plugins();
    // Check if Full version is installed or not. if installed, activate it
    if (array_key_exists($plugin.'/'.$plugin.'.php', $all_plugins)) {
        if (!is_plugin_active($plugin.'/'.$plugin.'.php')) {
            activate_plugin($plugin.'/'.$plugin.'.php');
        }
    } else { // If not, activate light version
        if (!is_plugin_active($plugin.'-light/'.$plugin.'-light.php')) {
            activate_plugin($plugin.'-light/'.$plugin.'-light.php');
        }
    }
}

// Register new custom post type
function wpse_custom_post_type() {
    $labels = array(
        'name' => __('Editor Profiles', 'wp-smart-editor'),                     //Profiles title
        'singular_name' => __('WPSE Profiles', 'wp-smart-editor'),
        'add_new' => __('New Profile', 'wp-smart-editor'),                      //New profile menu title
        'add_new_item' => __('Add New WPSE Profile', 'wp-smart-editor'),        //New profile title
        'edit_item' => __('Edit WPSE Profile', 'wp-smart-editor'),              //Edit profile title
        'all_items' => __('Editor Profiles', 'wp-smart-editor'),                //All profiles menu title
        'view_item' => __('View WPSE Profile', 'wp-smart-editor'),
        'search_items' => __('Search WPSE Profiles', 'wp-smart-editor'),        //Search button title
        'not_found' => __('No WPSE profiles found', 'wp-smart-editor'),
        'not_found_in_trash' => __('No WPSE found in trash', 'wp-smart-editor'),
        'parent_item_colon' => '',
        'menu_name' => __('WP Smart Editor', 'wp-smart-editor')                 //Menu title
    );
    register_post_type('wpse_profiles', array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'menu_position' => 8,
        'supports' => array('title', 'author'),
        'menu_icon' => 'dashicons-editor-alignleft',
        'capabilities' => array(
            'edit_posts' => 'edit_wpse_profiles',
            'edit_others_posts' => 'edit_others_wpse_profiles',
            'publish_posts' => 'publish_wpse_profiles',
            'read' => 'read_wpse_profile',
            'read_private_posts' => 'read_private_wpse_profiles',
            'delete_posts' => 'delete_wpse_profiles',
            'delete_others_posts' => 'delete_others_wpse_profiles',
            'create_posts' => 'create_wpse_profiles',
        ),
        'map_meta_cap' => true
    ));
}

// Add capabilities
function add_wpse_caps() {
    global $wp_roles;

    $wp_roles->add_cap('administrator', 'edit_wpse_profiles');
    $wp_roles->add_cap('administrator', 'edit_others_wpse_profiles');
    $wp_roles->add_cap('administrator', 'create_wpse_profiles');
    $wp_roles->add_cap('administrator', 'publish_wpse_profiles');
    $wp_roles->add_cap('administrator', 'delete_wpse_profiles');
    $wp_roles->add_cap('administrator', 'delete_others_wpse_profiles');
    $wp_roles->add_cap('administrator', 'read_wpse_profile');
    $wp_roles->add_cap('administrator', 'read_private_wpse_profiles');

    $wp_roles->add_cap('editor', 'read_wpse_profile');
    $wp_roles->add_cap('editor', 'read_private_wpse_profiles');
    $wp_roles->add_cap('editor', 'edit_wpse_profiles');

    $wp_roles->add_cap('author', 'edit_wpse_profiles');
    $wp_roles->add_cap('author', 'read_wpse_profile');
    $wp_roles->add_cap('author', 'read_private_wpse_profiles');

    $wp_roles->add_cap('contributor', 'read_wpse_profile');
    $wp_roles->add_cap('contributor', 'read_private_wpse_profiles');
}

//Change the update message display
function wpse_profiles_msg($msg) {
    $msg['wpse_profiles'] = array(
        1 => __('WP Smart Editor profiles updated.', 'wp-smart-editor')
    );

    return $msg;
}

// Method to save data from profiles
function wpse_save_post_data($post_id) {
    // Check nonce field
    if (!isset($_POST['wpse_nonce_field'])) {
        return $post_id;
    }
    // Verify nonce
    if (!wp_verify_nonce($_POST['wpse_nonce_field'], 'wpse_nonce')) {
        return $post_id;
    }

    // Save buttons to database
    if ($_POST['post_type'] == 'wpse_profiles' && current_user_can('edit_post', $post_id)) {
        if (isset($_POST['get_list_buttons']) && $_POST['get_list_buttons'] != '') {
            $buttons_submitted = $_POST['get_list_buttons'];
            $saved_buttons_array = array();
            // Get each toolbars row
            $exploded_buttons = explode('*', $buttons_submitted);

            // Make the toolbars row string to array
            foreach ($exploded_buttons as $rows) {
                if ($rows != '') {
                    $each_rows = explode(':', $rows);
                    $each_rows = str_replace(',', ' ', $each_rows);

                    // Save them to the last array
                    $saved_buttons_array[$each_rows[0]] = $each_rows[1];
                }
            }

            // Update the post meta data
            update_post_meta($post_id, 'saved_buttons', $saved_buttons_array);
        }

        // Save other data
        $extra_btns = array();
        if (isset($_POST['active_wpfdl'])) {
            $extra_btns['active_wpfdl'] = 1;
        } else {
            $extra_btns['active_wpfdl'] = 0;
        }
        if (isset($_POST['active_wptml'])) {
            $extra_btns['active_wptml'] = 1;
        } else {
            $extra_btns['active_wptml'] = 0;
        }
        update_post_meta($post_id, 'active_extra_btns', $extra_btns);

        // Save users permission
        $wpse_users = array();
        $wpse_roles = array();
        if (isset($_POST['wpse-users-access-list'])) {
            $wpse_users_string = trim($_POST['wpse-users-access-list']);
            $wpse_users = explode(' ', $wpse_users_string);
        }
        if (isset($_POST['wpse-roles'])) {
            $wpse_roles = $_POST['wpse-roles'];
        }
        update_post_meta($post_id, 'users_access', $wpse_users);
        update_post_meta($post_id, 'roles_access', $wpse_roles);

        // Save post types permission
	    $post_type_list = array();
	    if (isset($_POST['wpse-post-type'])) {
	    	$post_type_list = $_POST['wpse-post-type'];
	    }
	    update_post_meta($post_id, 'post_types_active', $post_type_list);

	    // Save device permission
	    $devices_list = array();
	    if (isset($_POST['wpse-device'])) {
	    	$devices_list = $_POST['wpse-device'];
	    }
	    update_post_meta($post_id, 'device_active', $devices_list);

        return $post_id;
    }
}
