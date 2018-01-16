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
use Joomunited\WPFramework\v1_0_2\Model;

defined( 'ABSPATH' ) || die();

class wpfdControllerFile extends Controller {

    public function download($id=0, $catid=0, $preview=0){
        if(empty($catid)) {
            $catid = Utilities::getInput('wpfd_category_id','GET','none');
        }
        if(empty($id)) {
            $id = Utilities::getInput('wpfd_file_id', 'GET', 'none');
        }
        if(empty($preview)) {
            $preview = Utilities::getInput('preview', 'GET', 'none');
        }
        if(empty($id) || empty($catid))  exit();

        $modelCategory = Model::getInstance('category');       
        $modelConfig = Model::getInstance('config');
        $config = $modelConfig->getGlobalConfig();
        $model = $this->getModel();

        //check if catid is correct category of file
        if(apply_filters('wpfdAddonCategoryFrom',$catid) == 'googleDrive'){
            $catid = apply_filters('wpfdAddonDownloadCheckGoogleDriveCategory',$catid,$id);
            if(empty($catid)) {
                exit( __('Download url is not correct','wp-smart-editor') );
            }
        }elseif(apply_filters('wpfdAddonCategoryFrom',$catid) == 'dropbox'){
            $catid = apply_filters('wpfdAddonDownloadCheckDropboxCategory',$catid,$id);
            if(empty($catid)) {
                exit( __('Download url is not correct','wp-smart-editor') );
            }
        }else{
            $file_catid = $model->getFileCategory($id);
            if($catid != $file_catid) {
                exit( __('Download url is not correct','wp-smart-editor') );
            }
        }
         //check access
            $category = $modelCategory->getCategory($catid); 
            if(empty($category) || is_wp_error($category)) exit( __('Category is not correct','wp-smart-editor') );
            if($category->access==1) {
                $user = wp_get_current_user();
                $roles = array();
                foreach($user->roles as $role){
                    $roles[] = strtolower($role);
                }
                $allows = array_intersect($roles, $category->roles);

                if(empty($allows)) {
                    $token = Utilities::getInput('token','GET','string');
                    $modelTokens =  Model::getInstance('tokens');
                    $modelTokens->removeTokens();
                    $tokenId = $modelTokens->tokenExists($token);
                    if($tokenId){
                        $modelTokens->updateToken($tokenId);
                    } else {

                        if (isset($category->params['canview']) && !empty($category->params['canview']) ) {
                            if ((int)$category->params['canview'] != 0 && (int)$category->params['canview'] != $user->ID) {
                                exit( __('You don\'t have permission','wp-smart-editor') );
                            }
                        } else {
                            exit( __('Not authorized','wp-smart-editor') );
                        }
                    }
                }
        }
      
        /**
         * download file from WP FileDownload when not exist $fileInfo or wpfdAddon not active
         */
        if(apply_filters('wpfdAddonCategoryFrom',$catid) == 'googleDrive'){
            /**
             * download file from Google Drive when $fileInfo exist
             */
            $file = apply_filters('wpfdAddonDownloadGoogleDriveFile', $id);
            if ($preview == '1') {
                $contenType = wpfdHelperFile::mime_type($file->ext);
            } else {
                if ($file->ext == 'pdf' && $config['open_pdf_in'] == 1) {
                    $contenType = wpfdHelperFile::mime_type($file->ext);
                } else {
                    $contenType = 'application/octet-stream';
                }
            }
            $filedownload = $file->title.'.'.$file->ext;
            $this->downloadHeader($filedownload, (int)$file->size, $contenType,$config, $file, $preview);
            echo $file->datas;
            
        }elseif(apply_filters('wpfdAddonCategoryFrom',$catid) == 'dropbox'){
            
            list($file,$fMeta) = apply_filters('wpfdAddonDownloadDropboxFile',$id,$catid); 
            $ext = strtolower(pathinfo($fMeta['path_display'], PATHINFO_EXTENSION));        

            if ($preview == '1') {
                $contenType = wpfdHelperFile::mime_type($ext);
            } else {
                if ($file->ext == 'pdf' && $config['open_pdf_in'] == 1) {
                    $contenType = wpfdHelperFile::mime_type($file->ext);
                } else {
                    $contenType = 'application/octet-stream';
                }
            }
            
            //incr hits
            $fileInfos = wpfdAddonHelper::getDropboxFileInfos();
            if(!empty($fileInfos)){
                $hits = $fileInfos[$catid][$id]['hits']+1;
                $fileInfos[$catid][$id]['hits'] = $hits;                 
            }
            
             wpfdAddonHelper::setDropboxFileInfos($fileInfos);
             
            $this->downloadHeader($fMeta['path_display'], filesize($file), $contenType,$config, $file, $preview);
            	
            readfile($file);
            unlink($file);            
            
        }else{
             $model = $this->getModel();
           
            $file = $model->getFullFile($id);
            $file_meta = get_post_meta( $id, '_wpfd_file_metadata', true );

            $remote_url = isset($file_meta['remote_url']) ? $file_meta['remote_url'] : false;

            $model->hit($id);
            $model->addCountChart($id);

            //todo : verifier les droits d'acces à la catéorgie du fichier
            if(!empty($file) && $file->ID){

                $filename = $file->post_name;
                if($filename==''){
                    $filename = 'download';
                }
                if ($remote_url) {
                    $url = $file_meta['file'];
                    header("Location: $url");
                } else {

                    $preview = Utilities::getInput('preview', 'GET', 'none');
                    if($preview == '1') {
                        $contenType = wpfdHelperFile::mime_type($file->ext);
                    }else {
                        if ($file->ext == 'pdf' && $config['open_pdf_in'] == 1) {
                            $contenType = wpfdHelperFile::mime_type($file->ext);
                        } else {
                            $contenType = 'application/octet-stream';
                        }
                    }

                }
            $sysfile = wpfdBase::getFilesPath($file->catid).'/'.$file->file;
                if (file_exists($sysfile)) {
                    $filedownload = $filename.'.'.$file->ext;
                    $this->downloadHeader($filedownload, filesize($sysfile), $contenType,$config, $file, $preview );
                    
                    readfile($sysfile);
                } else {
                    exit(__('File not found', 'wp-smart-editor'));
                }

            }
        }

        exit();
    }

    /**
     * Download header file
     * @param $file
     * @param $size
     * @param $contenType
     */
    public function downloadHeader($file,$size,$contenType, $config, $ob, $preview){
        while (ob_get_level()) ob_end_clean();
        ob_start();
        if ($config['open_pdf_in'] == 1 && $ob->ext == 'pdf' && $preview == 1) {
            header('Content-Disposition: inline; filename="' . basename($file) . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        }
        header('Content-Type: ' . $contenType);
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        if ($size != 0) {
            header('Content-Length: ' . $size);
        }
        ob_clean();
        flush();
    }
    
}

?>