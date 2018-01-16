(function () {
    parent.jQuery('#TB_window,#TB_iframeContent').height(560).css('z-index', 100000001);
})();

jQuery(document).ready(function ($) {
    $(window).load(function() {
        // Animate loader off screen
        $(".preload-img").fadeOut("slow");
        $(".container").show();
    });

    var editor = parent.tinyMCE.activeEditor;
    var selected = editor.selection.getNode();
    currentContent = '';
    if (editor) {
        currentContent = editor.getContent({format: 'raw'});
    }
    if (selected.nodeName === 'BODY') {
        editor.focus();
        selected = editor.selection.getNode();
    }

    $('#saveBtn').unbind('click').click(function () {
        templateName = $('#save-template-name').val().trim();
        if (templateName === '') {
            alert(data.emptyName);
            return false;
        }

        $.ajax({
            url: data.ajaxurl,
            method: 'POST',
            data: {
                action: 'wpse_htmltemplate_ajax',
                task: 'save',
                templateName: templateName,
                content: currentContent
            },
            success: function (res, stt) {
                new_template = '<li class="saved-template-item" id="template-'+res.id+'" data-id-template="'+res.id+'">' +
                                    '<a class="title"><span>'+templateName+'</span></a>' +
                                    '<a class="trash" title="Delete">' +
                                        '<i class="dashicons dashicons-trash"></i>' +
                                    '</a>' +
                                    '<a class="edit" title="Edit name">' +
                                        '<i class="dashicons dashicons-edit"></i>' +
                                    '</a>' +
                                '</li>';
                $('#saved-template-list').append(new_template);
                initButton();
            },
            error: function(jqxhr, textStatus, error) {
                alert(textStatus + " : " + error);
            }
        })
    });
    
    var initButton = function () {
        $('.saved-template-item a.title').unbind('click').click(function () {
            template_id = $(this).closest('li').data('id-template');

            $.ajax({
                url: data.ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpse_htmltemplate_ajax',
                    task: 'load',
                    template_id: template_id
                },
                success: function (res, stt) {
                    var editor = parent.tinyMCE.activeEditor;
                    wrapper = document.createElement('div');
                    wrapper.innerHTML = res.content;
                    wrapper.id = 'wpseHtmlTemplate';

                    if (editor) {
                        selected = editor.selection.getNode();
                        while(wrapper.firstChild) {
                            selected.parentNode.insertBefore(wrapper.firstChild, selected);
                        }
                        editor.undoManager.add();
                    }
                    parent.tb_remove();
                },
                error: function(jqxhr, textStatus, error) {
                    alert(textStatus + " : " + error);
                }
            })
        });

        $('.saved-template-item a.edit').unbind('click').click(function (e) {
            e.stopPropagation();
            tmplName = $(this).parent().find('a.title span');
            oldName = tmplName.text();
            $(tmplName).attr('contentEditable', true);
            $(tmplName).addClass('editable');
            $(tmplName).selectText();

            $('.saved-template-item span.editable').bind('click.mm', hstop);   // Click on the editable object
            $(tmplName).bind('keypress.mm', hpress);                        // Press enter to validate name
            $('*').not($(tmplName)).bind('click.mm', houtside);             // Click outside the editable object

            // Unbind all functions
            function unbindAll() {
                $('.saved-template-item a').unbind('click.mm', hstop);
                $(tmplName).unbind('keypress.mm', hpress);
                $('*').not($(tmplName)).unbind('click.mm', houtside);
            }

            $(tmplName).keyup(function (e) {
                // Press ESC button will cancel renaming action
                if (e.which === 27) {
                    e.preventDefault();
                    unbindAll();
                    $(tmplName).text(oldName);
                    $(tmplName).removeAttr('contentEditable');
                    $(tmplName).removeClass('editable');
                }
            });

            // Click on the editable object
            function hstop(e) {
                e.stopPropagation();
                return false;
            }

            // Click outside the editable object will validate the name
            function houtside(e) {
                unbindAll();
                updateTmplName($(tmplName).text());
                $(tmplName).removeAttr('contentEditable');
                $(tmplName).removeClass('editable');
            }

            // Press enter to validate name
            function hpress(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    unbindAll();
                    updateTmplName($(tmplName).text());
                    $(tmplName).removeAttr('contentEditable');
                    $(tmplName).removeClass('editable');
                }
            }

            function updateTmplName(name) {
                name = name.trim();
                template_id = $(tmplName).parents('li').data('id-template');

                if (name !== '') {
                    $.ajax({
                        url: data.ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'wpse_htmltemplate_ajax',
                            task: 'edit',
                            template_id: template_id,
                            name: name
                        },
                        success: function (res, stt) {
                            $(tmplName).text(res.name);
                        },
                        error: function(jqxhr, textStatus, error) {
                            $(tmplName).text(oldName);
                            alert(textStatus + " : " + error + " - " + jqxhr.responseText);
                        }
                    })
                } else {
                    $(tmplName).text(oldName);
                    return false;
                }

                $(tmplName).parent().css('white-space', 'normal');
                setTimeout(function() {
                    $(tmplName).parent().css('white-space', '');
                }, 200);
            }
        });

        $('.saved-template-item a.trash').unbind('click').click(function () {
            if (confirm(data.confirmText)) {
                template_id = $(this).closest('li').data('id-template');

                $.ajax({
                    url: data.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wpse_htmltemplate_ajax',
                        task: 'delete',
                        template_id: template_id
                    },
                    success: function (res, stt) {
                        $('#saved-template-list li[data-id-template='+res.id+']').fadeOut();
                    },
                    error: function(jqxhr, textStatus, error) {
                        alert(textStatus + " : " + error);
                    }
                })
            }
        })
    };

    initButton();

    // Function to select text when clicking edit
    $.fn.selectText = function(){
        var doc = document        , element = this[0]        , range, selection    ;
        if (doc.body.createTextRange) {
            range = document.body.createTextRange();
            range.moveToElementText(element);
            range.select();
        } else if (window.getSelection) {
            selection = window.getSelection();
            range = document.createRange();
            range.selectNodeContents(element);
            selection.removeAllRanges();
            selection.addRange(range);
        }
        element.focus();
    };
});
