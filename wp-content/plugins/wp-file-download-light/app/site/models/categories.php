<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Model;

defined( 'ABSPATH' ) || die();

class wpfdModelCategories extends Model
{
    /**
     * get all categories by parent category
     * @param $idcategory
     * @return mixed
     */
	public function getCategories($idcategory){
        $user = wp_get_current_user();
        $roles = array();
        foreach($user->roles as $role){
            $roles[] = strtolower($role);
        }
        $result =  array();
        $categories = get_terms( 'wpfd-category', 'orderby=term_group&hierarchical=1&hide_empty=0&parent='.$idcategory);
        if($categories) {
           foreach ($categories as $category) {
               $category->name=html_entity_decode($category->name);
               $term_meta = get_option( "taxonomy_".$category->term_id );
               $cat_roles =  isset($term_meta['roles'])? (array)$term_meta['roles']: array();
               $cat_access=  isset($term_meta['access'])? (int)$term_meta['access']: 0;
               $params = json_decode($category->description, true);
               $allows_single = false;

               if (isset($params['canview']) && $params['canview'] != '') {
                   if (($params['canview'] != 0 ) && $params['canview'] == $user->ID) {
                       $allows_single = true;
                   }
               }

               if($cat_access == 1) {
                    $allows = array_intersect($roles, $cat_roles);
                    if($allows || $allows_single) $result[] = $category;
               }else {
                   $result[] = $category;
               }
           }
        }

        return stripslashes_deep($result);
    }

    public function getSubCategoriesCount($idcategory) {
        $count = wp_count_terms( 'wpfd-category', 'orderby=term_group&hierarchical=1&hide_empty=0&parent='.$idcategory);
        return $count;
    }

    /**
     * Get level categories
     * @return array
     */
    public function getLevelCategories(){

        $results  = array();
        $root = new stdClass();
        $root->level = 0;
        $root->term_id = 0;
        $this->getCategoriesRecursive($root,$results);
        
        $user = wp_get_current_user();
        $roles = array();
        foreach($user->roles as $role){
            $roles[] = strtolower($role);
        }
        if ($results) {
            foreach ($results as $key => $category) {
                $cat = get_term($category->term_id,'wpfd-category');
                $params = array();
                if ($cat->description != '') {
                    $params = json_decode($cat->description, true);
                }

                $term_meta = get_option( "taxonomy_".$category->term_id );
                $cat_roles =  isset($term_meta['roles'])? (array)$term_meta['roles']: array();
                $cat_access=  isset($term_meta['access'])? (int)$term_meta['access']: 0;
                if (isset($params['canview']) && ($params['canview'] == '')) {
                    $params['canview'] = 0;
                }
                if($cat_access == 1) {
                    $allows = array_intersect($roles, $cat_roles);

                    if (isset($params['canview']) && $params['canview'] != 0 && !count($cat_roles)) {
                        if ((int)$params['canview'] != $user->ID) {
                            unset($results[$key]);
                            continue;
                        }

                    } else if (isset($params['canview']) && $params['canview'] != 0 && count($cat_roles)) {
                        if ((int)$params['canview'] == $user->ID || !empty($allows)) {

                        } else {
                            unset($results[$key]);
                            continue;
                        }
                    } else {
                        if (empty($allows)) {
                            unset($results[$key]);
                            continue;
                        }
                    }

                }
                $results[$key] = apply_filters('wpfd_level_category', $category);
                $results[$key] = apply_filters('wpfd_level_category_dropbox',$category);
            }
        }
        return $results;
    }

    /**
     * get categories recursive
     * @param $cat
     * @param $results
     */
    public function getCategoriesRecursive($cat, &$results) {
        if(!is_array($results)){$results=array();}
        $categories = get_terms( 'wpfd-category', 'orderby=term_group&hierarchical=1&hide_empty=0&parent='.$cat->term_id );
        if($categories) {
            foreach ($categories as $category) {
                $category->level = $cat->level + 1;
                $results[] = $category;
                $this->getCategoriesRecursive($category,$results);
            }
        }
    }

    /**
     * get child categories
     * @param $catid
     * @return array
     */
    public function getChildCategories($catid) {
        $results=array();

        $categories = get_terms( 'wpfd-category', 'orderby=term_group&hierarchical=1&hide_empty=0&parent='.$catid );
        if($categories) {
            foreach ($categories as $category) {
                $results[] = $category;
                $this->getCategoriesRecursive($category,$results);
            }
        }
        return $results;
    }

    public function getParentsCat($catid, $displaycat) {
        $results = array();
        $results[] = $catid;
        $this->getParentCat($catid, $results, $displaycat);
        return $results;
    }
    public function getParentCat($catid, &$results, $displaycat) {

        if ($catid != 0) {
            $cat = get_term($catid,'wpfd-category');
            if ($cat->parent !=0 && $cat->parent != $displaycat) {
                $results[] = $cat->parent;
                $this->getParentCat($cat->parent, $results, $displaycat);
            }
        }

    }
}
