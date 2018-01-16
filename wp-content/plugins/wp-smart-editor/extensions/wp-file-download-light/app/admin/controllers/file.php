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

defined('ABSPATH') || die();

class wpfdControllerFile extends Controller
{
    /**
     * Download file
     */
    public function download()
    {
        $model = $this->getModel();
        $id = Utilities::getInt('id');
        $version = Utilities::getInput('version', 'GET', 'string');
        $catid = Utilities::getInt('catid');
        if (apply_filters('wpfdAddonCategoryFrom', $catid) == 'dropbox') {
            $id_file = Utilities::getInput('id', 'GET', 'string');
            $vid = Utilities::getInput('vid', 'GET', 'string');
            if ($version) {
                apply_filters('wpfdAddonDownloadVersion', $id_file, $vid);
            }
            exit();
        } else {
            $file = $model->getFile($id);
            $remote_url = false;
            if (!$version) {
                $file = $model->getFile($id);
                $file_meta = get_post_meta($id, '_wpfd_file_metadata', true);
                $remote_url = isset($file_meta['remote_url']) ? $file_meta['remote_url'] : false;
                $url = $file_meta['file'];
            } else {
                $vid = Utilities::getInt('vid');
                $version = $model->getVersion($vid);
                if ($version) {
                    $file = array_merge($file, $version);
                    if ($version['remote_url']) {
                        $remote_url = true;
                        $url = $version['file'];
                    }
                }
            }

            //todo : verifier les droits d'acces à la catéorgie du fichier
            if (!empty($file) && $file['ID']) {
                $filename = preg_replace('/[^a-zA-Z0-9-_\.]/', '', $file['title']);
                if ($filename == '') {
                    $filename = 'download';
                }
                if ($remote_url) {
                    header("Location: $url");
                } else {

                    $contenType = 'application/octet-stream';
                    $sysfile = wpfdBase::getFilesPath($file['catid']) . '/' . $file['file'];
                    if (file_exists($sysfile)) {
                        @ob_end_clean();
                        ob_start();
                        header('Content-Description: File Transfer');
                        header('Content-Type: ' . $contenType);
                        header('Content-Disposition: attachment; filename=' . basename($filename . '.' . $file['ext']));
                        header('Content-Transfer-Encoding: binary');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($sysfile));
                        ob_clean();
                        flush();
                        readfile($sysfile);
                    }

                }
            }

            exit();
        }

    }

    /**
     * Restore file
     */
    public function restore()
    {
        $id_file = Utilities::getInt('id');
        $vid = Utilities::getInt('vid');
        $catid = Utilities::getInt('catid');
        if (apply_filters('wpfdAddonCategoryFrom', $catid) == 'dropbox') {
            $id_file = Utilities::getInput('id', 'GET', 'string');
            $vid = Utilities::getInput('vid', 'GET', 'string');
            $version = apply_filters('wpfdAddonRestoreVersion', $id_file, $vid);
            $this->exit_status($version);
        } else {
            $model = $this->getModel();
            $file = $model->getFile($id_file);
            $version = $model->getVersion($vid);

            $result = false;
            if ($version) {
                $result = $model->updateFile($id_file, array(
                    'title' => $file['title'],
                    'file' => $version['file'],
                    'ext' => $version['ext'],
                    'size' => $version['size'],
                    'version' => $version['version'],
                    'remote_url' => $version['remote_url']
                ));

                $result = (bool)$model->deleteVersion($vid);
                $this->exit_status(true);
            }
            $this->exit_status(false);
        }
    }

    /**
     * Delete file version
     */
    public function deleteVersion()
    {

        $idCategory = Utilities::getInt('catid');
        if (apply_filters('wpfdAddonCategoryFrom', $idCategory) == 'googleDrive') {
            $id_file = Utilities::getInput('id_file', 'GET', 'none');
            $vid = Utilities::getInput('vid', 'GET', 'none');
            if (apply_filters('wpfdAddonDeleteVersion', $idCategory, $id_file, $vid)) {
                $this->exit_status(true, array());
            } else {
                $this->exit_status('error validating');
            }
        } else {
            $vid = Utilities::getInt('vid');
            $model = $this->getModel();
            $id_file = Utilities::getInput('id_file', 'GET', 'none');
            $file = $model->getFile($id_file);
            $version = $model->getVersion($vid);
            $file_dir = wpfdBase::getFilesPath($file['catid']) . '/' . $version['file'];
            $result = (bool)$model->deleteVersion($vid);
            if ($result) {
                if (file_exists($file_dir)) {
                    unlink($file_dir);
                }
            }
            $this->exit_status($result);
        }

    }

    /**
     * Save file
     */
    public function save()
    {
        $model = $this->getModel();

        $form = new Form();
        if (!$form->load('file')) {
            $this->exit_status('error');
        }
//        if (!$form->validate()) {
//            $this->exit_status('error validating');
//        }

        $idCategory = Utilities::getInt('idCategory');
        if (apply_filters('wpfdAddonCategoryFrom', $idCategory) == 'googleDrive') {
            $data = $form->sanitize();
            $fileId = Utilities::getInput('id', 'Get', 'none');
            $data['id'] = $fileId;
            if (apply_filters('wpfdAddonSaveFileInfo', $data)) {
                $this->exit_status(true);
            } else {
                $this->exit_status('error saving');
            }

        } elseif (apply_filters('wpfdAddonCategoryFrom', $idCategory) == 'dropbox') {
            $data = $form->sanitize();
            $fileId = Utilities::getInput('id', 'Get', 'none');
            $data['id'] = $fileId;
            $new_id = apply_filters('wpfdAddonSaveDropboxFileInfo', $data, $idCategory);
            if (!empty($new_id)) {
                $this->exit_status(true, array('new_id' => $new_id));
            } else {
                $this->exit_status('error saving');
            }
        } else {
            $datas = $form->sanitize();
            $datas['id'] = Utilities::getInt('id');
            $datas['description'] = $_POST['description'];
            if (!$model->save($datas)) {
                $this->exit_status('error saving');
            }
            $this->exit_status(true);
        }
    }

    /**
     * Delete file
     */
    public function delete()
    {
        $idCategory = Utilities::getInt('id_category');
        $whereDelete = apply_filters('wpfdAddonCategoryFrom', $idCategory);

        if ($whereDelete === 'googleDrive') {
            $id_file = Utilities::getInput('id_file', 'GET', 'string');
            do_action("wpfdAddonDeleteGoogleCloudFiles", $idCategory, $id_file);
        } elseif ($whereDelete === 'dropbox') {
            $id_file = Utilities::getInput('id_file', 'GET', 'string');
            do_action("wpfdAddonDeleteDropboxFiles", $idCategory, $id_file);
        } else {
            $id_file = Utilities::getInt('id_file');
            $model = $this->getModel();
            $versions = $model->getVersions($id_file,$idCategory);
            $file = $model->getFile($id_file);
            if (!empty($versions)) {
                foreach ($versions as $key => $value) {
                    $version = $model->getVersion($value['meta_id']);
                    $file_dir = wpfdBase::getFilesPath($file['catid']) . '/' . $version['file'];
                    $result = (bool)$model->deleteVersion($value['meta_id']);
                    if ($result) {
                        if (file_exists($file_dir)) {
                            unlink($file_dir);
                        }
                    }
                }
            }
            if (!empty($file)) {
                if ($model->delete($id_file)) {
                    $file_dir = wpfdBase::getFilesPath($file['catid']);
                    if (file_exists($file_dir . $file['file'])) {
                        unlink($file_dir . $file['file']);
                        $this->exit_status(true);
                    }
                }
                $this->exit_status('error while deleting');
            }
        }
    }
}

?>