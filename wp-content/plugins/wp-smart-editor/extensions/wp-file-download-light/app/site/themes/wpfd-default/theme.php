<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Model;
//-- No direct access
defined( 'ABSPATH' ) || die();

class wpfdThemeDefault
{
    
    public $name = 'default';
    protected $options;

    public function getThemeName(){
        return $this->name;
    }
    
    public function showCategory($options){

        if(wpfdBase::checkExistTheme($this->name)) {
            $url = plugin_dir_url( __FILE__ );
        } else {
            $dirs = wp_get_upload_dir();
            $url = $dirs['baseurl'] . '/wpfd-themes/wpfd-' . $this->name .'/';
        }

        if(empty($options['files']) && empty($options['categories'])){
            return '';
        }
        $this->options = $options;
        $app = Application::getInstance('wpfd');
        $modelConfig = Model::getInstance('config');
        $this->config = $modelConfig->getGlobalConfig();
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('handlebars', $url . 'js/handlebars-1.0.0-rc.3.js');
        wp_enqueue_script('wpfd-front', plugins_url( 'app/site/assets/js/front.js' , WPFDL_PLUGIN_FILE,array(),WPFDL_VERSION ));
        wp_enqueue_style('wpfd-material-design', plugins_url( 'app/site/assets/css/material-design-iconic-font.min.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_enqueue_script('wpfd-foldertree', plugins_url( 'app/site/assets/js/jaofiletree.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_enqueue_style('wpfd-foldertree', plugins_url( 'app/site/assets/css/jaofiletree.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_enqueue_script('wpfd-helper', plugins_url( 'assets/js/helper.js' , Application::getInstance('wpfd')->getPath().DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'foobar'));
        wp_enqueue_script('wpfd-theme-default', $url . 'js/script.js',array(),WPFDL_VERSION);
        wp_localize_script('wpfd-theme-default','wpfdparams', array(
                    'ajaxurl' => Application::getInstance('wpfd')->getAjaxUrl(),
                    'ga_download_tracking' => $this->config['ga_download_tracking']
                ));
        wp_enqueue_style('wpfd-theme-default', $url . 'css/style.css',array(),WPFDL_VERSION);

        $content = '';
        $this->files = $this->options['files'];
        $this->category = $this->options['category'];
        $this->categories = $this->options['categories'];
        $this->params = $this->options['params'];
        

        if ($this->config['use_google_viewer'] == 'lightbox') {
            wp_enqueue_style('wpfd-videojs', plugins_url( 'app/site/assets/css/video-js.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_style('wpfd-colorbox', plugins_url( 'app/site/assets/css/colorbox.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_style('wpfd-viewer', plugins_url( 'app/site/assets/css/viewer.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_script('wpfd-colorboxjs', plugins_url( 'app/site/assets/js/jquery.colorbox-min.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_script('wpfd-colorbox-init', plugins_url( 'app/site/assets/js/colorbox.init.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_script('wpfd-videojs', plugins_url( 'app/site/assets/js/video.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_localize_script('wpfd-colorbox-init','wpfdcolorbox',array('ajaxurl' => Application::getInstance('wpfd')->getAjaxUrl()));
        }

        if(!empty($this->options['files']) || wpfdBase::loadValue($this->params,'showsubcategories',1)==1){
            $this->style  = 'margin : '.wpfdBase::loadValue($this->params,'margintop',5).'px '.wpfdBase::loadValue($this->params,'marginright',5).'px '.wpfdBase::loadValue($this->params,'marginbottom',5).'px '.wpfdBase::loadValue($this->params,'marginleft',5).'px;';

            ob_start();          
            require dirname(__FILE__).DIRECTORY_SEPARATOR.'tpl.php';
            
            $content = ob_get_contents();
            ob_end_clean();
            //fix conflict with wpautop in VC
            $content = str_replace(array("\n", "\r"), '', $content);
        }
        return $content;
    }    

    public function showFile($options){
        $this->options = $options;
        $modelConfig = Model::getInstance('config');
        $this->config = $modelConfig->getGlobalConfig();
        if(wpfdBase::checkExistTheme($this->name)) {
            $url = plugin_dir_url( __FILE__ );
        } else {
            $dirs = wp_get_upload_dir();
            $url = $dirs['baseurl'] . '/wpfd-themes/wpfd-' . $this->name .'/';
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script('handlebars', $url . 'js/handlebars-1.0.0-rc.3.js');
        wp_enqueue_script('wpfd-front', plugins_url( 'app/site/assets/js/front.js' , WPFDL_PLUGIN_FILE,array(),WPFDL_VERSION ));
        wp_enqueue_style('wpfd-theme-default', $url . 'css/style.css');
        wp_localize_script('wpfd-front','wpfdparams', array(
                    'ajaxurl' => Application::getInstance('wpfd')->getAjaxUrl(),
                    'ga_download_tracking' => $this->config['ga_download_tracking']
        ));

        if ($this->config['use_google_viewer'] == 'lightbox') {
            wp_enqueue_style('wpfd-videojs', plugins_url( 'app/site/assets/css/video-js.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_style('wpfd-colorbox', plugins_url( 'app/site/assets/css/colorbox.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_style('wpfd-viewer', plugins_url( 'app/site/assets/css/viewer.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_script('wpfd-colorboxjs', plugins_url( 'app/site/assets/js/jquery.colorbox-min.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_script('wpfd-colorbox-init', plugins_url( 'app/site/assets/js/colorbox.init.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_enqueue_script('wpfd-videojs', plugins_url( 'app/site/assets/js/video.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
            wp_localize_script('wpfd-colorbox-init','wpfdcolorbox',array('ajaxurl' => Application::getInstance('wpfd')->getAjaxUrl()));
        }

        $content = '';
        if(!empty($this->options['file'])){
            $this->file = $this->options['file'];
            $this->params = $this->options['params'];
            $this->file_params = $this->options['file_params'];

            $this->style  = 'margin : '.wpfdBase::loadValue($this->params,'margintop',5).'px '.wpfdBase::loadValue($this->params,'marginright',5).'px '.wpfdBase::loadValue($this->params,'marginbottom',5).'px '.wpfdBase::loadValue($this->params,'marginleft',5).'px;';
            
            
            ob_start();          
            require dirname(__FILE__).DIRECTORY_SEPARATOR.'tplsingle.php';
            
            $content = ob_get_contents();
            ob_end_clean();
            //fix conflict with wpautop in VC
            $content = str_replace(array("\n", "\r"), '', $content);
        }
        return $content;
    }

    
}
