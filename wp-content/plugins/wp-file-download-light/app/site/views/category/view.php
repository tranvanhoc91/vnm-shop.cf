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

class wpfdViewCategory extends View {

    public function render($tpl = null) {

        $modelCat = $this->getModel('category');

        $content = new stdClass();
        $content->category = $modelCat->getCategory(Utilities::getInt('id'));
        echo json_encode($content);
        die();

    }
    
}