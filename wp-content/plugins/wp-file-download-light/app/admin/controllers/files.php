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

defined('ABSPATH') || die();

class wpfdControllerFiles extends Controller
{

    private $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'tar', 'rar', 'odt', 'ppt', 'pps', 'txt');

    /**
     * Upload a file
     */
    public function upload()
    {
        $id_category = Utilities::getInt('id_category', 'POST');
        if ($id_category <= 0) {
            $this->exit_status(__('Wrong Category', 'wp-smart-editor'));
        }
        if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            $this->exit_status(__('Wrong http response', 'wp-smart-editor'));
        }

        $configModel = $this->getModel('config');
        $allowed = $configModel->getAllowedExt();
        if (!empty($allowed)) {
            $this->allowed_ext = $allowed;
        }

        $pic = $_FILES['pic'];
        $ext = strtolower(pathinfo($pic['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowed_ext)) {
            $this->exit_status(__('This type of file is not allowed to be uploaded. You can add new file types in the plugin configuration', 'wp-smart-editor'), array('allowed ' => $this->allowed_ext));
        }

        $placeUpload = apply_filters('wpfdAddonCategoryFrom', $id_category);
        if ($placeUpload == 'googleDrive') {
            $dataReturn = apply_filters('wpfdAddonGoogleDriveUpload', $id_category, $_FILES);
            $this->exit_status($dataReturn['status'], $dataReturn['data']);
        } elseif ($placeUpload == 'dropbox') {
            $dataReturn = apply_filters('wpfdAddonDropboxUpload', $id_category, $_FILES);
            $this->exit_status($dataReturn['status'], $dataReturn['data']);
        } else {
            //todo: vérifier les erreurs de création de fichier
            $file_dir = wpfdBase::getFilesPath($id_category);
            if (!file_exists($file_dir)) {
                mkdir($file_dir, 0777, true);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                $file = fopen($file_dir . 'index.html', 'w');
                fwrite($file, $data);
                fclose($file);
                $data = 'deny from all';
                $file = fopen($file_dir . '.htaccess', 'w');
                fwrite($file, $data);
                fclose($file);
            }

            if (array_key_exists('pic', $_FILES) && $_FILES['pic']['error'] == 0) {

                $newname = uniqid() . '.' . $ext;
                if (!move_uploaded_file($pic['tmp_name'], $file_dir . $newname)) {
                    $this->exit_status(__('Cant move uploaded file', 'wp-smart-editor'));
                }
                chmod($file_dir . $newname, 0777);

                //Insert new image into databse
                $model = $this->getModel();
                $file_title = pathinfo($pic['name'], PATHINFO_FILENAME);
                $file_title = str_replace("|","/",$file_title);
                $id_file = $model->addFile(array(
                    'title' => base64_decode( $file_title),
                    'id_category' => (int)$id_category,
                    'file' => $newname,
                    'ext' => $ext,
                    'size' => filesize($file_dir . $newname)
                ));
                if (!$id_file) {
                    unlink($file_dir . $newname);
                    $this->exit_status(__('Cant save to database', 'wp-smart-editor'));
                }
                $this->exit_status(true, array('id_file' => $id_file, 'name' => $newname));
            }
            $this->exit_status(__('Error while uploading', 'wp-smart-editor')); //todo : translate
        }
    }

    /**
     * Reorder category
     */
    public function reorder()
    {
        $files = Utilities::getInput('order', 'GET', 'string');

        $files = json_decode(stripslashes_deep($files));
        $model = $this->getModel();
        if ($model->reorder($files)) {
            $return = true;
        } else {
            $return = false;
        }
        $return = json_encode($return);
        $this->exit_status($return);
    }

    /**
     * Get version for file
     */
    public function version()
    {
        $idCategory = Utilities::getInt('id_category', 'POST');
        if (apply_filters('wpfdAddonCategoryFrom', $idCategory) == 'googleDrive') {
            $id_file = Utilities::getInput('id_file', 'POST', 'none');
            if (!$id_file) {
                $this->exit_status(__('Wrong file', 'wp-smart-editor'));
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
                $this->exit_status(__('Wrong http response', 'wp-smart-editor'));
            }

            if (array_key_exists('pic', $_FILES) && $_FILES['pic']['error'] == 0) {
                $pic = $_FILES['pic'];
                $ext = strtolower(pathinfo($pic['name'], PATHINFO_EXTENSION));
                if (!in_array(strtolower($ext), $this->allowed_ext)) {
                    $this->exit_status(__('This type of file is not allowed to be uploaded. You can add new file types in the plugin configuration', 'wp-smart-editor'), array('allowed ' => $this->allowed_ext));
                }
                $fileContent = file_get_contents($pic['tmp_name']);

                if (apply_filters(
                    'wpfdAddonUploadVersion',
                    array('id' => $id_file,
                        'newRevision' => true,
                        'title' => $pic['name'],
                        'data' => $fileContent,
                        'ext' => strtolower(($pic['name']))),
                    $idCategory
                )) {
                    $this->exit_status(true, array('id_file' => $id_file, 'name' => $pic['name']));
                } else {
                    $this->exit_status(__('Can\'t upload to google Drive', 'wp-smart-editor'));
                }

            }
        } elseif (apply_filters('wpfdAddonCategoryFrom', $idCategory) == 'dropbox') {
            $id_file = Utilities::getInput('id_file', 'POST', 'none');
            if (!$id_file) {
                $this->exit_status(__('Wrong file', 'wp-smart-editor'));
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
                $this->exit_status(__('Wrong http response', 'wp-smart-editor'));
            }

            if (array_key_exists('pic', $_FILES) && $_FILES['pic']['error'] == 0) {
                $pic = $_FILES['pic'];
                $ext = strtolower(pathinfo($pic['name'], PATHINFO_EXTENSION));
                if (!in_array(strtolower($ext), $this->allowed_ext)) {
                    $this->exit_status(__('This type of file is not allowed to be uploaded. You can add new file types in the plugin configuration', 'wp-smart-editor'), array('allowed ' => $this->allowed_ext));
                }
                $fileInfo = apply_filters('wpfdAddonDropboxGetFileInfo', $id_file, $idCategory);
                if (strpos($pic['name'], $fileInfo['ext'])) {
                    if (apply_filters(
                        'wpfdAddonUploadDropboxVersion',
                        array('newRevision' => true,
                            'old_file' => $id_file,
                            'new_file_name' => $pic['name'],
                            'new_file_size' => $pic['size'],
                            'new_tmp_name' => $pic['tmp_name']
                        ),
                        $idCategory
                    )) {
                        $this->exit_status(true, array('id_file' => $id_file, 'name' => $pic['name']));
                    } else {
                        $this->exit_status(__('can\'t upload to Dropbox', 'wp-smart-editor'));
                    }
                } else {
                    $this->exit_status(__('You need to upload a file which has same file type with current file. Dropbox only allow same file type for new version.', 'wp-smart-editor'));
                }
            }
        } else {
            $id_file = Utilities::getInt('id_file', 'POST');
            if ($id_file <= 0) {
                $this->exit_status(__('Wrong file', 'wp-smart-editor'));
            }

            if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
                $this->exit_status(__('Wrong http response', 'wp-smart-editor'));
            }

            if (array_key_exists('pic', $_FILES) && $_FILES['pic']['error'] == 0) {
                $pic = $_FILES['pic'];
                $ext = strtolower(pathinfo($pic['name'], PATHINFO_EXTENSION));
                if (!in_array(strtolower($ext), $this->allowed_ext)) {
                    $this->exit_status(__('This type of file is not allowed to be uploaded. You can add new file types in the plugin configuration', 'wp-smart-editor'), array('allowed ' => $this->allowed_ext));
                }

                $model = $this->getModel('file');
                $file = $model->getFile($id_file);
                if (file_exists(wpfdBase::getFilesPath($file['catid']) . $file['file'])) {
                    // unlink(wpfdBase::getFilesPath($file['catid']).$file['file']);
                }
                $newname = uniqid() . '.' . strtolower($ext);
                if (!move_uploaded_file($pic['tmp_name'], wpfdBase::getFilesPath($file['catid']) . $newname)) {
                    $this->exit_status(__('Cant move uploaded file', 'wp-smart-editor'));
                }
                chmod(wpfdBase::getFilesPath($file['catid']) . $newname, 0777);
                $result = $model->updateFile($id_file, array(
                    'title' => $file['title'],
                    'file' => $newname,
                    'ext' => $ext,
                    'size' => filesize(wpfdBase::getFilesPath($file['catid']) . $newname)
                ));

                if (!$result) {
                    unlink(wpfdBase::getFilesPath($file['catid']) . $newname);
                    $this->exit_status(__('Cant save to database', 'wp-smart-editor'));
                }

                //add old file into version history
                $model->addVersion($file);

                $this->exit_status(true, array('id_file' => $id_file, 'name' => $newname));
            }
        }
    }

    /**
     * Import files
     */
    public function import()
    {

        if (!is_admin()) {
            $this->exit_status(__('You don\'t have the sufficient permissions', 'wp-smart-editor'));
        }
        $id_category = Utilities::getInt('id_category');

        if ($id_category <= 0) {
            $this->exit_status(__('Category not found', 'wp-smart-editor'));
        }


        $modelCat = $this->getModel('category');
        $category = $modelCat->getCategory($id_category);


        $file_dir = wpfdBase::getFilesPath($id_category);
        if (!file_exists($file_dir)) {
            if (!file_exists($file_dir)) {
                mkdir($file_dir, 0777, true);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                $file = fopen($file_dir . 'index.html', 'w');
                fwrite($file, $data);
                fclose($file);
                $data = 'deny from all';
                $file = fopen($file_dir . '.htaccess', 'w');
                fwrite($file, $data);
                fclose($file);
            }
        }

        $files = Utilities::getInput('files', 'GET', 'none');

        if (!empty($files)) {
            $count = 0;

            $configModel = $this->getModel('config');
            $allowed = $configModel->getAllowedExt();
            if (!empty($allowed)) {
                $this->allowed_ext = $allowed;
            }

            foreach ($files as $file) {
                $file = get_home_path() . $file;

                if (!in_array(wpfd_getext($file), $this->allowed_ext)) {
                    $this->exit_status(__('This type of file is not allowed to be uploaded. You can add new file types in the plugin configuration', 'wp-smart-editor'), array('allowed ' => $this->allowed_ext));
                }

                $newname = uniqid() . '.' . strtolower(wpfd_getext($file));

                if (!copy($file, $file_dir . $newname)) {
                    $this->exit_status(__('Cant move uploaded file', 'wp-smart-editor'));
                }
                chmod($file_dir . $newname, 0777);
                //Insert new image into databse
                $model = $this->getModel();
                $id_file = $model->addFile(array(
                    'title' => preg_replace('#\.[^.]*$#', '', basename($file)),
                    'id_category' => $id_category,
                    'file' => $newname,
                    'ext' => strtolower(wpfd_getext($file)),
                    'size' => filesize($file_dir . $newname)
                ));

                if (!$id_file) {
                    unlink($file_dir . $newname);
                    $this->exit_status(__('Cannot save file to DB', 'wp-smart-editor'));
                }

                $count++;
            }
            $this->exit_status(true, array('nb' => $count));
        }

        $this->exit_status(__('Error while importing', 'wp-smart-editor'));
    }

    /**
     * Add a remote file url
     */
    public function addremoteurl()
    {

        $id_category = Utilities::getInt('id_category', 'GET');
        if ($id_category <= 0) {
            $this->exit_status(__('Wrong Category', 'wp-smart-editor'));
        }

        $remote_title = Utilities::getInput('remote_title', 'POST', 'string');
        $remote_url = Utilities::getInput('remote_url', 'POST', 'none');
        $remote_type = Utilities::getInput('remote_type', 'POST', 'none');

        if ($remote_title == null) {
            $this->exit_status(__('Enter title', 'wp-smart-editor'));
        } else if ($remote_type == null) {
            $this->exit_status(__('Enter type', 'wp-smart-editor'));
        } else if ($remote_url == null) {
            $this->exit_status(__('Enter url', 'wp-smart-editor'));
        } else {
            if (!preg_match("(http://|https://)", $remote_url)) {
                $this->exit_status(__($remote_url . " is not a valid URL"));
            }
        }
        //Insert new image into databse
        $model = $this->getModel();

        $id_file = $model->addFile(array(
            'title' => $remote_title,
            'id_category' => (int)$id_category,
            'file' => $remote_url,
            'ext' => $remote_type,
            'size' => wpfd_remote_file_size($remote_url)
        ), true);

        if (!$id_file) {
            $this->exit_status(__('Cant save to database', 'wp-smart-editor'));
        }

        $this->exit_status(true, array('id_file' => $id_file, 'name' => $remote_url));
    }

    public function showcolumn(){
        $check = Utilities::getInput('check','POST','none');
        $lists = Utilities::getInput('column_show','POST','none') != null ? Utilities::getInput('column_show','POST','none') : array();
        $string_lists = implode(',',$lists);
        setcookie('wpfd_show_columns', $string_lists, time() + (86400 * 30), "/");
        wp_send_json(true);
    }


}

?>
