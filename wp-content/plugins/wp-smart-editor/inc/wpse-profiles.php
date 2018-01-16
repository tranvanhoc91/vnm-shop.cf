<?php
defined('ABSPATH') or die('Restricted Access!');

// Prepare the option
$unused = 1;
$postid = get_the_ID();
$toolbar_saved = get_post_meta($postid, 'saved_buttons', true);
$toolbar_btns = $toolbar_saved ? $toolbar_saved : $this::$default_buttons_array;

$extra_btns_saved = get_post_meta($postid, 'active_extra_btns', true);
$extra_btns_saved = $extra_btns_saved ? $extra_btns_saved : $this::$default_extra_btns;

$roles_access_saved = get_post_meta($postid, 'roles_access', true);
if ($roles_access_saved == '') {
    $roles_access_saved = $this::$default_roles_access;
}

$users_access_saved = get_post_meta($postid, 'users_access', true);
$users_access_saved = $users_access_saved ? $users_access_saved : array();

$post_types_list = get_post_types(array('public' => true));
unset($post_types_list['wpfd_file']);

$post_types_saved = get_post_meta($postid, 'post_types_active', true);
$post_types_saved = $post_types_saved ? $post_types_saved : $post_types_list;

$devices_list = array('desktop', 'tablet', 'mobile');
$devices_list_saved = get_post_meta($postid, 'device_active', true);
$devices_list_saved = $devices_list_saved ? $devices_list_saved : $devices_list;

wp_nonce_field('wpse_nonce', 'wpse_nonce_field');
?>

<div id="profile-tabs">
    <ul class="tabs cyan z-depth-1">
        <li class="tab"><a href="#toolbars-tab" class="link-tab white-text waves-effect waves-light"><?php _e('Editor toolbars', 'wp-smart-editor') ?></a></li>
        <li class="tab"><a href="#users-tab" class="link-tab white-text waves-effect waves-light"><?php _e('Profile attribution', 'wp-smart-editor') ?></a></li>
        <li class="tab"><a href="#posttypes-tab" class="link-tab white-text waves-effect waves-light"><?php _e('Post types and devices', 'wp-smart-editor') ?></a></li>
    </ul>

    <!--Editor toolbars tab-->
    <div id="toolbars-tab" class="active tabs-content">
        <div class="toolbars-content">
            <h3><?php _e('Current Editor tools', 'wp-smart-editor') ?></h3>
            <div id="current-toolbars" class="toolbar-blocks">
                <!--Loop through all toolbars-->
                <?php foreach ($toolbar_btns as $toolbar => $list_btns) : ?>
                    <?php $toolbar_inused = !in_array('unused', explode(' ', $list_btns));
                        // Check if the button is not in used
                        if ( !$toolbar_inused && $unused == 1 || $toolbar == 'unused_toolbar1' && $list_btns == '') {
                            echo '</div><h3>'. __("Editors Tools Available", 'wp-smart-editor') . '</h3>
                            <div id="unused-toolbars" class="toolbar-blocks">';
                            $unused = 0;
                        }
                    ?>
                    <?php if ($toolbar && !empty($list_btns)) : ?>
                    <div class="toolbar-rows">
                    <?php
                        if (!empty($list_btns)) {
                            $list_btns = explode(' ', $list_btns);
                        }

                        //Set up buttons data to display
                        $btn_data = array(
                                    'bold' => array("class"=> 'dashicons dashicons-editor-bold', "title"=>__("Bold", 'wp-smart-editor'), "text" => ""),
                                    'italic' => array("class"=> 'dashicons dashicons-editor-italic', "title"=>__("Italic", 'wp-smart-editor'), "text" => ""),
                                    'strikethrough' => array("class"=> 'dashicons dashicons-editor-strikethrough', "title"=>__("Strikethrough", 'wp-smart-editor'), "text" => ""),
                                    'bullist' => array("class"=> 'dashicons dashicons-editor-ul', "title"=>__("Bullet List", 'wp-smart-editor'), "text" => ""),
                                    'numlist' => array("class"=> 'dashicons dashicons-editor-ol', "title"=>__("Numbered List", 'wp-smart-editor'), "text" => ""),
                                    'blockquote' => array("class"=> 'dashicons dashicons-editor-quote', "title"=>__("Blockquote", 'wp-smart-editor'), "text" => ""),
                                    'hr' => array("class"=> 'dashicons dashicons-minus', "title"=>__("Horizontal Rule", 'wp-smart-editor'), "text" => ""),
                                    'alignleft' => array("class"=> 'dashicons dashicons-editor-alignleft', "title"=>__("Align Left", 'wp-smart-editor'), "text" => ""),
                                    'aligncenter' => array("class"=> 'dashicons dashicons-editor-aligncenter', "title"=>__("Align Center", 'wp-smart-editor'), "text" => ""),
                                    'alignright' => array("class"=> 'dashicons dashicons-editor-alignright', "title"=>__("Align Right", 'wp-smart-editor'), "text" => ""),
                                    'link' => array("class"=> 'dashicons dashicons-admin-links', "title"=>__("Link", 'wp-smart-editor'), "text" => ""),
                                    'unlink' => array("class"=> 'dashicons dashicons-editor-unlink', "title"=>__("Unlink", 'wp-smart-editor'), "text" => ""),
                                    'wp_more' => array("class"=> 'dashicons dashicons-editor-insertmore', "title"=>__("More", 'wp-smart-editor'), "text" => ""),
                                    'formatselect' => array("class"=> '', "title"=>__("Format Select", 'wp-smart-editor'), "text" => "Paragraph"),
                                    'underline' => array("class"=> 'dashicons dashicons-editor-underline', "title"=>__("Underline", 'wp-smart-editor'), "text" => ""),
                                    'alignjustify' => array("class"=> 'dashicons dashicons-editor-justify', "title"=>__("Align Justify", 'wp-smart-editor'), "text" => ""),
                                    'forecolor' => array("class"=> 'dashicons dashicons-editor-textcolor', "title"=>__("Foreground Color/ Text color", 'wp-smart-editor'), "text" => ""),
                                    'pastetext' => array("class"=> 'dashicons dashicons-editor-paste-text', "title"=>__("Paste as text", 'wp-smart-editor'), "text" => ""),
                                    'removeformat' => array("class"=> 'dashicons dashicons-editor-removeformatting', "title"=>__("Remove Format", 'wp-smart-editor'), "text" => ""),
                                    'charmap' => array("class"=> 'dashicons dashicons-editor-customchar', "title"=>__("Character Map", 'wp-smart-editor'), "text" => ""),
                                    'outdent' => array("class"=> 'dashicons dashicons-editor-outdent', "title"=>__("Outdent selected paragraph", 'wp-smart-editor'), "text" => ""),
                                    'indent' => array("class"=> 'dashicons dashicons-editor-indent', "title"=>__("Indent selected paragraph", 'wp-smart-editor'), "text" => ""),
                                    'undo' => array("class"=> 'dashicons dashicons-undo', "title"=>__("Undo", 'wp-smart-editor'), "text" => ""),
                                    'redo' => array("class"=> 'dashicons dashicons-redo', "title"=>__("Redo", 'wp-smart-editor'), "text" => ""),
                                    'wp_help' => array("class"=> 'dashicons dashicons-editor-help', "title"=>__("Help", 'wp-smart-editor'), "text" => ""),
                                    'customstyles' => array("class"=> '', "title"=>__("Custom styles", 'wp-smart-editor'), "text" => "Custom styles"),
                                    'wpsebutton' => array("class"=> '', "title"=>__("Add button", 'wp-smart-editor'), "text" => "Button"),
                                    'columns' => array("class"=> 'dashicons dashicons-editor-table', "title"=>__("Add columns", 'wp-smart-editor'), "text" => ""),
                                    'fontselect' => array("class"=> '', "title"=>__("Font Select", 'wp-smart-editor'), "text" => "Font Family"),
                                    'fontsizeselect' => array("class"=> '', "title"=>__("Font Size Select", 'wp-smart-editor'), "text" => "Font Size"),
                                    'styleselect' => array("class"=> '', "title"=>__("Formats", 'wp-smart-editor'), "text" => "Formats"),
                                    'rtl' => array("class"=> 'wpseicon-rtl', "title"=>__("Text Direction Right to Left", 'wp-smart-editor'), "text" => ""),
                                    'ltr' => array("class"=> 'wpseicon-ltr', "title"=>__("Text Direction Left to Right", 'wp-smart-editor'), "text" => ""),
                                    'anchor' => array("class"=> 'dashicons dashicons-pressthis', "title"=>__("Anchor", 'wp-smart-editor'), "text" => ""),
                                    'preview' => array("class"=> 'dashicons dashicons-visibility', "title"=>__("Preview", 'wp-smart-editor'), "text" => ""),
                                    'bulletmngr' => array("class"=> 'dashicons dashicons-exerpt-view', "title"=>__("Bullet list manager", 'wp-smart-editor'), "text" => ""),
                                    'summary' => array("class"=> 'dashicons dashicons-clipboard', "title"=>__("Summary manager", 'wp-smart-editor'), "text" => ""),
                                    'print' => array("class"=> 'wpseicon-printer', "title"=>__("Print", 'wp-smart-editor'), "text" => ""),
                                    'searchreplace' => array("class"=> 'wpseicon-binoculars', "title"=>__("Search and Replace", 'wp-smart-editor'), "text" => ""),
                                    'visualblocks' => array("class"=> 'wpseicon-pilcrow', "title"=>__("Paragraph Tags", 'wp-smart-editor'), "text" => ""),
                                    'subscript' => array("class"=> 'wpseicon-subscript', "title"=>__("Subscript", 'wp-smart-editor'), "text" => ""),
                                    'superscript' => array("class"=> 'wpseicon-superscript', "title"=>__("Superscript", 'wp-smart-editor'), "text" => ""),
                                    'htmltemplate' => array("class"=> 'dashicons dashicons-media-document', "title"=>__("Template manager", 'wp-smart-editor'), "text" => ""),
                                    'wpsetooltips' => array("class"=> 'dashicons dashicons-lightbulb', "title"=>__("Add Tooltips", 'wp-smart-editor'), "text" => ""),
                                    'unused' => array("class"=> 'no-display', "title"=>"", "text" => ""),
                        );

                        // Create buttons
                        if (is_array($list_btns)) {
                            foreach ($list_btns as $btns) {
                                $class = $btn_data[$btns]['class'];
                                $title = $btn_data[$btns]['title'];
                                $text = $btn_data[$btns]['text'];

                                echo '<div id="'.$btns.'" class="'.$class.'" title="'.__($title, 'wp-smart-editor').'">'.$text.'</div>';
                            }
                        }
                        ?>
                    </div>
                    <?php endif; ?>
            <?php endforeach; ?>
            </div> <!--End Unused toolbars block-->
            <input type="hidden" name="get_list_buttons" class="get_list_buttons" value="">
        </div>
        <div class="extra-btns-block">
            <ul class="extra-btns-list">
                <li>
                    <label for="btn1" class="extra-btn-label wpse_qtip" style="vertical-align: middle;" alt="<?php _e('Activate WP File Download light, our file manager for WordPress in its light version. It’ll help regarding downloadable file management', 'wp-smart-editor') ?>"><?php _e('Active WP File Download light', 'wp-smart-editor'); ?></label>
                    <div class="switch-btn">
                        <label class="switch">
                            <input type="checkbox" class="extra-btn" id="btn1" name="active_wpfdl" value="<?php echo $extra_btns_saved['active_wpfdl']; ?>" <?php if ($extra_btns_saved['active_wpfdl'] == 1){echo 'checked';} ?>>
                            <div class="slider round"></div>
                        </label>
                    </div>
                </li>
                <li>
                    <label for="btn2" class="extra-btn-label wpse_qtip" style="vertical-align: middle;" alt="<?php _e('Activate WP Table Manager light, our table manager for WordPress in its light version. It’ll help to manage tables without HTML knowledge', 'wp-smart-editor') ?>"><?php _e('Active WP Table Manager light', 'wp-smart-editor'); ?></label>
                    <div class="switch-btn">
                        <label class="switch">
                            <input type="checkbox" class="extra-btn" id="btn2" name="active_wptml" value="<?php echo $extra_btns_saved['active_wptml']; ?>" <?php if ($extra_btns_saved['active_wptml'] == 1){echo 'checked';} ?>>
                            <div class="slider round"></div>
                        </label>
                    </div>
                </li>
            </ul>
        </div>
    </div> <!--end of Editor toolbars tab-->

    <!--Profile attribution tab-->
    <div id="users-tab" class="tabs-content">
        <h3><?php _e('Active this profile for this user(s)', 'wp-smart-editor') ?>:</h3>
        <div class="users-block">
            <div class="wpse-users-search-box">
                <input type="text" id="user-search-input" name="s" placeholder="<?php _e('Search users', 'wp-smart-editor') ?>" value="">
                <select name="wpse-roles-filter" id="wpse-roles-filter">
                    <option value=""><?php _e('Use role filter', 'wp-smart-editor') ?></option>
                    <?php
                    $wp_roles = wp_roles();
                    $roles_list = $wp_roles->get_names();
                    foreach ($roles_list as $role => $role_name) {
                        echo '<option value="'.$role.'">'.$role_name.'</option>';
                    }
                    ?>
                </select>
                <input type="button" name="wpse-clear-btn" id="wpse-clear-btn" class="button" value="<?php _e('Clear', 'wp-smart-editor')?>">
            </div>
            <table class="widefat fixed" id="wpse-users-list">
                <thead>
                <tr>
                    <th scope="col" id="wpse-users-select-box" class="manage-col"><input type="hidden" id="wpse-users-checkall" name="select-user" value=""></th>
                    <th scope="col" id="wpse-users-name" class="manage-col"><span><?php _e('Name', 'wp-smart-editor') ?></span></th>
                    <th scope="col" id="wpse-users-username" class="manage-col"><span><?php _e('Username', 'wp-smart-editor') ?></span></th>
                    <th scope="col" id="wpse-users-email" class="manage-col"><span><?php _e('Email', 'wp-smart-editor') ?></span></th>
                    <th scope="col" id="wpse-users-role" class="manage-col"><span><?php _e('Role', 'wp-smart-editor') ?></span></th>
                </tr>
                </thead>

                <tbody id="wpse-users-body">
                <?php
                $users_per_page = 20;
                $pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
                $paged = max( 1, $pagenum );
                $args = array(
                    'number' => $users_per_page,
                    'offset' => ( $paged-1 ) * $users_per_page,
                    'include' => wp_get_users_with_no_role(),
                    'fields' => 'all_with_meta'
                );

                // Query the user IDs for this page
                $wp_user_search = get_users($args);
                $total_users = count(get_users());
                $total_pages = ceil($total_users / $users_per_page);

                if (count($wp_user_search)) {
                    foreach ($wp_user_search as $userid => $user_object) {
                        echo '<tr>';
                        echo '<td class="select-box"><input type="checkbox" name="wpse-users[]" value="'.$userid.'" ></td>';
                        echo '<td class="name column-name"><span style="color: #0073aa">' . $user_object->display_name . '</span></td>';
                        echo '<td class="username column-username"><strong>' . $user_object->user_login . '</strong></td>';
                        echo '<td class="email column-email">' . $user_object->user_email . '</td>';

                        $role_list = array();
                        global $wp_roles;
                        foreach ($user_object->roles as $role) {
                            if (isset($wp_roles->role_names[$role])) {
                                $role_list[$role] = translate_user_role($wp_roles->role_names[$role]);
                            }
                        }

                        if (empty($role_list)) {
                            $role_list['none'] = _x('None', 'no user roles');
                        }
                        $roles_list = implode(', ', $role_list);

                        echo '<td class="role column-role">' . $roles_list . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5"> ';
                    echo __('No users found.', 'wp-smart-editor');
                    echo '</td></tr>';
                }
                ?>
                </tbody>
                <?php
                $list_users_access = implode(' ', $users_access_saved);
                ?>
                <input type="hidden" name="wpse-users-access-list" id="wpse-users-access-list" value="<?php echo $list_users_access?>">
            </table>
            <p id="pagination">
                <?php $doneLeft = $doneRight = $skipLeft = $skipRight = false;
                if ($total_pages > 1) {
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i < $pagenum - 2) {
                            $skipLeft = true;
                        } elseif ($i > $pagenum + 2) {
                            $skipRight = true;
                        } else {
                            $skipLeft = false;
                            $skipRight = false;
                        }
                        if ($i == 1) {
                            if ($pagenum == 1) {
                                echo '<i class="dashicons dashicons-controls-skipback" id="first-page"></i>';
                            } else {
                                echo '<a class="dashicons dashicons-controls-skipback" id="first-page"></a>';
                            }
                        }
                        if (!$skipLeft && !$skipRight) {
                            if ($i == $pagenum) {
                                echo '<strong>' . $i . '</strong>';
                            } else {
                                echo '<a class="switch-page">' . $i . '</a>';
                            }
                        } elseif ($skipLeft) {
                            if (!$doneLeft) {
                                echo '<span>...</span>';
                                $doneLeft = true;
                            }
                        } elseif ($skipRight) {
                            if (!$doneRight) {
                                echo '<span>...</span>';
                                $doneRight = true;
                            }
                        }
                        if ($i == $total_pages) {
                            if ($pagenum == $total_pages) {
                                echo '<i class="dashicons dashicons-controls-skipforward" id="last-page"></i>';
                            } else {
                                echo '<a class="dashicons dashicons-controls-skipforward" id="last-page"  title="'.__('Last page', 'wp-smart-editor').'"></a>';
                            }
                        }
                    }
                }?>
            </p>

        </div> <!--end Users blocks-->

        <h3><?php _e('Active this profile for this group(s)', 'wp-smart-editor') ?>:</h3>
        <div class="wpse-groups-block">
            <ul class="wpse-groups-list">
                <?php
                $roles_list = $wp_roles->get_names();
                foreach ($roles_list as $role => $role_name) :?>
                    <li>
                        <label for="<?php echo $role ?>" class="extra-btn-label" style="vertical-align: middle;"><?php echo $role_name ?></label>
                        <div class="switch-btn">
                            <label class="switch">
                                <input type="checkbox" class="extra-btn" id="<?php echo $role ?>" name="wpse-roles[]" value="<?php echo $role ?>" <?php if(in_array($role, $roles_access_saved)){echo 'checked';} ?>>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div> <!--end of Profile attribution tab-->

    <!--Post types and devices tab-->
    <div id="posttypes-tab" class="tabs-content">
        <div class="block-content" id="post-types-list-content">
            <h3><?php _e('Active this profile for this post type(s)', 'wp-smart-editor') ?>:</h3>
            <ul id="post-types-list">
                <?php if (count($post_types_list)) : ?>
                    <?php foreach ($post_types_list as $post_type) : ?>
                    <li>
                        <label for="<?php echo $post_type ?>" class="real-label" style="vertical-align: middle;"><?php echo ucfirst($post_type) ?></label>
                        <div class="switch-btn">
                            <label class="switch">
                                <input type="checkbox" class="post_type" id="<?php echo $post_type ?>" name="wpse-post-type[]" value="<?php echo $post_type ?>" <?php if(in_array($post_type, $post_types_saved)){echo 'checked';} ?>>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="block-content" id="devices-list-content">
            <h3><?php _e('Active this profile for this device(s)', 'wp-smart-editor') ?>:</h3>
            <ul id="devices-list">
                <?php foreach ($devices_list as $device) : ?>
                    <li>
                        <label for="<?php echo $device ?>" class="real-label" style="vertical-align: middle;"><?php echo ucfirst($device) ?></label>
                        <div class="switch-btn">
                            <label class="switch">
                                <input type="checkbox" class="device-item" id="<?php echo $device ?>" name="wpse-device[]" value="<?php echo $device ?>" <?php if(in_array($device, $devices_list_saved)){echo 'checked';} ?>>
                                <div class="slider round"></div>
                            </label>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div> <!--end of Post types and devices tab-->

    <div class="save-btns save-profiles waves-effect waves-light waves-input-wrapper">
        <input name="publish" type="submit" class="cyan submit-profiles" value="Save Profile">
    </div>
</div>