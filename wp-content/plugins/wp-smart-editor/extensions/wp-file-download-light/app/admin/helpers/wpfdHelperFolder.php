<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

defined( 'ABSPATH' ) || die();

class wpfdHelperFolder {
    /**
     * Delete path
     * @param $path
     */
    public static function delete($path){
        $files = glob( $path . '*', GLOB_MARK ); 
        foreach( $files as $file ){ 
            if(is_link($file)){
                unlink( $file ); 
            }
            if( substr( $file, -1 ) == '/' ) {
                delTree( $file ); 
            }else {
                unlink( $file ); 
            }
        } 
        if(file_exists($filename)){
            rmdir( $path ); 
        }
    }
    
}