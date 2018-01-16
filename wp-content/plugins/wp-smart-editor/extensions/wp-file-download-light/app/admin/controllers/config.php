<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Controller;
use Joomunited\WPFramework\v1_0_2\Form;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Application;
defined( 'ABSPATH' ) || die();

class wpfdControllerConfig extends Controller {
    /**
     * Save file params
     */
    public function savetfileparams(){
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('wpfd')->getPath().DIRECTORY_SEPARATOR. 'admin'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'file_config.xml';
        if(!$form->load($formfile)){
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if(!$form->validate()){
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if(!$model->saveFileParams($datas)){
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config');
    }

    /**
     * Save config
     */
    public function saveconfig(){
        $model = $this->getModel();

        $form = new Form();
        if(!$form->load('config')){
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if(!$form->validate()){
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if(!$model->save($datas)){
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config');
    }
             
    public function getTokenKey(){
        $dropAuthor = Utilities::getInput('dropAuthor', 'POST','string');
        
        $app = Application::getInstance('wpfdAddon');
        require_once $app->getPath().DIRECTORY_SEPARATOR.$app->getType().DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'wpfdAddonDropbox.php';
       
        $Dropbox = new wpfdAddonDropbox();
        
        if(!empty($dropAuthor)){
            //convert code authorCOde to Token
           $list = $Dropbox->convertAuthorizationCode($dropAuthor);
        }
        if($list['accessToken']){
            $app = Application::getInstance('wpfdAddon');
            require_once $app->getPath().DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'wpfdHelper.php';
            //save accessToken to database
            $saveParams = new wpfdAddonHelper();
            $params = $saveParams->getAllDropboxConfigs();
            $params['dropboxToken'] = $list['accessToken'];
            $saveParams->saveDropboxConfigs($params);
        }
        $this->exit_status(true,$list);
    }
    
}

?>
