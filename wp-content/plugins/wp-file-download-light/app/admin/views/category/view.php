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
use Joomunited\WPFramework\v1_0_2\Form;

defined( 'ABSPATH' ) || die();

class wpfdViewCategory extends View {

    public function render($tpl = null) {
        $id_category = Utilities::getInt('id'); 
        if(empty($id_category)) { echo ''; wp_die(); }
        $modelCat = $this->getModel('category');
        $this->category = $modelCat->getCategory($id_category);
        $this->params = (array)$this->category->params;
        $modelConfig =  $this->getModel('config');
        $this->mainConfig = $modelConfig->getConfig();
        $this->themes =  $modelConfig->getThemes();

        if(Utilities::getInput('onlyTheme','GET','int') ) {
            $newTheme = Utilities::getInput('theme','GET','string');
            $defaultThemeConfig = $modelConfig->getThemeParams( $newTheme);        
            $this->params = wp_parse_args($this->params,$defaultThemeConfig);

            if (wpfdBase::checkExistTheme($newTheme)) {
                echo $this->loadTemplate('theme-'. $newTheme);
            } else {
                $dir = wp_upload_dir();
                $this->setPath($dir['basedir'] . '/wpfd-themes/wpfd-'. $newTheme.'/');
                echo $this->loadTemplate('theme-'. $newTheme);
            }

            die();
        }        
        $defaultThemeConfig = $modelConfig->getThemeParams($this->params['theme']);
        $this->params = wp_parse_args($this->params, $defaultThemeConfig);

        $form = new Form();
        if($form->load('category',(array)$this->category->params)){
            $this->form = $form->render();
        }

        parent::render($tpl);
    }
    
}