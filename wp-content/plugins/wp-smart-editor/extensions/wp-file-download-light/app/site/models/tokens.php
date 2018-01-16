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

defined( 'ABSPATH' ) || die();

class WpfdModelTokens extends Model {      
    /**
     * Create a new token
     */
    public function createToken(){   
        $token = md5(uniqid(mt_rand(), true));
        $tokens = get_option('_wpfd_tokens', array());
        $tokens[$token] = time();
        update_option('_wpfd_tokens', $tokens, false);
        return $token;
    }
    
    /**
     * Methode to check if a token exists
     * @param int $token 
     * @return object file, false if an error occurs
     */
    public function tokenExists($token){
        $tokens = get_option('_wpfd_tokens', array());
        if(array_key_exists($token, $tokens)) {
            return $token;
        }
        return false;
    }
    
    /**
     * Methode to update a token
     * @param int $id_file 
     * @return object file, false if an error occurs
     */
    public function updateToken($id){
        $tokens = get_option('_wpfd_tokens', array());
        $tokens[$id] = time();
        update_option('_wpfd_tokens',$tokens, false);
    }
    
     /**
     * Method to delete all tokens
     * @return number of affected rows, false if an error occurs
     */
    public function removeTokens(){
         $tokens = get_option('_wpfd_tokens', array());
         $time = time() - 15 * 60; //15 mins
         if(count($tokens)>0) {
             foreach ($tokens as $key => $val) {
                 if((int)$val < $time) {
                     unset($tokens[$key]);
                 }
             }
         }
         
        update_option('_wpfd_tokens',$tokens, false);
    }
}
