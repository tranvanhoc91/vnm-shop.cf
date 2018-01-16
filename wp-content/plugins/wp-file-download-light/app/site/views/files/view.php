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
use Joomunited\WPFramework\v1_0_2\Model;

defined( 'ABSPATH' ) || die();

class wpfdViewFiles extends View {

    public function render($tpl = null) {


        $id_category = Utilities::getInt('id');
        $root_category = Utilities::getInt('rootcat');
        $modelCat = $this->getModel('category');
        $category = $modelCat->getCategory($id_category);
        $rootcategory = $modelCat->getCategory($root_category);

        $modelTokens = Model::getInstance('tokens');
        $sessionToken = isset($_SESSION['wpfdToken']) ? $_SESSION['wpfdToken'] : null ;
        if($sessionToken===null){
            $token = $modelTokens->createToken();
            $_SESSION['wpfdToken'] = $token;
        }else{
            $tokenId = $modelTokens->tokenExists($sessionToken);
            if($tokenId){
                $modelTokens->updateToken($tokenId);
                $token = $sessionToken;
            }else{
                $token = $modelTokens->createToken();
                $_SESSION['wpfdToken'] = $token;
            }
        }

        if (apply_filters('wpfdAddonCategoryFrom', $id_category) == 'googleDrive') {

            $ordering = Utilities::getInput('orderCol','GET','none') != null ? Utilities::getInput('orderCol','GET','none') : $category->ordering;
            $orderingdir = Utilities::getInput('orderDir','GET','none') != null ? Utilities::getInput('orderDir','GET','none') : $category->orderingdir;
            $files = apply_filters('wpfdAddonGetListGoogleDriveFile', $id_category, $ordering, $orderingdir,$category->slug,$token);

            $content = new stdClass();
            $content->files = $files;
            $content->category = $category;
        }elseif(apply_filters('wpfdAddonCategoryFrom', $id_category) == 'dropbox'){
            $ordering = Utilities::getInput('orderCol','GET','none') != null ? Utilities::getInput('orderCol','GET','none') : $category->ordering;
            $orderingdir = Utilities::getInput('orderDir','GET','none') != null ? Utilities::getInput('orderDir','GET','none') : $category->orderingdir;
            $files = apply_filters('wpfdAddonGetListDropboxFile', $id_category, $ordering, $orderingdir,$category->slug,$token);
          
            $content = new stdClass();
            $content->files = $files;
            $content->category = $category;
        } else {
            $modelFiles = $this->getModel('files');
            $ordering = Utilities::getInput('orderCol','GET','none') != null ? Utilities::getInput('orderCol','GET','none') : $category->ordering;
            $orderingdir = Utilities::getInput('orderDir','GET','none') != null ? Utilities::getInput('orderDir','GET','none') : $category->orderingdir;

            $content = new stdClass();
            $content->files = $modelFiles->getFiles($id_category, $ordering, $orderingdir);
            $content->category = $category;
        }

        $modelConfig = Model::getInstance('config');
        $global_settings = $modelConfig->getGlobalConfig();
        $limit = $global_settings['paginationnunber'];
        $page = Utilities::getInt('page');

        $total = ceil(count($content->files) / $limit);
        $page = (int)$page ? $page : 1;
        $offset = ($page - 1) * $limit;
        if( $offset < 0 ) $offset = 0;

        if ($rootcategory->params['theme'] != 'tree') {
            $content->files = array_slice( $content->files, $offset, $limit );
        }
        //crop file titles
        foreach ($content->files as $i => $file) {
            $content->files[$i]->crop_title = $file->post_title;
            if ($root_category){
                $content->files[$i]->crop_title = wpfdBase::cropTitle($rootcategory->params,$rootcategory->params['theme'], $file->post_title);
            }else{
                $content->files[$i]->crop_title = wpfdBase::cropTitle($category->params,$category->params['theme'], $file->post_title);
            }
        }

        $content->pagination = wpfd_category_pagination(array(
                'base' => '',
                'format' => '',
                'current' => max( 1, $page),
                'total' => $total
            )
        );

        echo json_encode($content);
        die();
    }
}