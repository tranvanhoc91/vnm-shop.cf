(function () {
    parent.jQuery('#TB_window,#TB_iframeContent').width(960).height(545);
    parent.jQuery('#TB_window').height(575).css('z-index', 100000001);
    parent.jQuery('#TB_window').css('margin-left', -(960 / 2));
})();

jQuery(document).ready(function ($) {
    $(window).load(function() {
        // Animate loader off screen
        $(".preload-img").fadeOut("slow");
        $(".container").show();
        defaultMax = $(".column-sliders").first().width();

        // Open from tinyMCE window
        var editor = parent.tinyMCE.activeEditor;
        if (typeof (editor) !== 'undefined') {
            var selected = editor.selection.getNode();
            if (selected.nodeName === 'BODY') {
                alert(myCol.chooseLine);
                parent.tb_remove();
            }
            var par = editor.dom.getParent(selected, 'div.csRow');
            if (par) {
                csColumns = par.getElementsByClassName('csColumn');
                for (var i = 0; i < csColumns.length; i++) {
                    var csColumn = csColumns[i],
                        csHTML = csColumn.getElementsByClassName('csColumn_text')[0].innerHTML,
                        posX0 = editor.dom.getAttrib(csColumn, 'data-posx0'),
                        posX1 = editor.dom.getAttrib(csColumn, 'data-posx1'),
                        col_pad = editor.dom.getAttrib(csColumn, 'data-padding'),
                        col_res = editor.dom.getAttrib(csColumn, 'data-responsive'),
                        colWidth = (posX1 - posX0) / 100 * defaultMax;

                    columnAdd(Math.floor(posX0/100*defaultMax), Math.floor(colWidth), csHTML);
                }

                col_res = parseInt(col_res);
                $('#col_responsive').prop('checked', col_res);
                $('#col_padding').val(col_pad);
                addColumnsPad();

                var index = $('#columnStructure li:has(input[value="-1"])').index();
                $('#columnStructure').ddslick('select', {index: index});
            }
            return false;
        }
    });

    var columns = [];
    var defaultSeperator = 12;
    var defaultWidth = 150;
    var minWidth = 100;

    $('#columnStructure').ddslick({
        width: 272,
        onSelected: function (data) {
            var colNum = data.selectedData.value;
            colNum = parseInt(colNum);
            if (colNum === 0 && columns.length !== 0) {
                var cfm = confirm(myCol.confirm);
                if (cfm) columnRemoveAll();
            }

            if (colNum > 0) {
                if (columns.length !== 0) {
                    cfm = confirm(myCol.confirm);
                    if (cfm) {
                        columnRemoveAll();
                        var colWidth = parseInt((defaultMax - defaultSeperator * (colNum - 1)) / colNum);
                        for (var i = 0; i < colNum; i++) {
                            columnAdd(i * (colWidth + defaultSeperator), colWidth);
                        }
                        $('.btns-add').addClass('disabled').unbind('click');
                    }
                } else {
                    colWidth = parseInt((defaultMax - defaultSeperator * (colNum - 1)) / colNum);
                    for (var i = 0; i < colNum; i++) {
                        columnAdd(i * (colWidth + defaultSeperator), colWidth);
                    }
                    $('.btns-add').addClass('disabled').unbind('click');
                }
            } else {
                $('.btns-add').removeClass('disabled').unbind('click');
                verifyAddButton();
                initAddButton();
                return false;
            }
        }
    });

    $('#btn_save').unbind('click').click(function () {
        insertColumns();
    });
    $('#col_padding').on('input change propertychange click', function () {
        addColumnsPad();
    });

    function addColumnsPad() {
        var pad_val = $('#col_padding').val();
        $('.columns_preview').css('padding', pad_val);
    }

    function verifyAddButton() {
        if (columns.length === 5) {
            $('.btns-add').addClass('disabled').unbind('click');
        } else {
            $('.btns-add').removeClass('disabled').bind('click');
        }
    }

    function initAddButton() {
        $('.btns-add:not(.disabled)').unbind('click').click(function () {
            var newPosX = 0;
            if (columns.length === 0) {
                newPosX = 1;
            } else {
                newPosX = columns[columns.length - 1]['posV1'] + 5;
            }

            if (columnAddPosValid(newPosX)) {
                columnAdd(newPosX);
            } else {
                return false;
            }
            verifyAddButton();
        });
    }

    function columnAddPosValid(posX) {
        if (columns.length === 0) return true;

        valid = true;
        var posX1 = posX + defaultWidth;
        if (posX1 >= defaultMax) {
            alert(myCol.noSpace);
            valid = false;
        }

        return valid;
    }

    function columnAdd(posX, colWidth, content) {
        if (typeof (colWidth) === 'undefined') {
            colWidth = defaultWidth;
        }
        var PosX1 = posX + colWidth;
        if (PosX1 > defaultMax) PosX1 = defaultMax;
        if (posX < 0) posX = 0;

        var guid = (ColumnsHelper.S4() + ColumnsHelper.S4() + "-" + ColumnsHelper.S4() + "-4" + ColumnsHelper.S4().substr(0, 3) + "-" + ColumnsHelper.S4() + "-" + ColumnsHelper.S4() + ColumnsHelper.S4() + ColumnsHelper.S4()).toLowerCase();
        var columnsContainer = $('.column-sliders').first();
        var newColumn = '<div class="slider-range" id="slider-range-' + guid + '"></div>';
        if (columns.length === 0) {
            newColOrdering = 0;
            columns[0] = {'posV0' : posX, 'posV1' : PosX1, 'width' : colWidth, 'guid' : guid};
            columnsContainer.html(newColumn);
        } else {
            columns.push({'posV0' : posX, 'posV1' : PosX1, 'width' : colWidth, 'guid' : guid});
            columns.sort(ColumnsHelper.comparePos);
            newColOrdering = ColumnsHelper.arrayObjectIndexOf(columns, guid, 'guid');

            if (newColOrdering > 0) {
                columnsContainer.find('.slider-range').eq(newColOrdering - 1).after(newColumn);
            } else {
                columnsContainer.prepend(newColumn);
            }
        }
        $('#droppable').append('<span class="slider-label" id="sliderMin-label-'+ guid +'"></span>')
            .append('<span class="slider-label" id="sliderMax-label-'+ guid +'"></span>');

        // Init content preview
        var pad_val = $('#col_padding').val();
        var contentPreview = '<div class="columns_wrapper" id="columns-wrapper-'+guid+'" style="position: relative; float: left">';
        contentPreview += '<div id="columns-preview-'+ guid +'" class="columns_preview" style="padding: '+pad_val+'px">';
        if (typeof (content) === 'undefined' ) {
            contentPreview += 'Lorem ipsum dolor sit amet';
        } else {
            contentPreview += content;
        }
        contentPreview += '</div>';
        contentPreview += '<div class="remove-columns-btn " id="remove-'+guid+'"><i class="dashicons dashicons-no-alt"></i></div>';
        contentPreview += '</div>';

        if (newColOrdering === 0) {
            $('.contentHolder').prepend(contentPreview);
        } else {
            $('.contentHolder').find('.columns_wrapper').eq(newColOrdering - 1).after(contentPreview);
        }
        updateSliders();
    }

    function columnRemove(col_id) {
        for (var i = 0; i < columns.length; i++) {
            if (columns[i].guid == col_id) {
                columns.splice(i, 1);
                $('#slider-range-'+ col_id).remove();
                $('#sliderMin-label-'+ col_id).remove();
                $('#sliderMax-label-'+ col_id).remove();
                $('#columns-wrapper-'+ col_id).remove();
                $('#columns-preview-'+ col_id).remove();
                verifyAddButton();
                initAddButton();
                return true;
            }
        }
    }

    function columnRemoveAll() {
        columns = [];
        $('.slider-range').remove();
        $('.slider-label').remove();
        $('.columns_wrapper').remove();
        $('.columns_preview').remove();
    }
    
    function updateSliders() {
        for (var i = 0; i < columns.length; i++) {
            guid = columns[i].guid;
            if (i === 0) {
                posMin = 0;
            } else {
                posMin = columns[i-1].posV1;
            }
            if (i === columns.length - 1) {
                posMax = defaultMax;
            } else {
                posMax = columns[i+1].posV0;
            }

            $('#slider-range-'+ guid).css({'left' : posMin + 'px', 'width' : (posMax - posMin) + 'px'});
            $('#sliderMin-label-'+ guid).css('left', columns[i].posV0 - 10).text(columns[i].posV0);
            $('#sliderMax-label-'+ guid).css('left', columns[i].posV1 - 20).text(columns[i].posV1);
            if (i === 0) {
                $('#columns-preview-'+ guid).css('margin-left' , columns[i].posV0).css('width', columns[i].posV1 - columns[i].posV0);
            } else {
                $('#columns-preview-'+ guid).css('margin-left', columns[i].posV0 - columns[i-1].posV1).css( 'width' , columns[i].posV1 - columns[i].posV0);
            }

            $('.columns_preview').attr('contenteditable', true).attr('spellcheck', true).hover(function () {
                thisBtn = $(this).parent().find('.remove-columns-btn');
                thisBtn.show();
                thisBtn.hover(function () {
                    thisBtn.show();
                }, function () {
                    thisBtn.hide();
                })
            }, function () {
                thisBtn = $(this).parent().find('.remove-columns-btn');
                thisBtn.hide();
            });
            $('.remove-columns-btn').unbind('click').click(function () {
                colID = $(this).attr('id');
                colID = colID.replace('remove-', '');
                cfrm = confirm(myCol.confirmRemove);
                if (cfrm) {
                    columnRemove(colID);
                    updateSliders();
                } else {
                    return false;
                }
            });

            $('#slider-range-'+ guid).slider({
                range: true,
                min: posMin,
                max: posMax,
                values: [columns[i].posV0, columns[i].posV1],
                slide: function (event, ui) {
                    var guid = this.id.replace('slider-range-', '');
                    currentCol = ColumnsHelper.arrayObjectIndexOf(columns, guid, 'guid');
                    if ((ui.values[1] - ui.values[0]) < minWidth) {
                        return false;
                    }
                    columns[currentCol].posV0 = ui.values[0];
                    columns[currentCol].posV1 = ui.values[1];
                    updateSliders();
                }
            })
        }

        tinymce.init({
            selector: ".columns_preview",
            menubar : false,
            inline: true,
            plugins: ['textcolor link image'],
            toolbar: ["styleselect | forecolor backcolor | link image"]
        });
    }

    function insertColumns() {
        var editor = parent.tinyMCE.activeEditor;
        if (typeof (editor) !== 'undefined') {
            var id = style_guid(),
                col_responsive = jQuery('#col_responsive').is(':checked');
            id = 'wpsecol-' + id;
            selected = editor.selection.getNode();
            par = editor.dom.getParent(selected, 'div.csRow');

            if (par) { // Remove old style block
                par_id = editor.dom.getAttrib(par, 'id');
                style_id = 'style-' + par_id;
                style_block = editor.dom.get(style_id);
                if (style_block) style_block.remove();
                updateColumns(id, col_responsive);
                return false;
            }

            content = getColumnsHtml(id);
            var wrapper = document.createElement('div');
            wrapper.innerHTML = content;

            selected.parentNode.insertBefore(wrapper.firstChild, selected);
            // Add responsive style block
            if (col_responsive) editor.dom.add(editor.getBody(), responsiveStyles(id));

            editor.undoManager.add();
            parent.tb_remove();
        }
        return false;
    }
    
    function updateColumns(id, responsive) {
        var editor = parent.tinyMCE.activeEditor;
        var par = editor.dom.getParent(selected, 'div.csRow');
        var content = getColumnsHtml(id);
        var wrapper = document.createElement('div');
        wrapper.innerHTML = content;

        editor.dom.replace(wrapper.firstChild, par, false);
        if (responsive) editor.dom.add(editor.getBody(), responsiveStyles(id));

        editor.undoManager.add();
        parent.tb_remove();
    }
});

function getColumnsHtml(id) {
    var $ = jQuery,
        columnsHtml = '',
        defaultMax = $('.column-sliders').first().width(),
        imgGap = '<img src="'+ myCol.url +'img/1x1-pixel.png" />',
        lastPoint = 0,
        totalWidth = 0,
        col_padding = $('#col_padding').val(),
        responsive = jQuery('#col_responsive').is(':checked') ? 1 : 0;
    
    if ($('.columns_preview').length > 0) {
        columnsHtml += '<div class="csRow" id="'+id+'">';
        $('.columns_preview').each(function (index, e) {
            var guid = e.id.replace('columns-preview-', '');
            editorContent = tinyMCE.get(e.id).getContent();
            var values = $('#slider-range-'+ guid).slider('values');
            colWidth = ((values[1] - values[0] - 1) / defaultMax) * 100;
            gapWidth = ((values[0] - lastPoint) / defaultMax) * 100;
            lastPoint = values[1];
            totalWidth += gapWidth + colWidth;
            posX0 = (values[0]/defaultMax)*100;
            posX1 = (values[1]/defaultMax)*100;
            columnsHtml += '<div class="csColumnGap" style="margin: 0; padding: 0; float: left; width: '+gapWidth+'%;">'+ imgGap +'</div>';
            columnsHtml += '<div data-posX0="'+ posX0 +'" data-posX1="'+ posX1 +'" data-padding="'+col_padding+'" data-responsive="'+responsive+'" class="csColumn" style="margin: 0; padding: 0; float: left; width:'+ colWidth+'%;" ><div style="padding: '+col_padding+'px;"><div class="preview_text csColumn_text">' + editorContent +'</div></div></div>';
        });

        var remainWidth = 100 - totalWidth;
        columnsHtml += '<div class="csColumnGap" style="margin: 0; padding: 0; float: left; width: '+remainWidth+'%;">'+ imgGap +'</div>';
        columnsHtml += '<div style="clear: both; float: none; display: block; visibility: hidden; width: 0; font-size: 0; line-height: 0;"></div>';
        columnsHtml += '</div>';
    }

    return columnsHtml;
}

function responsiveStyles(id) {
    var style = '';
    style += '<style>@media (max-width: 768px) {\n' +
        '    #'+id+' .csColumn {\n' +
        '        display: block;\n' +
        '        width: 100% !important;\n' +
        '    }\n' +
        '    #'+id+' .csColumnGap {\n' +
        '        display: none;\n' +
        '    }\n' +
        '}</style>';
    res_style_wrapper = document.createElement('div');
    res_style_wrapper.id = 'style-' + id;
    res_style_wrapper.innerHTML = style;

    return res_style_wrapper;
}

function style_guid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
        s4() + '-' + s4() + s4() + s4();
}

ColumnsHelper = {};
ColumnsHelper.S4 = function() {
    return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
};
ColumnsHelper.arrayObjectIndexOf = function(myArray, searchTerm, property) {
    for (var i = 0, len = myArray.length; i < len; i++) {
        if (myArray[i][property] === searchTerm)
            return i;
    }
    return -1;
};
ColumnsHelper.comparePos = function(a, b) {
    if (a.posV0 < b.posV0)
        return -1;
    if (a.posV0 > b.posV0)
        return 1;
    return 0;
};
