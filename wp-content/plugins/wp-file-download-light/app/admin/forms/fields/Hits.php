<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

namespace Joomunited\WP_File_Download\Admin\Fields;

use Joomunited\WPFramework\v1_0_2\Fields\Typeint;

defined( 'ABSPATH' ) || die();

class Hits extends Typeint {
    
    /**
    *  render <input> tag
    */
    public function getfield($field){
            $attributes = $field['@attributes'];
            $html = '';
            if(!empty($attributes['value'])){
                $attributes['value'] = (int)$attributes['value'];
            }else{
                $attributes['value'] = 0;
            }
            $html .= '<div class="form-inline">';
            $html.='<input type="text" readonly="true" ';
            if (!empty($attributes))
            {
                    foreach ($attributes as $attribute => $value)
                    {
                            if (in_array($attribute, array('id', 'class', 'name', 'value', 'size')) and isset($value))
                            {
                                    $html .= ' ' . $attribute . '="' . $value . '"';
                            }
                    }
            }
            $html .= ' />';
            $html .= '<button type="button" class="btn" onclick="jQuery(\'#'.$attributes['id'].'\').val(0);";" >'.__('Reset','wp-smart-editor').'</button><div class="clearfix"></div>';
            $html .= '</div>';
            return $html;
    }
}