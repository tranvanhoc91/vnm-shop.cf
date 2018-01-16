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

class wpfdViewFiles extends View {

    public function render($tpl = null) {
        $id_category = Utilities::getInt('id_category'); 
        if(empty($id_category)) return '';
        $model = $this->getModel();

        $category_model = $this->getModel('Category');;
      
        $this->category = $category_model->getCategory($id_category);

        $this->ordering = Utilities::getInput('orderCol','GET','none') != null ? Utilities::getInput('orderCol','GET','none') : $this->category->ordering;
        $this->orderingdir = Utilities::getInput('orderDir','GET','none') != null ? Utilities::getInput('orderDir','GET','none') : $this->category->orderingdir;


        $modelConfig = $this->getModel('Config');

        $this->params = $modelConfig->getConfig();

        if(apply_filters('wpfdAddonCategoryFrom', $id_category) == 'googleDrive') {
            $this->files = apply_filters('wpfdAddonDisplayGoogleDriveCategories', $id_category);           
        }
        elseif(apply_filters('wpfdAddonCategoryFrom', $id_category) == 'dropbox') {
            $this->files = apply_filters('wpfdAddonDisplayDropboxCategories', $id_category); 
        }
        else {
            $this->files = $model->getFiles($id_category, $this->ordering, $this->orderingdir);            
        }

        parent::render($tpl);
    }
}