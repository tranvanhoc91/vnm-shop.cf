<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_2\Model;

defined( 'ABSPATH' ) || die();

class wpfdModelFile extends Model {

    /**
     * Get file info by ID
     * @param $id_file
     * @return bool
     */
    public function getFile($id_file){
        $row = get_post($id_file,ARRAY_A);
        if($row===false){
            return false;
        }
        $row['title'] = $row['post_title'];
        $row['description'] = $row['post_excerpt'];
        $metadata = get_post_meta($id_file,'_wpfd_file_metadata',true) ;
        if(count($metadata) ) {
            foreach ($metadata as $key => $value) {
                $row[$key] = $value;
            }
        }
        $row['state'] = ($row['post_status'] == 'publish') ? 1 : 0;;
        $row['publish'] = $row['post_date'];
        $term_list = wp_get_post_terms($id_file, 'wpfd-category', array("fields" => "ids"));
        if( !is_wp_error($term_list) ) {
            $row['catid']= $term_list[0];
        }else {
            $row['catid']= 0;
        }
        
        return stripslashes_deep($row);
    }

    /**
     * Save file data
     * @param $datas
     * @return bool
     */
    public function save($datas){

        $my_post = array(
            'ID'           => $datas['id'],
            'post_title'   => $datas['title'],
            'post_modified' => date('Y-m-d H:i:s'),
            'post_date' => $datas['publish'],
            'post_status' => $datas['state'] == 1 ? 'publish' : 'private',
            'post_excerpt' => $datas['description']
        );
        $my_post['post_name'] = sanitize_title($datas['title'], $datas['id']);
      // Update the post into the database
        wp_update_post( $my_post );
        
        $metadata = get_post_meta($datas['id'],'_wpfd_file_metadata',true)    ;
        $metadata['hits'] = $datas['hits'];
        $metadata['state'] = $datas['state'];
        $metadata['version'] = $datas['version'];
        $metadata['file_tags'] = $datas['file_tags'];
        $metadata['canview'] = $datas['canview'];
        $metadata['social'] = isset($datas['social']) ? $datas['social'] : 0;
        update_post_meta( $datas['id'], '_wpfd_file_metadata', $metadata );
        wp_set_post_terms( $datas['id'],  $datas['file_tags'], 'wpfd-tag');
        return true;
    }

    /**
     * Update a file
     * @param $id
     * @param $datas
     * @return bool
     */
    public function updateFile($id,$datas) {
          $my_post = array(
            'ID'           => $id,
            'post_title'   => $datas['title'],
            'post_modified' => date('Y-m-d H:i:s')            
        );

      // Update the post into the database
        wp_update_post( $my_post );
        
        $metadata = get_post_meta($id,'_wpfd_file_metadata',true)    ; 
        foreach ($datas as $key => $value) {
            if(isset($metadata[$key])) {
                $metadata[$key] = $value;
            }
        }
                     
        update_post_meta($id, '_wpfd_file_metadata', $metadata );
                          
        return true;
    }

    /**
     * Delete file
     * @param $id
     * @return bool
     */
    public function delete($id){
       if(!wp_delete_post( $id, true )) {
           return false;
       }      
        return true;
    }


    /**
     * Delete file version
     * @param $vid
     * @return mixed
     */
    public function deleteVersion($vid) {
        $result = delete_metadata_by_mid('post',$vid );
        return $result;
    }

    /**
     * add version for file
     * @param $file
     */
    public function addVersion($file) {        
                 
        $metadata = array();                    
        $metadata['ext'] =  $file['ext'] ;
        $metadata['size'] =  $file['size'];                    
        $metadata['version'] = $file['version'];                    
        $metadata['file'] =  $file['file'];     
        $metadata['remote_url'] =  isset($file['remote_url'])? $file['remote_url']: "";
        $metadata['created_time'] =  date('Y-m-d H:i:s');
        
        add_post_meta($file['ID'], '_wpfd_file_versions', $metadata);      
    }

    /**
     * Get version file
     * @param $vid
     * @return bool
     */
    public function getVersion($vid) {        
     
       $metaData = get_metadata_by_mid('post',$vid); 
       $version= false;
       if($metaData !== null) {
            $version = $metaData->meta_value;
            //$version['ID'] = $metaData->post_id;            
        }
        
        return $version; 
    }

    /**
     * Get all versions of file
     * @param $file_id
     * @param $idCategory
     * @return array
     */
    function getVersions( $file_id ,$idCategory) {
                
        global $wpdb;
        $query= $wpdb->prepare("SELECT * FROM
                $wpdb->postmeta WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC", $file_id, "_wpfd_file_versions") ;
        $results = $wpdb->get_results($query,ARRAY_A);
        $versions = array();
        if(!empty($results)) {
            foreach ($results as $result) {
                $version = unserialize($result['meta_value']);
                $version['meta_id'] = $result['meta_id'];
                $version['catid'] = $idCategory;
                $versions[] = $version;
            }
        }
        
        return $versions;
    } 
}