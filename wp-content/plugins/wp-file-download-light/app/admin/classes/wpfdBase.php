<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

// no direct access
defined('ABSPATH') or die();

class wpfdBase {

    /**
     * Load value with default value
     * @param $var
     * @param $value
     * @param string $default
     * @return string
     */
    public static function loadValue($var,$value,$default=''){
        if(is_object($var) && isset($var->$value)){
            return $var->$value;
        }elseif(is_array($var) && isset($var[$value])){
            return $var[$value];
        }
        return $default;
    }
    
    /**
     * method to retrieve the path to the component image directory
     * @param type $id_category 
     * @return string directory path
     */
    public static function getFilesPath($id_category=null){
        $upload_dir = wp_upload_dir();
        if($id_category===null){
            return $upload_dir['basedir'].DIRECTORY_SEPARATOR.'wpfd'.DIRECTORY_SEPARATOR;
        }
        return $upload_dir['basedir'].DIRECTORY_SEPARATOR.'wpfd'.DIRECTORY_SEPARATOR.$id_category.DIRECTORY_SEPARATOR;
    }

    public static function checkExistTheme($name) {

        $themes = array('default','ggd','table','tree');
        if (in_array($name, $themes)) {
            return true;
        } else {
            return false;
        }
    }

    public static function cropTitle($catParams,$catTheme,$title)
    {
        $crop_title = $title;
        $croptitle = (int)wpfdBase::loadValue($catParams,$catTheme.'_croptitle', 0);
        if (!$croptitle) {
            $croptitle = (int)wpfdBase::loadValue($catParams, 'croptitle', 0);
        }
        if ($croptitle && strlen($title) > $croptitle) {
            $crop_title = substr($title, 0, $croptitle) . "...";
        }
        return $crop_title;
    }
}

?>
