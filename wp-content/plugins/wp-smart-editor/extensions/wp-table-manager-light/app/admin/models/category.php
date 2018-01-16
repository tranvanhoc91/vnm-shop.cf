<?php
/**
    * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Model;

defined( 'ABSPATH' ) || die();

class wptmModelCategory extends Model {
    
    function isCategoryExist($id_category) {
        global $wpdb;
        $query = 'SELECT c.id FROM '.$wpdb->prefix.'wptm_categories as c WHERE c.id='.(int)$id_category;
        $result = $wpdb->query($query);
        if(!$result){
            return false;
        }
        return true;
    }
}