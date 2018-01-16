/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

jQuery(document).ready(function ($) {
    if (typeof(Wpfd) == 'undefined') {
        Wpfd = {};
        Wpfd.maxfilesize = 300;
    }

    _ = function (text) {
        if (typeof(l10n) !== 'undefined') {
            return l10n[text];
        }
        return text;
    };

    var leftwidth = parseInt($("#mycategories").width());
    $("#mycategories").resizable({handles:"e"}).resize(function() {
        var width = parseInt(this.style.width);
        return this.style['-webkit-flex-basis'] = (width-leftwidth) + 'px';
    });
    var selectedFiles = [];

    // file action
    Wpfd.submitbutton = function ($task) {
        if($task == 'files.uncheck'){
            $('.file').removeClass('selected');
            $('.wpfd-btn-toolbar').hide();
            showCategory();
        }
        else if($task == 'files.delete'){
            bootbox.confirm( wpfd_admin.msg_ask_delete_files , function (result) {
                if (result === true) {
                    sourceCat = $('#categorieslist li.active').data('id-category');
                    selectedFiles = [];
                    $('#preview .file.selected').each(function (index) {
                        selectedFiles.push($(this).data('id-file'));
                    });
                    cat_target = $('#categorieslist li.active').data('id-category');
                    if (cat_target == sourceCat) {
                        while (selectedFiles.length > 0) {
                            id_file = selectedFiles.pop();
                            $.ajax({
                                url: ajaxurl + "task=file.delete&id_file=" + id_file + "&id_category=" + sourceCat,
                                type: "POST"
                            }).done(function (data) {
                            });
                            $('.actions .trash').parents('tr[data-id-file="'+id_file+'"]').fadeOut(500, function () {
                                $(this).remove();
                            });
                        }
                        $.gritter.add({text: wpfd_admin.msg_remove_files});
                    }
                }
            });
            return false;
        }

    };

    // set user value on user modal
    $('.button-select-user').on('click', function () {
        var $this = $(this);
        var username = $this.data('name');
        var uservalue = $this.data('user-value');
        var type = $('.fieldtype').val();
        window.parent.jQuery('.' + type + '-name').val(username);
        window.parent.jQuery('.' + type).val(uservalue);
        window.parent.tb_remove();
    });

    /**
     * Init sortable files
     * Save order after each sort
     */
    $('#preview').sortable({
        placeholder: 'highlight file',
        revert: true,
        distance: 5,
        items: ".file",
        tolerance: "pointer",
        appendTo: "body",
        cursorAt: {top: 0, left: 0},
        helper: function (e, item) {
            filename = $(item).find('.title').text() + "." + $(item).find('.ext').text();
            count = $('#preview').find('.file.selected').length;
            if (count > 1) {
                return $("<div id='file-handle' class='ui-widget-header' ><div>" + filename + "</div><span class='fCount'>" + count + "</span></div>");
            } else {
                return $("<div id='file-handle' class='ui-widget-header' ><div>" + filename + "</div></div>");
            }
        },
        update: function () {
            var json = '';
            $.each($('#preview .file'), function (i, val) {
                if (json !== '') {
                    json += ',';
                }
                json += '"' + i + '":' + $(val).data('id-file');
            });
            json = '{' + json + '}';
            $.ajax({
                url: ajaxurl + "task=files.reorder&order=" + json,
                type: "POST"
            }).done(function (data) {
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_ordering_file2});
                if ($('#ordering').val() != 'ordering') {
                    $('#ordering option[value="ordering"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                    $('#orderingdir option[value="asc"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                    id_category = $('input[name=id_category]').val();
                    $.ajax({
                        url: ajaxurl + "task=category.saveparams&id=" + id_category,
                        type: "POST",
                        data: $('#category_params').serialize()
                    }).done(function (data) { console.log(data) });
                }
            });
        },
        /** Prevent firefox bug positionnement **/
        start: function (event, ui) {
            $(ui.helper).css('width', 'auto');

            var userAgent = navigator.userAgent.toLowerCase();
            if (ui.helper !== "undefined" && userAgent.match(/firefox/)) {
                ui.helper.css('position', 'absolute');
            }
            ui.placeholder.html("<td colspan='8'></td>");

        },
        stop: function (event, ui) {

            $('#file-handle').removeClass('wpfdzoomin');

        },
        beforeStop: function (event, ui) {
            var userAgent = navigator.userAgent.toLowerCase();
            if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                ui.helper.css('margin-top', 0);
            }
        },
        beforeRevert: function(e, ui) {

            if( $('#categorieslist .wpfddropzoom').length >0 ) {
                return false; // copy/move file
            }
            $('#file-handle').addClass('wpfdzoomin');
            $('#file-handle').fadeOut();
            return  true ;
        }
    });
    $('#preview').disableSelection();

    /* init menu actions */
    initMenu();
    /*Color field*/
    initColor();
    /* Load category */
    updatepreview();

    /* Load nestable */
    $('.nested').nestable({maxDepth: 8}).on('change', function (event, e) {
        var isCloudItem = $(e).find('a.t i.google-drive-icon').length;
        var isDropboxItem = $(e).find('a.t i.dropbox-icon').length;
        var itemChangeType = 'default';
        if (isCloudItem > 0) {
            itemChangeType = 'googledrive';
        } else if (isDropboxItem > 0) {
            itemChangeType = 'dropbox';
        }

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

        var data = $('.nested').nestable('serialize');
        $.ajax({
            url: ajaxurl + "task=category.changeOrder&pk=" + pk + "&position=" + position + "&ref=" + ref + "&dragType=" + itemChangeType,
            type: "POST",
            dataType: 'json',
            data: {dto: data}
        }).done(function (result) {
            //result = jQuery.parseJSON(data);
            if (result.response === true) {
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_move_category});
            } else {
                bootbox.alert(result.response);
            }
        });
    });

    var ctrlDown = false;
    $(window).on("keydown", function (event) {
        if (event.ctrlKey || event.metaKey) {
            ctrlDown = true;
        }
    }).on("keyup", function (event) {
        ctrlDown = false;
    });
    // init categories items
    catDroppable = function () {
        $("#categorieslist .dd-content").droppable({
            accept: '.file',
            revert: 'valid',
            hoverClass: "dd-content-hover",
            tolerance: "pointer",
            over: function( event, ui ) {
                $(event.target).closest('li').addClass("wpfddropzoom");
            },
            out: function( event, ui ) {
                $(event.target).closest('li').removeClass("wpfddropzoom");
            },
            drop: function (event, ui) {

                $(this).addClass("ui-state-highlight");
                cat_target = $(event.target).closest('li').data("id-category");
                current_cat = $("#categorieslist .dd-item.active").data('id-category');

                if (current_cat != cat_target) {
                    count = $('#preview').find('.file.selected').length;
                    if (count > 0) { //multiple file
                        iFile = 0;
                        $('#preview').find('.file.selected').each(function () {
                            id_file = $(this).data("id-file");
                            if (ctrlDown) { //copy file
                                $.ajax({
                                    url: ajaxurl + "task=files.copyfile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                    type: "POST"
                                }).done(function (data) {
                                    iFile++;
                                    if (iFile == count) {
                                        $('.gritter-item-wrapper ').remove();
                                        $.gritter.add({text: wpfd_admin.msg_copy_file});
                                    }
                                });
                            } else {
                                $.ajax({
                                    url: ajaxurl + "task=files.movefile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                    type: "POST",
                                    dataType: "json",
                                }).done(function (result) {
                                    iFile++;
                                    if (typeof result.datas.id_file != "undefined") {
                                        $('tr[data-id-file="' + result.datas.id_file + '"]').remove();
                                    }
                                    if (iFile == count) {
                                        $('.gritter-item-wrapper ').remove();
                                        $.gritter.add({text: wpfd_admin.msg_move_file});
                                    }
                                });
                            }
                        })
                    }
                    else {  //single file
                        id_file = $(ui.draggable).data("id-file");
                        if (ctrlDown) { //copy file
                            $.ajax({
                                url: ajaxurl + "task=files.copyfile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                type: "POST"
                            }).done(function (data) {
                                $('.gritter-item-wrapper ').remove();
                                $.gritter.add({text: wpfd_admin.msg_copy_file});
                            });
                        } else {
                            $.ajax({
                                url: ajaxurl + "task=files.movefile&id_category=" + cat_target + '&active_category=' + current_cat + '&id_file=' + id_file,
                                type: "POST"
                            }).done(function (data) {
                                $('tr[data-id-file="' + id_file + '"]').remove();
                                $('.gritter-item-wrapper ').remove();
                                $.gritter.add({text: wpfd_admin.msg_move_file});
                            });
                        }
                    }
                }
                $(this).removeClass("ui-state-highlight");
                $(event.target).closest('li').removeClass("wpfddropzoom");
            }
        });
    }
    catDroppable();


    /* Init version dropbox */
    initDropboxVersion($('#fileversion'));
    $('#upload_button_version').on('click', function () {
        $('#upload_input_version').trigger('click');
        return false;
    });

    function showCategory() {
        $('.fileblock').fadeOut();
        $('#insertfile').fadeOut();
        $('#rightcol').fadeOut();
    }

    function showFile(e) {
        $('#rightcol').fadeIn();
        $('.fileblock').fadeIn();
        $('#insertfile').fadeIn();
    }


    /**
     * Reload a category preview
     * @param id_category
     * @param id_file
     */
    function updatepreview(id_category, id_file, $ordering, $ordering_dir) {
        if (typeof(id_category) === "undefined" || id_category === null) {
            id_category = $('#categorieslist li.active').data('id-category');
            $('input[name=id_category]').val(id_category);
        }
        if ($("#wpreview").length == 0) return;
        loading('#wpreview');

        var url = ajaxurl + "view=files&format=raw&id_category=" + id_category;
        if ($ordering != null) {
            url += '&orderCol=' + $ordering;
        }

        if ($ordering_dir === 'asc') {
            url += '&orderDir=desc';
        } else if ($ordering_dir === 'desc') {
            url += url + '&orderDir=asc';
        }

        $.ajax({
            url: url,
            type: "POST"
        }).done(function (data) {
            $('#wpfd_filter_catid').val(id_category);
            $('#preview').html($(data));
            if ($ordering != null) {
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_ordering_file});
            }

            if (selectedFiles.length == 0) {
                $('.wpfd-btn-toolbar').hide();
            }

            if (wpfd_permissions.can_edit_category) {
                var remote_file = _('add_remote_file') == '1' ? '<a href="" id="add_remote_file" class="button button-primary button-big">' + _('Add remote file', 'Add remote file') + '</a> ' : '';
                $('<div id="dropbox"><span class="message">' + _('Drop files here to upload', 'Drop files here to upload') + '. ' + _('Or use the button below', 'Or use the button below') + '</span><input class="hide" type="file" id="upload_input" multiple="">' + remote_file + '<a href="" id="upload_button" class="button button-primary button-big">' + _('Select files', 'Select files') + '</a></div><div class="clr"></div>').appendTo('#preview');
                $('#add_remote_file').on('click', function (e) {

                    var allowed = wpfd_admin.allowed.split(',');
                    allowed.sort();
                    var allowed_select = '<select id="wpfd-remote-type">';
                    $.each(allowed, function (i, v) {
                        allowed_select += '<option value="' + v + '">' + v + '</option>';
                    });
                    allowed_select += '</select>';
                    bootbox.dialog('<div class="">  ' +
                        '<div class="form-horizontal wpfd-remote-form"> ' +
                        '<div class="control-group"> ' +
                        '<label class=" control-label" for="wpfd-remote-title">Title</label> ' +
                        '<div class="controls"> ' +
                        '<input id="wpfd-remote-title" name="wpfd-remote-title" type="text" placeholder="title" class=""> ' +
                        '</div> ' +
                        '</div> ' +
                        '<div class="control-group"> ' +
                        '<label class="control-label" for="wpfd-remote-url">Remote URL</label> ' +
                        '<div class="controls">' +
                        '<input id="wpfd-remote-url" name="wpfd-remote-url" type="text" placeholder="URL" class=""> ' +
                        '</div> </div>' +
                        '<div class="control-group"> ' +
                        '<label class="control-label" for="wpfd-remote-type">File Type</label> ' +
                        '<div class="controls">' +
                        allowed_select +
                        '</div> </div>' +
                        '</div>  </div>',
                        [{
                            "label": "Save",
                            "class": "button-primary",
                            "callback": function () {
                                var category_id = $('input[name=id_category]').val();
                                var remote_title = $('#wpfd-remote-title').val();
                                var remote_url = $('#wpfd-remote-url').val();
                                var remote_type = $('#wpfd-remote-type').val();
                                $.ajax({
                                    url: ajaxurl + "task=files.addremoteurl&id_category=" + category_id,
                                    data: {remote_title:remote_title,remote_url:remote_url,remote_type:remote_type},
                                    type: "POST"
                                }).done(function (data) {

                                    result = $.parseJSON(data);
                                    if (result.response === true) {
                                        updatepreview();
                                    } else {
                                        bootbox.alert(result.response);
                                    }
                                });
                            }
                        }, {
                            "label": "Cancel",
                            "class": "s",
                            "callback": function () {

                            }
                        }]
                    );
                    return false;
                });
            }

            $('#preview .restable').restable({
                type: 'hideCols',
                priority: {0: 'persistent', 1: 3, 2: 'persistent'},
                hideColsDefault: [4, 5]
            });

            showhidecolumns();
            $('#preview').sortable('refresh');

            initDeleteBtn();
            $('#preview input[name="restable-toggle-cols"]').click(function (e) {
                setcookie_showcolumns();
            });

            /** Init ordering **/
            $('#preview .restable thead a').click(function (e) {
                e.preventDefault();
                updatepreview(null, null, $(this).data('ordering'), $(this).data('direction'));

                if ($(this).data('direction') === 'asc') {
                    direction = 'desc';
                } else {
                    direction = 'asc';
                }

                $('#ordering option[value="' + $(this).data('ordering') + '"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                $('#orderingdir option[value="' + direction + '"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                id_category = $('input[name=id_category]').val();
                $.ajax({
                    url: ajaxurl + "task=category.saveparams&id=" + id_category,
                    type: "POST",
                    data: $('#category_params').serialize()
                }).done(function (data) {  });
            });

            /** Show/hide right colum **/

            initUploadBtn();

            initFiles();


            $('#wpreview').unbind();
            initDropbox($('#wpreview'));

            if (typeof(id_file) !== "undefined") {
                $('#preview .file[data-id-file="' + id_file + '"]').trigger('click');
            } else {
                showCategory();
                if (typeof($ordering) === 'undefined') {
                    loadGalleryParams();
                }
            }
            rloading('#wpreview');
        });
        initEditBtn();
        initDeleteBtn();

    }

    $('#wpreview .restablesearch').click(function (e) {
        e.preventDefault();
        $('.wpfd-search-file').addClass('show').removeClass('hide');
        $('#mycategories').hide();
        $(this).hide();
    });

    $('.wpfd-btn-exit-search').click(function (e) {
        e.preventDefault();
        $('.wpfd-search-file').addClass('hide').removeClass('show');
        $('#mycategories').show();
        $('.wpfd-iconsearch').show();
        $('.wpfd-filter-file').css('right','55px');
    });

    $('#wpfd_filter_catid').change(function (e) {
        e.preventDefault();
        var filter_catid = $(this).val();
        if(filter_catid) {
            var keyword = $('.wpfd-search-file-input').val();
            searchFiles(keyword, filter_catid);
        }

    });

    $(".wpfd-search-file-input").on('keyup', function (e) {
        if (e.keyCode == 13) {
            var keyword = $(this).val();
            if(keyword) {
                searchFiles(keyword);
            }
        }
    });

    $('.wpfd-btn-search').click(function (e) {
        e.preventDefault();
        var keyword = $('.wpfd-search-file-input').val();
        searchFiles(keyword);
    });

    function searchFiles(keyword,filter_catid, ordering, ordering_dir) {
        if (typeof(filter_catid) === "undefined" || filter_catid === null) {
            filter_catid = $('#wpfd_filter_catid').val();
        }
        var url = ajaxurl + "task=files.search&format=raw" ;
        $.ajax({
            url: url,
            type: "POST",
            data: {
                "s":        keyword,
                "cid":      filter_catid,
                "orderCol": ordering,
                "orderDir": ordering_dir
            }
        }).done(function (data) {

            $('#preview').html($(data));

            $('#preview .restable').restable({
                type: 'hideCols',
                priority: {0: 'persistent', 1: 3, 2: 'persistent'},
                hideColsDefault: [4, 5]
            });

            $('#preview').sortable('refresh');
            showhidecolumns();
            initDeleteBtn();

            /** Init ordering **/
            $('#preview .restable thead a').click(function (e) {
                e.preventDefault();
                searchFiles(keyword, $(this).data('ordering'), $(this).data('direction'));

                if ($(this).data('direction') === 'asc') {
                    direction = 'desc';
                } else {
                    direction = 'asc';
                }

                $('#ordering option[value="' + $(this).data('ordering') + '"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
                $('#orderingdir option[value="' + direction + '"]').attr('selected', 'selected').parent().css({'background-color': '#ACFFCD'});
            });


            initUploadBtn();

            initFiles();


            $('#wpreview').unbind();
            initDropbox($('#wpreview'));

            if (typeof(id_file) !== "undefined") {
                $('#preview .file[data-id-file="' + id_file + '"]').trigger('click');
            } else {
                showCategory();
                if (typeof($ordering) === 'undefined') {
                    loadGalleryParams();
                }
            }
            rloading('#wpreview');

        })
    }

    $( window ).resize(function() {
        hideColumns();
    });

    //hide columns base on window size
    function hideColumns() {

        var w = $( window ).width();
        if(w <= 1600 && w > 1440){
            $('input[name="restable-toggle-cols"]').prop('checked',true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0').prop('checked',false);
        }else if(w <= 1440 && w > 1200){
            $('input[name="restable-toggle-cols"]').prop('checked',true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0').prop('checked',false);
        }else if(w <= 1200 && w > 1024){
            $('input[name="restable-toggle-cols"]').prop('checked',true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0,#restable-toggle-col-3-0').prop('checked',false);
        }else if(w <= 1024){
            $('input[name="restable-toggle-cols"]').prop('checked',true);
            $('#restable-toggle-col-6-0,#restable-toggle-col-5-0,#restable-toggle-col-4-0,#restable-toggle-col-3-0,#restable-toggle-col-2-0').prop('checked',false);
        }
    }
    //show/hide columns base on cookie
    function showhidecolumns() {
            if(!wpfd_admin.listColumns.length){
                hideColumns();
                return;
            }
            $('.restable thead th').hide();
            $('.restable tbody td').hide();
            $('input[name="restable-toggle-cols"]').prop('checked',false);
            $.each(wpfd_admin.listColumns,function(i,v){
                $('#'+v).prop('checked',true);
                var col = parseInt($('#'+v).data('col')) + 1;
                $('.restable thead th:nth-child('+ col +')').show();
                $('.restable tbody td:nth-child('+ col +')').show();
            });
    }

    function setcookie_showcolumns() {
        var column_show = [];
        $('input[name="restable-toggle-cols"]').each(function(i,v){
            if($(v).is(':checked')){
                column_show.push($(v).attr('id'));
            }
        });

        var url = ajaxurl + "task=files.showcolumn" ;
        $.ajax({
            url: url,
            type: "POST",
            data: {
                column_show: column_show
            }
        }).done(function (data) {

        });
    }

    /**
     * Init delete button
     */
    function initDeleteBtn() {
        $('.actions .trash').unbind('click').click(function (e) {
            that = this;
            bootbox.confirm( wpfd_admin.msg_ask_delete_file , function (result) {
                if (result === true) {
                    //Delete file
                    id_file = $(that).parents('.file').data('id-file');
                    var id_category = $('li.dd-item.dd3-item.active').data('id-category');
                    $.ajax({
                        url: ajaxurl + "task=file.delete&id_file=" + id_file + "&id_category=" + id_category,
                        type: "POST"
                    }).done(function (data) {
                        $(that).parents('.file').fadeOut(500, function () {
                            $(this).remove();
                            $('.gritter-item-wrapper ').remove();
                            $.gritter.add({text: wpfd_admin.msg_remove_file});
                        });
                    });
                }
            });
            return false;
        });
    }

    /**
     * Init files
     */
    function initFiles() {

        $(document).unbind('click.window').bind('click.window', function (e) {

            if ($(e.target).is('#rightcol')
                || $(e.target).parents('#rightcol').length > 0
                || $(e.target).parents('.bootbox.modal').length > 0
                || $(e.target).parents('.tagit-autocomplete').length > 0
                || $(e.target).parents('.mce-container').length > 0
                || $(e.target).parents('.calendar').length > 0
                || $(e.target).parents('.wpfd-btn-toolbar').length > 0
            ) {
                return;
            }
            $('#preview .file').removeClass('selected');
            $('.wpfd-btn-toolbar').hide();
            showCategory();
        });

        $('#preview .file').unbind('click').click(function (e) {

            iselected = $(this).find('tr.selected').length;

            //Allow multiselect
            if (!(e.ctrlKey || e.metaKey) ) {
                $('#preview .file.selected').removeClass('selected');
            }
            if (iselected === 0) {
                $(this).addClass('selected');
            }

            if ($('#preview .file.selected').length == 1) {
                loadFileParams();
                loadVersions();
                showFile();
                $('.wpfd-btn-toolbar').show();
            } else {
                showCategory();
            }

            e.stopPropagation();
        });
    }

    /**
     * Init the file edit btn
     */
    function initEditBtn() {
        $('.wbtn a.edit').unbind('click').click(function (e) {
            that = this;
            id_file = $(that).parents('.wimg').find('img.img').data('id-file');
            $.ajax({
                url: ajaxurl + "view=file&format=raw&id=" + id_file,
                type: "POST"
            }).done(function (data) {
                bootbox.dialog(data, [{
                    'label': _('Save', 'Save'), 'class': 'btn-success', 'callback': function () {
                        var p = '';
                        $('#file-form .wpfdinput').each(function (index) {
                            p = p + $(this).attr('name') + '=' + $(this).attr('value') + '&';
                        });
                        $.ajax({
                            url: $('#file-form').attr('action'),
                            type: 'POST',
                            data: p
                        }).done(function (data) {
                            //do nothing
                        });
                    }
                }, {
                    'label': _('Cancel', 'Cancel'),
                    'class': 'btn-warning'
                }], {header: _('Image parameters', 'Image parameters')});

            });
            return false;
        });
    }

    /**
     * Load category layout params
     */
    function loadGalleryParams() {
        id_category = $('input[name=id_category]').val();
        $.cookie('wpfd_selected_category', id_category);
        loading('#rightcol');
        $.ajax({
            url: ajaxurl + "task=category.edit&layout=form&id=" + id_category
        }).done(function (data) {
            $('#galleryparams').html(data);
//            rloading($('.wpfdparams'));

            $('#galleryparams .wpfdparams #visibility').change(function () {
                if ($(this).val() == 0) {
                    $('#galleryparams .wpfdparams #visibilitywrap').hide();
                    $('#galleryparams .wpfdparams #visibilitywrap input').attr('checked', false);
                } else {
                    $('#galleryparams .wpfdparams #visibilitywrap').show();
                }
            }).trigger('change');

            $('#wpfd-theme').change(function () {
                changeTheme();
            });
            initColor();
            $('.user-clear').on('click', function () {
                $('.field-user-input-name').val('');
                $('.field-user-input').val('');
            });
            $('.user-clear-category').on('click', function () {
                $('.field-user-category-own-name').val('');
                $('.field-user-category-own').val('');
            });
            $('#galleryparams .wpfdparams button[type="submit"]').click(function (e) {
                e.preventDefault();
                id_category = $('input[name=id_category]').val();
                $.ajax({
                    url: ajaxurl + "task=category.saveparams&id=" + id_category,
                    type: "POST",
                    data: $('#category_params').serialize()
                }).done(function (data) {

                    result = jQuery.parseJSON(data);
                    if (result.response === true) {
                        $('.gritter-item-wrapper ').remove();
                        $.gritter.add({text: wpfd_admin.msg_save_category});
                        updatepreview();
                        loadGalleryParams();
                    } else {
                        bootbox.alert(result.response);
                    }
                    loadGalleryParams();
                });
                return false;
            });
            rloading('#rightcol');
        });
    }

    // save temp
    function saveTemp() {
        id_category = $('input[name=id_category]').val();
        $.ajax({
            url: ajaxurl + "task=category.saveparams&id=" + id_category,
            type: "POST",
            data: $('#category_params').serialize()
        }).done(function (data) {
        });
    }

    // init change theme for category
    function changeTheme() {
        theme = $('#wpfd-theme').val();
        id_category = $('input[name=id_category]').val();

        $.ajax({
            url: ajaxurl + "task=category.edit&layout=form&theme=" + theme + "&onlyTheme=1&id=" + id_category
        }).done(function (data) {
            $('#category-theme-params').html(data);
            initColor();
        })

    }

    // loading file layout
    function loadFileParams() {
        id_file = jQuery('.file.selected').data('id-file');
        var idCategory = jQuery('li.dd-item.dd3-item.active').data('id-category');
        loading('#rightcol');
        $.ajax({
            url: ajaxurl + "task=file.display&id=" + id_file + "&idCategory=" + idCategory
        }).done(function (data) {
            $('#fileparams').html(data);
            $('#fileparams .wpfdparams button[type="submit"]').click(function (e) {
                e.preventDefault();
                var idCategory = jQuery('li.dd-item.dd3-item.active').data('id-category');
                id_file = jQuery('.file.selected').data('id-file');
                var new_title = jQuery('#fileparams input[name="title"]').val();
                $.ajax({
                    url: ajaxurl + "task=file.save&id=" + id_file + "&idCategory=" + idCategory,
                    method: "POST",
                    //dataType: 'json',
                    data: $('#fileparams .wpfdparams').serialize()
                }).done(function (data) {
                    if (typeof data == 'string') {
                        result = jQuery.parseJSON(data);
                    } else {
                        result = data;
                    }
                    if (result.response === true) {
                        loadFileParams();
                        $('.gritter-item-wrapper ').remove();
                        $.gritter.add({text: wpfd_admin.msg_save_file});
                        $('.file.selected td.title').text(new_title);
                    } else {
                        bootbox.alert(result.response);
                        loadFileParams();
                    }

                    if (typeof result.datas.new_id !== 'undefined') {
                        updatepreview(null, result.datas.new_id);
                    } else {
                        if($('.wpfd-search-file').hasClass('hide')) {
                            updatepreview(null, id_file);
                        }
                    }
                });
                return false;
            });
            $('.user-clear').on('click', function () {
                $('.field-user-input-name').val('');
                $('.field-user-input').val('');
            });

            Calendar.setup({
                // Id of the input field
                inputField: "publish",
                // Format of the input field
                ifFormat: "%Y-%m-%d %H:%M:%S",
                // Trigger for the calendar (button ID)
                button: "publish_img",
                // Alignment (defaults to "Bl")
                align: "Tl",
                cache: true,
                singleClick: true
            });

            rloading('#rightcol');
        });
    }

    // load file versions
    function loadVersions() {
        id_category = $('input[name=id_category]').val();
        id_file = jQuery('.file.selected').data('id-file');
        var idCategory = jQuery('li.dd-item.dd3-item.active').data('id-category');
        loading('#fileversion');
        $.ajax({
            url: ajaxurl + "view=file&layout=versions&id=" + id_file + "&idCategory=" + idCategory
        }).done(function (data) {
            $('#versions_content').html(data);
            $('#versions_content a.trash').unbind('click').click(function (e) {
                e.preventDefault();
                that = this;
                bootbox.confirm(_('Are you sure remove version') + '?', function (result) {
                    if (result === true) {
                        vid = $(that).data('vid');
                        $.ajax({
                            url: ajaxurl + "task=file.deleteVersion&vid=" + vid + "&id_file=" + id_file + "&catid=" + id_category,
                            type: "POST"
                        }).done(function (data) {
                            result = jQuery.parseJSON(data);
                            if (result.response === true) {
                                $(that).parents('tr').remove();
                            } else {
                                bootbox.alert(result.response);
                            }
                        });
                    }
                });

                return false;
            });
            $('#versions_content a.restore').click(function (e) {
                e.preventDefault();
                that = this;
                file_ext = jQuery('.file.selected .txt').text();
                file_title = jQuery('.file.selected .title').text();
                bootbox.confirm(_('Are you sure restore file') + file_title + "." + file_ext + '?', function (result) {
                    if (result === true) {
                        vid = $(that).data('vid');
                        fid = $(that).data('id');
                        catid = $(that).data('catid');
                        $.ajax({
                            url: ajaxurl + "task=file.restore&vid=" + vid + "&id=" + fid + "&catid=" + catid,
                            type: "POST"
                        }).done(function (data) {
                            result = jQuery.parseJSON(data);
                            if (result.response === true) {
                                $(that).parents('tr').remove();

                                id_file = jQuery('.file.selected').data('id-file');
                                updatepreview(null, id_file);

                            } else {
                                bootbox.alert(result.response);
                            }
                        });
                    }
                });

                return false;
            });

            rloading('#fileversion');
        });
    }

    // init upload button
    function initUploadBtn() {
        $('#upload_button').on('click', function () {
            $('#upload_input').trigger('click');
            return false;
        });
    }

    /**
     * Click to Sync with Google Drive
     */
    $('#btn-sync-gg').click(function (e) {
        e.preventDefault();
        var $btn = $(this).button('loading');

        $.ajax({
            url: wpfd_var.ajaxurl + '?action=googleSync'
        }).done(function (data) {
            window.location.reload();
            // business logic...
            $btn.button('complete');
            //$btn.button('reset');                       
        });
    });

    /**
     * Click to Sync with Dropbox
     */
    $('#btn-sync-drop').click(function (e) {
        e.preventDefault();
        var $btn = $(this).button('loading');
        $.ajax({
            url: wpfd_var.ajaxurl + '?action=dropboxSync'
        }).done(function (data) {
            window.location.reload();
            $btn.button('complete');
        });
    });

    /**
     * Init the dropbox
     **/
    function initDropbox(dropbox) {
        dropbox.filedrop({
            paramname: 'pic',
            fallback_id: 'upload_input',
            maxfiles: 30,
            maxfilesize: Wpfd.maxfilesize,
            queuefiles: 2,
            data: {
                id_category: function () {
                    return $('input[name=id_category]').val();
                }
            },
            url: ajaxurl + 'task=files.upload',

            uploadFinished: function (i, file, response) {
                if (response.response === true) {
                    $.data(file).addClass('done');
                    $.data(file).find('img').data('id-file', response.datas.id_file);
                } else {
                    bootbox.alert(response.response);
                    $.data(file).remove();
                }
            },

            error: function (err, file) {
                switch (err) {
                    case 'BrowserNotSupported':
                        bootbox.alert(_('Your browser does not support HTML5 file uploads', 'Your browser does not support HTML5 file uploads!'));
                        break;
                    case 'TooManyFiles':
                        bootbox.alert(_('Too many files', 'Too many files') + '!');
                        break;
                    case 'FileTooLarge':
                        bootbox.alert(file.name + ' ' + _('is too large', 'is too large') + '!');
                        break;
                    default:
                        break;
                }
            },

            // Called before each upload is started
            beforeEach: function (file) {
                if (!wpfd_permissions.can_edit_category) {
                    bootbox.alert(wpfd_permissions.translate.wpfd_edit_category);
                    return false;
                }
            },

            uploadStarted: function (i, file, len) {
                var preview = $('<div class="wpfd_process_full" style="display: block;">' +
                    '<div class="wpfd_process_run" data-w="0" style="width: 0%;"></div>' +
                    '</div>');

                var reader = new FileReader();

                // Reading the file as a DataURL. When finished,
                // this will trigger the onload function above:
                reader.readAsDataURL(file);

                $('#preview .restable').after(preview);
//                        $('#dropbox').before(preview);

                // Associating a preview container
                // with the file, using jQuery's $.data():

                $.data(file, preview);
            },

            progressUpdated: function (i, file, progress) {
                $.data(file).find('.wpfd_process_run').width(progress + '%');
            },

            afterAll: function () {
                $('#preview .progress').delay(300).fadeIn(300).hide(300, function () {
                    $(this).remove();
                });
                $('#preview .uploaded').delay(300).fadeIn(300).hide(300, function () {
                    $(this).remove();
                });
                $('#preview .file').delay(1200).show(1200, function () {
                    $(this).removeClass('done placeholder');
                });
                updatepreview();
                $('.gritter-item-wrapper ').remove();
                $.gritter.add({text: wpfd_admin.msg_upload_file});
            },
            rename: function (name) {
                ext = name.substr(name.lastIndexOf('.'), name.length);
                name = name.substr(0, name.lastIndexOf('.'));

                var uint8array = new TextEncoderLite().encode(name);

                base64 = fromByteArray(uint8array);
                base64 = base64.replace("/","|");
                return base64 + ext;
            }
        });
    }

    if (_('close_categories') == '1') {
        $('.nested').nestable('collapseAll');
    }

    if (typeof(window.parent.tinyMCE) !== 'undefined') {
        var content = "";
        if (window.parent.tinyMCE.activeEditor != null) {
            content = window.parent.tinyMCE.activeEditor.selection.getContent();
        }
        var file = content.match('<img.*data\-file="([0-9a-zA-Z_]+)".*?>');
        var category = content.match('<img.*data\-category="([0-9]+)".*?>');
        var file_category = content.match('<img.*data\-category="([0-9]+)".*?>');
        if (file !== null && file_category !== null) {
            $('#categorieslist li').removeClass('active');
            $('#categorieslist li[data-id-category="' + file_category[1] + '"]').addClass('active');
            $('input[name=id_category]').val(file_category[1]);
            updatepreview(file_category[1], file[1]);

        } else if (category !== null) {
            $('#categorieslist li').removeClass('active');
            $('#categorieslist li[data-id-category="' + category[1] + '"]').addClass('active');
            $('input[name=id_category]').val(category[1]);
            updatepreview(category[1]);
            loadGalleryParams();
        } else {
            var cate = $.cookie('wpfd_selected_category');

            if (cate != null) {
                $('#categorieslist li').removeClass('active');
                $('#categorieslist li[data-id-category="' + cate + '"]').addClass('active');
                $('input[name=id_category]').val(cate);
                setTimeout(function () {
                    updatepreview(cate);
                    loadGalleryParams();
                }, 100);

            } else {
                updatepreview();
                loadGalleryParams();
            }

        }
    }

    /**
     * Init the dropbox
     **/
    function initDropboxVersion(dropbox) {
        dropbox.filedrop({
            paramname: 'pic',
            fallback_id: 'upload_input_version',
            maxfiles: 1,
            maxfilesize: Wpfd.maxfilesize,
            queuefiles: 1,
            data: {
                id_file: function () {
                    return $('.file.selected').data('id-file');
                },
                id_category: function () {
                    return $('input[name=id_category]').val();
                }
            },
            url: ajaxurl + 'task=files.version',

            uploadFinished: function (i, file, response) {

                if (response.response === true) {

                } else {
                    bootbox.alert(response.response);

                    $('#dropbox_version .progress').addClass('hide');
                    $('#dropbox_version .upload').removeClass('hide');
                }
            },

            error: function (err, file) {
                switch (err) {
                    case 'BrowserNotSupported':
                        bootbox.alert(_('Your browser does not support HTML5 file uploads', 'Your browser does not support HTML5 file uploads!'));
                        break;
                    case 'TooManyFiles':
                        bootbox.alert(_('Too many files', 'Too many files') + '!');
                        break;
                    case 'FileTooLarge':
                        bootbox.alert(file.name + ' ' + _('is too large', 'is too large') + '!');
                        break;
                    default:
                        break;
                }
            },

            // Called before each upload is started
            beforeEach: function (file) {
//                        if(!file.type.match(/^image\//)){
//                                bootbox.alert(_('Only images are allowed','Only images are allowed')+'!');
//                                return false;
//                        }
            },

            uploadStarted: function (i, file, len) {

                // Associating a preview container
                // with the file, using jQuery's $.data():
                $('#dropbox_version .upload').addClass('hide');
                $('#dropbox_version .progress').removeClass('hide');
//                        $.data(file,preview);
            },

            progressUpdated: function (i, file, progress) {
                $('#dropbox_version .wpfd_process_run').width(progress + '%');
            },

            afterAll: function () {

                $('#dropbox_version .progress').addClass('hide');
                $('#dropbox_version .upload').removeClass('hide');
                id_file = $('.file.selected').data('id-file');
                updatepreview(null, id_file);


            }
        });
    }


    /* Title edition */
    function initMenu() {
        /**
         * Click on delete category btn
         */
        $('#categorieslist .dd-content .trash').unbind('click').on('click', function () {
            var id_category = $(this).closest('li').data('id-category');
            var hasElement = $(this).parent().find("a.t i.google-drive-icon");
            var hasDropElement = $(this).parent().find("a.t i.dropbox-icon");
            var typeCloud = "null";
            if (hasElement.length > 0) {
                typeCloud = "googledrive";
            }
            if (hasDropElement.length > 0) {
                typeCloud = "dropbox";
            }
            bootbox.confirm(_('Do you want to delete &quot;', 'Do you really want to delete "') + $(this).parent().find('.title').text() + '"?', function (result) {
                if (result === true) {
                    var wpfdAjaxurl = ajaxurl + "task=category.delete&id_category=" + id_category;
                    if (typeCloud === 'googledrive') {
                        wpfdAjaxurl = wpfd_var.ajaxurl + "?action=wpfdAddonDeleteCategory&id_category=" + id_category
                    } else if (typeCloud === 'dropbox') {
                        wpfdAjaxurl = wpfd_var.ajaxurl + "?action=wpfdAddonDeleteDropboxCategory&id_category=" + id_category
                    }
                    $.ajax({
                        url: wpfdAjaxurl,
                        type: 'POST',
                        data: $('#categoryToken').attr('name') + '=1'
                    }).done(function (data) {
                        result = jQuery.parseJSON(data);
                        if (result.response === true) {
                            $('#mycategories #categorieslist li[data-id-category=' + id_category + ']').remove();
                            $('#preview').contents().remove();
                            first = $('#mycategories #categorieslist li .dd-content').first();
                            if (first.length > 0) {
                                first.click();
                            } else {
                                $('#insertcategory').hide();
                            }
                            $('.gritter-item-wrapper ').remove();
                            $.gritter.add({text: wpfd_admin.msg_remove_category});
                        } else {
                            bootbox.alert(result.response);
                        }
                    });
                }
            });
            return false;
        });

        /* Set the active category on menu click */
        $('#categorieslist .dd-content').unbind('click').click(function (e) {
            id_category = $(this).parent().data('id-category');
            $('input[name=id_category]').val(id_category);
            updatepreview(id_category);
            $('#categorieslist li').removeClass('active');
            $(this).parent().addClass('active');
            return false;
        });

        $('#categorieslist .dd-content a.edit').unbind().click(function (e) {

            if (!wpfd_permissions.can_edit_category) {
                bootbox.alert(wpfd_permissions.translate.wpfd_edit_category);
                return false;
            }

            e.stopPropagation();
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
                id_category = $(link).parents('li').data('id-category');
                if (title !== '') {
                    $.ajax({
                        url: ajaxurl + "task=category.setTitle",
                        data: {id_category: id_category, title: title},
                        type: "POST"
                    }).done(function (data) {
                        result = jQuery.parseJSON(data);
                        if (result.response === true) {
                            $('.gritter-item-wrapper ').remove();
                            $.gritter.add({text: wpfd_admin.msg_edit_category});
                            return true;
                        }
                        $(link).text(oldTitle);
                        return false;
                    });
                } else {
                    $(link).text(oldTitle);
                    return false;
                }

            }
        });
    }

    if (typeof l10n != 'undefined' && l10n.show_file_import) {

        $('#wpfd-jao').wpfd_jaofiletree({
            script: ajaxurl + "task=category.listdir",
            usecheckboxes: 'files',
            showroot: '/'
        });

        $('#importFilesBtn').click(function (e) {

            e.preventDefault();
            id_category = $('input[name=id_category]').val();
            var files = '';
            $($('#wpfd-jao').wpfd_jaofiletree('getchecked')).each(function () {
                files += '&files[]=' + this.file;
            });
            if (files === '') {
                return;
            }
            loading('#wpreview');
            $.ajax({
                url: ajaxurl + "task=files.import&" + $('#categoryToken').attr('name') + "=1&id_category=" + id_category,
                type: 'GET',
                data: files
            }).done(function (data) {
                result = jQuery.parseJSON(data);
                if (result.response === true) {
                    bootbox.alert(result.datas.nb + ' files imported');
                    updatepreview(id_category);
                }
            });

        });

        $('#selectAllImportFiles').click(function (e) {

            e.preventDefault();
            $('#filesimport input[type="checkbox"]').attr('checked', true);

        });
        $('#unselectAllImportFiles').click(function (e) {

            e.preventDefault();
            $('#filesimport input[type="checkbox"]').attr('checked', false);

        });
    }
    // init color field
    function initColor() {
        $('.wp-color-field').wpColorPicker({width: 180});
    }

    function loading(e) {
        $(e).addClass('dploadingcontainer');
        $(e).append('<div class="dploading"></div>');
    }

    function rloading(e) {
        $(e).removeClass('dploadingcontainer');
        $(e).find('div.dploading').remove();
    }

    $("#dropboxAuthor").change(function () {
        var dropAuthor = $("input[name=dropboxAuthor]").val();
        $('#submitDrop').prop('disabled', true);
        $.ajax({
            url: ajaxurl + "task=config.getTokenKey",
            type: "POST",
            dataType: 'json',
            data: {dropAuthor: dropAuthor}
        }).done(function (res) {
            var $accessToken = res.datas.accessToken;
            $('#submitDrop').prop('disabled', false);
            $("#dropboxToken").attr('value', $accessToken);
            $("#dropboxAuthor").attr('type', 'hidden');
            window.location.replace("admin.php?page=wpfd-config");
        });
    });

    $('#search_config select').on('change', function () {
        shortcode_generator();
    });
    // search shortcode generator
    function shortcode_generator() {
        var cat = $('#cat_filter'),
            tag = $('#tag_filter'),
            display_tag = $('#display_tag'),
            create_filter = $('#create_filter'),
            update_filter = $('#update_filter'),
            file_per_page = $('#file_per_page'),
            shortcode_value = $('#shortcode_value');
        var shortcode = '[wpfd_search cat_filter="' + cat.val() + '" tag_filter="' + tag.val() + '" display_tag="' + display_tag.val() + '" create_filter="' + create_filter.val() + '" update_filter="' + update_filter.val() + '" file_per_page="' + file_per_page.val() + '"]'
        shortcode_value.val(shortcode);
    }

    $('#wpfd-container-config').tooltip();

    function setCookie(cname, cvalue) {
        var d = new Date();
        d.setTime(d.getTime() + (30*(60*60*24*1000))); // set cookie time to 30 days
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    $('.updateHideBtn, .updateHideTxt').unbind('click').click(function () {
        $('#updateGroup').hide(300);
        setCookie('WPFD_hide_upgrade', 1);
    })
});

/**
 * Insert the current category into a content editor
 */
function insertCategory() {
    id_category = jQuery('input[name=id_category]').val();
    code = '<img src="' + dir + '/app/admin/assets/images/t.gif"' +
        'data-wpfdcategory="' + id_category + '"' +
        'style="background: url(' + dir + '/app/admin/assets/images/folder_download.png) no-repeat scroll center center #D6D6D6;' +
        'border: 2px dashed #888888;' +
        'height: 200px;' +
        'border-radius: 10px;' +
        'width: 99%;" data-category="' + id_category + '" />';
    window.parent.tinyMCE.execCommand('mceInsertContent', false, code);
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdmodal', window.parent.document).fadeOut(300);
    return false;
}

/**
 * Insert the current file into a content editor
 */
function insertFile() {
    id_file = jQuery('.file.selected').data('id-file');
    id_category = jQuery('input[name=id_category]').val();
    code = '<img src="' + dir + '/app/admin/assets/images/t.gif"' +
        'data-file="' + id_file + '"' +
        'data-wpfdfile="' + id_file + '"' +
        'data-category="' + id_category + '"' +
        'style="background: url(' + dir + '/app/admin/assets/images/file_download.png) no-repeat scroll center center #D6D6D6;' +
        'border: 2px dashed #888888;' +
        'height: 100px;' +
        'border-radius: 10px;' +
        'width: 99%;" />';
    window.parent.tinyMCE.execCommand('mceInsertContent', false, code);
    jQuery("#lean_overlay", window.parent.document).fadeOut(300);
    jQuery('#wpfdmodal', window.parent.document).fadeOut(300);
    return false;
}

//From http://jquery-howto.blogspot.fr/2009/09/get-url-parameters-values-with-jquery.html
function getUrlVars() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

function getUrlVar(v) {
    if (typeof(getUrlVars()[v]) !== "undefined") {
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
jQuery.fn.single_double_click = function (single_click_callback, double_click_callback, timeout) {
    return this.each(function () {
        var clicks = 0, self = this;
        jQuery(this).click(function (event) {
            clicks++;
            if (clicks == 1) {
                setTimeout(function () {
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

//http://stackoverflow.com/questions/11103447/jquery-sortable-cancel-and-revert-not-working-as-expected
//modified by joomunited.com
var _mouseStop = jQuery.ui.sortable.prototype._mouseStop;
jQuery.ui.sortable.prototype._mouseStop = function(event, noPropagation) {
    if ( !event ) {
        return;
    }
    $ = jQuery;
    //If we are using droppables, inform the manager about the drop
    if ( $.ui.ddmanager && !this.options.dropBehaviour ) {
        $.ui.ddmanager.drop( this, event );
    }

    var options = this.options;
    var $item = $(this.currentItem);
    var el = this.element[0];
    var ui = this._uiHash(this);
    var current = $item.css(['top', 'left', 'position', 'width', 'height']);
    var cancel = options.revert && $.isFunction(options.beforeRevert) && !options.beforeRevert.call(el, event, ui);

    if (cancel) {
        this.cancel();
        $item.css(current);
        $item.animate(this.originalPosition, {
            duration: isNaN(options.revert) ? 500 : options.revert,
            always: function() {
                $('body').css('cursor', '');
                $item.css({position: '', top: '', left: '', width: '', height: '', 'z-index': ''});
                if ($.isFunction(options.update)) {
                    options.update.call(el, event, ui);
                }
            }
        });
    }

    return !cancel && _mouseStop.call(this, event, noPropagation);
};