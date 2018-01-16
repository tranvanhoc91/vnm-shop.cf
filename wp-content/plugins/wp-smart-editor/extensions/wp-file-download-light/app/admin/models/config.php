<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Model;
use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Application;

defined('ABSPATH') || die();

class wpfdModelConfig extends Model
{
    /**
     * Get theme config
     * @return mixed
     */
    public function getThemeConfig()
    {
        $theme = get_option('_wpfd_theme', 'default');
        return $theme;
    }

    /**
     * Get theme param
     * @param $theme
     * @return mixed
     */
    public function getThemeParams($theme)
    {

        $deaults = array('default' => '{"marginleft":"10","marginright":"10", "margintop":"10", "marginbottom":"10", "showsize":"1","showtitle":"1","croptitle":"0","showdescription":"1","showversion":"1","showhits":"1","showdownload":"1","bgdownloadlink":"#76bc58","colordownloadlink":"#ffffff","showdateadd":"1","showdatemodified":"0","showsubcategories":"1","showcategorytitle":"1","showbreadcrumb":"1","showfoldertree":"0"}');
        $theme_params = get_option('_wpfd_' . $theme . '_config', @$deaults[$theme]);
        if (is_string($theme_params)) {
            $theme_params = json_decode($theme_params, true);
        }
        return $theme_params;
    }

    /**
     * List all themes inside themes folder
     * @return array
     */
    public function getThemes()
    {
        $app = Application::getInstance('wpfd');
        $results = array();
        foreach (glob($app->getPath() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-*', GLOB_ONLYDIR) as $rep) {
            $dir = explode(DIRECTORY_SEPARATOR, $rep);
            $results[] = substr($dir[count($dir) - 1], 5);
        }

        $dirs = wp_upload_dir();
        $clonedThemes = $dirs['basedir'] . '/wpfd-themes/';

        if (file_exists($clonedThemes)) {
            foreach (glob($clonedThemes.'wpfd-*', GLOB_ONLYDIR) as $rep) {
                $results[] = str_replace('wpfd-', '', basename($rep));
            }
        }

        return $results;
    }

    /**
     * Get global config
     * @return array
     */
    public function getConfig()
    {

        $defaultConfig = array('allowedext' => '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,flac,m3u,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,rm,swf,vob,wmv,css,img');
        $defaultConfig['deletefiles'] = 0;
        $defaultConfig['catparameters'] = 1;
        $defaultConfig['defaultthemepercategory'] = 'default';
        $defaultConfig['date_format'] = 'd-m-Y';
        $defaultConfig['use_google_viewer'] = 'lightbox';
        $defaultConfig['extension_viewer'] = 'png,jpg,pdf,ppt,doc,xls,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4';
        $defaultConfig['show_file_import'] = 0;
        $defaultConfig['uri'] = "download";
        $defaultConfig['ga_download_tracking'] = 0;
        $defaultConfig['useeditor'] = 0;
        $defaultConfig['restrictfile'] = 0;
        $defaultConfig['categoryown'] = 0;
        $defaultConfig['shortcodecat'] = 1;
        $defaultConfig['paginationnunber'] = 100;
        $defaultConfig['open_pdf_in'] = 0;
        $config = get_option('_wpfd_global_config', $defaultConfig);
        $config = array_merge($defaultConfig, $config);
        return (array)$config;
    }

    /**
     * Get file config
     * @return array
     */
    public function getFileConfig()
    {

        $defaultConfig = array(
            'singlebg' => '#444444',
            'singlehover' => '#888888',
            'singlefontcolor' => '#ffffff',
        );

        $config = get_option('_wpfd_global_file_config', $defaultConfig);

        return (array)$config;
    }

    /**
     * Get search config
     * @return array
     */
    public function getSearchConfig()
    {

        $defaultConfig = array(
            'search_page' => (int)get_option('_wpfd_search_page_id'),
            'plain_text_search' => 0,
            'cat_filter' => 1,
            'tag_filter' => 1,
            'display_tag' => 'searchbox',
            'create_filter' => 1,
            'update_filter' => 1,
            'file_per_page' => 15,
            'shortcode' => '[wpfd_search]'
        );

        $config = get_option('_wpfd_global_search_config', $defaultConfig);

        return (array)$config;
    }

    /**
     * Save global config
     * @param $datas
     * @return mixed
     */
    public function save($datas)
    {
        $config = get_option('_wpfd_global_config');
        foreach ($datas as $key => $value) {
            $config[$key] = $value;
        }

        $result = update_option('_wpfd_global_config', $config);
        return $result;
    }

    /**
     * Save file params
     * @param $datas
     * @return mixed
     */
    public function saveFileParams($datas)
    {
        $result = update_option('_wpfd_global_file_config', $datas);
        return $result;
    }

    /**
     * Get allowed ext for uploading file
     * @return array
     */
    public function getAllowedExt()
    {
        $params = $this->getConfig();
        return explode(',', $params['allowedext']);
    }

    /**
     * Copy folder
     * @param $src
     * @param $dst
     */
    function copyfolder($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyfolder($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

}