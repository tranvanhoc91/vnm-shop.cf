<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

defined( 'ABSPATH' ) || die();

class wpfdHelperFiles {

    public static function bytesToSize($bytes, $precision = 2){
        $sz = array('b','kb','mb','gb','tb','pb');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$precision}f", $bytes / pow(1000, $factor)) . ' ' . __($sz[$factor],'wp-smart-editor');
    }
    
}
