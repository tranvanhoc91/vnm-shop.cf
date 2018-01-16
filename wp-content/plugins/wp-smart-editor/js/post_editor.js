(function ($) {
    var currentSetting = 'wp-settings-' + user.id;
    var editor_status = getCookie(currentSetting, 'editor');

    $(window).load(function() {
        initPostEditor();

        var searchTooltip = '<div>'
            + '<p style="margin-top: 0"><strong>Ctrl-F: </strong>Search</p>'
            + '<p><strong>Ctrl-Shift-F: </strong>Replace</p>'
            + '<p><strong>Alt-G: </strong>Jump to line</p>'
            + '</div>';

        $('#qt_content_search').qtip({
            content: searchTooltip,
            position: {
                my: 'top left',
                at: 'bottom middle'
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
    });

    function getCookie(key, sub_key) {
        currentcookie = document.cookie;
        if(currentcookie.length > 0) {
            firstidx = currentcookie.indexOf(key + "=");
            if(firstidx != -1) {
                firstidx = firstidx + key.length + 1;
                lastidx = currentcookie.indexOf(";",firstidx);
                if(lastidx == -1) {
                    lastidx = currentcookie.length;
                }
                if(sub_key) {
                    var result = {};
                    unescape(currentcookie.substring(firstidx, lastidx)).split("&").forEach(function(part) {
                        var item = part.split("=");
                        result[item[0]] = decodeURIComponent(item[1]);
                    });
                    return result[sub_key];
                }
                return unescape(currentcookie.substring(firstidx, lastidx));
            }
        }
        return "";
    }

    function initPostEditor() {
        if(editor_status === 'html') {
            window.deployCodeMirror('content');
        }
    }

    window.deployCodeMirror = function (elm) {
        var cssTop = $('#content').css('margin-top');
        wpse_post_editor = CodeMirror.fromTextArea(document.getElementById(elm), {
            mode: 'xml',
            lineNumbers: true,
            lineWrapping: true,
            extraKeys: {"Ctrl-Space": "autocomplete"}
        });
        $('.CodeMirror').css({
            'top': cssTop,
            'margin-bottom': cssTop,
            'min-height': 500
        });
    };

    window.wpse_custom_qt = function( element, start ) {
        if (typeof start === 'undefined') {
            start = true;
        }
        if ( start ) {
            wpse_post_editor.save();
            var fromCursor = wpse_post_editor.getCursor('from');
            var toCursor = wpse_post_editor.getCursor('to');
            window.set_content_cursor(element, fromCursor, toCursor);
        }
        else {
            var newCursorPos = window.get_content_cursor(element, 'to');
            wpse_post_editor.setValue(element.value);
            wpse_post_editor.setCursor(newCursorPos.line, newCursorPos.ch);
            wpse_post_editor.refresh();
            wpse_post_editor.focus();
        }
    };

    window.get_content_cursor = function( element, pos ) {
        if (typeof pos === 'undefined') {
            pos = 'from';
        }
        var caret;
        if ( document.selection ) { // IE
            var sel = document.selection.createRange();
            var selLength = document.selection.createRange().text.length;
            if(pos == 'from') {
                sel.moveStart('character', -element.value.length);
                caret = sel.text.length - selLength;
            }
            else if(pos == 'to') {
                sel.moveStart('character', -element.value.length);
                caret = sel.text.length;
            }
        } else if ( element.selectionStart || element.selectionStart === 0 ) { // FF, WebKit, Opera
            if(pos == 'from') {
                caret = element.selectionStart;
            }
            else if(pos == 'to') {
                caret = element.selectionEnd;
            }
        }

        var lines = element.value.substr(0, caret).split("\n");
        var newLength = 0, line = 0, lineArray = [];
        $.each(lines, function(key, value) {
            newLength = newLength + value.length + 1;
            lineArray[line] = newLength;
            if(caret > value.length) {
                caret -= value.length + 1
            }
            else {
                return false;
            }
            line++;
        });
        return {"line": line, "ch": caret};
    };

    window.set_content_cursor = function( element, fromCursor, toCursor) {
        var startLines = element.value.substr(0).split("\n");
        var endLines = element.value.substr(0).split("\n");
        var startNewLength = 0, startLine = 1, startLineArray = [];
        var endNewLength = 0, endLine = 1, endLineArray = [];

        startLineArray[0] = 0;
        $.each(startLines, function(key, value) {
            startNewLength = startNewLength + value.length + 1;
            startLineArray[startLine] = startNewLength;
            startLine++;
        });

        endLineArray[0] = 0;
        $.each(endLines, function(key, value) {
            endNewLength = endNewLength + value.length + 1;
            endLineArray[endLine] = endNewLength;
            endLine++;
        });

        var start = startLineArray[fromCursor.line] + fromCursor.ch, end = endLineArray[toCursor.line] + toCursor.ch;

        if(element.setSelectionRange) {
            $(element).show();
            element.focus();
            element.setSelectionRange(start, end);
            $(element).hide();
        }
        else if(element.createTextRange) {
            var range = element.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    };
    
    $(document).ready(function () {
        $('#content-tmce').click(function() {
            if(editor_status !== 'tmce') {
                wpse_post_editor.toTextArea();
                id = $(this).data( 'wp-editor-id' );
                switchEditors.go(id, 'tmce');
                editor_status = 'tmce';
                return false;
            }
        });

        $('#content-html').click(function() {
            if(editor_status !== 'html') {
                id = $(this).data( 'wp-editor-id' );
                switchEditors.go(id, 'html');
                setTimeout(function() {
                    window.deployCodeMirror('content');
                }, 0);
                editor_status = 'html';
                return false;
            }
            else {
                wpse_post_editor.toTextArea();
                window.deployCodeMirror('content');
                return false;
            }
        });

        if (typeof (QTags) !== 'undefined') {
            QTags.addButton('search', 'Search/Replace', wpse_search_func);
            function wpse_search_func() {
                wpse_post_editor.execCommand('replace');
            }
        }

        $('#wp-link-submit').on('click', function() {
            if (editor_status === 'html') {
                wpse_post_editor.toTextArea();
                wpLink.update();
                var element = document.getElementById('content');
                var cursor = window.get_content_cursor(element);
                window.deployCodeMirror('content');
                wpse_post_editor.setCursor(cursor.line, cursor.ch);
            }
        });
    })
})(jQuery);