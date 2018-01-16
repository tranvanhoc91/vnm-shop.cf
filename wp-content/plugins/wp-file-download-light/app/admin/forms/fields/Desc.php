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
use Joomunited\WPFramework\v1_0_2\Model;
defined( 'ABSPATH' ) || die();

class Desc extends Field {

        public function getfield($field){
                $attributes = $field['@attributes'];

            $modelConfig = Model::getInstance('config');
            $config = $modelConfig->getConfig();
            $html = '';


            $tooltip = isset($attributes['tooltip']) ? $attributes['tooltip'] : '';
                $html .= '<div class="control-group">';
                if(!empty($attributes['label']) && $attributes['label']!='' && !empty($attributes['name']) && $attributes['name']!=''){
                        $html .= '<label title="'.$tooltip.'" class="control-label" for="'.$attributes['name'].'">'.__($attributes['label'],  Application::getInstance('wpfd')->getName()).'</label>';
                }
                $html.= '<div class="controls">';
            if ($config['useeditor'] == 0) {

                $html .= '<textarea name="' . $attributes['name'] . '" id="' . $attributes['name'] . '" class="'.$attributes['class'].'">'
                    . htmlspecialchars($attributes['value'], ENT_COMPAT, "UTF-8") . '</textarea>';

            } else {
                $settings = array(
                    'textarea_name' => $attributes['name'],
                    //'quicktags'     => array( 'buttons' => 'em,strong,link' ),
                    'media_buttons' => false,
                    'tinymce'       => array(
                        'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                        'theme_advanced_buttons2' => '',
                    ),
                    'editor_css'    => '<style>#wp-description-editor-tools {display:none}</style>'
                );
                ob_start();
                wp_editor( htmlspecialchars_decode( $attributes['value'] ), $attributes['name'], $settings );
                $html .= ob_get_clean();
                if(!empty($attributes['help']) && $attributes['help']!=''){
                    $html .= '<p class="help-block">'.$attributes['help'].'</p>';
                }
            }


            $html .= '</div></div>';


            if ($config['useeditor'] == 1) {
                ob_start();
                global $wp_version, $tinymce_version, $concatenate_scripts, $compress_scripts;

                $version = 'ver=' . $tinymce_version;
                $compressed = $compress_scripts && $concatenate_scripts && isset($_SERVER['HTTP_ACCEPT_ENCODING'])
                    && false !== stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');

                // Load tinymce.js when running from /src, else load wp-tinymce.js.gz (production) or tinymce.min.js (SCRIPT_DEBUG)
                $mce_suffix = false !== strpos( $wp_version, '-src' ) ? '' : '.min';
                $baseurl = includes_url( 'js/tinymce' );

                $suffix = SCRIPT_DEBUG ? '' : '.min';

                if ( $compressed ) {

                    echo "<script type='text/javascript' src='{$baseurl}/wp-tinymce.php?c=1&amp;$version'></script>\n";
                } else {
                    echo "<script type='text/javascript' src='{$baseurl}/tinymce{$mce_suffix}.js?$version'></script>\n";
                    echo "<script type='text/javascript' src='{$baseurl}/plugins/compat3x/plugin{$suffix}.js?$version'></script>\n";
                }

                ?>
                <script type="text/javascript">

                    ( function() {
                        tinymce.init({
                            selector: "#<?php echo $attributes['name'];?>",
                            theme: 'modern',
                            skin: 'lightgray',
                            height: 150,
                            toolbar1: "insertfile undo redo | styleselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'",
                            menubar: false,
                            formats:{
                                alignleft: [
                                    {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'left'}},
                                    {selector: 'img,table,dl.wp-caption', classes: 'alignleft'}
                                ],
                                aligncenter: [
                                    {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'center'}},
                                    {selector: 'img,table,dl.wp-caption', classes: 'aligncenter'}
                                ],
                                alignright: [
                                    {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: {textAlign:'right'}},
                                    {selector: 'img,table,dl.wp-caption', classes: 'alignright'}
                                ],
                                strikethrough: {inline: 'del'}
                            },
                            setup: function (editor) {
                                editor.on('change', function () {
                                    tinymce.triggerSave();
                                });
                            }
                        });

                    }());
                </script>
                <?php

                $html .= ob_get_clean();
            }

            return $html;
        }

}