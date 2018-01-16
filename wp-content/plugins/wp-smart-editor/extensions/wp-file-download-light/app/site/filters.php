<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Filter;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Model;
use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Application;

defined('ABSPATH') || die();

class wpfdFilter extends Filter
{

    public function load()
    {
        add_filter('the_content', array($this, 'wpfd_replace'));
        add_filter('themify_builder_module_content', array($this, 'themify_module_content'));

        add_filter('template_include', array($this, 'include_template'), 99);
        add_filter('rewrite_rules_array', array($this, 'wpfd_insert_rewrite_rules'));
        add_filter('query_vars', array($this, 'wpfd_insert_query_vars'));
        add_action('wp_loaded', array($this, 'wpfd_flush_rules'));
        add_action('parse_request', array($this, 'wpfd_redirect'), 1, 1);

        add_shortcode('wpfd_category', array($this, 'category_shortcode'));
        add_shortcode('wpfd_single_file', array($this, 'single_file_shortcode'));
    }

    function wpfdAddonCategoryFrom($termId)
    {

    }

    /**
     *
     * redirect to download link
     * @param $query
     */
    function wpfd_redirect($query)
    {
        if (!empty($query->query_vars['wpfd_filename']) && !empty($query->query_vars['wpfd_file_id']) && !empty($query->query_vars['wpfd_category_id']) && !empty($query->query_vars['wpfd_category_name'])) {
            $app = Application::getInstance('wpfd');
            include_once(dirname(WPFDL_PLUGIN_FILE).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'file.php');
            $fileController = new wpfdControllerFile();
            $fileController->download($query->query_vars['wpfd_file_id'], $query->query_vars['wpfd_category_id'] );;
            exit;
        }
    }

    function wpfd_flush_rules()
    {
        $rules = get_option('rewrite_rules');
        if (!isset($rules['index.php/([^/]*)/([0-9]+)/([^/]*)/(.*)/([^/]*)/?']) || !isset($rules['([^/]*)/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'])) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

    /**
     * Insert rewrite rules
     */
    function wpfd_insert_rewrite_rules($rules)
    {
        $config = get_option('_wpfd_global_config');
        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download';
        } else {
            $seo_uri = $config['uri'];
        }

        $newrules = array();
        $url1 = site_url();
        $url2 = home_url();
        $index = str_replace($url2, '', $url1);
        $index = trim($index, '/');
        if ($index != '') {
            $newrules['index.php/'.$index.'/' . $seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = site_url('/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]&wpfd_filename=$matches[4]');
            $newrules[$index.'/'.$seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = site_url('/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]&wpfd_filename=$matches[4]');
        } else {
            $newrules['index.php/' . $seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = site_url('/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]&wpfd_filename=$matches[4]');
            $newrules[$seo_uri . '/([0-9]+)/([^/]*)/(.*)/([^/]*)/?'] = site_url('/wp-admin/admin-ajax.php?juwpfisadmin=false&action=wpfd&task=file.download&wpfd_category_id=$matches[1]&wpfd_category_name=$matches[2]&wpfd_file_id=$matches[3]&wpfd_filename=$matches[4]');
        }
        return $newrules + $rules;
    }

    /**
     * Append vars for download
     * @param $vars
     * @return mixed
     */
    function wpfd_insert_query_vars($vars)
    {
        foreach (array('wpfd_filename', 'wpfd_file_id', 'wpfd_category_id', 'wpfd_category_name') as $v) {
            array_push($vars, $v);
        }
        return $vars;
    }

    /**
     * archive template for category
     * @param $template_path
     * @return string
     */
    function include_template($template_path)
    {
        $post_type = get_query_var('post_type');
        if (is_tax('wpfd-category')) {
            if (get_post_type() == 'wpfd_file') {
                if (is_archive()) {
                    if ($theme_file = locate_template(array('archive-wpfd-category.php'))) {
                        $template_path = $theme_file;
                    } else {
                        $template_path = plugin_dir_path(WPFDL_PLUGIN_FILE) . 'app/site/themes/archive-wpfd-category.php';
                    }
                }
            } else {
                //empty category 
                $wpfd_category = Utilities::getInput('wpfd-category', 'GET', 'none');
                if (!empty($wpfd_category)) {
                    if ($theme_file = locate_template(array('empty-wpfd-category.php'))) {
                        $template_path = $theme_file;
                    } else {
                        $template_path = plugin_dir_path(WPFDL_PLUGIN_FILE) . 'app/site/themes/empty-wpfd-category.php';
                    }
                }

            }
        } else if ($post_type == 'wpfd_file') {
            if ($theme_file = locate_template(array('single-wpfd-file.php'))) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path(WPFDL_PLUGIN_FILE) . 'app/site/themes/single-wpfd-file.php';
            }
        }

        return $template_path;
    }

    /**
     * category shortcode
     * @param $atts
     * @return string
     */
    function category_shortcode($atts)
    {
        if (isset($atts['id']) && $atts['id']) {
            add_action( 'wp_footer', array($this, 'wpfd_footer') );
            return $this->callTheme($atts['id']);
        }

        return '';
    }

    public function themify_module_content($content)
    {
        $content = $this->wpfd_replace($content);
        return $content;
    }

    public function wpfd_replace($content)
    {
        $content = preg_replace_callback('@<img[^>]*?data\-wpfdcategory="([0-9]+)".*?>@', array($this, 'replace'), $content);

        //Replace single file
        $content = preg_replace_callback('@<img[^>]*?data\-wpfdfile="(.*?)".*?>@', array($this, 'replaceSingle'), $content);

        return $content;
    }

    /**
     * Display single category
     * @param $match
     * @return mixed
     */
    private function replace($match)
    {
        add_action( 'wp_footer', array($this, 'wpfd_footer') );
        return $this->callTheme($match[1]);
    }
    function wpfd_footer() {
        echo '<div id="wpfd-loading-wrap"><div class="wpfd-loading"></div></div>';
        echo '<div id="wpfd-loading-tree-wrap"><div class="wpfd-loading-tree-bg"></div></div>';

        wp_enqueue_script('wpfd-frontend', plugins_url( 'app/site/assets/js/frontend.js' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);
        wp_localize_script('wpfd-frontend','wpfdfrontend',array('pluginurl' => plugins_url('',WPFDL_PLUGIN_FILE)));
    }
    /**
     * Display single file
     * @param $match
     * @return string
     */
    private function replaceSingle($match)
    {

        //get category of file then check access role
        preg_match('@.*data\-category="([0-9]+)".*@', $match[0], $matchCat);
        if (!empty($matchCat)) {
            $catid = (int)$matchCat[1];
        } else {
            $term_list = wp_get_post_terms((int)$match[1], 'wpfd-category', array("fields" => "ids"));
            $catid = $term_list[0];
        }

        return $this->callSinglefile($match[1], $catid);
    }


    function single_file_shortcode($atts)
    {
        if (isset($atts['id']) && $atts['id']) {
            if (isset($atts['catid'])) {
                $catid = $atts['catid'];
            } else {
                $term_list = wp_get_post_terms((int)$atts['id'], 'wpfd-category', array("fields" => "ids"));
                $catid = $term_list[0];
            }
            return $this->callSinglefile($atts['id'], $catid);
        }

        return '';
    }

    /**
     * get content of a single file
     * @param $file_id
     * @param $catid
     * @return string
     */
    function callSinglefile($file_id, $catid) {


        wp_enqueue_style('wpfd-front', plugins_url( 'app/site/assets/css/front.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);

        global $wpdb;
        $app = Application::getInstance('wpfd');

        require_once $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'wpfdBase.php';
        $modelConfig = Model::getInstance('config');
        $modelCategory = Model::getInstance('category');
        $modelFile = Model::getInstance('file');

        $modelTokens = Model::getInstance('tokens');
        $sessionToken = isset($_SESSION['wpfdToken']) ? $_SESSION['wpfdToken'] : null ;
        if($sessionToken===null){
            $token = $modelTokens->createToken();
            $_SESSION['wpfdToken'] = $token;
        }else{
            $tokenId = $modelTokens->tokenExists($sessionToken);
            if($tokenId){
                $modelTokens->updateToken($tokenId);
                $token = $sessionToken;
            }else{
                $token = $modelTokens->createToken();
                $_SESSION['wpfdToken'] = $token;
            }
        }


        $category = $modelCategory->getCategory((int)$catid);


        if (!$category) {
            return '';
        }
        if ($category->access == 1) {
            $user = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);
            if (empty($allows)) return '';
        }

        $themefile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-default' . DIRECTORY_SEPARATOR . 'theme.php';
        if (file_exists($themefile)) {
            include_once $themefile;
        }
        $theme = new wpfdThemeDefault();

        $params = $modelConfig->getConfig();
        $file_params = $modelConfig->getFileConfig();
        $config = $modelConfig->getGlobalConfig();
        $idFile = $file_id;
        if (apply_filters('wpfdAddonCategoryFrom', $catid) == 'googleDrive') {
            $file = apply_filters('wpfdAddonGetGoogleDriveFile', $idFile, $catid, $token);
        } elseif (apply_filters('wpfdAddonCategoryFrom', $catid) == 'dropbox') {
            $file = apply_filters('wpfdAddonGetDropboxFile', $idFile, $catid, $token);
        } else {
            $file = $modelFile->getFile($idFile,$catid);
        }
        if (!$file) return '';

        if ($config['restrictfile'] == 1) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $canview = isset($file->canview) ? $file->canview : 0;
            if ($user_id) {
                if ($canview == $user_id || $canview == 0) {

                } else {
                    return '';
                }
            } else {
                if ($canview == 0) {

                } else {
                    return '';
                }
            }
        }
        $file = (object)$file;
        $file->social = isset($file->social) ? $file->social : 0;

        $options = array('file' => $file, 'params' => $params, 'file_params' => $file_params);

        if ($file->social == 1 && defined('WPFDA_VERSION')) {
            return do_shortcode('[wpfdasocial]'.$theme->showFile($options).'[/wpfdasocial]');
        } else {
            return $theme->showFile($options);
        }

    }
    /**
     * call category theme
     * @param $param
     * @return string
     */
    private function callTheme($param)
    {
        wp_enqueue_style('wpfd-front', plugins_url( 'app/site/assets/css/front.css' , WPFDL_PLUGIN_FILE ),array(),WPFDL_VERSION);

        global $wpdb;
        $app = Application::getInstance('wpfd');

        require_once $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'wpfdBase.php';
        $modelConfig = Model::getInstance('config');
        $global_settings = $modelConfig->getGlobalConfig();


        $modelCategory = Model::getInstance('category');
        $category = $modelCategory->getCategory($param);
        if (empty($category)) return '';
        $themename = $category->params['theme'];
        $params = $category->params;
        $params['social'] = isset($params['social']) ? $params['social'] : 0;
        if ($category->access == 1) {
            $user = wp_get_current_user();
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);

            $singleuser = false;

            if (isset($params['canview']) && $params['canview'] == '') {
                $params['canview'] = 0;
            }

            $canview = isset($params['canview']) ? $params['canview'] : 0;

            if ($global_settings['restrictfile'] == 1) {
                $user = wp_get_current_user();
                $user_id = $user->ID;

                if ($user_id) {
                    if ($canview == $user_id || $canview == 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                } else {
                    if ($canview == 0) {
                        $singleuser = true;
                    } else {
                        $singleuser = false;
                    }
                }
            }
            if ($canview != 0 && !count($category->roles)) {
                if ($singleuser == false) return '';
            } elseif ($canview != 0 && count($category->roles)) {
                if (!empty($allows) || ($singleuser == true)) {
                } else {
                    return '';
                }
            } else {
                if (empty($allows)) return '';
            }

        }

        if (wpfdBase::checkExistTheme(strtolower($themename))) {
            $themefile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-' . strtolower($themename) . DIRECTORY_SEPARATOR . 'theme.php';
        } else {
            $dir = wp_upload_dir();
            $themefile = $dir['basedir'] . '/wpfd-themes/wpfd-'. strtolower($themename).DIRECTORY_SEPARATOR.'theme.php';
        }
        if (file_exists($themefile)) {
            include_once $themefile;
        } else {
            $themefile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-default' . DIRECTORY_SEPARATOR . 'theme.php';
            include_once $themefile;
            $themename = 'default';
        }
        $class = 'wpfdTheme' . ucfirst($themename);
        $theme = new $class();

        $modelFiles = Model::getInstance('files');
        $modelCategories = Model::getInstance('categories');
        $modelCategory = Model::getInstance('category');

        $modelTokens = Model::getInstance('tokens');
        $sessionToken = isset($_SESSION['wpfdToken']) ? $_SESSION['wpfdToken'] : null ;
        if($sessionToken===null){
            $token = $modelTokens->createToken();
            $_SESSION['wpfdToken'] = $token;
        }else{
            $tokenId = $modelTokens->tokenExists($sessionToken);
            if($tokenId){
                $modelTokens->updateToken($tokenId);
                $token = $sessionToken;
            }else{
                $token = $modelTokens->createToken();
                $_SESSION['wpfdToken'] = $token;
            }
        }



        $tpl = null;
        if (apply_filters('wpfdAddonCategoryFrom', $param) == 'googleDrive') {
            $tpl = 'googleDrive';
            $category = $modelCategory->getCategory($param);
            $ordering = Utilities::getInput('orderCol', 'GET', 'none') != null ? Utilities::getInput('orderCol', 'GET', 'none') : $category->ordering;
            $orderingdir = Utilities::getInput('orderDir', 'GET', 'none') != null ? Utilities::getInput('orderDir', 'GET', 'none') : $category->orderingdir;
            $files = apply_filters('wpfdAddonGetListGoogleDriveFile', $param, $ordering, $orderingdir, $category->slug, $token);
            $categories = $modelCategories->getCategories($param);

        } elseif (apply_filters('wpfdAddonCategoryFrom', $param) == 'dropbox') {
            $tpl = 'dropbox';
            $category = $modelCategory->getCategory($param);
            $ordering = Utilities::getInput('orderCol', 'GET', 'none') != null ? Utilities::getInput('orderCol', 'GET', 'none') : $category->ordering;
            $orderingdir = Utilities::getInput('orderDir', 'GET', 'none') != null ? Utilities::getInput('orderDir', 'GET', 'none') : $category->orderingdir;
            $files = apply_filters('wpfdAddonGetListDropboxFile', $param, $ordering, $orderingdir, $category->slug, $token);
            $categories = $modelCategories->getCategories($param);

        } else {
            $category = $modelCategory->getCategory($param);
            $ordering = Utilities::getInput('orderCol', 'GET', 'none') != null ? Utilities::getInput('orderCol', 'GET', 'none') : $category->ordering;
            $orderingdir = Utilities::getInput('orderDir', 'GET', 'none') != null ? Utilities::getInput('orderDir', 'GET', 'none') : $category->orderingdir;
            $files = $modelFiles->getFiles($param, $ordering, $orderingdir);
            $categories = $modelCategories->getCategories($param);

            if (!empty($files) && ($global_settings['restrictfile'] == 1)) {
                $user = wp_get_current_user();
                $user_id = $user->ID;
                foreach ($files as $key => $file) {
                    $metadata = get_post_meta($file->ID, '_wpfd_file_metadata', true);
                    $canview = isset($metadata['canview']) ? $metadata['canview'] : 0;
                    if ($user_id) {
                        if ($canview == $user_id || $canview == 0) {

                        } else {
                            unset($files[$key]);
                        }
                    } else {
                        if ($canview == 0) {

                        } else {
                            unset($files[$key]);
                        }
                    }

                }
            }
        }

        $limit = $global_settings['paginationnunber'];
        $total = ceil(count($files) / $limit);

        $page = Utilities::getInput('paged', 'POST', 'string');
        $page = $page!= '' ? $page : 1;
        $offset = ($page - 1) * $limit;
        if( $offset < 0 ) $offset = 0;

        if ($theme->getThemeName() != 'tree') {
            $files = array_slice( $files, $offset, $limit );
        }
        //crop file titles
        foreach ($files as $i => $file) {
            $files[$i]->crop_title = wpfdBase::cropTitle($params,$theme->getThemeName(), $file->post_title);
        }

        $content = '';

        $options = array('files' => $files, 'category' => $category, 'categories' => $categories, 'params' => $params, 'tpl' => $tpl);
        if ($params['social'] == 1 && defined('WPFDA_VERSION')) {
            $content =  do_shortcode('[wpfdasocial]'.$theme->showCategory($options). ($category->params['theme'] != 'tree' ? wpfd_category_pagination(array(
                        'base' => '',
                        'format' => '',
                        'current' => max( 1, $page),
                        'total' => $total
                    )
                ) : '').'[/wpfdasocial]');
        } else {
            $content =  $theme->showCategory($options) .($category->params['theme'] != 'tree'  ? wpfd_category_pagination(array(
                        'base' => '',
                        'format' => '',
                        'current' => max( 1, $page),
                        'total' => $total
                    )
                ) : '');
        }

        return $content;
    }
}