jQuery(document).ready(function ($) {
    $('#wpse-config-close').click(function () {
        $('#wpse-config-success').slideUp();
    });

    $('.wpse_qtip').qtip({
        content: {
            attr: 'alt'
        },
        position: {
            my: 'top left',
            at: 'bottom bottom'
        },
        style: {
            tip: {
                corner: true
            },
            classes: 'wpsetips_qtip'
        },
        show: 'hover',
        hide: {
            fixed: true,
            delay: 10
        }
    });

    // Function for Custom Style tab
    initCustomStyleMenu();

    function initCustomStyleMenu() {
        (initCustomStyleNew = function () {
            $('#mybootstrap a.wpse-customstyles-new').unbind('click').click(function (e) {
                that = this;
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wpse_custom_styles_ajax',
                        task: 'new'
                    },
                    success: function (res, stt) {
                        if (stt === 'success') {
                            $(that).parent().before('<li class="wpse-customstyles-items" data-id-customstyle="' + res.id + '"><a><i class="wpseicon-quill"></i> <span class="wpse-customstyles-items-title">' + res.title + '</span></a><a class="copy"><i class="wpseicon-copy"></i></a><a class="trash"><i class="wpseicon-trash"></i></a><ul style="margin-left: 30px"><li class="wpse-customstyles-items-class">('+ res.name +')</li></ul></li>');
                            initCustomStyleMenu();
                        } else {
                            alert(stt);
                        }
                    }
                });
                return false;
            })
        })();

        (initCustomStyleDelete = function () {
            $('#mybootstrap .wpse-customstyles-items a.trash').unbind('click').click(function (e) {
                that = this;
                var cf = confirm('Do you really want to delete "' + $(this).prev().prev().text().trim() + '"?');
                if (cf === true) {
                    var id = $(that).parent().data('id-customstyle');
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'wpse_custom_styles_ajax',
                            id: id,
                            task: 'delete'
                        },
                        success: function (res, stt) {
                            if (stt === 'success') {
                                $(that).parent().remove();
                                if (res.id == myStyleId) {
                                    customStylePreview();
                                } else {
                                    customStylePreview(myStyleId);
                                }
                            } else {
                                alert(stt);
                            }
                        }
                    });
                    return false;
                }
            })
        })();

        (initCustomStyleCopy = function () {
            $('#mybootstrap .wpse-customstyles-items a.copy').unbind('click').click(function (e) {
                that = this;
                var id = $(that).parent().data('id-customstyle');
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wpse_custom_styles_ajax',
                        id: id,
                        task: 'copy'
                    },
                    success: function (res, stt) {
                        if (stt === 'success') {
                            $(that).parents('.wpse-customstyles-list').find('li').last().before('<li class="wpse-customstyles-items" data-id-customstyle="' + res.id + '"><a><i class="wpseicon-quill"></i> <span class="wpse-customstyles-items-title">' + res.title + '</span></a><a class="copy"><i class="wpseicon-copy"></i></a><a class="trash"><i class="wpseicon-trash"></i></a><ul style="margin-left: 30px"><li class="wpse-customstyles-items-class">('+ res.name +')</li></ul></li>');
                            initCustomStyleMenu();
                        } else {
                            alert(stt);
                        }
                    }
                });
                return false;
            })
        })();

        (initTableLinks = function () {
            $('#mybootstrap .wpse-customstyles-items a:not(".copy, .edit, .trash, .wpse-customstyles-new"), #mybootstrap .wpse-customstyles-items ul').unbind('click').click(function (e) {
                id = $(this).parent().data('id-customstyle');
                $('#mybootstrap .wpse-customstyles-list li').removeClass('active');
                $(this).parent().addClass('active');
                customStylePreview(id);

                return false;
            })
        })();

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
    }

    var myCssArea, myEditor, myCustomCss, myStyleId;
    myCssArea = document.getElementById('wpse-customstyles-css');
    myEditor = CodeMirror.fromTextArea(myCssArea, {
        mode: 'css',
        lineNumbers: true,
        extraKeys: {"Ctrl-Space": "autocomplete"}
    });

    $(myCssArea).on('change', function() {
        myEditor.setValue($(myCssArea).val());
    });

    myEditor.on("blur", function() {
        myEditor.save();
        $(myCssArea).trigger('propertychange');

    });

    $('#custom-styles-tab').one('click', function () {
        myEditor.refresh();
        customStylePreview();
    });

    customStylePreview();
    function customStylePreview(id_element) {
        if (typeof (id_element) === "undefined") {
            id_element = $('#mybootstrap ul.wpse-customstyles-list li:first-child').data('id-customstyle');
            $('#mybootstrap ul.wpse-customstyles-list li:first-child').addClass('active');
        }
        if (typeof (id_element) === "undefined" || id_element ==="") return;
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpse_custom_styles_ajax',
                id: id_element,
                task: 'preview'
            },
            success: function (res, stt) {
                if (stt === 'success') {
                    $('#wpse-customstyles-title').val(res.title);
                    $('#wpse-customstyles-classname').val(res.name);

                    myStyleId = id_element;
                    myCustomCss = '{\n' + res.css + '\n}';

                    var previewTarget = $(".wpse-customstyles-target");
                    previewTarget.attr('style','');

                    if (typeof(myCustomCss) !== 'undefined' || myCustomCss !== '') {
                        $('#wpse-customstyles-css').val(myCustomCss);
                    } else {
                        $('#wpse-customstyles-css').val('');
                    }
                    myEditor.setValue(myCustomCss);
                    parseCustomStyleCss();
                } else {
                    alert(stt);
                }
            },
            error: function(jqxhr, textStatus, error) {
                alert(textStatus + " : " + error);
            }
        })
    }

    String.prototype.replaceAll = function(search, replace) {
        if (replace === undefined) {
            return this.toString();
        }
        return this.split(search).join(replace);
    };

    function parseCustomStyleCss() {
        var previewTarget = $("#wpse-customstyles-preview .wpse-customstyles-target");
        var parser = new (less.Parser);
        var content = '#wpse-customstyles-preview .wpse-customstyles-target ' + myEditor.getValue();
        parser.parse(content, function(err, tree) {
            if (err) {
                // Show error to the user
                if (err.message == 'Unrecognised input') {
                    err.message = configData.message;
                }
                alert(err.message);
                return false;
            } else {
                cssString = tree.toCSS().replace("#wpse-customstyles-preview .wpse-customstyles-target {","");
                cssString = cssString.replace("}","").trim();
                cssString = cssString.replaceAll("  ", "");
                myCustomCss = cssString;

                var attributes = cssString.split(';');
                for(var i=0; i<attributes.length; i++) {
                    if( attributes[i].indexOf(":") > -1) {
                        var entry = attributes[i].split(/:(.+)/);
                        previewTarget.css( jQuery.trim(""+entry[0]+""), jQuery.trim(entry[1]) );
                    }
                }

                return true;
            }
        })
    }

    (initCustomCssObserver = function () {
        var cssChangeWait;
        $('#wpse-customstyles-css').bind('input propertychange', function() {
            clearTimeout(cssChangeWait);
            cssChangeWait = setTimeout(function() {
                parseCustomStyleCss();
                saveCustomStyleChanges();
            }, 500);
        });
    })();

    $('#wpse-customstyles-title, #wpse-customstyles-classname').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            saveCustomStyleChanges();
        }
    });
    $('#wpse-customstyles-title, #wpse-customstyles-classname').on('change', function (e) {
        saveCustomStyleChanges();
    });

    function saveCustomStyleChanges() {
        var myTitle =  $('#wpse-customstyles-title').val().trim();
        var myClassname =  $('#wpse-customstyles-classname').val().trim();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpse_custom_styles_ajax',
                id: myStyleId,
                title: myTitle,
                name: myClassname,
                mycss: myCustomCss,
                task: 'style_save'
            },
            success: function (res, stt) {
                if (stt === 'success') {
                    // Update list items
                    thisStyle = $('.wpse-customstyles-list').find('li[data-id-customstyle='+myStyleId+']');
                    thisStyle.find('.wpse-customstyles-items-title').text(myTitle);
                    thisStyle.find('.wpse-customstyles-items-class').text('('+myClassname+')');

                    autosaveNotification = setTimeout(function() {
                        $('#savedInfo').fadeIn(200).delay(2000).fadeOut(1000);
                    }, 500);
                } else {
                    alert(stt)
                }
            },
            error: function(jqxhr, textStatus, error) {
                alert(textStatus + " : " + error + ' - ' + jqxhr.responseJSON);
            }
        })
    }
});