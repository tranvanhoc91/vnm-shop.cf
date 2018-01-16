(function () {
    parent.jQuery('#TB_window').height(560).css('z-index', 100000001);
})();

jQuery(document).ready(function ($) {
    $(window).load(function() {
        // Animate loader off screen
        $(".preload-img").fadeOut("slow");
        $(".container").show();
    });

    if (typeof (myBullets) === 'undefined') {
        myBullets = {};
    }
    if (typeof (myBullets.styles) === 'undefined') {
        myBullets.styles = {};
    }
    myBullets.styles_markup = '';
    myBullets.pixel_properties = ['font-size', 'line-height', 'margin', 'padding', 'text-size'];

    var editor = parent.tinyMCE.activeEditor;
    var selected = editor.selection;
    var elm = editor.selection.getRng(true);
    var container = elm.commonAncestorContainer;

    if (container.nodeName === '#text' || container.nodeName === 'LI') {
        el = editor.dom.getParent(selected.getNode(), 'ul');
        if (el) {
            container = el;
        }
        else {
            return;
        }
    }

    $('#btn_add').unbind('click').click(function () {
        insertBulleted();
    });

    $('#btn_save').unbind('click').click(function () {
        updateBulleted($('#previewList').data('id-bullet'))
    });

    $('#bulleted_icon').ddslick({
        onSelected: function(data) {
            //callback function: do something with selectedData;
            $("#previewList").removeClass();
            if (data.selectedData.value !== "") {
                $("#previewList").addClass("wpse-bullet wpsebul-" + data.selectedData.value);
                $('.observeListChanges').each(function (e) {
                    var css_property = $(this).data('css-prop');
                    myBullets.styles[css_property] = $(this).val();
                    update_styles();
                });
            }
        },
        width: 200
    });

    $('.minicolors-input').minicolors('settings', {
        change: function() {
            jQuery(this).trigger('change');
        }
    }).attr('maxlength', '7');

    $('.incr').unbind('click').click(function (e) {
        var id = $(this).attr('id').replace('_incr','');
        var c_val = $('#'+id).val();
        if (c_val === '') {
            c_val = 0;
        }
        $('#'+id).val((parseInt(c_val)) + 1).trigger('change');
    });

    $('.decr').unbind('click').click(function (e) {
        var id = $(this).attr('id').replace('_decr','');
        var c_val = $('#'+id).val();
        if (c_val === '') {
            c_val = 0;
        }
        if (c_val === '0') return;
        $('#'+id).val((parseInt(c_val)) - 1).trigger('change');
    });

    $('.observeListChanges').on('input change propertychange click', function (e) {
        var css_property = $(this).data('css-prop');
        myBullets.styles[css_property] = $(this).val();
        update_styles();
    });

    function update_styles() {
        myBullets.styles_markup = generate_style_markup(myBullets.styles);
        text_style = generate_style_text_markup(myBullets.styles);
        // Wrap style markup content
        myBullets.styles_markup = '#previewList li {' + text_style + '} ' + '#previewList li:before {' + myBullets.styles_markup + '}';

        render_styles(myBullets.styles_markup);
    }

    function render_styles(styles) {
        var style_tag = '<style id="dynamic-styles" type="text/css">' + styles + '</style>';
        $('#dynamic-styles').replaceWith(style_tag);
    }

    function render_style_bullets() {
        var output = '',
            style_markup = '',
            style_text_markup = '';
        $.each(mySavedBullets, function (bul_id, bul_style) {
            style_markup = generate_style_markup(bul_style);
            style_text_markup = generate_style_text_markup(bul_style);

            style_text_markup = '#bul-' + bul_id + ' li {\n' + style_text_markup + '}\n';
            style_markup = '#bul-' + bul_id + ' li:before {\n' + style_markup + '}\n';

            output += style_text_markup + style_markup;
        });

        var style_tag = '<style id="bulleted-styles" type="text/css">'+ output +'</style>';
        $('#bulleted-styles').replaceWith(style_tag);
    }

    render_style_bullets();

    function generate_style_markup(styles) {
        var style_markup = '';
        $.each(styles, function (css_property, css_value) {
            if (css_property !== 'text-size') {
                style_markup += render_style_line(css_property, css_value);
            }
        });

        return style_markup;
    }

    function generate_style_text_markup(styles) {
        var style_markup = '';
        $.each(styles, function (css_property, css_value) {
            if (css_property === 'text-size') {
                style_markup += render_style_line(css_property, css_value);
            }
        });

        return style_markup;
    }

    function render_style_line(css_property, css_value) {
        var px_value = $.inArray(css_property, myBullets.pixel_properties) > -1 ? 'px' : '';
        if (css_property !== 'text-size' || css_property !== 'classes') {
            style_content = css_property + ': ' + css_value + px_value + ';';
        }
        if (css_property === 'text-size') {
            style_content = 'font-size' + ': ' + css_value + px_value + ';';
        }

        return style_content;
    }

    function insertBulleted(styles, cls) {
        if (typeof styles === 'undefined') {
            styles = myBullets.styles;
        }
        if (typeof cls === 'undefined') {
            cls = $('.dd-selected-value').val();
        }
        if (cls !== '') {
            cls = "wpse-bullet wpsebul-" + cls;
        }

        var id = guid();
        var data_attr = generate_data_attr(styles);

        styles_markup = generate_style_markup(styles);
        text_style = generate_style_text_markup(styles);
        styles_markup = '#bul-' + id + ' li {' + text_style + '} '
            + '#bul-' + id + ' li:before {' + styles_markup + '}';

        var style_tag = '<style class="style-bul">' + styles_markup + '</style>' + '\n';

        var editor = parent.tinyMCE.activeEditor;
        selected = editor.selection;
        elm = editor.selection.getRng(true);
        container = elm.commonAncestorContainer;

        if (container.nodeName === '#text' || container.nodeName === 'LI') {
            el = editor.dom.getParent(selected.getNode(), 'ul');
            if (el) {
                container = el;
            }
            else {
                return;
            }
        }
        if (container.nodeName !== 'UL') return;

        inner_html = container.innerHTML;
        elm_id = editor.dom.getAttrib(container, 'id');
        if (elm_id) { // Update
            // Get old style block and remove it
            style_id = 'style-' + elm_id;
            style_block = editor.dom.get(style_id);
            if (style_block) style_block.remove();
        }
        // Insert new
        wrapper = document.createElement('div');
        list_html = '<ul id="bul-'+ id +'" class="'+ cls +'" '+ data_attr +' ></ul>';
        wrapper.innerHTML = list_html;

        style_wrapper = document.createElement('div');
        style_wrapper.id += 'style-bul-' + id;
        style_wrapper.innerHTML = style_tag;

        editor.dom.replace(wrapper.firstChild, container, true);
        editor.dom.add(editor.getBody(), style_wrapper);

        parent.tb_remove();
    }

    function updateBulleted(id_buls, callBack) {
        if (typeof(id_buls) === 'undefined') {
            addBulleted();
            return false;
        }

        classes = $('#previewList').attr('class');

        $.ajax({
            url: myBul.ajaxurl,
            method: 'POST',
            data: {
                action: 'wpse_bulleted_ajax',
                task: 'update',
                id_bul: id_buls,
                classes: classes,
                styles_bul: myBullets.styles
            },
            success: function (res, stt) {
                if (stt === 'success') {
                    $('#save-notice').fadeIn(200).delay(2000).fadeOut(200);
                    $('ul#bul-'+id_buls).attr('class', classes);
                    mySavedBullets[id_buls] = $.extend({}, myBullets.styles);
                    render_style_bullets();
                    initMenu();
                } else {
                    alert(stt);
                }
            },
            error: function(jqxhr, textStatus, error) {
                alert(textStatus + " : " + error);
            }
        })

    }
    
    function addBulleted() {
        var bul_cls = $('#previewList').attr('class');

        $.ajax({
            url: myBul.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpse_bulleted_ajax',
                task: 'addnew',
                classes: bul_cls,
                styles_bul: myBullets.styles
            },
            success: function (res, stt) {
                if (stt === 'success') {
                    $('#save-notice').fadeIn(200).delay(2000).fadeOut(200);

                    $('#saved-bullets-list').append('<li class="bul-items" data-id-bullet="'+res.id_bul+'">'
                                                    + '<ul id="bul-'+res.id_bul+'" class="'+res.classes+'"><li>List items</li></ul><br />'
                                                    + '<div> <a class="insert" title="Insert"><i class="dashicons dashicons-plus-alt"></i></a> <a class="edit" title="Edit"><i class="dashicons dashicons-edit"></i></a> <a class="trash" title="Delete"><i class="dashicons dashicons-trash"></i></a> </div>'
                                                    + '</li>');
                    mySavedBullets[res.id_bul] = $.extend({}, myBullets.styles);
                    render_style_bullets();
                    initMenu();
                } else {
                    alert(stt);
                }
            },
            error: function(jqxhr, textStatus, error) {
                alert(textStatus + " : " + error);
            }
        })
    }

    function initMenu() {
        $('#saved-bullets-list .trash').unbind('click').click(function () {
            id_bul = $(this).closest('li').data('id-bullet');
            cfm = confirm(myBul.confirmText);

            if (cfm) {
                $.ajax({
                    url: myBul.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wpse_bulleted_ajax',
                        task: 'delete',
                        id_bul: id_bul
                    },
                    success: function (res, stt) {
                        if (stt === 'success') {
                            $('#saved-bullets-list li[data-id-bullet='+id_bul+']').fadeOut();
                        } else {
                            alert(stt);
                        }
                    },
                    error: function(jqxhr, textStatus, error) {
                        alert(textStatus + " : " + error);
                    }
                })
            }
        });

        $('#saved-bullets-list .edit').unbind('click').click(function () {
            id_bul = $(this).closest('li').data('id-bullet');
            params = $.extend({}, mySavedBullets[id_bul]);
            $('#btn_save_text').html(myBul.updateText);
            $('#previewList').data('id-bullet', id_bul);

            populateParams(params);
        });

        $('#saved-bullets-list .insert').unbind('click').click(function () {
            id_bul = $(this).closest('li').data('id-bullet');
            styles_bul = $.extend({}, mySavedBullets[id_bul]);

            insertBulleted(styles_bul, styles_bul['classes']);
        })
    }

    initMenu();
    
    function populateParams(params) {
        $.each(params, function(css_prop, css_val){
            if (css_prop === 'classes') {
                icon = css_val.replace(/wpse-bullet wpsebul-/g, '');
                var index = $('#bulleted_icon li:has(input[value="' + icon + '"])').index();
                $('#bulleted_icon').ddslick('select', {index: index});
            }
            if($('[data-css-prop="'+css_prop+'"]').hasClass('minicolors-input')) {
                $('[data-css-prop="'+css_prop+'"]').minicolors('value', css_val).change();
            } else {
                $('[data-css-prop="'+css_prop+'"]').val(css_val).change();
            }
        });
    }
    
    // Load existing data when open Bullet Manager
    elm_id = editor.dom.getAttrib(container, 'id');
    if (elm_id.indexOf('bul-') !== -1) {
        icon = editor.dom.getAttrib(container, "class");
        if (icon) {
            icon = icon.replace(/wpse-bullet wpsebul-/g, '');
            var index = $('#bulleted_icon li:has(input[value="' + icon + '"])').index();
            $('#bulleted_icon').ddslick('select', {index: index});
        }
        font_size =  editor.dom.getAttrib(container, "data-font-size");
        text_size =  editor.dom.getAttrib(container, "data-text-size");
        line_height =  editor.dom.getAttrib(container, "data-line-height");
        margin =  editor.dom.getAttrib(container, "data-margin");
        padding = editor.dom.getAttrib(container, "data-padding");
        color = editor.dom.getAttrib(container, "data-color");

        $('#font_size').val(font_size).change();
        $('#color_icon').minicolors('value', color).change();
        $('#text_fontsize').val(text_size).change();
        $('#line_height').val(line_height).change();
        $('#bulleted_margin').val(margin).change();
        $('#bulleted_padding').val(padding).change();
    }

    function generate_data_attr(styles) {
        var data_attr = '';

        $.each(styles, function(css_property, css_value){
            data_attr += 'data-' + css_property+'="' + css_value+'" ' ;
        });

        return data_attr;

    }

    function guid() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
            s4() + '-' + s4() + s4() + s4();
    }
});