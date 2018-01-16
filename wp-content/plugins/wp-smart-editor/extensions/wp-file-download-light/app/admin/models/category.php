<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Model;

defined('ABSPATH') || die();

class wpfdModelCategory extends Model
{

    /**
     * Add new category by title
     * @param $title
     * @return bool
     */
    public function addCategory($title)
    {

        $title = trim(sanitize_text_field($title));
        if ($title == '') {
            return false;
        }
        $inserted = wp_insert_term($title, 'wpfd-category', array('slug' => sanitize_title($title)));
        if (is_wp_error($inserted)) {
            //try again
            $inserted = wp_insert_term($title, 'wpfd-category', array('slug' => sanitize_title($title) . '-' . time()));
            if (is_wp_error($inserted)) {
                wp_send_json($inserted->get_error_message());
            }
        }
        $lastCats = get_terms('wpfd-category', 'orderby=term_group&order=DESC&hierarchical=0&hide_empty=0&parent=0&number=1');
        if (is_array($lastCats) && count($lastCats)) {
            $this->updateTermOrder($inserted['term_id'], $lastCats[0]->term_group + 1);
        }
        return $inserted['term_id'];

    }


    /**
     * Update term order
     * @param $term_id
     * @param $order
     */
    function updateTermOrder($term_id, $order)
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "
                                        UPDATE $wpdb->terms SET term_group = '%d' WHERE term_id ='%d'
                                    ",
            $order,
            $term_id
        )
        );

    }

    /**
     * Change order tree categories
     * @param $tree
     * @return bool
     */
    public function changeOrder($tree)
    {
        $result = count($tree);
        for ($i = 0; $i < $result; $i++) {
            $node = $tree[$i];
            $idCategory = $node['idCategory'];
            $children = isset($node['children']) ? (array)$node['children'] : array();
            wp_update_term($idCategory, 'wpfd-category', array('parent' => 0));
            $this->updateOrder($idCategory, $children, $i);
        }
        return true;
    }

    public function updateOrder($idCategory, $children, $order)
    {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "
					UPDATE $wpdb->terms SET term_group = '%d' WHERE term_id ='%d'
				",
            $order,
            $idCategory
        ));


        if (count($children)) {
            for ($i = 0; $i < count($children); $i++) {
                wp_update_term($children[$i]['idCategory'], 'wpfd-category', array(
                    'parent' => $idCategory
                ));
                $children1 = isset($children[$i]['children']) ? (array)$children[$i]['children'] : array();
                $this->updateOrder($children[$i]['idCategory'], $children1, $i);
            }
        }
    }

    /**
     * Get child categories
     * @param $id
     * @return array
     */
    public function getChildren($id)
    {
        $results = array();
        $this->getChildrenRecursive($id, $results);
        return $results;

    }

    public function getChildrenRecursive($catid, &$results)
    {
        if (!is_array($results)) {
            $results = array();
        }
        $categories = get_terms('wpfd-category', 'orderby=term_group&hierarchical=1&hide_empty=0&parent=' . $catid);
        if ($categories) {
            foreach ($categories as $category) {
                $results[] = $category->term_id;
                $this->getChildrenRecursive($category->term_id, $results);
            }
        }
    }

    /**
     * Get category by ID
     * @param $id
     * @return mixed
     */
    public function getCategory($id)
    {

        $result = get_term($id, 'wpfd-category');

        $modelConfig = $this->getInstance('Config');
        $main_config = $modelConfig->getConfig();

        if (!empty($result) && !is_wp_error($result)) {
            $term_meta = get_option("taxonomy_$id");
            //$result->params = isset($term_meta['params'])? $term_meta['params']: array();
            if ($result->description == 'null' || $result->description == '') {
                $result->params = array();
            } else {
                $result->params = json_decode($result->description, true);
            }

            if (!isset($result->params['theme'])) {
                $result->params['theme'] = $main_config['defaultthemepercategory'];
            }
            $canview = 0;
            $categoryOwn = 0;
            if (!isset($result->params['canview'])) {
                $result->params['canview'] = 0;
            } else {
                $canview = $result->params['canview'];
            }

            if (!isset($result->params['category_own'])) {
                $categoryOwn = $result->params['category_own'] = get_current_user_id();
            } else {
                $categoryOwn = $result->params['category_own'];
            }

            $ordering = isset($result->params['ordering']) ? $result->params['ordering'] : 'title';
            $orderingdir = isset($result->params['orderingdir']) ? $result->params['orderingdir'] : 'desc';

            if ($main_config['catparameters'] == '0') {
                $result->params = $modelConfig->getThemeParams($main_config['defaultthemepercategory']);
                $result->params['theme'] = $main_config['defaultthemepercategory'];
                $result->params['canview'] = $canview;
                $result->params['category_own'] = $categoryOwn;
            }

            $result->roles = isset($term_meta['roles']) ? (array)$term_meta['roles'] : array();
            $result->access = isset($term_meta['access']) ? (int)$term_meta['access'] : 0;
            $result->ordering = $ordering;
            $result->orderingdir = $orderingdir;

        }

        return $result;
    }

    /**
     * Save category param
     * @param $id
     * @param $params
     * @return bool
     */
    public function saveParams($id, $params)
    {

        $datas = json_encode($params);

        if (isset($params['category_own']) && $params['category_own'] != '') {
            //$user_id = get_current_user_id();

            if ($params['category_own']) {

                $user_categories = get_user_meta($params['category_own'], 'wpfd_user_categories', true);

                if (is_array($user_categories)) {
                    if (!in_array($id, $user_categories)) {
                        $user_categories[] = $id;
                    }
                } else {
                    $user_categories[] = $id;
                }

                if ($params['category_own_old'] != '' && $params['category_own_old'] != $params['category_own']) {
                    $user_categories_old = get_user_meta($params['category_own_old'], 'wpfd_user_categories', true);
                    $user_categories_old = array_diff($user_categories_old, array($id));
                    $user_categories_old = array_values($user_categories_old);
                    update_user_meta($params['category_own_old'], 'wpfd_user_categories', $user_categories_old);
                }

                update_user_meta($params['category_own'], 'wpfd_user_categories', $user_categories);
            }

        }
        $updated = wp_update_term($id, 'wpfd-category', array('description' => $datas));
        if (is_wp_error($updated)) {
            return false;
        }
        return true;
    }

    /**
     * save access, roles category
     * @param $id
     * @param $params
     * @return bool
     */
    public function save($id, $params)
    {

//        $term_meta = get_option( "taxonomy_$id" );
        $visibility = $params['visibility'];

        $params['access'] = $visibility;
        if (!isset($params['roles'])) {
            $roles = array();
        } else {
            $roles = $params['roles'];
        }
        if ($visibility == '1') {
            $params['roles'] = $roles;
        }
        update_option("taxonomy_$id", $params);

        return true;
    }
}