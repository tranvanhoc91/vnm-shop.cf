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

class wpfdModelCategories extends Model
{

    /**
     * Get categories
     * @return array
     */
    public function getCategories()
    {

        $results = array();
        $root = new stdClass();
        $root->level = -1;
        $root->term_id = 0;
        $this->getCategoriesRecursive($root, $results);

        $results = $this->extractOwnCategories($results);

        return $results;
    }

    public function getCategoriesRecursive($cat, &$results)
    {
        if (!is_array($results)) {
            $results = array();
        }
        $categories = get_terms('wpfd-category', 'orderby=term_group&hierarchical=1&hide_empty=0&parent=' . $cat->term_id);
        if ($categories) {
            foreach ($categories as $category) {
                $category->level = $cat->level + 1;
                $results[] = $category;
                $this->getCategoriesRecursive($category, $results);
            }
        }
    }

    /**
     * Get sub categories by parent category
     * @param $parent
     * @return mixed
     */
    public function getSubCategories($parent)
    {
        $categories = get_terms('wpfd-category', 'orderby=term_group&hierarchical=1&hide_empty=0&parent=' . $parent);
        return $categories;
    }

    public function getCategoriesOld()
    {
        global $wpdb;
        $query = 'SELECT c.* FROM ' . $wpdb->prefix . 'wpfd_categories as c WHERE c.level >0 ORDER BY c.lft ASC';
        $result = $wpdb->query($query);
        if ($result === false) {
            return false;
        }
        return stripslashes_deep($wpdb->get_results($query, OBJECT));
    }

    /**
     * Extract categories for the user having own category permission
     * @param $items
     * @return array
     */
    public function extractOwnCategories($items)
    {

        $user_id = get_current_user_id();
        $is_edit_all = false;
        if (user_can($user_id, 'wpfd_edit_category')) {
            // Allows edit all categories
            $is_edit_all = true;
        } else {
            $user_categories = (array)get_user_meta($user_id, 'wpfd_user_categories', true);
        }

        if (!empty($items) && !$is_edit_all) {

            foreach ($items as $key_cat => $cat) {
                if (!in_array($cat->term_id, $user_categories)) {
                    unset($items[$key_cat]);
                }
            }

            //reset index array
            $items = array_values($items);
        }

        return $items;

    }
}