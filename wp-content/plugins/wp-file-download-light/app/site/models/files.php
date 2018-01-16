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

defined( 'ABSPATH' ) || die();

class WpfdModelFiles extends Model{

    /**
     * Get files by ordering
     * @param $category
     * @param string $ordering
     * @param string $ordering_dir
     * @return array
     */
	function getFiles($category, $ordering = 'menu_order', $ordering_dir = 'ASC'){


        $modelCat = $this->getInstance('category');
        $categorys = $modelCat->getCategory($category);
        $modelConfig = $this->getInstance('config');
        $params = $modelConfig->getGlobalConfig();
        $user = wp_get_current_user();
        $roles = array();
        foreach($user->roles as $role){
            $roles[] = strtolower($role);
        }
        
        $modelTokens =  Model::getInstance('tokens');        
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
            
        if($ordering=='ordering') $ordering = 'menu_order';

        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'wpfd_file' ,
            'orderby' => $ordering,
            'order' => $ordering_dir,
            'tax_query' => array(
                array(
                 'taxonomy' => 'wpfd-category',
                 'terms' => (int)$category,
                 'include_children' => false
                )
            ),
            'suppress_filters' => false
        );
        $results = get_posts( $args );
        $files =  array();

        $viewer_type = wpfdBase::loadValue($params, 'use_google_viewer', 'lightbox') ;
        $extension_viewer = explode(',', wpfdBase::loadValue($params, 'extension_viewer', 'pdf,ppt,doc,xls,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4'));
        $extension_viewer = array_map('trim', $extension_viewer);
        $user = wp_get_current_user();
        $user_id = $user->ID;

        foreach ($results as $result) {

            $ob = new stdClass();
            $metaData = get_post_meta($result->ID,'_wpfd_file_metadata',true);
            if (wpfdBase::loadValue($params, 'restrictfile', 0)== 1) {
                $canview = isset($metaData['canview']) ? $metaData['canview'] : 0;
                if ($canview!=0 && $canview != $user_id) {
                    continue;
                }
            }
            $remote_url = isset($file_meta['remote_url']) ? $file_meta['remote_url'] : false;

            $ob->ID = $result->ID ;
            $ob->post_title = $result->post_title ;
            $ob->post_name = $result->post_name ;
            $ob->ext = isset($metaData['ext'])? $metaData['ext']: '' ;
            $ob->hits = isset($metaData['hits'])?(int)$metaData['hits']: 0 ;
            $ob->versionNumber =  isset($metaData['version'])? $metaData['version']: '' ;
            $ob->version = '';
            $ob->description =  $result->post_excerpt ;
            $ob->size =  isset($metaData['size'])? $metaData['size']: 0 ;
            $ob->created = mysql2date(wpfdBase::loadValue($params, 'date_format', get_option('date_format')), $result->post_date);
            $ob->modified = mysql2date(wpfdBase::loadValue($params, 'date_format', get_option('date_format')), $result->post_modified);
            
            $term_list = wp_get_post_terms($result->ID, 'wpfd-category', array("fields" => "ids"));     
            $wpfd_term = get_term($term_list[0], 'wpfd-category');
            $ob->catname = sanitize_title($wpfd_term->name);
            $ob->cattitle = $wpfd_term->name;
            if( !is_wp_error($term_list) ) {
                $ob->catid= $term_list[0];
            }else {
                $ob->catid= 0;
            }
            
             if($viewer_type != 'no' &&
                in_array($ob->ext, $extension_viewer)
                && ($remote_url == false)){
                 $ob->viewer_type = $viewer_type;
                 $ob->viewerlink = wpfdHelperFile::isMediaFile($ob->ext) ? wpfdHelperFile::getMediaViewerUrl($result->ID, $ob->ext) : wpfdHelperFile::getViewerUrl($result->ID,$ob->catid,$token);
            }

            $open_pdf_in = wpfdBase::loadValue($params, 'open_pdf_in', 0) ;

            if ($open_pdf_in == 1 && $ob->ext == 'pdf') {
                $ob->openpdflink = wpfdHelperFile::getPdfUrl($result->ID,$ob->catid,$token). '&preview=1';
            }
            $config = get_option('_wpfd_global_config');
            if(empty($config) || empty($config['uri'])){
                $seo_uri = 'download';
            }else{
                $seo_uri = $config['uri'];
            }
            $ob->seouri = $seo_uri;
            $perlink = get_option('permalink_structure');
            $rewrite_rules = get_option('rewrite_rules');
            
            if(!empty($rewrite_rules)){
                if(strpos($perlink, 'index.php')){
                    $ob->linkdownload = get_site_url().'/index.php/'.$seo_uri.'/'.$ob->catid.'/'.$ob->catname.'/'.$result->ID.'/'.$result->post_name;
                }else{
                    $ob->linkdownload = get_site_url().'/'.$seo_uri.'/'.$ob->catid.'/'.$ob->catname.'/'.$result->ID.'/'.$result->post_name;
                }
                if($ob->ext) { $ob->linkdownload .= '.'.$ob->ext; };
            }else{
                $ob->linkdownload = admin_url('admin-ajax.php').'?juwpfisadmin=false&action=wpfd&task=file.download&wpfd_category_id='.$ob->catid.'&wpfd_file_id='.$result->ID;
            }
            //crop file titles
            $ob->crop_title = wpfdBase::cropTitle($categorys->params,$categorys->params['theme'], $result->post_title);
            $files[] = $ob;
        }

        $reverse = strtoupper($ordering_dir) == 'DESC' ? true : false;

        if ($ordering == 'size') {
            $files = wpfd_sort_by_property($files, 'ID', 'size', $reverse);
        } else if ($ordering == 'version') {
            $files = wpfd_sort_by_property($files, 'ID', 'version', $reverse);
        } else if ($ordering == 'hits') {
            $files = wpfd_sort_by_property($files, 'ID', 'hits', $reverse);
        } else if ($ordering == 'ext') {
            $files = wpfd_sort_by_property($files, 'ID', 'ext', $reverse);
        }

        return $files;

        }

}