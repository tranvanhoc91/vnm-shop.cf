<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\View;
use Joomunited\WPFramework\v1_0_2\Utilities;

defined( 'ABSPATH' ) || die();

class wpfdViewCategories extends View {

    public function render($tpl = null) {
        $modelCats = $this->getModel('categories');
        $modelCat = $this->getModel('category');
        
        $content = new stdClass();
        $content->categories = $modelCats->getCategories(Utilities::getInt('id'));
        $content->category = $modelCat->getCategory(Utilities::getInt('id'));

        if(Utilities::getInt('id') == Utilities::getInt('top') ) {
            $content->category->parent = false;
        }

        echo json_encode($content);
        die();
    }
    
}