<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */
use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Model;
//-- No direct access
defined( 'ABSPATH' ) || die();

class wpfdHelperFile {

    /**
     * Convert bytes to size
     * @param $bytes
     * @param int $precision
     * @return string
     */
    static function bytesToSize($bytes, $precision = 2){
        $sz = array('b','kb','mb','gb','tb','pb');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$precision}f", $bytes / pow(1000, $factor)) . ' ' . @$sz[$factor];
    }

    /**
     * Get viewer url
     * @param $id
     * @param $catid
     * @param string $token
     * @return string
     */
    static function getViewerUrl($id,$catid,$token=""){
        $app = Application::getInstance('wpfd');
        $url = $app->getAjaxUrl() .'task=file.download&wpfd_category_id='.$catid.'&wpfd_file_id=' . $id .'&token='.$token.'&preview=1';
        return 'https://docs.google.com/viewer?url='.urlencode($url).'&embedded=true';
    }
    /**
     * Get url to open pdf in browser
     * @param $id
     * @param $catid
     * @param string $token
     * @return string
     */
    static function getPdfUrl($id,$catid,$token=""){
        $app = Application::getInstance('wpfd');
        $url = $app->getAjaxUrl() .'task=file.download&wpfd_category_id='.$catid.'&wpfd_file_id=' . $id .'&token='.$token.'';
        return $url;
    }

    /**
     * Get media viewer url
     * @param $id
     * @param string $ext
     * @return string
     */
    static function getMediaViewerUrl($id,  $ext=''){

        $app = Application::getInstance('wpfd');

        $imagesType = array('jpg','png','gif','jpeg','jpe','bmp','ico','tiff','tif','svg','svgz');
        $videoType = array('mp4','mpeg','mpe','mpg','mov','qt','rv','avi','movie','flv','webm','ogv');//,'3gp'
        $audioType = array('mid','midi','mp2','mp3','mpga','ram','rm','rpm','ra','wav');  // ,'aif','aifc','aiff'
        if(in_array($ext, $imagesType)) {
            $type= 'image';
        }else if(in_array($ext, $videoType)) {
            $type ='video';
        }else if(in_array($ext, $audioType)) {
            $type='audio';
        }else {
            $type='';
        }

        return $app->getAjaxUrl() .'task=frontviewer.display&view=frontviewer&id='.$id.'&type='.$type.'&ext='.$ext;
    }

    /**
     * check if it is media file
     * @param $ext
     * @return bool
     */
    static function isMediaFile($ext) {
        $media_arr = array('mid','midi','mp2','mp3','mpga','ram','rm','rpm','ra','wav', //,'aif','aifc','aiff'
            'mp4','mpeg','mpe','mpg','mov','qt','rv','avi','movie','flv','webm','ogv', //'3gp',
            'jpg','png','gif','jpeg','jpe','bmp','ico','tiff','tif','svg','svgz');
        if(in_array($ext, $media_arr)) {
            return true;
        }
        return false;
    }

    /**
     * Get mime type
     * @param $ext
     * @return string
     */
    public static function mime_type($ext) {

            $mime_types = array(
                //flash
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',
                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',

                // audio          
                'mid' => 'audio/midi',
                'midi' => 'audio/midi',
                'mp2' => 'audio/mpeg',
                'mp3' => 'audio/mpeg',
                'mpga' => 'audio/mpeg',
                'aif' => 'audio/x-aiff',
                'aifc' => 'audio/x-aiff',
                'aiff' => 'audio/x-aiff',
                'ram' => 'audio/x-pn-realaudio',
                'rm' => 'audio/x-pn-realaudio',
                'rpm' => 'audio/x-pn-realaudio-plugin',
                'ra' => 'audio/x-realaudio',
                'wav' => 'audio/x-wav',
                'wma' => 'audio/wma',
                
                //Video
                'mp4' => 'video/mp4',
                'mpeg' => 'video/mpeg',
                'mpe' => 'video/mpeg',
                'mpg' => 'video/mpeg',
                'mov' => 'video/quicktime',
                'qt' => 'video/quicktime',
                'rv' => 'video/vnd.rn-realvideo',
                'avi' => 'video/x-msvideo',
                'movie' => 'video/x-sgi-movie' ,
                '3gp' => 'video/3gpp',
                'webm' => 'video/webm',
                'ogv' => 'video/ogg',
                //doc
                'pdf' => 'application/pdf'
            );
            
            if (array_key_exists($ext, $mime_types)) {
                return $mime_types[$ext];
            }else {
                return 'application/octet-stream';
            }
    }

    /**
     * search assets
     */
    static function wpfd_assets() {

        wp_enqueue_script('jquery');
        wp_enqueue_style('jquery-ui-1.9.2',plugins_url('app/admin/assets/css/ui-lightness/jquery-ui-1.9.2.custom.min.css',WPFDL_PLUGIN_FILE));
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_script('jquery-ui-1.11.4',plugins_url('app/admin/assets/js/jquery-ui-1.11.4.custom.min.js',WPFDL_PLUGIN_FILE));
        wp_enqueue_script('wpfd-colorbox',plugins_url('app/site/assets/js/jquery.colorbox-min.js',WPFDL_PLUGIN_FILE));
        wp_enqueue_script('wpfd-colorbox-init', plugins_url( 'app/site/assets/js/colorbox.init.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_enqueue_script('wpfd-videojs', plugins_url( 'app/site/assets/js/video.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_localize_script('wpfd-colorbox','wpfdcolorbox',array('ajaxurl' => Application::getInstance('wpfd')->getAjaxUrl()));

        wp_enqueue_style('wpfd-videojs', plugins_url( 'app/site/assets/css/video-js.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_enqueue_style('wpfd-colorbox', plugins_url( 'app/site/assets/css/colorbox.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_enqueue_style('wpfd-viewer', plugins_url( 'app/site/assets/css/viewer.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);

    }
}