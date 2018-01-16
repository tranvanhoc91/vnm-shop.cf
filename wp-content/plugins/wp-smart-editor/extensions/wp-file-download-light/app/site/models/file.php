<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */
use Joomunited\WPFramework\v1_0_2\Application;
use Joomunited\WPFramework\v1_0_2\Model;

defined('ABSPATH') || die();

class WpfdModelFile extends Model
{

    function getFileCategory($id_file) {
        $catid =  0;
        $term_list = wp_get_post_terms($id_file, 'wpfd-category', array("fields" => "ids"));

        if (!is_wp_error($term_list)) {
            $catid = $term_list[0];
        }

        return $catid;
    }

    /**
     * Get file info by ID
     * @param $id_file
     * @return mixed
     */
    function getFile($id_file,$rootcat=0)
    {
        $app = Application::getInstance('wpfd', __FILE__);
        $modelConfig = $this->getInstance('config');
        $params = $modelConfig->getGlobalConfig();


        $user = wp_get_current_user();
        $roles = array();
        foreach ($user->roles as $role) {
            $roles[] = strtolower($role);
        }

        $row = get_post($id_file, OBJECT);

        if ($row == false || $row->post_status == 'private' || $row->post_date > current_time('mysql')) {
            return false;
        }

        $row->title = $row->post_title;
        $row->description = trim($row->post_excerpt);

        $row->created = mysql2date(wpfdBase::loadValue($params, 'date_format', get_option('date_format')), $row->post_date);
        $row->modified = mysql2date(wpfdBase::loadValue($params, 'date_format', get_option('date_format')), $row->post_modified);

        $metadata = get_post_meta($id_file, '_wpfd_file_metadata', true);
        if (count($metadata)) {
            foreach ($metadata as $key => $value) {
                $row->$key = $value;
            }
        }

        $term_list = wp_get_post_terms($id_file, 'wpfd-category', array("fields" => "ids"));
        $wpfd_term = get_term($term_list[0], 'wpfd-category');
        $row->catname = sanitize_title($wpfd_term->name);
        $row->cattitle = $wpfd_term->name;
        if (!is_wp_error($term_list)) {
            $row->catid = $term_list[0];
        } else {
            $row->catid = 0;
        }

        $remote_url = isset($metadata['remote_url']) ? $metadata['remote_url'] : false;
        $viewer_type = wpfdBase::loadValue($params, 'use_google_viewer', 'lightbox');

        $extension_viewer = explode(',', wpfdBase::loadValue($params, 'extension_viewer', 'pdf,ppt,doc,xls,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4'));
        $extension_viewer = array_map('trim', $extension_viewer);

        if ($viewer_type != 'no' &&
            in_array($row->ext, $extension_viewer)
            && ($remote_url == false)
        ) {
            $row->viewer_type = $viewer_type;

            $modelCategory = Model::getInstance('category');
            //check access
            $category = $modelCategory->getCategory($row->catid);
            $rootcategory = $modelCategory->getCategory($rootcat);
            if (empty($category) || is_wp_error($category)) return;

            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);

            if (wpfdHelperFile::isMediaFile($row->ext)) {
                if ($category->access == 1) {
                    if (!empty($allows)) {
                        $row->viewerlink = wpfdHelperFile::getMediaViewerUrl($row->ID, $row->ext);
                    }
                } else {
                    $row->viewerlink = wpfdHelperFile::getMediaViewerUrl($row->ID, $row->ext);
                }

            } else {
                $modelTokens = Model::getInstance('tokens');
                $sessionToken = isset($_SESSION['wpfdToken']) ? $_SESSION['wpfdToken'] : null;
                if ($sessionToken === null) {
                    $token = $modelTokens->createToken();
                    $_SESSION['wpfdToken'] = $token;
                } else {
                    $tokenId = $modelTokens->tokenExists($sessionToken);
                    if ($tokenId) {
                        $modelTokens->updateToken($tokenId);
                        $token = $sessionToken;
                    } else {
                        $token = $modelTokens->createToken();
                        $_SESSION['wpfdToken'] = $token;
                    }
                }
                if ($category->access == 1) {
                    if (!empty($allows)) {
                        $row->viewerlink = wpfdHelperFile::getViewerUrl($row->ID, $row->catid, $token);
                    }
                } else {
                    $row->viewerlink = wpfdHelperFile::getViewerUrl($row->ID, $row->catid, $token);
                }

            }
            //crop file titles
            $row->crop_title = $row->post_title;
            if ($rootcat){
                $row->crop_title = wpfdBase::cropTitle($rootcategory->params,$rootcategory->params['theme'], $row->post_title);
            }else{
                $row->crop_title = wpfdBase::cropTitle($category->params,$category->params['theme'], $row->post_title);
            }
        }

        $open_pdf_in = wpfdBase::loadValue($params, 'open_pdf_in', 0) ;

        if ($open_pdf_in == 1 && $row->ext == 'pdf') {
            $row->openpdflink = wpfdHelperFile::getPdfUrl($row->ID,$row->catid,$token). '&preview=1';
        }

        $config = get_option('_wpfd_global_config');
        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download';
        } else {
            $seo_uri = $config['uri'];
        }
        $row->seouri = $seo_uri;

        $perlink = get_option('permalink_structure');
        $rewrite_rules = get_option('rewrite_rules');
        if (!empty($rewrite_rules)) {
            if (strpos($perlink, 'index.php')) {
                $row->linkdownload = get_site_url() . '/index.php/' . $seo_uri . '/' . $row->catid . '/' . $row->catname . '/' . $row->ID . '/' . $row->post_name;
            } else {
                $row->linkdownload = get_site_url() . '/' . $seo_uri . '/' . $row->catid . '/' . $row->catname . '/' . $row->ID . '/' . $row->post_name;
            }
            if (isset($row->ext) && $row->ext) {
                $row->linkdownload .= '.' . $row->ext;
            };
        } else {
            $row->linkdownload = admin_url('admin-ajax.php') . '?juwpfisadmin=false&action=wpfd&task=file.download&wpfd_category_id=' . $row->catid . '&wpfd_file_id=' . $row->ID;
        }

        return $row;
    }

    /**
     * get full info for file
     * @param $id_file
     * @return bool
     */
    function getFullFile($id_file)
    {
        $app = Application::getInstance('wpfd');
        $modelConfig = $this->getInstance('config');
        $params = $modelConfig->getGlobalConfig();

        $row = get_post($id_file, OBJECT);
        if ($row === false) {
            return false;
        }

        require_once $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'wpfdBase.php';

        $ob = new stdClass();
        $ob->ID = $row->ID ;
        $ob->post_name = $row->post_name ;
        $ob->title = $row->post_title;
        $ob->description = $row->post_excerpt;
        $ob->created = mysql2date(wpfdBase::loadValue($params, 'date_format', get_option('date_format')), $row->post_date);
        $ob->modified = mysql2date(wpfdBase::loadValue($params, 'date_format', get_option('date_format')), $row->post_modified);
        $metadata = get_post_meta($id_file, '_wpfd_file_metadata', true);
        if (count($metadata)) {
            foreach ($metadata as $key => $value) {
                $ob->$key = $value;
            }
        }

        $term_list = wp_get_post_terms($id_file, 'wpfd-category', array("fields" => "ids"));
        if (!is_wp_error($term_list)) {
            $ob->catid = $term_list[0];
        } else {
            $ob->catid = 0;
        }

        return $ob;

    }

    /**
     * increase hit for file
     * @param $id_file
     * @return bool
     */
    public function hit($id_file)
    {

        $metadata = get_post_meta($id_file, '_wpfd_file_metadata', true);
        $hits = (int)$metadata['hits'];
        $metadata['hits'] = $hits + 1;
        update_post_meta($id_file, '_wpfd_file_metadata', $metadata);
        return true;
    }

    /**
     * Add a file for statistic when downloading file
     * @param $file_id
     * @param $date
     */
    public function addChart($file_id, $date)
    {
        global $wpdb;
        $query = "INSERT INTO {$wpdb->prefix}wpfd_statistics (related_id,type,date,count) VALUES (" . esc_sql($file_id) . ",'default','" . esc_sql($date) . "',1)";
        $wpdb->query($query);
    }

    /**
     * Add file to chart
     * @param $file_id
     * @return bool
     */
    public function addCountChart($file_id)
    {
        global $wpdb;
        $date = date("Y-m-d");
        $querycheck = "SELECT * FROM {$wpdb->prefix}wpfd_statistics WHERE related_id=" . esc_sql($file_id) . " AND date='" . esc_sql($date) . "'";

        $object = $wpdb->get_row($querycheck);;

        if ($object) {
            $query = "UPDATE {$wpdb->prefix}wpfd_statistics SET count=(count+1) WHERE related_id=" . esc_sql($file_id) . " AND date='" . esc_sql($date) . "'";
            $wpdb->query($query);
        } else {
            $this->addChart($file_id, $date);
        }
        return true;
    }
}