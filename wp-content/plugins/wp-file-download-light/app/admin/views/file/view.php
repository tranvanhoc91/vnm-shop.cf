<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\View;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Form;

defined( 'ABSPATH' ) || die();

class wpfdViewFile extends View {
    public function render($tpl = null) {
        $model = $this->getModel('file');
        $idCategory = Utilities::getInt('idCategory');
        if (apply_filters('wpfdAddonCategoryFrom', $idCategory) == 'googleDrive') {
            $fileId = Utilities::getInput('id', 'GET', 'none');
            $datas = apply_filters('wpfdAddonGetFileInfo', $fileId);
        }
        elseif (apply_filters('wpfdAddonCategoryFrom', $idCategory) == 'dropbox') {
            $fileId = Utilities::getInput('id', 'GET', 'none');
            $datas = apply_filters('wpfdAddonDropboxGetFileInfo', $fileId,$idCategory);
        }
        else {
            $datas = $model->getfile(Utilities::getInt('id'));
        }

            $layout = Utilities::getInput('layout','GET','string');
            if($layout == 'versions') {
                $this->file_id = $datas['ID'];
                if(apply_filters('wpfdAddonCategoryFrom', $idCategory) == 'dropbox'){
                    $this->versions = apply_filters('wpfdAddonDropboxVersionInfo',$datas['ID'],$idCategory);
                }
                else{
                $this->versions = $model->getVersions($datas['ID'],$idCategory) ; //get_post_meta($datas['ID'], '_wpfd_file_versions', false);
                }
                parent::render($layout);
                wp_die();
            }
//        $application = Application::getInstance('wpfd');
//        require_once $application->getPath().DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR.'Hits.php';

            $form = new Form();
            if($form->load('file',$datas)){
                $this->form = $form->render('link');
            }

        $tags = get_terms( 'wpfd-tag', array(
            'orderby'    => 'count',
            'hide_empty' => 0,
        ) );

        $allTagsFiles = '';
        if ($tags) {
            foreach ($tags as $tag) {
                $allTagsFiles[] = ''.$tag->slug;
            }
            $this->allTagsFiles = '["'.implode('","',  $allTagsFiles ).'"]';
        } else {
            $this->allTagsFiles = '[]';
        }

            parent::render($tpl);
            wp_die();
        
    }
}