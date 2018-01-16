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

class wpfdModelCategory extends Model {

    /**
     * Get a category info
     * @param $id
     * @return bool
     */
    public function getCategory($id){

        $result = get_term($id,'wpfd-category');
        $app = Application::getInstance('wpfd', __FILE__);
        $modelConfig =  $this->getInstance('Config');
        $main_config = $modelConfig->getGlobalConfig();

        if(!empty($result) && !is_wp_error($result)){
            $term_meta = get_option( "taxonomy_$id" );
            $result->name=html_entity_decode($result->name);
            if ($result->description == 'null' || $result->description == '') {
                $result->params = array();
            } else {
                $result->params = json_decode($result->description,true);
            }

            if(!isset($result->params['theme'])) {
                $result->params['theme'] = $main_config['defaultthemepercategory'];
            }

            $ordering = isset($result->params['ordering'])? $result->params['ordering']: 'title';
            $orderingdir = isset($result->params['orderingdir'])? $result->params['orderingdir']: 'desc';

            if ($main_config['catparameters'] == '0') {
                $result->params = array_merge( $result->params, $modelConfig->getConfig($main_config['defaultthemepercategory']) );
                $result->params['theme'] = $main_config['defaultthemepercategory'];
            }
            if(!empty($result->parent)) {
                $parentCat = get_term($result->parent,'wpfd-category');
                if(!empty($parentCat) && !is_wp_error($parentCat)){
                    $result->parent_title = $parentCat->name;    
                }                
            }
            $result->roles =  isset($term_meta['roles'])? (array)$term_meta['roles']: array();
            $result->access=  isset($term_meta['access'])? (int)$term_meta['access']: 0;
            $result->ordering =  $ordering;
            $result->orderingdir =  $orderingdir;
        }else {
            $result = false;
        }

        return $result;
    }

}