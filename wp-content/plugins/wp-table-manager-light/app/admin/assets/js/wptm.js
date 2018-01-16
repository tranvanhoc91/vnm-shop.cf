/** 
 * Wptm
 * 
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 * @package Wptm
 * @copyright Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2014 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

jQuery(document).ready(function($) {
    isSelectionProcess = false; //hack because minicolos trigger change when value modified by js
    if (typeof (Wptm) == 'undefined') {
        Wptm = {};
        Wptm.can = {};
        Wptm.can.create = true;
        Wptm.can.edit = true;
        Wptm.can.delete = true;
        Wptm.selection = {};
    }
    if (typeof (Wptm.can) == 'undefined') {
        Wptm.can = {};
        Wptm.can.create = true;
        Wptm.can.edit = true;
        Wptm.can.delete = true;
    }
    if(typeof(wptm_isAdmin) == 'undefined') {
        wptm_isAdmin = false; 
    }
    //Categories toggle button
    $('#cats-toggle').toggle(
        function () {
            $('#mycategories').animate({left: -275},50,function(){
                $('#pwrapper').css({'margin-left':30});

                // $('#pwrapper .ht_clone_top.handsontable').css({'left':  parseInt($('#pwrapper .ht_clone_top.handsontable').css('left')) - 280});
                // $('#pwrapper .ht_clone_left.handsontable').css({'left': parseInt($('#pwrapper .ht_clone_left.handsontable').css('left')) - 280 });
                // $('#pwrapper .ht_clone_corner.handsontable').css({'left': parseInt($('#pwrapper .ht_clone_corner.handsontable').css('left')) - 280});

                $(this).addClass('mycategories-hide');
                setTimeout(function(){resizeTable();},300) ;
            });
            $(this).html('<span class="dashicons-before dashicons-arrow-right-alt">');
        },
        function () {
            $('#mycategories').animate({left: 0},100,function(){
                $('#pwrapper').css({'margin-left':'310px'});

                // $('#pwrapper .ht_clone_top.handsontable').css({'left':  parseInt($('#pwrapper .ht_clone_top.handsontable').css('left')) + 280});
                // $('#pwrapper .ht_clone_left.handsontable').css({'left': parseInt($('#pwrapper .ht_clone_left.handsontable').css('left')) + 280 });
                // $('#pwrapper .ht_clone_corner.handsontable').css({'left': parseInt($('#pwrapper .ht_clone_corner.handsontable').css('left')) + 280});

                $(this).removeClass('mycategories-hide');
                setTimeout(function(){resizeTable();},300) ;
            });
            $(this).html('<span class="dashicons-before dashicons-arrow-left-alt">');
        }
    );
         
    /* init menu actions */
    initMenu();
  
    /* Load nestable */   
    if (Wptm.can.edit && !gcaninsert) {
        $('.nested').nestable({maxDepth:8}).on('change', function(event, e) {
            pk = $(e).data('id-category');
            if ($(e).prev('li').length === 0) {
                position = 'first-child';
                if ($(e).parents('li').length === 0) {
                    //root
                    ref = 0;
                } else {
                    ref = $(e).parents('li').data('id-category');
                }
            } else {
                position = 'after';
                ref = $(e).prev('li').data('id-category');
            }
            $.ajax({
                url: wptm_ajaxurl+"task=categories.order&pk=" + pk + "&position=" + position + "&ref=" + ref,
                type: "POST"
            }).done(function(data) {
                result = jQuery.parseJSON(data);
                if (result.response === true) {

                } else {
                    bootbox.alert(result.response);
                }
            });
        });
        if (Wptm.collapse === true) {
            $('.nested').nestable('collapseAll');
        }
    }

    $(".wptm-tables-list").sortable({
        axis: 'y',
        revert: false,
        items: "> li.wptmtable",
        connectWith: ".wptm-tables-list",
        start: function(event, ui) {
            ui.item.addClass('sorting');             
        },
        stop: function(event, ui) {               
            setInterval(function () { ui.item.removeClass('sorting');  }, 1000);             
        },
        update: function( event, ui ) {
            
            var sortedIDs = $(this).sortable( "toArray", { attribute: "data-id-table" } ); 
            $.ajax({
                url: wptm_ajaxurl+"task=table.order&data="+sortedIDs.join(),
                type: "POST"                
            }).done(function(data) {
                result = jQuery.parseJSON(data);
                if (result.response === true) {
                    //do nothing
                } else {
                    bootbox.alert(result.response);
                }
            });
        }
     });

    $("#categorieslist > li.dd-item > .dd-content").droppable({
            hoverClass: "dd-content-hover",
            drop: function( event, ui ) {                
                $(this).addClass( "ui-state-highlight" );
                cat_target = $(event.target).parent().data("id-category");
                id_table = $(ui.draggable).data("id-table"); 
                is_active = $(ui.draggable).hasClass('active');
                $.ajax({
                    url: wptm_ajaxurl+"task=table.changeCategory&id="+id_table+"&category="+cat_target,                              
                }).done(function(data) {
                    result = jQuery.parseJSON(data);
                    if (result.response === true) {
                        //move to new category
                        $(event.target).parent().find("ul.wptm-tables-list").prepend( $(ui.draggable) );
                        $(ui.draggable).css('top','').css('left',''); //reset offset position
                    
                        if(is_active) {                          
                             $('#categorieslist li').removeClass('active');
                             $(event.target).parent().addClass('active');   
                             $(event.target).parent().find('ul.wptm-tables-list li:first').addClass('active');
                        }
                    } else {
                        bootbox.alert(result.response);
                    }
                });  
              
            }
        }             
    );
     
    //Check what is loaded via editor
    if (typeof (gcaninsert) !== 'undefined' && gcaninsert === true) {
        if (typeof (window.parent.tinyMCE) !== 'undefined' ) {
            if(window.parent.tinyMCE.activeEditor == null) {            
                return;
            }
            content = window.parent.tinyMCE.activeEditor.selection.getContent();
            imgparent = window.parent.tinyMCE.activeEditor.selection.getNode().parentNode;
            exp = '<img.*data\-wptmtable="([0-9]+)".*?>';
            table = content.match(exp); 
            Wptm.selection = new Array();
            Wptm.selection.content = content;
                       
            if (table !== null) {
                $('#categorieslist .wptmtable[data-id-table=' + table[1] + ']').addClass('active');
                updatepreview(table[1]);
                
                exp2 = '<img.*data\-wptm\-chart="([0-9]+)".*?>';
                table2 = content.match(exp2); 
                if (table2 !== null) {
                    Wptm.chart_id =  table2[1] ;
                }
            }
            else {
                updatepreview();
            }
        }
        //DropEditor
        else if(typeof window.parent.CKEDITOR != 'undefined') {
            var ckEditor = window.parent.CKEDITOR.instances[e_name];  
            imgElement = ckEditor.getSelection().getSelectedElement();           
            if(typeof imgElement != "undefined" && imgElement != null ) {
                 table_id = imgElement.getAttribute('data-wptmtable');                 
                 if (table_id !== null) {
                      $('#categorieslist .wptmtable[data-id-table=' + table_id + ']').addClass('active');
                      updatepreview(table_id);
                      chart_id = imgElement.getAttribute('data-wptm-chart');
                      if (chart_id !== null) {
                            Wptm.chart_id =  chart_id ;
                      }
                 }else {
                    updatepreview();
                }
            }else{
                 updatepreview();
            }
        } //end DropEditor
    } else {
        /* Load gallery */
        if(idTable) {
            updatepreview(idTable);
        }else {
            updatepreview();
        }
        
    }

    /** Check new version **/
    /*
    $.getJSON(wptm_ajaxurl+"task=update.check", function(data) {
        if (data !== false) {
            $('#updateGroup').show().find('span.versionNumber').html(data);
        }
    }); */

    $('#hideUpdateBtn').click(function(e) {
        e.preventDefault();
        var today = new Date(), expires = new Date();
        expires.setTime(today.getTime() + (7 * 24 * 60 * 60 * 1000));
        document.cookie = "com_wptm_noCheckUpdates =true; expires=" + expires.toGMTString();
        $('#updateGroup').hide();
    });

    if ($('#headMainCss').length === 0) {
        $('head').append('<style id="headMainCss"></style>');
    }
    var styleToRender = [];
    if ($('#headCss').length === 0) {
        $('head').append('<style id="headCss"></style>');
    }

    var autosaveNotification;
    var dataReadOnly;
    /**
     * Reload a category preview
     */
    function updatepreview(id, ajaxCallBack) {
        
        if (typeof (id) !== "undefined") {
            $('#categorieslist .dd-item').removeClass('active');
            selectedElem = $('#categorieslist ul.wptm-tables-list li[data-id-table=' + id + ']');
            selectedElem.addClass('active');
            selectedElem.parents('.dd-item').first().addClass('active');
        } else {
            id = $('#categorieslist li.active > ul.wptm-tables-list li:first').data('id-table');
            $('#categorieslist li.active > ul.wptm-tables-list li:first').addClass('active');
            selectedElem ='';
        }
  
        if (typeof (id) === 'undefined' && typeof (selectedElem) !== 'undefined' && selectedElem.length === 0) {
            $('#tableTitle,#rightcol').hide();
            $('#tableContainer').html(wptmText.LAYOUT_WPTM_SELECT_ONE);
            return;
        }
        $('#tableTitle,#rightcol').show();
        $('#tableContainer').empty();
        $('#tableContainer').handsontable('destroy');
    
	// make the Table tab active
        $('ul#mainTable li a:first').tab('show');   
            
        loading('#wpreview');
        url =  wptm_ajaxurl+"view=table&format=json&id=" + id;
        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
        }).done(function(data) {
           
                Wptm.id = id;
                Wptm.container = $("#tableContainer");
                cols = [];
                rows = [];

                if (data.datas === "") {
                    var tableData = [
                        ["", "", "", "", "", "", "", "", "", ""],
                        ["", "", "", "", "", "", "", "", "", ""],
                        ["", "", "", "", "", "", "", "", "", ""],
                        ["", "", "", "", "", "", "", "", "", ""],
                        ["", "", "", "", "", "", "", "", "", ""],
                        ["", "", "", "", "", "", "", "", "", ""],
                        ["", "", "", "", "", "", "", "", "", ""],
                        ["", "", "", "", "", "", "", "", "", ""]
                    ];
                    delete Wptm.style;
                } else {
                  
                    try {
                         tableData = $.parseJSON(data.datas); 
                    }catch(err) {
                        var tableData = [
                            ["", "", "", "", "", "", "", "", "", ""],
                            ["", "", "", "", "", "", "", "", "", ""],
                            ["", "", "", "", "", "", "", "", "", ""],
                            ["", "", "", "", "", "", "", "", "", ""],
                            ["", "", "", "", "", "", "", "", "", ""],
                            ["", "", "", "", "", "", "", "", "", ""],
                            ["", "", "", "", "", "", "", "", "", ""],
                            ["", "", "", "", "", "", "", "", "", ""]
                        ];
                    }
                 
                    Wptm.style = $.parseJSON(data.style);
                    Wptm.css = data.css.replace(/\\n/g,"\n" ) ;
                }
               
                dataReadOnly = false;
                $(".dbtable_params").hide();
                $('#rightcol .table-styles').show();
                $('#rightcol .spreadsheet_sync').show();                
                if (data.params === "" || data.params === null || data.params.length == 0) {
                    mergeCellsSetting = true;
                } else {
                    if(typeof (data.params) == 'string') { data.params = $.parseJSON(data.params); }
                    mergeCellsSetting = $.parseJSON(data.params.mergeSetting);
                    if(mergeCellsSetting==null) mergeCellsSetting = [];                    
                
                    if(typeof data.params.table_type != 'undefined' && data.params.table_type=='mysql') {
                        dataReadOnly = true;
                        $(".dbtable_params").show();
                        $('#rightcol .table-styles').hide();
                        $('#rightcol .spreadsheet_sync').hide();
                    }
                    
                }
               
                if (typeof (Wptm.style) === 'undefined' || Wptm.style === null) {
                    $.extend(Wptm, {
                        style: {
                            table: {},
                            rows: {},
                            cols: {},
                            cells: {}
                        },
                        css: ''
                    });
                }
                $defaultParams = {'use_sortable':'0','table_align':'center','responsive_type':'scroll','freeze_col':0,'freeze_row':0,'enable_filters':0,'spreadsheet_url':''};
                Wptm.style.table = $.extend( {}, $defaultParams, Wptm.style.table );
                
                $('#jform_css').val(Wptm.css);
                $('#jform_css').change();
                parseCss();

                initBtnPosition();
                
                initHandsontable(tableData);
                
                $(".wptm_warning").remove();
                               
                $('h3#tableTitle').html(data.title);
                if(typeof Wptm.style.table.spreadsheet_url != 'undefined' &&  Wptm.style.table.spreadsheet_url != "" && typeof Wptm.style.table.auto_sync != 'undefined' &&  Wptm.style.table.auto_sync != "0") {
                    $('h3#tableTitle').after('<div class="wptm_warning"><p>'+ wptmText.notice_msg_table_syncable +'</p></div>');
                }
              
                if(dataReadOnly) {
                    $('h3#tableTitle').after('<div class="wptm_warning"><p>'+ wptmText.notice_msg_table_database +'</p></div>');
                }
              
                initBtnPosition();
                $(Wptm.container).handsontable('render');
                $(Wptm.container).handsontable('selectCell', 0, 0);
                resizeTable();
                 
                $("#fetch_spreadsheet").unbind('click').click(function(e) {
                    e.preventDefault();
                   
                    tableId = $('li.wptmtable.active').data('id-table');
                    spreadsheet_url = $("#jform_spreadsheet_url").val();
                     
                    loading('#wpreview');
                    url =  wptm_ajaxurl+"task=excel.fetchSpreadsheet&id=" + tableId ;
                    var jsonVar = {
                        spreadsheet_url: encodeURI(spreadsheet_url),
                        id: Wptm.id
                    };
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: jsonVar 
                    }).done(function(data) {     
                        result = jQuery.parseJSON(data);                     
                        if (result.response === true) {                           
                            updatepreview(tableId);                           
                        }
                        rloading('#wpreview');
                    });
                 });
                 
                 if(typeof ajaxCallBack == "function") {
                     ajaxCallBack();
                 }
            //}

            rloading('#wpreview');
        });   
        
    }

    initHandsontable = function(tableData) {
        var needSaveAfterRender = false;
        
        Wptm.container.handsontable({
            data: tableData,
            startRows: 5,
            startCols: 5,
            renderAllRows: true,
            colHeaders: true,
            rowHeaders: true,
            fixedRowsTop: (Wptm.style.table.responsive_type=='scroll' ) ? parseInt(Wptm.style.table.freeze_row) : 0,
            fixedColumnsLeft: (Wptm.style.table.responsive_type=='scroll' ) ? parseInt(Wptm.style.table.freeze_col) : 0,
            manualColumnResize: (Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author)),
            manualRowResize: (Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author)),
            outsideClickDeselects: false,
            renderer: customRenderer,
            columnSorting: false,
            undo: true,
            mergeCells: mergeCellsSetting,
            readOnly: ( ((Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author)) && !dataReadOnly) ? false : true),
            contextMenu: ( ((Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author)) && !dataReadOnly) ? ["row_above", "row_below", "---------", "col_left", "col_right", "---------", "remove_row", "remove_col", "---------", "undo", "---------", "mergeCells"] : false),
            editor: CustomEditor,
            beforeChange: function (changes, source) {
                // for (var i = changes.length - 1; i >= 0; i--) {
                //     if (!validateCharts(changes[i]) ) {
                //         bootbox.alert(wptmText.CHANGE_INVALID_CHART_DATA);
                //         return false;
                //     }
                // }
            },
            afterChange: function(change, source) {
                if(wptm_isAdmin) {
                    loadTableContructor();
                }
                if (source === 'loadData' || !(Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author))) {
                    return; //don't save this change
                }
                clearTimeout(autosaveNotification);
                saveChanges();
            },
            beforeKeyDown: function(e) {
                if ($("#categorieslist span.title.editable").length > 0) {
                    e.stopImmediatePropagation();
                }
            },
            afterColumnResize: function(width, col) {
                saveChanges();
            },
            afterRowResize: function(height, row) {
                saveChanges();
            },
            beforeRender: function() {
                styleToRender = '';
            },
            afterRender: function() {

                var parser = new (less.Parser);
                content = '#preview .handsontable .ht_master .htCore {' + styleToRender + '}';
                if(Wptm.style.table.responsive_type=='scroll' && Wptm.style.table.freeze_row) {
                    content += ' #preview .handsontable .ht_clone_top .htCore {' + styleToRender + '}';
                }
                if(Wptm.style.table.responsive_type=='scroll' && Wptm.style.table.freeze_col) {
                    content += ' #preview .handsontable .ht_clone_left .htCore {' + styleToRender + '}';
                }
                if(Wptm.style.table.responsive_type=='scroll' && Wptm.style.table.freeze_row && Wptm.style.table.freeze_col) {
                    content += ' #preview .handsontable .ht_clone_corner .htCore {' + styleToRender + '}';
                }

                parser.parse(content, function(err, tree) {
                    if (err) {
                        //Here we can throw the erro to the user
                        return false;
                    } else {
                        Wptm.css = $('#jform_css').val();
                        if ($('#headMainCss').length === 0) {
                            $('head').append('<style id="headMainCss"></style>');
                        }
                        $('#headMainCss').text(tree.toCSS());
                        return true;
                    }
                });
                $('table.htCore a').unbind('click').click(function(e){
                    e.preventDefault();
                });
                pushDims();
                if (needSaveAfterRender === true) {
                    saveChanges();
                    needSaveAfterRender = false;
                }
                initBtnPosition();
                //fix row height of overlay table
                for(i=0;i<$('#tableContainer .ht_master .htCore tr').length;i++) {
                    var h = $('#tableContainer .ht_master .htCore tr').eq(i).height() ;
                    $('#tableContainer .ht_clone_left .htCore tr').eq(i).height(h) ;
                }

            },
            afterCreateRow: function(index, amount) {
                selection = $(Wptm.container).handsontable('getSelected');
                if (typeof (Wptm.style.cells) !== 'undefined') {
                    newCells = {};
                    for (cell in Wptm.style.cells) {
                        if (Wptm.style.cells[cell][0] < index) {
                            //no changes to cells
                            newCells[cell] = clone(Wptm.style.cells[cell]);
                        } else if (Wptm.style.cells[cell][0] === index) {

                            if (index === selection[0]) {
                                //inserted before
                                newCells[cell] = clone(Wptm.style.cells[cell]);
                                newCells[(Wptm.style.cells[cell][0] + amount) + '!' + Wptm.style.cells[cell][1]] = [Wptm.style.cells[cell][0] + amount, Wptm.style.cells[cell][1], clone(Wptm.style.cells[(Wptm.style.cells[cell][0]) + '!' + Wptm.style.cells[cell][1]][2])];
                            } else {
                                //inserted after
                                newCells[cell] = [Wptm.style.cells[cell][0], Wptm.style.cells[cell][1], clone(Wptm.style.cells[(Wptm.style.cells[cell][0] - amount) + '!' + Wptm.style.cells[cell][1]][2])];
                                newCells[(Wptm.style.cells[cell][0] + amount) + '!' + Wptm.style.cells[cell][1]] = [Wptm.style.cells[cell][0] + amount, Wptm.style.cells[cell][1], clone(Wptm.style.cells[(Wptm.style.cells[cell][0]) + '!' + Wptm.style.cells[cell][1]][2])];
                            }

                        } else {
                            newCells[(Wptm.style.cells[cell][0] + amount) + '!' + Wptm.style.cells[cell][1]] = [Wptm.style.cells[cell][0] + amount, Wptm.style.cells[cell][1], clone(Wptm.style.cells[cell][2])];
                        }
                    }
                    if ($(Wptm.container).handsontable('countRows') === index + amount) {
                        //row added at the bottom
                        for (ij = 0; ij < $(Wptm.container).handsontable('countCols'); ij++) {
                            if (typeof (Wptm.style.cells[selection[0] + '!' + ij]) !== 'undefined') {
                                newCells[index + '!' + ij] = [index, ij, clone(Wptm.style.cells[selection[0] + '!' + ij][2])];
                            }
                        }
                    }
                    Wptm.style.cells = clone(newCells);
                }
                needSaveAfterRender = true;
            },
            afterCreateCol: function(index, amount) {
                selection = $(Wptm.container).handsontable('getSelected');
                if (typeof (Wptm.style.cells) !== 'undefined') {
                    newCells = {};
                    for (cell in Wptm.style.cells) {
                        if (Wptm.style.cells[cell][1] < index) {
                            //no changes to cells
                            newCells[cell] = clone(Wptm.style.cells[cell]);
                        } else if (Wptm.style.cells[cell][1] === index) {

                            if (index === selection[1]) {
                                //inserted before
                                newCells[cell] = clone(Wptm.style.cells[cell]);
//                                    newCells[(Wptm.style.cells[cell][0]+amount)+'!'+Wptm.style.cells[cell][1]] = [Wptm.style.cells[cell][0]+amount,Wptm.style.cells[cell][1],clone(Wptm.style.cells[(Wptm.style.cells[cell][0])+'!'+Wptm.style.cells[cell][1]][2])];
                                newCells[Wptm.style.cells[cell][0] + '!' + (Wptm.style.cells[cell][1] + amount)] = [Wptm.style.cells[cell][0], Wptm.style.cells[cell][1] + amount, clone(Wptm.style.cells[(Wptm.style.cells[cell][0]) + '!' + Wptm.style.cells[cell][1]][2])];
                            } else {
                                //inserted after
                                newCells[cell] = [Wptm.style.cells[cell][0], Wptm.style.cells[cell][1], clone(Wptm.style.cells[Wptm.style.cells[cell][0] + '!' + (Wptm.style.cells[cell][1] - amount)][2])];
                                newCells[(Wptm.style.cells[cell][0]) + '!' + (Wptm.style.cells[cell][1] + amount)] = [Wptm.style.cells[cell][0], Wptm.style.cells[cell][1] + amount, clone(Wptm.style.cells[(Wptm.style.cells[cell][0]) + '!' + Wptm.style.cells[cell][1]][2])];
                            }

                        } else {
                            newCells[(Wptm.style.cells[cell][0]) + '!' + (Wptm.style.cells[cell][1] + amount)] = [Wptm.style.cells[cell][0], Wptm.style.cells[cell][1] + amount, clone(Wptm.style.cells[cell][2])];
                        }
                    }
                    if ($(Wptm.container).handsontable('countCols') === index + amount) {
                        //col added at the right
                        for (ij = 0; ij < $(Wptm.container).handsontable('countRows'); ij++) {
                            if (typeof (Wptm.style.cells[ij + '!' + selection[1]]) !== 'undefined') {
                                newCells[ij + '!' + index] = [ij, index, clone(Wptm.style.cells[ij + '!' + selection[1]][2])];
                            }
                        }
                    }
                    Wptm.style.cells = clone(newCells);
                }
                needSaveAfterRender = true;
            },
            afterRemoveRow: function(index, amount) {
                selection = $(Wptm.container).handsontable('getSelected');
                if (typeof (Wptm.style.cells) !== 'undefined') {
                    newCells = {};
                    for (cell in Wptm.style.cells) {
                        if (Wptm.style.cells[cell][0] > index) {
                            newCells[parseInt(Wptm.style.cells[cell][0] - amount) + '!' + Wptm.style.cells[cell][1]] = [Wptm.style.cells[cell][0] - amount, Wptm.style.cells[cell][1], $.extend({}, Wptm.style.cells[cell][2])];
                        } else if (Wptm.style.cells[cell][0] === index) {

                        } else {
                            newCells[Wptm.style.cells[cell][0] + '!' + Wptm.style.cells[cell][1]] = $.extend([], Wptm.style.cells[cell]);
                        }
                    }
                    Wptm.style.cells = newCells;
                }
                needSaveAfterRender = true;
            },
            afterRemoveCol: function(index, amount) {
                selection = $(Wptm.container).handsontable('getSelected');
                if (typeof (Wptm.style.cells) !== 'undefined') {
                    newCells = {};
                    for (cell in Wptm.style.cells) {
                        if (Wptm.style.cells[cell][1] > index) {
                            newCells[Wptm.style.cells[cell][0] + '!' + parseInt(Wptm.style.cells[cell][1] - amount)] = [Wptm.style.cells[cell][0], Wptm.style.cells[cell][1] - amount, $.extend({}, Wptm.style.cells[cell][2])];
                        } else if (Wptm.style.cells[cell][1] === index) {

                        } else {
                            newCells[Wptm.style.cells[cell][0] + '!' + Wptm.style.cells[cell][1]] = $.extend([], Wptm.style.cells[cell]);
                        }
                    }
                    Wptm.style.cells = newCells;
                }
                needSaveAfterRender = true;
            },
            afterSelection: function() {
                isSelectionProcess = true;
                loadSelection();
                isSelectionProcess = false;
                $('#rightcol .referCell a').trigger('click');
            },
            colWidths: function(index) {
                if (typeof (Wptm.style.cols) !== 'undefined' && typeof (Wptm.style.cols[index]) !== 'undefined' && typeof (Wptm.style.cols[index][1]) !== 'undefined' && typeof (Wptm.style.cols[index][1].width) !== 'undefined') {
                    return Wptm.style.cols[index][1].width;
                }
            },
            rowHeights: function(index) {
                if (typeof (Wptm.style.rows) !== 'undefined' && typeof (Wptm.style.rows[index]) !== 'undefined' && typeof (Wptm.style.rows[index][1]) !== 'undefined' && typeof (Wptm.style.rows[index][1].height) !== 'undefined') {
                    return Wptm.style.rows[index][1].height;
                } else {
                    var h = jQuery('#tableContainer .ht_master .htCore tr').eq(index+1).height() ;
                    return h ;
                }
            },
            undoChangeStyle: function(oldStyle) {
                //alert('after change style');
                Wptm.style = oldStyle;
                selection = $(Wptm.container).handsontable('getSelected');

                needSaveAfterRender = true;
                $(Wptm.container).handsontable('render');
                $(Wptm.container).handsontable("selectCell", selection[0], selection[1], selection[2], selection[3]);
                resizeTable();
            }
        });
    };
    
    resizeTable = function() {
        var offset = $('#tableContainer').offset();
        availableWidth = $(window).width() - offset.left + $(window).scrollLeft() - 310 + (getUrlVar('caninsert') && 15);
      
        $('#tableContainer').width(availableWidth) ;
        resizeBtnPosition();
        $(window).scrollTop($(window).scrollTop()+1); //trigger window scroll event
    };
    
    $(window).smartresize(function(){
        resizeTable();
    });
   
    $(document).on( 'wp-collapse-menu', function() {
	    resizeTable();
    });
    
    initBtnPosition = function() {
        if (!(Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author))) {
            return;
        }
        if ($('#insertColBtn').length === 0) {
            btnCol = $('<a href="#" id="insertColBtn"><i class="icon-plus-circle"></i></a>');
            btnCol.insertBefore($('#tableContainer'));

            $('#insertColBtn')
                    .css('height', parseInt(parseInt($('#tableContainer').height()) - 20) + "px")
                    .css('line-height', parseInt(parseInt($('#tableContainer').height()) - 20) + "px")
                    .bind("contextmenu", function(e) {
                        e.preventDefault();
                        return false;
                    })
                    .unbind('click').bind('click', function() {
                nbCols = $(Wptm.container).handsontable('countCols');
                if (nbCols === 0) {
                    $(Wptm.container).handsontable('loadData', [[""]]);
                } else {
                    selection = $(Wptm.container).handsontable('getSelected');
                    $(Wptm.container).handsontable('selectCell', selection[0], nbCols - 1);
                    $(Wptm.container).handsontable('alter', 'insert_col', nbCols);
                }
                saveChanges();
                return false;
            });
           
        }
        if ($('#insertRowBtn').length === 0) {
            btnRow = $('<div style="height:50px;"><a href="#" id="insertRowBtn"><i class="icon-plus-circle"></i></a></div>');
            btnRow.insertAfter($('#tableContainer'));

            $('#insertRowBtn').bind("contextmenu", function(e) {
                e.preventDefault();
                return false;
            })
                    .unbind('click').bind('click', function() {
                nbRows = $(Wptm.container).handsontable('countRows');
                if (nbRows === 0) {
                    $(Wptm.container).handsontable('loadData', [[""]]);
                } else {
                    selection = $(Wptm.container).handsontable('getSelected');
                    $(Wptm.container).handsontable('selectCell', nbRows - 1, selection[1]);
                    $(Wptm.container).handsontable('alter', 'insert_row', nbRows);
                }
               // saveChanges(); duplicate saveChange 
                return false;
            });
          
        }
    };
    
    resizeBtnPosition = function() {
         $('#insertRowBtn')
                        .css('width', parseInt(parseInt($('#tableContainer').width()) ) + "px");
         $('#insertColBtn')
                        .css('height', parseInt(parseInt($('#tableContainer').height()) - 20) + "px")
                        .css('line-height', parseInt(parseInt($('#tableContainer').height()) - 20) + "px");
    }
    
    $("#saveTable").click(function(e) {
       e.preventDefault();    	
       saveChanges(true);
    });
    
    function saveChanges(autosave, ajaxCallback) {   
    
        if (!(Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author))) {
            return;
        }
        
        if(typeof autosave == 'undefined' && !enable_autosave) {
            return;
        }
        var ht = Wptm.container.handsontable('getInstance'); 
        var mergeSetting = ht.mergeCells.mergedCellInfoCollection;

        var jsonVar = {
            jform: {
                datas: (dataReadOnly)? '' : JSON.stringify(Wptm.container.handsontable('getData')),
                style: JSON.stringify(Wptm.style),
                css: Wptm.css,
                params: (dataReadOnly)? '': {"mergeSetting": JSON.stringify(mergeSetting)}
            },
            id: Wptm.id
        };
        //jsonVar[Wptm.token] = "1";
        
        Wptm.style = cleanStyle(Wptm.style, $(Wptm.container).handsontable('countRows'), $(Wptm.container).handsontable('countCols'));
        //console.log(Wptm.style.cells);
        $.ajax({
            url: wptm_ajaxurl+"task=table.save",
            dataType: "json",
            type: "POST",
            data: jsonVar,
            success: function(datas) {
                if (datas.response === true) {
                    autosaveNotification = setTimeout(function() {
                        $('#savedInfo').fadeIn(200).delay(2000).fadeOut(1000);
                    }, 1000);
                } else {
                    bootbox.alert(datas.response);
                }
                if(typeof ajaxCallback == 'function') { ajaxCallback(Wptm.id) }
            },
            error: function(jqxhr, textStatus, error) {
                bootbox.alert(textStatus + " : " + error);
            }
        });
    }

    /* Title edition */
    function initMenu() {

        (initTableNew = function() {
            $('#categorieslist a.newTable').unbind('click').click(function(e) {
                if (!(Wptm.can.create)) {
                    return;
                }
                id_category = $(this).parents('.dd-item').data('id-category');
                that = this;
                $.ajax({
                    url: wptm_ajaxurl+"task=table.add&id_category=" + id_category,
                    type: "POST",
                    dataType: "json",
                    success: function(datas) {
                        if (datas.response === true) {
                            $(that).parent().before('<li class="wptmtable" data-id-table="' + datas.datas.id + '"><a href="#"><i class="icon-database"></i> <span class="title">' + datas.datas.title + '</span></a><a class="edit"><i class="icon-edit"></i></a><a class="copy"><i class="icon-copy"></i></a><a class="trash"><i class="icon-trash"></i></a></li>');
                            initMenu();                            
                            $('#categorieslist .wptm-tables-list li[data-id-table="'+ datas.datas.id+ '"] a:not(".newTable,.trash,.edit,.copy")').click();
                        } else {
                            bootbox.alert(datas.response);
                        }
                    },
                    error: function(jqxhr, textStatus, error) {
                        bootbox.alert(textStatus + " : " + error);
                    }
                });
                return false;
            });
        })();

        (initTableDelete = function() {
            if (!(Wptm.can.delete)) {
                return false;
            }
            $('#categorieslist .wptm-tables-list a.trash').unbind('click').click(function(e) {
                that = this;
                bootbox.confirm(wptmText.JS_WANT_DELETE + "\"" + $(this).parent().find('.title').text().trim() + '"?',wptmText.Cancel,wptmText.Ok, function(result) {
                    if (result === true) {
                        id = $(that).parent().data('id-table');
                        $.ajax({
                            url: wptm_ajaxurl+"task=table.delete&id=" + id,
                            type: "POST",
                            dataType: "json",
                            success: function(datas) {
                                if (datas.response === true) {
                                    $(that).parent().remove();
                                    updatepreview();
                                } else {
                                    bootbox.alert(datas.response,wptmText.Ok);
                                }
                            },
                            error: function(jqxhr, textStatus, error) {
                                bootbox.alert(textStatus,wptmText.Ok);
                            }
                        });
                        return false;
                    }
                });
            });
        })();

        (initTableCopy = function() {
            $('#categorieslist .wptm-tables-list a.copy').unbind('click').click(function(e) {
                if (!(Wptm.can.create)) {
                    return false;
                }
                that = this;
                id = $(that).parent().data('id-table');
                $.ajax({
                    url: wptm_ajaxurl+"task=table.copy&id=" + id,
                    type: "POST",
                    dataType: "json",
                    success: function(datas) {
                        if (datas.response === true) {
                            $(that).parents('.wptm-tables-list').find('li').last().before('<li class="wptmtable" data-id-table="' + datas.datas.id + '"><a href="#"><i class="icon-database"></i> <span class="title">' + datas.datas.title + '</span></a><a class="edit"><i class="icon-edit"></i></a><a class="copy"><i class="icon-copy"></i></a><a class="trash"><i class="icon-trash"></i></a></li>');
                            initMenu();                            
                        } else {
                            bootbox.alert(datas.response);
                        }
                    },
                    error: function(jqxhr, textStatus, error) {
                        bootbox.alert(textStatus);
                    }
                });
                return false;
            });
        })();

        (initTablesLinks = function() {
            $('#categorieslist .wptm-tables-list a:not(".newTable,.trash,.edit,.copy")').unbind('click').click(function(e) {
                
                if( $(this).parent().hasClass('sorting') ) return false;
                id = $(this).parent().data('id-table');
                $('#categorieslist .wptm-tables-list li').removeClass('active');
                $(this).parent().addClass('active');
                updatepreview(id);

                return false;
            });
        })();

        $('#categorieslist a.edit').unbind().click(function(e) {
            e.stopPropagation();
            if (!(Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author))) {
                return false;
            }
            $this = this;
            link = $(this).parent().find('a span.title');
            oldTitle = link.text();
            $(link).attr('contentEditable', true);
            $(link).addClass('editable');
            $(link).selectText();

            $('#categorieslist a span.editable').bind('click.mm', hstop);  //let's click on the editable object
            $(link).bind('keypress.mm', hpress); //let's press enter to validate new title'
            $('*').not($(link)).bind('click.mm', houtside);

            function unbindall() {
                $('#categorieslist a span').unbind('click.mm', hstop);  //let's click on the editable object
                $(link).unbind('keypress.mm', hpress); //let's press enter to validate new title'
                $('*').not($(link)).unbind('click.mm', houtside);
            }

            //Validation
            function hstop(event) {
                event.stopPropagation();
                return false;
            }

            //Press enter
            function hpress(e) {
                if (e.which == 13) {
                    e.preventDefault();
                    unbindall();
                    updateTitle($(link).text());
                    $(link).removeAttr('contentEditable');
                    $(link).removeClass('editable');
                }
            }

            //click outside
            function houtside(e) {
                unbindall();
                updateTitle($(link).text());
                $(link).removeAttr('contentEditable');
                $(link).removeClass('editable');
            }


            function updateTitle(title) {
                if (!(Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author))) {
                    return false;
                }
                if ($(link).parents('.wptm-tables-list').length === 0) {
                    id = $(link).parents('li').data('id-category');
                    url = wptm_ajaxurl+"task=category.setTitle&id_category=" + id + '&title=' + title;
                    type = 'category';
                } else {
                    id = $(link).parents('li').data('id-table');
                    url = wptm_ajaxurl+"task=table.setTitle&id=" + id + '&title=' + title;
                    type = 'table';
                }

                if (title !== '') {
                    $.ajax({
                        url: url,
                        type: "POST",
                        dataType: "json",
                        success: function(datas) {
                            if (datas.response === true) {
                                if (type === 'table' && Wptm.id == id) {
                                    $('h3#tableTitle').html(title);
                                }
                            } else {
                                $(link).text(oldTitle);
                                bootbox.alert(datas.response);
                            }
                        },
                        error: function(jqxhr, textStatus, error) {
                            $(link).text(oldTitle);
                            bootbox.alert(textStatus);
                        }
                    });
                } else {
                    $(link).text(oldTitle);
                    return false;
                }
                $(link).parent().css('white-space', 'normal');
                setTimeout(function() {
                    $(link).parent().css('white-space', '');
                }, 200);

            }
        });
    }

    (initObserver = function() {
        if (!(Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author))) {
            return false;
        }
        
        $('.observeChanges').on('change click', function(e) {
            if (isSelectionProcess === true) {
                return;
            }
            selection = $(Wptm.container).handsontable('getSelected');
            if (!selection) {
                return;
            }
            if (selection[0] > selection[2]) {
                selection = [selection[2], selection[3], selection[0], selection[1]];
            }

            //for undo                         
            var oldStyle = JSON.parse(JSON.stringify(Wptm.style));
            //for mergecells
            var ht = Wptm.container.handsontable('getInstance');

            switch ($(this).attr('name')) {
                case 'jform[jform_cell_type]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            if ($(this).val() === '') {
                                Wptm.style.cells = fillArray(Wptm.style.cells, {cell_type: null}, ij, ik);
                            } else {
                                Wptm.style.cells = fillArray(Wptm.style.cells, {cell_type: $(this).val()}, ij, ik);
                            }
                        }
                    }
                    break;
                case 'jform[jform_cell_background_color]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            if ($(this).val() === '') {
                                Wptm.style.cells = fillArray(Wptm.style.cells, {cell_background_color: null}, ij, ik);
                            } else {
                                Wptm.style.cells = fillArray(Wptm.style.cells, {cell_background_color: $(this).val()}, ij, ik);
                            }
                        }
                    }
                    break;
                case 'jform[jform_cell_border_top]':
                    for (ik = selection[1]; ik <= selection[3]; ik++) {
                        if ($('#jform_cell_border_width').val()) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_top: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, selection[0], ik);
                        } else {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_top: null});
                        }
                    }
                    break;
                case 'jform[jform_cell_border_right]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        if ($('#jform_cell_border_width').val()) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_right: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, ij, selection[3]);
                        } else {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_right: null});
                        }
                    }

                    //check if selection[0], selection[1] is merged cell then fill cell_border_right                           
                    var info = ht.mergeCells.mergedCellInfoCollection.getInfo(selection[0], selection[1]);
                    if (info) {
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_right: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, selection[0], selection[1]);
                    }

                    break;
                case 'jform[jform_cell_border_bottom]':
                    for (ik = selection[1]; ik <= selection[3]; ik++) {
                        if ($('#jform_cell_border_width').val()) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_bottom: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, selection[2], ik);
                        } else {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_bottom: null});
                        }
                    }

                    //check if selection[0], selection[1] is merged cell then fill cell_border_bottom                           
                    var info = ht.mergeCells.mergedCellInfoCollection.getInfo(selection[0], selection[1]);
                    if (info) {
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_bottom: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, selection[0], selection[1]);
                    }
                    break;
                case 'jform[jform_cell_border_left]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        if ($('#jform_cell_border_width').val()) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_left: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, ij, selection[1]);
                        } else {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_left: null});
                        }
                    }
                    break;
                case 'jform[jform_cell_border_all]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            if ($('#jform_cell_border_width').val()) {
                                val = $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val();
                            } else {
                                val = null;
                            }
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_left: val, cell_border_top: val, cell_border_right: val, cell_border_bottom: val}, ij, ik);
                        }
                    }
                    //check if selection[0], selection[1] is merged cell then fill cell_border_bottom, cell_border_right                              
                    var info = ht.mergeCells.mergedCellInfoCollection.getInfo(selection[0], selection[1]);
                    if (info) {
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_right: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, selection[0], selection[1]);
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_bottom: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, selection[0], selection[1]);
                    }
                    break;
                case 'jform[jform_cell_border_inside]':
                    if ($('#jform_cell_border_width').val()) {
                        val = $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val();
                    } else {
                        val = null;
                    }
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1] + 1; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_left: val}, ij, ik);
                        }
                    }
                    for (ij = selection[0]; ij < selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_bottom: val}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_border_outline]':
                    if ($('#jform_cell_border_width').val()) {
                        val = $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val();
                    } else {
                        val = null;
                    }

                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_left: val}, ij, selection[1]);
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_right: val}, ij, selection[3]);

                    }
                    for (ik = selection[1]; ik <= selection[3]; ik++) {
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_top: val}, selection[0], ik);
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_bottom: val}, selection[2], ik);
                    }
                    //check if selection[0], selection[1] is merged cell then fill cell_border_bottom, cell_border_right                           
                    var info = ht.mergeCells.mergedCellInfoCollection.getInfo(selection[0], selection[1]);
                    if (info) {
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_right: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, selection[0], selection[1]);
                        Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_bottom: $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val()}, selection[0], selection[1]);
                    }
                    break;
                case 'jform[jform_cell_border_vertical]':
                    if ($('#jform_cell_border_width').val()) {
                        val = $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val();
                    } else {
                        val = null;
                    }
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1] + 1; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_left: val}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_border_horizontal]':
                    if ($('#jform_cell_border_width').val()) {
                        val = $('#jform_cell_border_width').val() + "px " + $('#jform_cell_border_type').val() + " " + $('#jform_cell_border_color').val();
                    } else {
                        val = null;
                    }
                    for (ij = selection[0]; ij < selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_bottom: val}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_border_remove]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_border_left: null, cell_border_top: null, cell_border_right: null, cell_border_bottom: null}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_font_size]':
                case 'jform[jform_cell_font_family]':
                case 'jform[jform_cell_font_color]':                    
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_font_family: $('#jform_cell_font_family').val(), cell_font_size: $('#jform_cell_font_size').val(), cell_font_color: $('#jform_cell_font_color').val()}, ij, ik);                  
                        }
                    }
                    break;
                case 'jform[jform_cell_font_bold]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = toggleArray(Wptm.style.cells, {cell_font_bold: true}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_font_italic]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = toggleArray(Wptm.style.cells, {cell_font_italic: true}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_font_underline]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = toggleArray(Wptm.style.cells, {cell_font_underline: true}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_align_left]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_text_align: 'left'}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_align_right]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_text_align: 'right'}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_align_center]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_text_align: 'center'}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_align_justify]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_text_align: 'justify'}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_vertical_align_middle]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_vertical_align: 'middle'}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_vertical_align_bottom]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_vertical_align: 'bottom'}, ij, ik);
                        }
                    }
                    break;
                case 'jform[jform_cell_vertical_align_top]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {cell_vertical_align: 'top'}, ij, ik);
                        }
                    }
                    break;

                case 'jform[jform_row_height]':
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        Wptm.style.rows = fillArray(Wptm.style.rows, {height: $('#jform_row_height').val()}, ij);
                    }
                    pullDims();
                    break;
                case 'jform[jform_col_width]':
                    for (ij = selection[1]; ij <= selection[3]; ij++) {
                        Wptm.style.cols = fillArray(Wptm.style.cols, {width: $('#jform_col_width').val()}, ij);
                    }
                    pullDims();
                    break;
               
                case 'jform[jform_tooltip_width]':                     
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {tooltip_width: $('#jform_tooltip_width').val()}, ij, ik);                  
                        }
                    }                  
                     break;
                case 'tooltip_content':                    
                    for (ij = selection[0]; ij <= selection[2]; ij++) {
                        for (ik = selection[1]; ik <= selection[3]; ik++) {
                            Wptm.style.cells = fillArray(Wptm.style.cells, {tooltip_content: $('#tooltip_content').val()}, ij, ik);                  
                        }
                    }
                    break;
            }
            
            if(e.type=="change" && ($(this).attr('name')=='freeze_row' || $(this).attr('name')=='freeze_col') ) {
                
                var ht = $(Wptm.container).handsontable('getInstance');
                var htContents =  ht.getData();
                var selection = $(Wptm.container).handsontable('getSelected');
                $('#tableContainer').handsontable('destroy');
                initHandsontable(htContents);
                $(Wptm.container).handsontable('render');                
                $(Wptm.container).handsontable("selectCell", selection[0], selection[1], selection[2], selection[3]);
                $('#rightcol .referTable a').trigger('click');
            }else{
                $(Wptm.container).handsontable('render');
            }
            saveChanges();             
            
            
           
            //undo                       
            var ht = $(Wptm.container).handsontable('getInstance');
            
            if (JSON.stringify(Wptm.style) != JSON.stringify(oldStyle)) {
                ht.runHooks('afterChangeStyle', true, oldStyle);
            }
        });
    })();

    (initCssObserver = function() {
        if (!(Wptm.can.edit || (Wptm.can.editown && data.author === Wptm.author))) {
            return false;
        }
        var cssChangeWait;
        $('#jform_css').bind('input propertychange', function() {
            clearTimeout(cssChangeWait);
            cssChangeWait = setTimeout(function() {
                parseCss();
                saveChanges();
            }, 1000);
        });
    })();
    
    parseCss = function() {
        var parser = new (less.Parser);
        content = '#preview .handsontable .ht_master .wtHider .wtSpreader .htCore tbody {' + $('#jform_css').val() + '}';
        content += '.reset {background-color: rgb(238, 238, 238);border-bottom-color: rgb(204, 204, 204);border-bottom-style: solid;border-bottom-width: 1px;border-collapse: collapse;border-left-color: rgb(204, 204, 204);border-left-style: solid;border-left-width: 1px;border-right-color: rgb(204, 204, 204);border-right-style: solid;border-right-width: 1px;border-top-color: rgb(204, 204, 204);border-top-style: solid;border-top-width: 1px;box-sizing: content-box;color: rgb(34, 34, 34);display: table-cell;empty-cells: show;font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 21px;outline-width: 0px;overflow-x: hidden;overflow-y: hidden;padding-bottom: 0px;padding-left: 4px;padding-right: 4px;padding-top: 0px;text-align: center;vertical-align: top;white-space: nowrap;position: relative;}';
        content += '#preview .handsontable .ht_master .wtHider .wtSpreader .htCore tbody tr th {.reset() !important;}'
        parser.parse(content, function(err, tree) {
            if (err) {
                //Here we can throw the erro to the user
                return false;
            } else {
                Wptm.css = $('#jform_css').val();
                if ($('#headCss').length === 0) {
                    $('head').append('<style id="headCss"></style>');
                }
                $('#headCss').text(tree.toCSS());
                return true;
            }
        });
    };

    function pullDims() {
        cols = [];
        rows = [];
        for (row in Wptm.style.rows) {
            if (typeof (Wptm.style.rows[row]) !== 'undefined' && (typeof (Wptm.style.rows[row][1].height) !== 'undefined')) {
                rows[row] = Wptm.style.rows[row][1].height;
            } else {
                rows[row] = null;
            }
        }
        for (col in Wptm.style.cols) {
            if (typeof (Wptm.style.cols[col]) !== 'undefined' && (typeof (Wptm.style.cols[col][1].width) !== 'undefined')) {
                cols[col] = Wptm.style.cols[col][1].width;
            } else {
                cols[col] = null;
            }
        }
        $(Wptm.container).handsontable('updateSettings', {colWidths: cols, rowHeights: rows});
    }

    function pushDims() {
        rows = $(Wptm.container).handsontable('countRows');
        tableHeight = 0;
        for (var ij = 0; ij < rows; ij++) {
            var h = $('#tableContainer').handsontable('getRowHeight', ij);
            if (!h) {
                if (typeof (Wptm.style.rows[ij]) !== 'undefined' && (typeof (Wptm.style.rows[ij][1].height) !== 'undefined')) {
                    h = parseInt(Wptm.style.rows[ij][1].height);
                } else {
                    h = null;
                }
            }
            if (!h) {
                h = 22;
            }
            rowHeight = h;
            tableHeight += parseInt(rowHeight);
            Wptm.style.rows = fillArray(Wptm.style.rows, {height: parseInt(rowHeight)}, ij);
        }

        cols = $(Wptm.container).handsontable('countCols');
        tableWidth = 0;
        for (var ij = 0; ij < cols; ij++) {
            colWidth = $('#preview .handsontable .ht_master .htCore colgroup col:nth-child(' + parseInt(ij + 2) + ')').outerWidth();
            tableWidth += parseInt(colWidth);
            Wptm.style.cols = fillArray(Wptm.style.cols, {width: parseInt(colWidth)}, ij);
        }
        Wptm.style.table.width = tableWidth;
        configHeight = parseInt(Wptm.style.table.table_height);
        if(configHeight > 0 && tableHeight> configHeight) {
            tableHeight = configHeight;
        }else {
            var offset = $('#tableContainer').offset();            
            availableHeight = $(window).height() - offset.top + $(window).scrollTop() - 150 ;//- (getUrlVar('caninsert') && 50);             
            if(tableHeight > availableHeight) {
                tableHeight = availableHeight;
            }
        }
        $('#tableContainer').height(tableHeight + 100).trigger('resize');                                
        $(window).scrollTop($(window).scrollTop()-1); //trigger window scroll event
    }

    var customRenderer = function(instance, td, row, col, prop, value, cellProperties) {
        css = {};
        celltype = '';

        if (typeof (Wptm.style.cells) !== 'undefined') {
            //table rendering
            if ((row % 2) && typeof (Wptm.style.table.alternate_row_odd_color) !== 'undefined' && Wptm.style.table.alternate_row_odd_color) {
                css["background-color"] = Wptm.style.table.alternate_row_odd_color;
            }
            if (!(row % 2) && typeof (Wptm.style.table.alternate_row_even_color) !== 'undefined' && Wptm.style.table.alternate_row_even_color) {
                css["background-color"] = Wptm.style.table.alternate_row_even_color;
            }

            //Cells rendering
            if (typeof (Wptm.style.cells[row + "!" + col]) !== 'undefined') {
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_type) !== 'undefined' && Wptm.style.cells[row + "!" + col][2].cell_type !== '') {
                    celltype = Wptm.style.cells[row + "!" + col][2].cell_type;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_background_color) !== 'undefined' && Wptm.style.cells[row + "!" + col][2].cell_background_color !== 'undefined' && Wptm.style.cells[row + "!" + col][2].cell_background_color !== null) {
                    css["background-color"] = Wptm.style.cells[row + "!" + col][2].cell_background_color;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_border_top) !== 'undefined') {
                    css["border-top"] = Wptm.style.cells[row + "!" + col][2].cell_border_top;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_border_right) !== 'undefined') {
                    css["border-right"] = Wptm.style.cells[row + "!" + col][2].cell_border_right;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_border_bottom) !== 'undefined') {
                    css["border-bottom"] = Wptm.style.cells[row + "!" + col][2].cell_border_bottom;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_border_left) !== 'undefined') {
                    css["border-left"] = Wptm.style.cells[row + "!" + col][2].cell_border_left;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_font_bold) !== 'undefined' && Wptm.style.cells[row + "!" + col][2].cell_font_bold === true) {
                    css["font-weight"] = "bold";
                } else {
                    delete css["font-weight"];
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_font_italic) !== 'undefined' && Wptm.style.cells[row + "!" + col][2].cell_font_italic === true) {
                    css["font-style"] = "italic";
                } else {
                    delete css["font-style"];
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_font_underline) !== 'undefined' && Wptm.style.cells[row + "!" + col][2].cell_font_underline === true) {
                    css["text-decoration"] = "underline";
                } else {
                    delete css["text-decoration"];
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_text_align) !== 'undefined') {
                    css["text-align"] = Wptm.style.cells[row + "!" + col][2].cell_text_align;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_vertical_align) !== 'undefined') {
                    css["vertical-align"] = Wptm.style.cells[row + "!" + col][2].cell_vertical_align;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_font_family) !== 'undefined') {
                    css["font-family"] = Wptm.style.cells[row + "!" + col][2].cell_font_family;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_font_size) !== 'undefined') {
                    css["font-size"] = Wptm.style.cells[row + "!" + col][2].cell_font_size + "px";
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_font_color) !== 'undefined') {
                    css["color"] = Wptm.style.cells[row + "!" + col][2].cell_font_color;
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_padding_left) !== 'undefined') {
                    css["padding-left"] = Wptm.style.cells[row + "!" + col][2].cell_padding_left + "px";
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_padding_top) !== 'undefined') {
                    css["padding-top"] = Wptm.style.cells[row + "!" + col][2].cell_padding_top + "px";
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_padding_right) !== 'undefined') {
                    css["padding-right"] = Wptm.style.cells[row + "!" + col][2].cell_padding_right + "px";
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_padding_bottom) !== 'undefined') {
                    css["padding-bottom"] = Wptm.style.cells[row + "!" + col][2].cell_padding_bottom + "px";
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_background_radius_left_top) !== 'undefined') {
                    css["border-top-left-radius"] = Wptm.style.cells[row + "!" + col][2].cell_background_radius_left_top + "px";
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_background_radius_right_top) !== 'undefined') {
                    css["border-top-right-radius"] = Wptm.style.cells[row + "!" + col][2].cell_background_radius_right_top + "px";
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_background_radius_right_bottom) !== 'undefined') {
                    css["border-bottom-right-radius"] = Wptm.style.cells[row + "!" + col][2].cell_background_radius_right_bottom + "px";
                }
                if (typeof (Wptm.style.cells[row + "!" + col][2].cell_background_radius_left_bottom) !== 'undefined') {
                    css["border-bottom-left-radius"] = Wptm.style.cells[row + "!" + col][2].cell_background_radius_left_bottom + "px";
                }
            }
            //$(td).css(css);
            if (Object.keys(css).length > 0) {
                styleToRender += '.dtr' + row + '.dtc' + col + '{';
                $.each(css, function(index, value) {
                    styleToRender += index + ':' + value + ';';
                });
                styleToRender += '}';
            }
        }

        switch (celltype) {
            case 'html':
                var escaped = Handsontable.helper.stringify(value);
                //escaped = strip_tags(escaped, '<div><span><img><em><b><a>'); //be sure you only allow certain HTML tags to avoid XSS threats (you should also remove unwanted HTML attributes)
                td.innerHTML = escaped;
                $(td).addClass('isHtmlCell');
                break;
            default:
                $(td).removeClass('isHtmlCell');
                Handsontable.renderers.TextRenderer.apply(this, arguments);
                break;
        }

        /* Calculs rendering */
        if (typeof (value) === 'string' && value[0] === '=') {
            var error = false;
            var regex = new RegExp('^=(SUM|COUNT|MIN|MAX|AVG|CONCAT|sum|count|min|max|avg|concat)\\((.*?)\\)$');
            result = regex.exec(value);
            if (result !== null) {
                var cells = result[2].split(";");
                var values = [];
                for (var ij = 0; ij < cells.length; ij++) {
                    var vals = cells[ij].split(":");
                    var regex2 = new RegExp('([a-zA-Z]+)([0-9]+)');
                    if (vals.length === 1) { //single cell
                        val1 = regex2.exec(vals[0]);
                        if (val1 !== null) {
                            var datas = $("#tableContainer").handsontable('getDataAtCell', val1[2] - 1, convertAlpha(val1[1]) - 1);
                            values.push(datas);
                        } else {
                            error = true;
                        }
                    } else { //range          
                        val1 = regex2.exec(vals[0]);
                        val2 = regex2.exec(vals[1]);
                        if (val1 !== null && val2 !== null) {
                            rCells = $("#tableContainer").handsontable('getData', val1[2] - 1, convertAlpha(val1[1]) - 1, val2[2] - 1, convertAlpha(val2[1]) - 1);
                            for (var il = 0; il < rCells.length; il++) {
                                for (var ik = 0; ik < rCells[il].length; ik++) {
                                    values.push(rCells[il][ik]);
                                }
                            }
                        } else {
                            error = true;
                        }
                    }
                }
                if (error === false) {
                    var resultCalc;
                    switch (result[1].toUpperCase()) {
                        case 'SUM':
                            resultCalc = 0;
                            values.map(function(foo) {
                                v = Number(foo);
                                if (!isNaN(v)) {
                                    resultCalc = resultCalc + v;
                                }
                            });
                            break;
                        case 'COUNT':
                            resultCalc = 0;
                            values.map(function(foo) {
                                v = Number(foo);
                                if (!isNaN(v) && foo !== '') {
                                    resultCalc = resultCalc + 1;
                                }
                            });
                            break;
                        case 'MIN':
                            resultCalc = null;
                            values.map(function(foo) {
                                v = Number(foo);
                                if (!isNaN(v) && foo !== '') {
                                    if (resultCalc === null || resultCalc > v) {
                                        resultCalc = v;
                                    }
                                }
                            });
                            break;
                        case 'MAX':
                            resultCalc = null;
                            values.map(function(foo) {
                                v = Number(foo);
                                if (!isNaN(v)) {
                                    if (resultCalc === null || resultCalc < v) {
                                        resultCalc = v;
                                    }
                                }
                            });
                            break;
                        case 'AVG':
                            resultCalc = 0;
                            var n = 0;
                            values.map(function(foo) {
                                v = Number(foo);
                                if (!isNaN(v) && foo !== '') {
                                    resultCalc = resultCalc + v;
                                    n++;
                                }
                            });
                            if (n > 0) {
                                resultCalc = resultCalc / n;
                            }
                            break;
                        case 'CONCAT':
                            resultCalc = '';
                            values.map(function(foo) {
                                resultCalc = resultCalc + foo;
                            });
                            break;
                    }
                }
            }
            if (error === false) {
                $(td).text(resultCalc);
            }
        }

        $(td).addClass('dtr' + row + ' dtc' + col);

        return td;
    };

    (loadSelection = function() {
        selection = $(Wptm.container).handsontable('getSelected');
        if (!selection) {
            return;
        }
        if (typeof (Wptm.style) !== 'undefined' && typeof (Wptm.style.cells) !== 'undefined') {
            if (typeof (Wptm.style.cells[selection[0] + "!" + selection[1]]) !== 'undefined' && typeof (Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_type) !== 'undefined' && Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_type !== '') {
                $('#jform_cell_type').val(Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_type);
            } else {
                $('#jform_cell_type').val('');
            }
            $('#jform_cell_type').trigger('liszt:updated');

            if (typeof (Wptm.style.cells[selection[0] + "!" + selection[1]]) !== 'undefined' && typeof (Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_background_color) !== 'undefined') {
                $('#jform_cell_background_color').wpColorPicker('color', Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_background_color);
            } else {                
                $('#jform_cell_background_color').val('').trigger('change');
            }

            if (typeof (Wptm.style.cells[selection[0] + "!" + selection[1]]) !== 'undefined' && typeof (Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_font_size) !== 'undefined') {
                $('#jform_cell_font_size').val(Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_font_size);
            } else {
                $('#jform_cell_font_size').val(13);
            }

            if (typeof (Wptm.style.cells[selection[0] + "!" + selection[1]]) !== 'undefined' && typeof (Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_font_color) !== 'undefined') {
                $('#jform_cell_font_color').wpColorPicker('color', Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_font_color);
            } else {
                $('#jform_cell_font_color').wpColorPicker('color', '');
            }

            if (typeof (Wptm.style.cells[selection[0] + "!" + selection[1]]) !== 'undefined' && typeof (Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_font_family) !== 'undefined' && Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_font_family !== '') {
                $('#jform_cell_font_family').val(Wptm.style.cells[selection[0] + "!" + selection[1]][2].cell_font_family);
            } else {
                $('#jform_cell_font_family').val('Arial');
            }
            $('#jform_cell_font_family').trigger('liszt:updated');

            if (typeof (Wptm.style.rows[selection[0]]) !== 'undefined' && typeof (Wptm.style.rows[selection[0]][1].height) !== 'undefined') {
                $('#jform_row_height').val(Wptm.style.rows[selection[0]][1].height);
            }
            if (typeof (Wptm.style.cols[selection[0]]) !== 'undefined' && typeof (Wptm.style.cols[selection[0]][1].width) !== 'undefined') {
                $('#jform_col_width').val(Wptm.style.cols[selection[0]][1].width);
            }
            
            if( $('#tooltip_content').length > 0) {                
                if (typeof (Wptm.style.cells[selection[0] + "!" + selection[1]]) !== 'undefined' && typeof (Wptm.style.cells[selection[0] + "!" + selection[1]][2].tooltip_width) !== 'undefined') {
                    $('#jform_tooltip_width').val(Wptm.style.cells[selection[0] + "!" + selection[1]][2].tooltip_width);
                } else {
                    $('#jform_tooltip_width').val(0);
                }
               
               tinyMCE.EditorManager.execCommand('mceRemoveEditor', true, 'tooltip_content');
                if (typeof (Wptm.style.cells[selection[0] + "!" + selection[1]]) !== 'undefined' && typeof (Wptm.style.cells[selection[0] + "!" + selection[1]][2].tooltip_content) !== 'undefined') {              
                    $('#tooltip_content').val(Wptm.style.cells[selection[0] + "!" + selection[1]][2].tooltip_content);                 
                } else {
                    $('#tooltip_content').val("");                 
                }
                var contenNeedToset = $('#tooltip_content').val();
                                
                var initTT = tinymce.extend( {}, tinyMCEPreInit.mceInit[ 'tooltip_content' ] );
                try { tinymce.init( initTT ); } catch(e){}

                //add tinymce to this               
               tinyMCE.EditorManager.execCommand('mceAddEditor',true, 'tooltip_content');                            
               if(tinyMCE.EditorManager.get('tooltip_content') != null) {                   
                    var ttEditor = tinyMCE.EditorManager.get('tooltip_content');
                   if (ttEditor && ttEditor.getContainer()) {
                       ttEditor.setContent(contenNeedToset);
                   }
               }                   
            }
        } 
    });

    $('#cell_border_width_incr').click(function() {
        $('#jform_cell_border_width').val((parseInt($('#jform_cell_border_width').val() || 0)) + 1);
    });
    $('#cell_border_width_decr').click(function() {
        if ($('#jform_cell_border_width').val() === '0')
            return;
        $('#jform_cell_border_width').val(Math.abs(parseInt($('#jform_cell_border_width').val() || 1) - 1));
    });

    $('#cell_font_size_incr').click(function() {
        $('#jform_cell_font_size').val((parseInt($('#jform_cell_font_size').val() || 0)) + 1).trigger('change');
    });
    $('#cell_font_size_decr').click(function() {
        if ($('#jform_cell_font_size').val() === '0')
            return;
        $('#jform_cell_font_size').val(Math.abs(parseInt($('#jform_cell_font_size').val() || 1) - 1)).trigger('change');
    });

    function loading(e) {
        $(e).addClass('dploadingcontainer');
        $(e).append('<div class="dploading"></div>');
    }
    function rloading(e) {
        $(e).removeClass('dploadingcontainer');
        $(e).find('div.dploading').remove();
    }

    function cleanStyle(style, nbRows, nbCols) {
        for (col in style.cols) {
            if (!style.cols[col] || style.cols[col][0] >= nbCols) {
                delete style.cols[col];
            }
        }
        for (row in style.rows) {
            if (!style.rows[row] || style.rows[row][0] >= nbRows) {
                delete style.rows[row];
            }
        }
        for (cell in style.cells) {
            if (style.cells[cell][0] >= nbRows || style.cells[cell][1] >= nbCols) {
                delete style.cells[cell];
            }
        }
        for (obj in style) {
            for (cell in style[obj]) {
                propertiesPos = style[obj][cell].length - 1;
                for (property in style[obj][cell][propertiesPos]) {
                    if (style[obj][cell][propertiesPos][property] === null) {
                        delete style[obj][cell][propertiesPos][property];
                    }
                }
            }
        }
        return style;
    }

    var CustomEditor = Handsontable.editors.TextEditor.prototype.extend();

    CustomEditor.prototype.init = function() {
        //Call the original createElements method               
        Handsontable.editors.TextEditor.prototype.init.apply(this, arguments);
    };

    CustomEditor.prototype.open = function() {
        $(this.TEXTAREA).attr('id', 'editor1');
        if (typeof (Wptm.style.cells[this.row + '!' + this.col]) !== 'undefined' && typeof (Wptm.style.cells[this.row + '!' + this.col][2].cell_type) !== 'undefined' && Wptm.style.cells[this.row + '!' + this.col][2].cell_type === 'html') {
                
            tinyMCE.EditorManager.execCommand('mceRemoveEditor', true, 'editor1');
            var init = tinymce.extend( {}, tinyMCEPreInit.mceInit[ 'editor1' ] );
            try { tinymce.init( init ); } catch(e){}

                //add tinymce to this               
               tinyMCE.EditorManager.execCommand('mceAddEditor',true, 'editor1');
              
        } else {
            tinyMCE.EditorManager.execCommand('mceRemoveEditor',true, 'editor1');
            //if (typeof (CKEDITOR.instances.editor1) !== 'undefined') {
               // CKEDITOR.instances.editor1.destroy();
           // }
        }
        Handsontable.editors.TextEditor.prototype.open.apply(this, arguments);
    };

    CustomEditor.prototype.getValue = function() {        
        if (typeof (tinyMCE) !== 'undefined' && tinyMCE.EditorManager.get('editor1')) {          
           return tinyMCE.EditorManager.get('editor1').getContent(); 
        } else {
            return Handsontable.editors.TextEditor.prototype.getValue.apply(this, arguments);
        }
    };

    CustomEditor.prototype.setValue = function(newValue) {
        if (typeof (tinyMCE) !== 'undefined' && tinyMCE.EditorManager.get('editor1')) {       
            tinyMCE.EditorManager.get('editor1').setContent(newValue); 
        } else {
            return Handsontable.editors.TextEditor.prototype.setValue.apply(this, arguments);
        }
    };

    CustomEditor.prototype.close = function() {       
        
        if (typeof (Wptm.style.cells[this.row + '!' + this.col]) !== 'undefined' && typeof (Wptm.style.cells[this.row + '!' + this.col][2].cell_type) !== 'undefined' && Wptm.style.cells[this.row + '!' + this.col][2].cell_type === 'html') {
            // updateDimession();
        }     
        
        return Handsontable.editors.TextEditor.prototype.close.apply(this, arguments);
    };

    $('#editToolTip').wptm_leanModal({ top : 100,closeButton: '#cancelToolTipbtn', modalShow: function(){ } });   
    $("#saveToolTipbtn").click(function(){
        var ttEditor = tinyMCE.EditorManager.get('tooltip_content');
        ttEditor.save();
        $("#tooltip_content").trigger("change");
        //close leanModal
        $("#lean_overlay").fadeOut(200);
        $("#wptm_editToolTip").css({"display":"none"})
    });
    
    tinyMCEPreInit.mceInit[ 'editor1' ] =  tinyMCEPreInit.mceInit[ 'wptmditor' ] ;    
    tinyMCEPreInit.mceInit[ 'tooltip_content' ] =  tinyMCEPreInit.mceInit[ 'wptm_tooltip' ] ;    
    tinyMCE.EditorManager.execCommand('mceRemoveEditor', true, 'wptmditor');
    $('#wp-wptmditor-wrap').hide();


    function setCookie(cname, cvalue) {
        var d = new Date();
        d.setTime(d.getTime() + (30*(60*60*24*1000))); // set cookie time to 30 days
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    $('.updateHideBtn, .updateHideTxt').unbind('click').click(function () {
        $('#updateGroup').hide(300);
        setCookie('WPTM_hide_upgrade', 1);
    })
});

(function($,sr){

  // debouncing function from John Hann
  // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
  var debounce = function (func, threshold, execAsap) {
      var timeout;

      return function debounced () {
          var obj = this, args = arguments;
          function delayed () {
              if (!execAsap)
                  func.apply(obj, args);
              timeout = null;
          };

          if (timeout)
              clearTimeout(timeout);
          else if (execAsap)
              func.apply(obj, args);

          timeout = setTimeout(delayed, threshold || 100);
      };
  }
  // smartresize 
  jQuery.fn[sr] = function(fn){  return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

})(jQuery,'smartresize');

function updateDimession() {
   
        rows = [];
        var i = 0;
        for (row in Wptm.style.rows) {
            var h = jQuery('#tableContainer .ht_master .htCore tr').eq(i+1).height() ;
            rows[row]  = h;
            i++;            
        }
       
        jQuery(Wptm.container).handsontable('updateSettings', {rowHeights: rows});
            
        var ht = jQuery(Wptm.container).handsontable('getInstance');
        ht.runHooks('afterRowResize');                         
}
/**
 * Insert the current table into a content editor
 */
function insertTable() {
    id = jQuery('#categorieslist li.wptmtable.active').data('id-table');    
    code = '<img src="' + wptm_dir + '/app/admin/assets/images/t.gif"' +
            'data-wptmtable="' + id + '"' +
            'style="background: url(' + wptm_dir + '/app/admin/assets/images/spreadsheet.png) no-repeat scroll center center #D6D6D6;' +
            'border: 2px dashed #888888;' +
            'height: 150px;' +
            'border-radius: 10px;' +
            'width: 99%;" />';
    window.parent.tinyMCE.execCommand('mceInsertContent',false,code);
    jQuery("#lean_overlay",window.parent.document).fadeOut(300);
    jQuery('#wptmmodal',window.parent.document).fadeOut(300);
    return false;
   
}

//From http://jquery-howto.blogspot.fr/2009/09/get-url-parameters-values-with-jquery.html
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function getUrlVar(v) {
    if (typeof (getUrlVars()[v]) !== "undefined") {
        return getUrlVars()[v];
    }
    return null;
}

function preg_replace(array_pattern, array_pattern_replace, my_string) {
    var new_string = String(my_string);
    for (i = 0; i < array_pattern.length; i++) {
        var reg_exp = RegExp(array_pattern[i], "gi");
        var val_to_replace = array_pattern_replace[i];
        new_string = new_string.replace(reg_exp, val_to_replace);
    }
    return new_string;
}

//https://gist.github.com/ncr/399624
jQuery.fn.single_double_click = function(single_click_callback, double_click_callback, timeout) {
    return this.each(function() {
        var clicks = 0, self = this;
        jQuery(this).click(function(event) {
            clicks++;
            if (clicks == 1) {
                setTimeout(function() {
                    if (clicks == 1) {
                        single_click_callback.call(self, event);
                    } else {
                        double_click_callback.call(self, event);
                    }
                    clicks = 0;
                }, timeout || 300);
            }
        });
    });
}


function fillArray(array, val, val1, val2) {
    if (typeof (val2) === 'undefined') {
        if (typeof (array[val1]) !== 'undefined') {
            array[val1][1] = jQuery.extend(array[val1][1], val);
        } else {
            array[val1] = [val1, {}];
            array[val1][1] = val;
        }
    } else {
        if (typeof (array[val1 + "!" + val2]) !== 'undefined') {
            array[val1 + "!" + val2][2] = jQuery.extend(array[val1 + "!" + val2][2], val);
        } else {
            array[val1 + "!" + val2] = [val1, val2, {}];
            array[val1 + "!" + val2][2] = val;
        }
    }
    return array;
}

function toggleArray(array, val, val1, val2) {
    if (typeof (val2) === 'undefined') {
        if (typeof (array[val1]) !== 'undefined') {
            if (typeof (val) === 'object') {
                for (key in val) {
                    if (typeof (array[val1][1][key] !== 'undefined')) {
                        array[val1][1][key] = !array[val1][1][key];
                    } else {
                        array[val1][1][key] = val[key];
                    }
                }
            } else {
                array[val1][1] = jQuery.extend(array[val1][1], val);
            }
        } else {
            array[val1] = [val1, {}];
            array[val1][1] = val;
        }
    } else {
        if (typeof (array[val1 + "!" + val2]) !== 'undefined') {
            if (typeof (val) === 'object') {
                for (key in val) {
                    if (typeof (array[val1 + "!" + val2][2][key] !== 'undefined')) {
                        array[val1 + "!" + val2][2][key] = !array[val1 + "!" + val2][2][key];
                    } else {
                        array[val1 + "!" + val2][2][key] = val[key];
                    }
                }
            } else {
                array[val1 + "!" + val2][2] = jQuery.extend(array[val1 + "!" + val2][2], val);
            }
        } else {
            array[val1 + "!" + val2] = [val1, val2, {}];
            array[val1 + "!" + val2][2] = val;
        }
    }

    return array;
}

//Code from http://stackoverflow.com/questions/9905533/convert-excel-column-alphabet-e-g-aa-to-number-e-g-25
var convertAlpha = function(val) {
    var base = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', i, j, result = 0;

    for (i = 0, j = val.length - 1; i < val.length; i += 1, j -= 1) {
        result += Math.pow(base.length, j) * (base.indexOf(val[i]) + 1);
    }

    return result;
};

function strip_tags(input, allowed) {
    //  discuss at: http://phpjs.org/functions/strip_tags/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Luke Godfrey
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

    allowed = (((allowed || '') + '')
            .toLowerCase()
            .match(/<[a-z][a-z0-9]*>/g) || [])
            .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
            commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '')
            .replace(tags, function($0, $1) {
                return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
            });
}

clone = function(v) {
    return JSON.parse(JSON.stringify(v));
};

function loadTableContructor(){
    var $ = jQuery;
    var id_table = $('li.wptmtable.active').data('id-table');
    var table_type = $('li.wptmtable.active').data('table-type');

    $("#mainTable .tabDataSource").hide();
    $("#mainTable .groupTable" + id_table).show();
    if(table_type=='mysql') {
        if ($("#tabDataSource_" + id_table).length == 0) {
            var firstTab = $('#mainTable li').get(0);
            $(firstTab).after('<li><a data-toggle="tab" id="tabDataSource_'+id_table+'" class="tabDataSource groupTable' + id_table + '" href="#dataSource_' + id_table+ '">Data Source</a></li>');
            $('#mainTabContent.tab-content').append('<div class="tab-pane" id="dataSource_' + id_table + '">' +
                    '<div class="dataSourceContainer" style="padding-top:10px" ></div></div>');

            $.ajax({
                url: wptm_ajaxurl+"view=dbtable&id_table=" + id_table,
                type: "GET"
            }).done(function(data) {
                $("#dataSource_"+id_table ).html (data);
            });
        }
    }
    //do nothing
}


