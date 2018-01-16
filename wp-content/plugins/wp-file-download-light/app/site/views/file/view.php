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

class wpfdViewFile extends View {
    public function render($tpl = null) {

        $categoryid = Utilities::getInput('categoryid','GET','none');  
        $idFile = Utilities::getInput('id','GET','none');

        $modelTokens = $this->getModel('tokens');
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

        if(apply_filters('wpfdAddonCategoryFrom', $categoryid) == 'googleDrive'){
             $file = apply_filters('wpfdAddonGetGoogleDriveFile',$idFile,$categoryid,$token);
        }elseif(apply_filters('wpfdAddonCategoryFrom', $categoryid) == 'dropbox'){
             $file = apply_filters('wpfdAddonGetDropboxFile',$idFile,$categoryid, $token);
        }
        if(!$file || ($file == $idFile) ){
             $model = $this->getModel('file');           
             $file = $model->getFile(Utilities::getInt('id'),Utilities::getInt('rootcat'));
        }

        //crop file titles
        $rootcat = Utilities::getInt('rootcat');
        $modelCat = $this->getModel('category');
        $categorys = $modelCat->getCategory($categoryid);
        $rootcategory = $modelCat->getCategory($rootcat);
        $file = (object)($file);
        if ($rootcat){
            $file->crop_title = wpfdBase::cropTitle($rootcategory->params,$rootcategory->params['theme'], $file->post_title);
        }else{
            $file->crop_title = wpfdBase::cropTitle($categorys->params,$categorys->params['theme'], $file->post_title);
        }
        if (!$file || ($file == $idFile) ) {
            return json_encode(new stdClass());
        }

        //fix : access check        
        $content = new stdClass();
        $content->file = $file;

        echo json_encode($content);
        exit();
    }
}