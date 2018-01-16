<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Controller;
use Joomunited\WPFramework\v1_0_2\Utilities;

defined( 'ABSPATH' ) || die();

class wpfdControllerCategories extends Controller {
    function getSubs() {
        
        $modelCats = $this->getModel('categories');        
        $cats = $modelCats->getCategories(Utilities::getInt('dir'));    
        if(count($cats)) {
            foreach ($cats as $cat) {
                $cat->count_child = $modelCats->getSubCategoriesCount($cat->term_id);
            }
        }
        echo json_encode($cats);
        die();
    }

    function getParentsCats() {
        $modelCats = $this->getModel('categories');
        $cats = $modelCats->getParentsCat(Utilities::getInt('id'), Utilities::getInt('displaycatid'));
        $cats = array_reverse($cats);
        echo json_encode($cats);
        die();
    }
}

?>