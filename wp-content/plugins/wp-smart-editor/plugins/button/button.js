(function () {
    parent.jQuery('#TB_window,#TB_iframeContent').height(560).css('z-index', 100000001);
})();

jQuery(document).ready(function ($) {
    $(window).load(function() {
        // Animate loader off screen
        $(".preload-img").fadeOut("slow");
        $(".container").show();
        $('#accordion').accordion({
            collapsible: true
        });
    });

    if (typeof (myButton) === 'undefined') {
        myButton = {};
    }
    myButton.styles = {};
    myButton.in_px = ['border-radius', 'border-width', 'font-size', 'padding-left', 'padding-top', 'padding-right', 'padding-bottom'];
    myButton.styles_markup = '';

    $('#btn_add').unbind('click').click(function () {
        insertButton();
    });

    $('#btn_save').unbind('click').click(function () {
        updateButton($("#previewBtn").data('id-button'));
    });

    $('.minicolors-input').minicolors('settings', {
        change: function() {
            $(this).trigger('change');
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

    $('.observeBtnChanges').on('input change propertychange click', function (e) {
        var css_property = $(this).data('css-prop');
        myButton.styles[css_property] = $(this).val();

        if (css_property === 'text') {
            vals = $(this).val();
            $('#previewBtn').text(vals);
        }
        update_styles();
    });

    function update_styles() {
        myButton.styles_markup = generate_style_markup(myButton.styles);
        hover_style = generate_style_markup_hover(myButton.styles);
        // Wrap style markup content
        myButton.styles_markup = '#previewBtn {' + myButton.styles_markup + '} ' + '#previewBtn:hover {' + hover_style + '}';

        render_styles(myButton.styles_markup);
    }

    function render_styles(styles) {
        var style_tag = '<style id="dynamic-styles" type="text/css">' + styles + '</style>';
        $('#dynamic-styles').replaceWith(style_tag);
    }

    function render_styles_btns() {
        var output = '',
            style_markup = '',
            style_hover_markup = '';
        $.each(mySavedButtons, function (btn_id, btn_styles) {
            if (btn_styles !== null || btn_styles !== '') {
                style_markup = generate_style_markup(btn_styles);
                style_hover_markup = generate_style_markup_hover(btn_styles);

                style_markup = '#btn-' + btn_id + '{\n' + style_markup + '}\n';
                style_hover_markup = '#btn-' + btn_id + ':hover {\n' + style_hover_markup + '}\n';

                output += style_markup + style_hover_markup;
            }
        });

        var style_tag = '<style id="btn-styles" type="text/css">' + output + '</style>';
        $('#btn-styles').replaceWith(style_tag);
    }

    render_styles_btns();

    function generate_style_markup(styles) {
        var style_markup = '';
        $.each(styles, function (css_property, css_value) {
            if (css_property.indexOf('-hover') === -1) {
                style_markup += render_style_line(css_property, css_value);
            }
        });

        return style_markup;
    }

    function generate_style_markup_hover(styles) {
        var styles_hover_markup = '';
        if(typeof styles['background-hover'] !== undefined && styles['background-hover'] !== '') {
            styles_hover_markup += render_style_line('background', styles['background-hover']);
        }
        if(typeof styles['color-hover'] !== undefined && styles['color-hover'] !== '') {
            styles_hover_markup += render_style_line('color', styles['color-hover']);
        }
        if(typeof styles['box-shadow-color-hover'] !== undefined && styles['box-shadow-color-hover'] !== '') {
            styles_hover_markup += render_box_shadow_line(styles['box-shadow-horz-hover'], styles['box-shadow-vert-hover'], styles['box-shadow-blur-hover'], styles['box-shadow-spread-hover'], styles['box-shadow-color-hover']);
        }
        return styles_hover_markup;
    }

    function render_style_line(css_property, css_value) {
        var px_value = $.inArray(css_property, myButton.in_px) > -1 ? 'px' : '';
        style_content = '';
        if (css_property !== 'text' && css_property !== 'btn-link') {
            style_content = css_property + ': ' + css_value + px_value + ';';
        }
        if (css_property === 'border-radius') {
            style_content += '-moz-' + css_property + ': ' + css_value + px_value + ';';
            style_content += '-webkit-' + css_property + ': ' + css_value + px_value + ';';
        }

        return style_content;
    }
    
    function render_box_shadow_line(h_off, v_off, blur, spread, color) {
        style_content = 'box-shadow: ' + h_off + 'px ' + v_off +'px '+ blur + 'px ' + spread +'px '+ color + ';';
        style_content += '-moz-' + style_content + '-webkit-' + style_content;


        return style_content;
    }

    function insertButton(styles, cls) {
        if (typeof (styles) === 'undefined') {
            styles = myButton.styles;
        }

        var btn_link = $('#link_target').val().trim();
        if (btn_link.indexOf('http://') === -1) {
            if (btn_link !== '') {
                btn_link = 'http://' + btn_link;
            }
            if (btn_link === '') {
                btn_link = '#';
            }
        }

        var id = guid();
        var data_attr = generate_data_attr(styles);

        styles_markup = generate_style_markup(styles);
        hover_style = generate_style_markup_hover(styles);
        styles_markup = '#btn-' + id + ' {' + styles_markup + '} '
            + '#btn-' + id + ':hover {' + hover_style + '}';

        var style_tag = '<style class="style-btn">' + styles_markup + '</style>' + '\n';

        var editor = parent.tinyMCE.activeEditor;
        selected = editor.selection;
        container = editor.dom.getParent(selected.getStart(), 'a.wpsebtn');

        if (container) {  // Update, remove old block
            elm_id = editor.dom.getAttrib(container, 'id');
            style_id = 'style-' + elm_id;
            style_block = editor.dom.get(style_id);
            if (style_block) style_block.remove();
            container.remove();
        }
        // Insert new
        btn_html = '<a class="wpsebtn" id="btn-'+ id +'" '+ data_attr +' href="'+ btn_link +'" type="button"> '+ styles.text +' </a>';

        style_wrapper = document.createElement('div');
        style_wrapper.id = 'style-btn-' + id;
        style_wrapper.innerHTML = style_tag;

        editor.insertContent(btn_html);
        editor.dom.add(editor.getBody(), style_wrapper);

        parent.tb_remove();
    }
    
    function updateButton(id_button, callBack) {
        if(typeof id_button === 'undefined') {
            addButton();
            return false;
        }

        var btn_link = $('#link_target').val().trim();
        if (btn_link.indexOf('http://') === -1) {
            if (btn_link !== '') {
                btn_link = 'http://' + btn_link;
            }
            if (btn_link === '') {
                btn_link = '#';
            }
        }
        $.ajax({
            url: myButtons.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpse_button_ajax',
                task: 'update',
                id_btn: id_button,
                btn_link: btn_link,
                style_btn: myButton.styles
            },
            success: function (res, stt) {
                if (stt === 'success') {
                    $('#save-notice').fadeIn(200).delay(2000).fadeOut(200);
                    $('a#btn-'+res.id_btn).html($('#button_label').val());

                    mySavedButtons[res.id_btn] = $.extend({}, myButton.styles);
                    render_styles_btns();
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
    
    function addButton() {
        var btn_link = $('#link_target').val().trim();
        if (btn_link.indexOf('http://') === -1) {
            if (btn_link !== '') {
                btn_link = 'http://' + btn_link;
            }
            if (btn_link === '') {
                btn_link = '#';
            }
        }

        $.ajax({
            url: myButtons.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpse_button_ajax',
                task: 'addnew',
                btn_link: btn_link,
                style_btn: myButton.styles
            },
            success: function (res, stt) {
                if (stt === 'success') {
                    $('#save-notice').fadeIn(200).delay(2000).fadeOut(200);

                    $('#saved-btns-block ul').append('<li class="btns-item" data-id-button="'+res.id_btn+'">'
                                                    + '<a id="btn-'+res.id_btn+'" data-id-button="'+res.id_btn+'" class="wpsebtn">'+myButton.styles['text']+'</a><br />'
                                                    + '<div> <a class="insert" title="Insert"><i class="dashicons dashicons-plus-alt"></i></a> <a class="edit" title="Edit"><i class="dashicons dashicons-edit"></i></a> <a class="trash" title="Delete"><i class="dashicons dashicons-trash"></i></a> </div>'
                                                    + '</li>');
                    mySavedButtons[res.id_btn] = $.extend({}, myButton.styles);
                    render_styles_btns();
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

    // Get value when open button from tinyMCE
    var editor = parent.tinyMCE.activeEditor;
    var selected = editor.selection;
    var container = editor.dom.getParent(selected.getStart(), 'a.wpsebtn');

    if (container) {
        font_size = editor.dom.getAttrib(container, 'data-font-size');
        text_color = editor.dom.getAttrib(container, 'data-color');
        back_color = editor.dom.getAttrib(container, 'data-background');
        padding_left = editor.dom.getAttrib(container, 'data-padding-left');
        padding_top = editor.dom.getAttrib(container, 'data-padding-top');
        padding_right = editor.dom.getAttrib(container, 'data-padding-right');
        padding_bottom = editor.dom.getAttrib(container, 'data-padding-bottom');
        border_radius = editor.dom.getAttrib(container, 'data-border-radius');
        border_color = editor.dom.getAttrib(container, 'data-border-color');
        border_width = editor.dom.getAttrib(container, 'data-border-width');
        color_hover = editor.dom.getAttrib(container, 'data-color-hover');
        back_hover = editor.dom.getAttrib(container, 'data-background-hover');
        boxshadow_color_hover = editor.dom.getAttrib(container, 'data-box-shadow-color-hover');
        boxshadow_horz_hover = editor.dom.getAttrib(container, 'data-box-shadow-horz-hover');
        boxshadow_vert_hover = editor.dom.getAttrib(container, 'data-box-shadow-vert-hover');
        boxshadow_blur_hover = editor.dom.getAttrib(container, 'data-box-shadow-blur-hover');
        boxshadow_spread_hover = editor.dom.getAttrib(container, 'data-box-shadow-spread-hover');
        link_target = editor.dom.getAttrib(container, 'href');
        if (link_target === '#') {
            link_target = '';
        }
        if (link_target !== '#') {
            link_target = link_target.replace('http://', '');
        }
        btn_text = container.innerText.trim();

        $('#button_label').val(btn_text).change();
        $('#button_fontsize').val(font_size).change();
        $('#text_color').minicolors('value', text_color).change();
        $('#back_color').minicolors('value', back_color).change();
        $('#link_target').val(link_target).change();
        $('#button_padding_left').val(padding_left).change();
        $('#button_padding_top').val(padding_top).change();
        $('#button_padding_right').val(padding_right).change();
        $('#button_padding_bottom').val(padding_bottom).change();
        $('#button_radius').val(border_radius).change();
        $('#border_color').minicolors('value', border_color).change();
        $('#button_border_width').val(border_width).change();
        $('#text_hover_color').minicolors('value', color_hover).change();
        $('#back_hover_color').minicolors('value', back_hover).change();
        $('#boxshadow_hover_color').minicolors('value', boxshadow_color_hover).change();
        $('#boxshadow_horz').val(boxshadow_horz_hover).change();
        $('#boxshadow_vert').val(boxshadow_vert_hover).change();
        $('#boxshadow_blur').val(boxshadow_blur_hover).change();
        $('#boxshadow_spread').val(boxshadow_spread_hover).change();
    }

    function initMenu() {
        $('#saved-btns-block .btns-item .trash').unbind('click').on('click', function () {
            id_btn = $(this).closest('li').data('id-button');
            cfm = confirm(myButtons.confirmText);
            if (cfm) {
                $.ajax({
                    url: myButtons.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpse_button_ajax',
                        id_btn: id_btn,
                        task: 'delete'
                    },
                    success: function (res, stt) {
                        if (stt === 'success') {
                            $('#saved-btns-block li[data-id-button='+ id_btn +']').fadeOut();
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

        $('#saved-btns-block .btns-item .edit').unbind('click').on('click', function () {
            var id_btn = $(this).closest('li').data('id-button');
            var btn_styles = mySavedButtons[id_btn];

            $('#btn_save_text').html(myButtons.updateText);
            $('#previewBtn').data('id-button', id_btn);
            $('#previewBtn').html($('#btn-'+ id_btn).text());

            link_target = mySavedButtons[id_btn]['btn-link'];
            if (link_target) {
                if (link_target === '#') {
                    link_target = '';
                }
                if (link_target !== '#') {
                    link_target = link_target.replace('http://', '');
                }
            }
            $('#link_target').val(link_target).change();

            populateParams(btn_styles);
        });
        
        $('#saved-btns-block .btns-item .insert').unbind('click').on('click', function () {
            var id_btn = $(this).closest('li').data('id-button');
            var btn_styles = mySavedButtons[id_btn];

            insertButton(btn_styles);
        })
    }

    initMenu();

    function populateParams(params) {
        $.each(params, function(css_prop, css_val){
            if($('[data-css-prop="'+css_prop+'"]').hasClass('minicolors-input')) {
                $('[data-css-prop="'+css_prop+'"]').minicolors('value', css_val).change();
            } else {
                $('[data-css-prop="'+css_prop+'"]').val(css_val).change();
            }
        });
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

    $(function () {
        // Get default properties if user doesn't change anything and insert
        setTimeout(function () {
            $('.observeBtnChanges').each(function (e) {
                var css_property = $(this).data('css-prop');
                myButton.styles[css_property] = $(this).val();
                update_styles();
            })
        }, 500);
    });
});