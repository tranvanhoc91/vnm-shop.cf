<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Model;
defined( 'ABSPATH' ) || die();

$app = Application::getInstance('wptm');
if (!defined('WPSE_LANG_DIR')) {
	define('WPSE_LANG_DIR', WP_PLUGIN_DIR . '/wp-smart-editor/languages/');
}
load_plugin_textdomain( 'wp-smart-editor', null, WPSE_LANG_DIR);

add_action( 'media_buttons_context', 'wptm_button');

function wptm_button($context){
    $app = Application::getInstance('wptm'); 
    $modelConfig = Model::getInstance('config');
    $config = $modelConfig->getConfig();
    if ($config['enable_frontend'] == 1) {
        wp_enqueue_style('wptm-modal',plugins_url('app/admin/assets/css/leanmodal.css',WPTML_PLUGIN_FILE));
        wp_enqueue_script('wptm-modal',plugins_url('app/admin/assets/js/jquery.leanModal.min.js',WPTML_PLUGIN_FILE));
        wp_enqueue_script('wptm-modal-init',plugins_url('app/site/assets/js/leanmodal.init.js',WPTML_PLUGIN_FILE));
        wp_localize_script('wptm-modal-init','wptmVars',array('adminurl' => admin_url()));
         
        $context .= "<a href='#wptmmodal' class='button wptmlaunch' id='wptmlaunch' title='WP Table Manager'>"
                    . " <span class='dashicons dashicons-screenoptions' style='line-height: inherit;'></span>WP Table Manager</a>";
         $context .= "
            <script type='text/javascript'>
                jQuery(document).ready(function($){
            
                   jQuery('.wptmlaunch').wptm_leanModal({ top : 20, beforeShow: function(){jQuery('#wptmmodal').css('height','90%');jQuery('#wptmmodalframe').hide();jQuery('#wptmmodalframe').attr('src',jQuery('#wptmmodalframe').attr('src'));jQuery('#wptm_loader').show(); } });
                   return false;
                });
            </script>
        ";
    
    }
    
    return $context;
}