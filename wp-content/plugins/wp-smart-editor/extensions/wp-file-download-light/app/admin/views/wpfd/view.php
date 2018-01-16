<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\View;

defined( 'ABSPATH' ) || die();

class wpfdViewWpfd extends View {

    public function render($tpl = null) {
        $modelCat = $this->getModel('categories');
        $this->categories = $modelCat->getCategories();
        parent::render($tpl);
    }

}