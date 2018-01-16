<?php
/**
 * WP Table Manager
 *
 * @package WP Table Manager
 * @author Joomunited
 * @version 1.0W
 */

use Joomunited\WPFramework\v1_0_2\Factory;
use Joomunited\WPFramework\v1_0_2\Utilities;
use Joomunited\WPFramework\v1_0_2\Model;

// No direct access.
defined( 'ABSPATH' ) || die();

wp_localize_script('wptm-main','wptmText',array(                
                'Delete'=>__('Delete','wp-smart-editor'),
                'Edit'=>__('Edit','wp-smart-editor'),
                'Cancel'=>__('Cancel','wp-smart-editor'),
                'Ok'=>__('Ok','wp-smart-editor'),
                'Confirm'=>__('Confirm','wp-smart-editor'),
                'Save'=>__('Save','wp-smart-editor'),
                'GOT_IT'=>__('Got it!','wp-smart-editor'),
                'LAYOUT_WPTM_SELECT_ONE' => __('Please select a table a create a new one', 'wp-smart-editor') ,
                'VIEW_WPTM_TABLE_ADD' =>  __('Add new table', 'wp-smart-editor') ,
                'JS_WANT_DELETE' => __('Do you really want to delete ', 'wp-smart-editor') ,
                'CHANGE_INVALID_CHART_DATA' => __('Invalid chart data', 'wp-smart-editor') ,
                'CHART_INVALID_DATA'=>__('Invalid data, please make a new data range selection with at least one row or column with only numeric data, thanks!','wp-smart-editor'),
                'CHOOSE_EXCEL_FIE_TYPE' => __('Please choose a file with type of xls or xlsx.', 'wp-smart-editor') ,
                'WARNING_CHANGE_THEME' => __('Warning - all data and styles will be removed & replaced on theme switch', 'wp-smart-editor') ,
                'Your browser does not support HTML5 file uploads'=>__('Your browser does not support HTML5 file uploads','wp-smart-editor'),
                'Too many files'=>__('Too many files','wp-smart-editor'),
                'is too large'=>__('is too large','wp-smart-editor'),
                'Only images are allowed'=>__('Only images are allowed','wp-smart-editor'),
                'Do you want to delete &quot;'=>__('Do you want to delete &quot;','wp-smart-editor'),
                'Select files'=>__('Select files','wp-smart-editor'),
                'Image parameters'=>__('Image parameters','wp-smart-editor'),
                'notice_msg_table_syncable'=>__('This spreadsheet is currently sync with an external file, you may lose content in case of modification','wp-smart-editor'),
                'notice_msg_table_database'=>__('Table data are from database, only the 50 first rows are displayed for performance reason.','wp-smart-editor'),
                
        ));
wp_localize_script('wptm-bootbox','wptmCmd',array(                
                'Delete'=>__('Delete','wp-smart-editor'),
                'Edit'=>__('Edit','wp-smart-editor'),
                'CANCEL'=>__('Cancel','wp-smart-editor'),
                'OK'=>__('Ok','wp-smart-editor'),
                'CONFIRM'=>__('Confirm','wp-smart-editor'),
                'Save'=>__('Save','wp-smart-editor'),
        ));

if (isset($_GET['noheader'])){
    global $hook_suffix;
    _wp_admin_html_begin();
    do_action( 'admin_enqueue_scripts', $hook_suffix );
    do_action( "admin_print_scripts-$hook_suffix" );
    do_action( 'admin_print_scripts' );
}

$alone = '';

$editor_id = 'wptmditor';
$editor_args = array(
    'tabfocus_elements' => 'content-html,save-post',
    'quicktags' => true,
    'media_buttons' => false,
    'editor_height' => 400,
    'tinymce' => array(
		'resize' => true,             
		'wp_autoresize_on' => true,
		'add_unload_trigger' => false                
    )
);
wp_editor( '<p></p><p></p>', $editor_id,$editor_args );

   
$editor_args1 = $editor_args;
$editor_args1['quicktags'] = false;
$editor_args1['tinymce'] = array(
    'setup' => 'function (ed) {                               
                               ed.on("keyup", function (e) {
                                  // ed.save();                                   
                                   //wptm_tooltipChange();
                                
                                });
                                ed.on("change", function(e) {
                                   // ed.save();
                                    //wptm_tooltipChange();                                   
                                });
                            }',
);
wp_editor('', 'wptm_tooltip', $editor_args1);
?>
<style>
 #wp-wptmditor-wrap, #wp-wptm_tooltip-wrap { display: none} 
</style>    
<script type="text/javascript">
    ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    wptm_ajaxurl = "<?php echo Factory::getApplication('wptm')->getAjaxUrl(); ?>";
    wptm_dir = "<?php echo Factory::getApplication('wptm')->getBaseUrl(); ?>";
<?php if(Utilities::getInput('caninsert','GET','bool')): ?>
    gcaninsert=true;
    <?php $alone = 'wptmalone wp-core-ui '; ?>
<?php else: ?>
    gcaninsert=false;
<?php endif; ?>
   
    var Wptm = {};
    if(typeof(addLoadEvent)==='undefined'){addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};};
</script>
<style>
    <?php if(Utilities::getInput('caninsert','GET','bool')): ?>
    html.wp-toolbar {padding-top: 0 !important}
    <?php endif; ?>
</style>
<div id="mybootstrap" class="<?php echo $alone; ?>">   
    
    <div id="mycategories">
        <div class="categories-toggle" id="cats-toggle"><span class="dashicons-before dashicons-arrow-left-alt"></span></div>

        <div class="nested dd">
            <ol id="categorieslist" class="dd-list nav bs-docs-sidenav2 ">
                <?php if(!empty($this->categories)){    
                    $content = '';
                    $previouslevel = 1;
                    for ($index = 0; $index < count($this->categories); $index++) {
                        if($index+1!=count($this->categories)){
                            $nextlevel = $this->categories[$index+1]->level;
                        }else{
                            $nextlevel = 0;
                        }
                        $content .= openItem($this->categories[$index],$index);
                        $content .= '<ul class="wptm-tables-list">';
                        if($this->categories[$index]->id == $this->dbtable_cat) {
                                $tableType = 'mysql';
                        }else {
                                $tableType = '';
                        }
                        if(isset($this->tables[$this->categories[$index]->id])){                                              
                            foreach ($this->tables[$this->categories[$index]->id] as $table) {                                
                                $content .= '<li class="wptmtable" data-id-table="'.$table->id.'" data-table-type="'.$tableType.'">';
                                $content .= '<a href="#"><i class="icon-database"></i> <span class="title">'.$table->title.'</span></a>';				
				$content .= ' <a class="edit"><i class="icon-edit"></i></a>';								
				$content .= ' <a class="copy"><i class="icon-copy"></i></a>';							
				$content .= ' <a class="trash"><i class="icon-trash"></i></a>';				
                                $content .= '</li>';
                            }
                        }
                        if($tableType != 'mysql') {
                            $content .= '<li><a class="newTable" href="#"><i class="dashicons dashicons-plus-alt"></i> '. __('New table','wp-smart-editor').'</a></li>';
                        }
                        $content .= '</ul>';
                        
                        if($nextlevel>$this->categories[$index]->level){
                            $content .= openlist($this->categories[$index]);
                        }elseif($nextlevel==$this->categories[$index]->level){
                            $content .= closeItem($this->categories[$index]);
                        }else{
                            $c = '';
                            $c .= closeItem($this->categories[$index]);
                            $c .= closeList($this->categories[$index]);
                            $content .= str_repeat($c,$this->categories[$index]->level-$nextlevel);
                        }
                        $previouslevel = $this->categories[$index]->level;                    
                    }
                }
                if (!isset($content))
                {
                    $content ='';
                }
                echo $content;
                ?>
            </ol>
            <input type="hidden" id="categoryToken" name="" /> 
        </div>
    </div>
    
    <div id="rightcol" class="">
        <?php if(Utilities::getInput('caninsert', 'GET', 'bool')): ?>            
            <a id="inserttable" class="button button-primary button-big" href="javascript:void(0)" onclick="if (window.parent) insertTable();"><?php _e('Insert this table','wp-smart-editor'); ?></a>
        <?php endif; ?>
         <?php if(isset($this->params['enable_autosave']) && $this->params['enable_autosave'] == '0'): ?>
                <div class="control-group">
                    <label id="jform_saveTable-lbl">
                          <a id="saveTable" class="button button-primary button-big" title="<?php _e('Save modifications','wp-smart-editor'); ?>" ><?php _e('Save modifications','wp-smart-editor');?></a>
                    </label>
                </div>
         <?php endif; ?>
                    
        <div>
            <ul class="nav nav-tabs" id="configTable">
              <li class="referCell"><a data-toggle="tab" href="#cell"><?php _e('Format','wp-smart-editor');  ?></a></li>
            </ul>
            <div class="tab-content" id="tableTabContent">
               <!-- Cell  -->
                <div id="cell" class="tab-pane ">
                    <div class="control-group">
                        <div class="control-label">
                            <label id="jform_cell_type-lbl" for="jform_cell_type">
                                <?php _e('Cell type','wp-smart-editor');?> :
                            </label>
                        </div>
                        <div class="controls">
                            <select class="chzn-select observeChanges" name="jform[jform_cell_type]" id="jform_cell_type">
                                <option value=""><?php _e('Default','wp-smart-editor');?></option>
                                <option value="html"><?php _e('Html','wp-smart-editor');?></option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <div class="control-label">
                            <label id="jform_cell_background_color-lbl" for="jform_cell_background_color">
                                <?php _e('Cell background color','wp-smart-editor');?> :
                            </label>
                        </div>
                        <div class="controls">
                            <input class="minicolors minicolors-input observeChanges"  data-position="left" data-control="hue" type="text" name="jform[jform_cell_background_color]" id="jform_cell_background_color" value="" size="7" />
                        </div>
                    </div>
                    <hr/>
                    <div class="control-group">
                        <div class="control-label">
                            <label id="jform_cell_border_type-lbl" for="jform_cell_border_type">
                                <?php _e('Borders','wp-smart-editor');?> :
                            </label>
                        </div>
                        <div class="clr"></div>
                        <div class="controls">
                            <div>
                                <select class="chzn-select" name="jform[jform_cell_border_type]" id="jform_cell_border_type">
                                    <option value="solid"><?php _e('Solid','wp-smart-editor');?></option>
                                    <option value="dashed"><?php _e('Dashed','wp-smart-editor');?></option>
                                    <option value="dotted"><?php _e('Dotted','wp-smart-editor');?></option>
                                    <option value="none"><?php _e('No border','wp-smart-editor');?></option>
                                </select>
                                <div class="form-inline">
                                    <div class="input-append">
                                        <input type="text" name="jform[jform_cell_border_width]" id="jform_cell_border_width" value="1"/>
                                        <button class="btn" type="button" id="cell_border_width_incr">+</button>
                                        <button class="btn" type="button" id="cell_border_width_decr">-</button>
                                    </div>
                                </div>
                                <input class="minicolors minicolors-input observeChanges"  data-position="left" data-control="hue" type="text" name="jform[jform_cell_border_color]" id="jform_cell_border_color" value="#CCCCCC" size="7" />
                            </div>
                            <div class="aglobuttons">
                                <button class="btn observeChanges" name="jform[jform_cell_border_top]" type="button"><span class="sprite sprite_border_top"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_right]" type="button"><span class="sprite sprite_border_right"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_bottom]" type="button"><span class="sprite sprite_border_bottom"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_left]" type="button"><span class="sprite sprite_border_left"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_all]" type="button"><span class="sprite sprite_border_all"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_inside]" type="button"><span class="sprite sprite_border_inside"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_outline]" type="button"><span class="sprite sprite_border_outline"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_horizontal]" type="button"><span class="sprite sprite_border_horizontal"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_vertical]" type="button"><span class="sprite sprite_border_vertical"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_border_remove]" type="button"><span class="sprite sprite_border_remove"></span></button>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="control-group">
                        <div class="control-label">
                            <label id="jform_cell_font_family-lbl" for="jform_cell_font_family">
                            <?php _e('Font','wp-smart-editor');?> :
                            </label>
                        </div>
                        <div class="controls">
                            <select class="chzn-select observeChanges" name="jform[jform_cell_font_family]" id="jform_cell_font_family">
                                <option value="Arial">Arial</option>
                                <option value="Arial Black">Arial Black</option>
                                <option value="Comic Sans MS">Comic Sans MS</option>
                                <option value="Courier New">Courier New</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Impact">Impact</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Trebuchet MS">Trebuchet MS</option>
                                <option value="Verdana">Verdana</option>
                            </select>
                            <div class="form-inline">
                                <div class="input-append">
                                    <input class="observeChanges"  type="text" name="jform[jform_cell_font_size]" id="jform_cell_font_size" value="13"/>
                                    <button class="btn" type="button" id="cell_font_size_incr">+</button>
                                    <button class="btn" type="button" id="cell_font_size_decr">-</button>
                                </div>
                            </div>
                            <input class="minicolors minicolors-input observeChanges"  data-position="left" data-control="hue" type="text" name="jform[jform_cell_font_color]" id="jform_cell_font_color" value="#000000" size="7" />
                            <div class="aglobuttons">
                                <button class="btn observeChanges" name="jform[jform_cell_font_bold]" type="button"><span class="sprite sprite_text_bold"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_font_underline]" type="button"><span class="sprite sprite_text_underline"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_font_italic]" type="button"><span class="sprite sprite_text_italic"></span></button>
                                <br/>
                                <button class="btn observeChanges" name="jform[jform_cell_align_left]" type="button"><span class="sprite sprite_text_align_left"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_align_center]" type="button"><span class="sprite sprite_text_align_center"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_align_right]" type="button"><span class="sprite sprite_text_align_right"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_align_justify]" type="button"><span class="sprite sprite_text_align_justify"></span></button>
                                <br/>
                                <button class="btn observeChanges" name="jform[jform_cell_vertical_align_top]" type="button"><span class="sprite sprite_vertical_align_top"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_vertical_align_middle]" type="button"><span class="sprite sprite_vertical_align_middle"></span></button>
                                <button class="btn observeChanges" name="jform[jform_cell_vertical_align_bottom]" type="button"><span class="sprite sprite_vertical_align_bottom"></span></button>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="control-group">
                        <div class="control-label">
                            <label id="jform_row_height-lbl" for="jform_row_height">
                                <?php _e('Row height','wp-smart-editor');?> :
                            </label>
                        </div>
                        <div class="controls">
                            <input class="observeChanges input-mini" type="text" name="jform[jform_row_height]" id="jform_row_height" value="" size="7" />
                        </div>
                        <div class="control-label">
                            <label id="jform_col_width-lbl" for="jform_col_width">
                                <?php _e('Column width','wp-smart-editor');?> :
                            </label>
                        </div>
                        <div class="controls">
                            <input class="observeChanges input-mini" type="text" name="jform[jform_col_width]" id="jform_col_width" value="" size="7" />
                        </div>
                    </div>

                    <?php if(isset($this->params['enable_tooltip']) && $this->params['enable_tooltip'] == '1'): ?>
                    <div class="control-group">
                        <label id="jform_tooltip_content-lbl" for="jform_tooltip_content">
                              <a id="editToolTip" class="button button-primary button-big" title="<?php _e('Edit','wp-smart-editor'); ?>" href="#wptm_editToolTip"><?php _e('Edit Tooltip','wp-smart-editor');?></a>
                        </label>

                        <div id="wptm_editToolTip" style="display:none">
                            <div id="tooltip_editor">
                                <textarea id="tooltip_content" name="tooltip_content" class="observeChanges"></textarea>
                                <a id="saveToolTipbtn" class="button button-primary button-large" title="<?php _e('Save','wp-smart-editor'); ?>" href="javascript:void(0)"><?php _e('Save','wp-smart-editor');?></a>
                                <a id="cancelToolTipbtn" class="button button-large" title="<?php _e('Cancel','wp-smart-editor'); ?>" href="javascript:void(0)"><?php _e('Cancel','wp-smart-editor');?></a>
                            </div>
                        </div>

                        <div class="control-label">
                          <label id="jform_tooltip_width-lbl" for="jform_tooltip_width">
                              <?php _e('Tooltip width (in px)','wp-smart-editor');?> :
                          </label>
                        </div>
                        <div class="controls">
                            <input class="observeChanges input-mini" type="text" name="jform[jform_tooltip_width]" id="jform_tooltip_width" value="" size="7" />
                        </div>
                    </div>
                    <?php endif ?>
                </div>
               <!-- More tab -->
            </div>
        </div>
    </div>

    <div id="pwrapper">
        <div id="wpreview">
            <div id="preview">
                <span id="savedInfo" style="display:none;"><?php _e('All modifications were saved','wp-smart-editor'); ?></span>
                <ul class="nav nav-tabs" id="mainTable">
                    <li class="active"><a data-toggle="tab" href="#dataTable"><?php _e('Table','wp-smart-editor'); ?></a></li>
                </ul>
                <div id="mainTabContent" class="tab-content">
                    <div id="dataTable" class="tab-pane active">
                        <div>
                            <h3 id="tableTitle"></h3>
                            <div class="clearfix"></div>
                            <div id="tableContainer" style="overflow:scroll;"></div>
                      </div>
                    </div>
                </div>

            </div>
        </div>
        <input type="hidden" name="id_category" value="" />

        <?php if (!isset($_COOKIE['WPTM_hide_upgrade'])) : ?>
        <div id="updateGroup">
            <div id="updateInfo">
                <p style="text-transform: uppercase; font-weight: bold; font-size: 16px"><?php _e('WP Table Manager Full Version', 'wp-smart-editor') ?></p>
                <p id="updateDesc"><?php _e('Save time using WP Table Manager full version to manage table themes, charts, cell tooltips, Excel Import/Export/Synchronization and many more ...', 'wp-smart-editor') ?></p>
            </div>
            <button type="button" class="updateHideBtn"><i class="dashicons dashicons-dismiss" ></i></button>
            <a class="updateBtn" href="https://www.joomunited.com/wordpress-products/wp-table-manager" target="_blank" title="<?php _e('WP Table Manager Full Version', 'wp-smart-editor') ?>"><?php _e('More Information','wp-smart-editor') ?></a>
            <a class="updateBtn updateHideTxt" href="#" onclick="return false" title="<?php _e('Hide','wp-smart-editor') ?>"><?php _e('Close Notification','wp-smart-editor') ?></a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
var wptm_isAdmin = <?php echo (int)current_user_can( 'manage_options' ); ?>;
jQuery(document).ready(function($) {
    var myOptions = {
       width: 220,
        // a callback to fire whenever the color changes to a valid color
       change: function(event, ui){          
           
           var hexcolor = $( this ).wpColorPicker( 'color' ); 
           $(event.target).val(hexcolor);
           $(event.target).trigger('change');
       }
    };
       
    $('.minicolors').wpColorPicker(myOptions);
});

var wptmChangeWait;
function wptm_tooltipChange() {    
      clearTimeout(wptmChangeWait);
        wptmChangeWait = setTimeout(function() {            
            jQuery("#tooltip_content").trigger("change");            
        }, 1000);
}
var enable_autosave = true;
 <?php if(isset($this->params['enable_autosave']) && $this->params['enable_autosave'] == '0'): ?>
enable_autosave = false;
<?php endif;?>
    
 <?php
  $id_table = Utilities::getInt('id_table'); ?>  
  var idTable = <?php echo $id_table;?> ;  
</script>    

<?php
function openItem($category,$key){
    return '<li class="dd-item dd3-item '.($key?'':'active').'" data-id-category="'.$category->id.'">';
}

function closeItem($category){
    return '</li>';
}

function itemContent($category){
    return '<div class="dd-handle dd3-handle"></div>
    <div class="dd-content dd3-content"
        <i class="icon-chevron-right"></i>
        <a class="edit"><i class="icon-edit"></i></a>
        <a href="" class="t">
            <span class="title">'.$category->title.'</span>
        </a>
    </div>';
}

function openlist($category){
    return '<ol class="dd-list">';
}

function closelist($category){
    return '</ol>';
}
?>