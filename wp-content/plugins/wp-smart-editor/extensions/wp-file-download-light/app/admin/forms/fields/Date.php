<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

namespace Joomunited\WP_File_Download\Admin\Fields;

use Joomunited\WPFramework\v1_0_2\Field;
use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Application;
defined( 'ABSPATH' ) || die();

class Date extends Field {

        public function getfield($field){
                $attributes = $field['@attributes'];

                $html = '';
            $tooltip = isset($attributes['tooltip']) ? $attributes['tooltip'] : '';
                $html .= '<div class="control-group">';
                if(!empty($attributes['label']) && $attributes['label']!='' && !empty($attributes['name']) && $attributes['name']!=''){
                        $html .= '<label title="'.$tooltip.'" class="control-label" for="'.$attributes['name'].'">'.__($attributes['label'],  Application::getInstance('wpfd')->getName()).'</label>';
                }
                $html.= '<div class="controls">';

                $html .= '<div class="input-append">
                    <input type="text" name="'.$attributes['name'].'" id="'.$attributes['name'].'" value="'.$attributes['value'].'" maxlength="45" class="'.$attributes['class'].'">
                    <button type="button" class="btn" id="'.$attributes['name'].'_img"><span class="icon-calendar"></span> </button>
                </div>';

                $html .= '</div></div>';

                return $html;
        }

}