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
use Joomunited\WPFramework\v1_0_2\Filesystem;

defined( 'ABSPATH' ) || die();

class wpfdControllerCategory extends Controller {

    /**
     * Save file params
     */
    public function saveparams(){
        $modelRoles = $this->getModel('roles');
        $params = Utilities::getInput('params','POST','none');
        $id = Utilities::getInput('id','GET','int');
        $roles = isset($params['roles']) ? $params['roles'] : array();
        if(!$modelRoles->save($id, $params['visibility'],$roles)){
            $this->exit_status(false,'error while saving');
        }
        
        $model = $this->getModel();
        if(!$model->saveParams($id,$params)) {
            $this->exit_status(false,'error while saving params');
        }
         
        $this->exit_status(true);
    }

    public function listdir(){

        if(!is_admin()){
            return json_encode(array());
        }

        $modelConfig = $this->getModel('config');
        $config = $modelConfig->getConfig();
        $allowed_ext = explode(',', $config['allowedext']);
        foreach ($allowed_ext as $key => $value) {
            $allowed_ext[$key] = strtolower(trim($allowed_ext[$key]));
            if($allowed_ext[$key]==''){
                unset($allowed_ext[$key]);
            }
        }

        $path = get_home_path(). DIRECTORY_SEPARATOR;

        $dir = Utilities::getInput('dir', 'GET', 'none');

        $return = $dirs = $fi = array();

        if( file_exists($path.$dir) ) {
            $files = scandir($path.$dir);

            natcasesort($files);
            if( count($files) > 2 ) {
                // All dirs
                foreach( $files as $file ) {

                    if( file_exists($path . $dir . DIRECTORY_SEPARATOR . $file) && $file != '.' && $file != '..' && is_dir($path . $dir. DIRECTORY_SEPARATOR . $file) ) {
                        $dirs[] = array('type'=>'dir','dir'=>$dir,'file'=>$file);
                    }elseif( file_exists($path . $dir . DIRECTORY_SEPARATOR . $file) && $file != '.' && $file != '..' && !is_dir($path . $dir . DIRECTORY_SEPARATOR . $file) && in_array(wpfd_getext($file), $allowed_ext) ) {
                        $fi[] = array('type'=>'file','dir'=>$dir,'file'=>$file,'ext'=>strtolower(wpfd_getext($file)));
                    }
                }

                $return = array_merge($dirs,$fi);
            }
        }
        echo json_encode( $return );
        wp_die();
    }

}

?>
