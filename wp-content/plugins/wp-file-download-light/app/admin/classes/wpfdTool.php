<?php

/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

// no direct access
defined('ABSPATH') or die();
use Joomunited\WPFramework\v1_0_2\Application;

class wpfdTool {
    /**
     * Show error message
     */
    function wpfd_import_notice(){
        global $wpdb;
        if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."wpfd_categories'") == $wpdb->prefix."wpfd_categories") {
            echo '<script type="text/javascript">'.PHP_EOL
		. 'function importWpfdTaxonomy(doit,button){'.PHP_EOL
		    .'jQuery(button).find(".spinner").show().css({"visibility":"visible"});'.PHP_EOL
		    .'jQuery.post(ajaxurl, {action: "wpfd_import",doit:doit}, function(response) {'.PHP_EOL
			.'jQuery(button).closest("div#wpfd_error").hide();'.PHP_EOL
			.'if(doit===true){'.PHP_EOL
			    .'jQuery("#wpfd_error").after("<div class=\'updated\'> <p><strong>'. __('Categories imported into taxanomies. Enjoy!!!','wp-smart-editor') .'</strong></p></div>");'.PHP_EOL
			.'}'.PHP_EOL
                        .'window.location.reload(true);'.PHP_EOL
		    .'});'.PHP_EOL
		. '}'.PHP_EOL
	    . '</script>';
            echo '<div class="error" id="wpfd_error">'
                    . '<p>'
                    . __('You\'ve just installed new version WP File Download, You can import your categories into taxonomies','wp-smart-editor')
                        . '<a href="#" class="button button-primary" style="margin: 0 5px;" onclick="importWpfdTaxonomy(true,this);" id="wpfdImportBtn">'.__('Import categories now','wp-smart-editor').' <span class="spinner" style="display:none"></span></a> or <a href="#" onclick="importWpfdTaxonomy(false,this);" style="margin: 0 5px;" class="button">'.__('No thanks ','wp-smart-editor').' <span class="spinner" style="display:none"></span></a>'
                    . '</p>'
                . '</div>';
       } else {
           wpfdTool::createCategoryIfNoneExist();         
       }
}

    /**
     * Import categories
     */
    static function wpfd_import_categories() {
      $option_import_taxo = get_option('_wpfd_import_notice_flag');
        if(isset($option_import_taxo) && $option_import_taxo == 'yes'){
            die();
        }
      
        if($_POST['doit']==='true'){
           
            $app = Application::getInstance('wpfd');
            require_once $app->getPath().DIRECTORY_SEPARATOR.$app->getType().DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'categories.php';            
            $modelCats = new wpfdModelCategories();
            $categories = $modelCats->getCategoriesOld();
            if(!$categories ) {
                 if($_POST['doit']==='true'){
                    update_option('_wpfd_import_notice_flag', 'yes');
                }else{
                    update_option('_wpfd_import_notice_flag', 'no');
                }
                die();
            }
            
            require_once $app->getPath().DIRECTORY_SEPARATOR.$app->getType().DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'category.php';            
            $modelCat = new wpfdModelCategory();
            $termsRel = array('0'=>0);
            foreach ($categories as $category) {
                $inserted = wp_insert_term($category->title, 'wpfd-category',array('slug'=>sanitize_title($category->title)));
                if ( is_wp_error($inserted) ) {
                    //try again
                    $inserted = wp_insert_term($category->title, 'wpfd-category',array('slug'=>sanitize_title($category->title).'-'.time() ));
                    if ( is_wp_error($inserted) ) {
                        wp_send_json($inserted->get_error_message());
                    }                    
                }
                
                $modelCat->updateTermOrder($inserted['term_id'],$category->lft);
			
                $termsRel[$category->id] = $inserted['term_id'];
            }
            foreach ($categories as $category) {
                wp_update_term($termsRel[$category->id], 'wpfd-category',array('parent'=>$termsRel[$category->parent_id]));
            }

             //update files to attachments
            global $wpdb;
            $query = 'SELECT f.* FROM '.$wpdb->prefix.'wpfd_files as f ORDER BY f.ordering ASC';
            $result = $wpdb->query($query);            
            if($result===false){
                return false;
            }
            $files = stripslashes_deep($wpdb->get_results($query,OBJECT)); 
            // Get the path to the upload directory.
            $wp_upload_dir = wp_upload_dir();
            
            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            foreach ($files as $file) {
                                
                $filename = $wp_upload_dir['basedir'].'/wpfd/'.$file->catid.'/'.$file->file ;
                //move file to new term_id: $termsRel[$file->catid]
                if(file_exists($filename)) {
                    $file_dir = wpfdBase::getFilesPath($termsRel[$file->catid]);
                    if(!file_exists($file_dir)){
                        mkdir($file_dir,0777,true);
                        $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                        $tmpfile = fopen($file_dir.'index.html', 'w');
                        fwrite($tmpfile, $data);
                        fclose($tmpfile);
                        $data = 'deny from all';
                        $tmpfile = fopen($file_dir.'.htaccess', 'w');
                        fwrite($tmpfile, $data);
                        fclose($tmpfile);
                    }
                    $newFile = $wp_upload_dir['basedir'].'/wpfd/'. $termsRel[$file->catid] .'/'.$file->file ;
                    copy($filename, $newFile);
                    $filename = $newFile;
                }
                    
                // Check the type of file. We'll use this as the 'post_mime_type'.
                $filetype = wp_check_filetype( basename( $filename ), null );
                $post_title = $file->title;
                if(empty($post_title)) $post_title = preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
                // Prepare an array of post data for the attachment.
                $attachment = array(
                        'guid'           => $wp_upload_dir['baseurl'].'/wpfd/'.$termsRel[$file->catid] . '/' . basename( $filename ), 
                        'post_type'      => 'wpfd_file',
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => $post_title,
                        'post_excerpt'  =>  $file->description,
                        'post_content'   => '',
                        'post_status'    => 'publish',
                        'menu_order'    => $file->ordering
                );
                $attach_id =  wp_insert_post( $attachment );            
                if($attach_id) {
                    // Generate the metadata for the attachment, and update the database record.
                    //$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                    //wp_update_attachment_metadata( $attach_id, $attach_data );
                    
                    $metadata = array();                    
                    $metadata['ext'] =  $file->ext;
                    $metadata['size'] =  $file->size;
                    $metadata['hits'] =  $file->hits;
                    $metadata['version'] =  $file->version;      
                    $metadata['file'] =  $file->file;    
                    update_post_meta( $attach_id, '_wpfd_file_metadata', $metadata );
                    
                    $termsArray = array();                
                    $termsArray[] = $termsRel[$file->catid];                
                    wp_set_post_terms( $attach_id, $termsArray, 'wpfd-category');
                }
            }
                    
        }else { //if there isn't any categories then create one   
            wpfdTool::createCategoryIfNoneExist();
        }
       
        if($_POST['doit']==='true'){
            update_option('_wpfd_import_notice_flag', 'yes');
        }else{
            update_option('_wpfd_import_notice_flag', 'no');
        }
        die();
    }

    /**
     * Create category if not exist
     */
    static function createCategoryIfNoneExist() {
        $app = Application::getInstance('wpfd');
        require_once $app->getPath().DIRECTORY_SEPARATOR.$app->getType().DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'categories.php';            
        $modelCats = new wpfdModelCategories();
        $cats = $modelCats->getSubCategories(0);
        if(count($cats)==0) { //if there isn't any categories then create one              
            require_once $app->getPath().DIRECTORY_SEPARATOR.$app->getType().DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'category.php';            
            $modelCat = new wpfdModelCategory();
            $cat_id = $modelCat->addCategory(__('New category','wp-smart-editor'));
        }
    }

    /**
     * Delete all data
     */
    function deleteAllData() {
        $taxonomy = 'wpfd-category';
        $terms = get_terms($taxonomy);
        $count = count($terms);
        if ( $count > 0 ){

            foreach ( $terms as $term ) {
                wp_delete_term( $term->term_id, $taxonomy );
            }
        }
        
        //delete posts and meta key
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'wpfd_file' ,
            'orderby' => 'menu_order', 
            'order' => 'ASC'                     
        );
        $results = get_posts( $args ); 
        if(count($results)>0) {
            foreach ($results as $result) {
                  // Delete's each post.
                wp_delete_post( $result->ID, true);
                delete_post_meta($result->ID, '_wpfd_file_metadata');
            }
        }
       
    }
}
