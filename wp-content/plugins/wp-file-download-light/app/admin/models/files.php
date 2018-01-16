<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Model;

defined('ABSPATH') || die();

class wpfdModelFiles extends Model
{
    /**
     * Get file by ordering
     * @param $id_category
     * @param string $ordering
     * @param string $ordering_dir
     * @return array
     */
    public function getFiles($id_category, $ordering = 'menu_order', $ordering_dir = 'ASC')
    {

        $modelConfig = $this->getInstance('config');

        $params = $modelConfig->getConfig();
        if ($ordering == 'ordering') $ordering = 'menu_order';
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'wpfd_file',
            'post_status' => 'any',
            'orderby' => $ordering,
            'order' => $ordering_dir,
            'tax_query' => array(
                array(
                    'taxonomy' => 'wpfd-category',
                    'terms' => (int)$id_category,
                    'include_children' => false
                )
            )

        );
        $results = get_posts($args);
        $files = array();

        $config = get_option('_wpfd_global_config');
        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download';
        } else {
            $seo_uri = $config['uri'];
        }
        $perlink = get_option('permalink_structure');
        $rewrite_rules = get_option('rewrite_rules');

        foreach ($results as $result) {

            $metaData = get_post_meta($result->ID, '_wpfd_file_metadata', true);

            $result->ext = isset($metaData['ext']) ? $metaData['ext'] : '';
            $result->hits = isset($metaData['hits']) ? (int)$metaData['hits'] : 0;
            $result->version = isset($metaData['version']) ? $metaData['version'] : '';
            $result->size = isset($metaData['size']) ? $metaData['size'] : 0;
            $result->created = mysql2date(wpfdBase::loadValue($params, 'date_format', get_option('date_format')), $result->post_date);
            $result->modified = mysql2date(wpfdBase::loadValue($params, 'date_format', get_option('date_format')), $result->post_modified);


            $term_list = wp_get_post_terms($result->ID, 'wpfd-category', array("fields" => "ids"));
            $wpfd_term = get_term($term_list[0], 'wpfd-category');
            $result->catname = sanitize_title($wpfd_term->name);
            if (!is_wp_error($term_list)) {
                $result->catid = $term_list[0];
            } else {
                $result->catid = 0;
            }

            $result->seouri = $seo_uri;

            if (!empty($rewrite_rules)) {
                if (strpos($perlink, 'index.php')) {
                    $result->linkdownload = get_site_url() . '/index.php/' . $seo_uri . '/' . $result->catid . '/' . $result->catname . '/' . $result->ID . '/' . $result->post_name;
                } else {
                    $result->linkdownload = get_site_url() . '/' . $seo_uri . '/' . $result->catid . '/' . $result->catname . '/' . $result->ID . '/' . $result->post_name;
                }
                if ($result->ext) {
                    $result->linkdownload .= '.' . $result->ext;
                };
            } else {
                $result->linkdownload = admin_url('admin-ajax.php') . '?juwpfisadmin=false&action=wpfd&task=file.download&wpfd_category_id=' . $result->catid . '&wpfd_file_id=' . $result->ID;
            }
            $files[] = $result;

        }

        $reverse = strtoupper($ordering_dir) == 'DESC' ? true : false;

        if ($ordering == 'size') {
            $files = wpfd_sort_by_property($files, 'ID', 'size', $reverse);
        } else if ($ordering == 'version') {
            $files = wpfd_sort_by_property($files, 'ID', 'version', $reverse);
        } else if ($ordering == 'hits') {
            $files = wpfd_sort_by_property($files, 'ID', 'hits', $reverse);
        } else if ($ordering == 'ext') {
            $files = wpfd_sort_by_property($files, 'ID', 'ext', $reverse);
        }

        return $files;

    }

    public function FileExt($fileName)
    {
        $pieces = explode('.', $fileName);
        return array_pop($pieces);

    }

    /**
     * Method to add a file into database
     * @param string $file
     * @param int $id_category
     * @return inserted row id, false if an error occurs
     */
    public function addFile($data, $remote_url = false)
    {
        global $wpdb;
        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();
        if ($remote_url) {
            $filename = $data['file'];
        } else {
            $filename = $wp_upload_dir['basedir'] . '/wpfd/' . $data['id_category'] . '/' . $data['file'];
        }
        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype(basename($filename), null);

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid' => $filename,
            'post_type' => 'wpfd_file',
            'post_mime_type' => $filetype['type'],
            'post_title' => $data['title'],
            'post_content' => '',
            'post_status' => 'publish'
        );
        $attach_id = wp_insert_post($attachment);
        if ($attach_id) {
            // Generate the metadata for the attachment, and update the database record.
            //$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            //wp_update_attachment_metadata( $attach_id, $attach_data );

            $metadata = array();
            $metadata['ext'] = $data['ext'];
            $metadata['size'] = $data['size'];
            $metadata['hits'] = 0;
            $metadata['version'] = '';
            $metadata['file'] = $data['file'];
            $metadata['remote_url'] = $remote_url;
            update_post_meta($attach_id, '_wpfd_file_metadata', $metadata);

            wp_set_post_terms($attach_id, $data['id_category'], 'wpfd-category');
        }


        return $attach_id;
    }

    /**
     * Methode to retrieve the next file ordering for a category
     * @param int $id_category
     * @return int next ordering
     */
    private function getNextPosition($id_category)
    {
        global $wpdb;
        $query = 'SELECT ordering FROM ' . $wpdb->prefix . 'wpfd_files WHERE catid=' . (int)$id_category . ' ORDER BY ordering DESC LIMIT 0,1';
        if ($wpdb->query($query) === false) {
            return false;
        }
        $ordering = $wpdb->get_var(null);
        if ($ordering > 0) {
            return $ordering + 1;
        }
        return 0;
    }


    /**
     * reorder file
     * @param $files
     * @return bool
     */
    public function reorder($files)
    {
        global $wpdb;

        foreach ($files as $key => $file) {
            $wpdb->update($wpdb->posts, array('menu_order' => $key), array('ID' => intval($file)));
        }
        return true;
    }

}