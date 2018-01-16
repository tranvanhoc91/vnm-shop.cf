(function () {
    tinyMCE.PluginManager.add('wpsetooltips', function (editor, url) {
        var myButton = null;
        editor.addButton('wpsetooltips', {
            title: 'Add Tooltips',
            icon: 'myicon dashicons-lightbulb',
            onPostRender: function () {
                myButton = this;
            },
            onclick: function() {
                jQuery('div.mce-inline-toolbar-grp').hide();
                var par = editor.dom.getParent(editor.selection.getNode(), '.wpsetooltips'),
                    wpsebtn = editor.dom.getParent(editor.selection.getNode(), '.wpsebtn'),
                    old_tooltip = '',
                    update = false,
                    selectedText = editor.selection.getContent();

                if (par) {
                    old_tooltip = editor.dom.getAttrib(par, 'data-qtip');
                    old_tooltip = old_tooltip.replace(/(<br \/>|<br\/>)/g, '\n');
                    selectedText = par.innerText;
                    if (par.nodeName === 'IMG') {
                        selectedText = '<IMG/>'
                    }
                    update = true;
                }
                if (wpsebtn) {
                    selectedText = wpsebtn.innerText;
                }

                editor.windowManager.open({
                    title: 'Tooltips',
                    body: [{
                        type: 'textbox',
                        label: 'Tooltip source',
                        name: 'sourceText',
                        id: 'sourceText',
                        disabled: true,
                        value: selectedText
                    }, {
                        type: 'container',
                        html: '<textarea id="wpse-tooltips-text" style="width: 400px; height: 100px; margin: 3px; padding: 5px; resize: none; overflow: auto" placeholder="Enter your tooltips here...">'+ old_tooltip +'</textarea>'
                    }],
                    buttons: [{
                        text: 'Remove',
                        tooltip: 'Remove tooltips',
                        subtype: 'pull-left',
                        onclick: function () {
                            removeTooltips();
                        },
                        onPostRender: function () {
                            this.disabled(!update)
                        }
                    }, {
                        text: 'OK',
                        subtype: 'primary',
                        onclick: function () {
                            var tooltip_text = document.getElementById('wpse-tooltips-text').value;
                            tooltip_text = tooltip_text.trim();
                            tooltip_text = tooltip_text.replace(/\n/g , '<br/>');
                            if (tooltip_text === '') {
                                removeTooltips();
                            } else {
                                insertTooltips(tooltip_text);
                            }
                        }
                    }, {
                        text: 'Cancel',
                        onclick: 'close'
                    }]
                });

                function insertTooltips(text) {
                    if (!update) {
                        var wpsebtn = editor.dom.getParent(editor.selection.getNode(), '.wpsebtn');
                        if (editor.selection.getNode().nodeName === 'IMG') {
                            imgElm = editor.selection.getNode();
                            editor.dom.addClass(imgElm, 'wpsetooltips');
                            editor.dom.setAttrib(imgElm, 'data-qtip', text);
                            editor.undoManager.add();
                        } else if (wpsebtn) {
                            wpsebtn.className += ' wpsetooltips';
                            editor.dom.setAttrib(wpsebtn, 'data-qtip', text);
                            editor.undoManager.add();
                        } else {
                            var atext = editor.selection.getContent();
                            editor.insertContent('<span class="wpsetooltips" data-qtip="'+ text +'">'+ atext +'</span>');
                        }
                    } else {
                        editor.dom.setAttrib(par, 'data-qtip', text);
                    }

                    editor.windowManager.close();
                }
                
                function removeTooltips() {
                    editor.dom.removeClass(par, 'wpsetooltips');
                    editor.dom.setAttrib(par, 'data-qtip', '');
                    editor.windowManager.close();
                    editor.nodeChanged();
                }
            }
        });

        editor.on('NodeChange', function(event) {
            if(myButton) {
                myButton.disabled(!(editor.dom.getParent(event.element, '.wpsetooltips')) && !editor.selection.getContent() && !(editor.dom.getParent(event.element, '.wpsebtn')));
            }

            // Load quick-edit panel for tooltiped element
            $ = jQuery;
            cTooltips = editor.dom.getParent(event.element, 'span.wpsetooltips');
            if (typeof oTooltips != 'undefined') {
                if (oTooltips == cTooltips) return;
                oTooltips.removeAttribute('data-mce-selected');
            }
            toolbar = $('.wpsetooltip-qe-block');
            toolbar.remove();

            if (cTooltips) {
                cTooltips.setAttribute('data-mce-selected', 1);
                oTooltips = cTooltips;
                editorWindows = editor.windowManager.getWindows().length;
                // Remove the panel if popup windows is opened
                if (editorWindows) return false;
                thisQtip = editor.dom.getAttrib(cTooltips, 'data-qtip');

                // Append panel to editor
                divHTML = '<div class="mce-toolbar-grp mce-inline-toolbar-grp mce-container mce-panel wpsetooltip-qe-block">' +
                            '<div class="wpsetooltip-qe-container">' +
                                '<div class="wpsetooltip-qe-preview"><p>'+ thisQtip +'</p></div>' +
                                '<div class="wpsetooltip-qe-btn">' +
                                    '<div class="wpsetooltip-qe-edit"><button title="Quick edit tooltips"><i class="mce-ico mce-i-dashicon dashicons-edit"></i></button></div>' +
                                    '<div class="wpsetooltip-qe-remove"><button title="Remove tooltips"><i class="mce-ico mce-i-dashicon dashicons-dismiss"></i></button></div>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
                $('body').append(divHTML);

                function initButtons() {
                    $('.wpsetooltip-qe-edit').unbind('click').click(function () {
                        editMode(thisQtip);

                        newQtip = thisQtip;
                        $('.wpsetooltip-qe-preview input').on('change input propertychange', function () {
                            newQtip = $(this).val().trim();
                        });

                        $('.wpsetooltip-qe-preview input').on('keypress keyup', function (e) {
                            if (e.which === 13) { //Apply new href if user hit enter
                                e.preventDefault();
                                applyQtip(newQtip);
                            }
                            if (e.which === 27) { //Cancel editing if user hit ESC
                                e.preventDefault();
                                applyQtip(thisQtip);
                            }
                        });

                        $('.wpsetooltip-qe-apply').unbind('click').click(function () {
                            applyQtip(newQtip);
                        })
                    });
                    
                    $('.wpsetooltip-qe-remove').unbind('click').click(function () {
                        editor.dom.removeClass(cTooltips, 'wpsetooltips');
                        editor.dom.setAttrib(cTooltips, 'data-qtip', '');
                        editor.nodeChanged();
                        editor.undoManager.add();
                        toolbar.remove();
                    })
                }
                initButtons();
                setPosition();

                // For full height editor.
                editor.on( 'resizewindow scrollwindow', setPosition );
                // For scrollable editor.
                editor.dom.bind( editor.getWin(), 'resize scroll', setPosition );

                function applyQtip(qtip) {
                    if (qtip !== '') {
                        editor.dom.setAttrib(cTooltips, 'data-qtip', qtip);
                        editor.undoManager.add();
                        thisQtip = qtip;
                        previewMode(qtip);
                    } else {
                        editor.dom.setAttrib(cTooltips, 'data-qtip', '');
                        editor.dom.removeClass(cTooltips, 'wpsetooltips');
                        editor.nodeChanged();
                        editor.undoManager.add();
                        toolbar.remove();
                    }
                }
                
                function editMode(qtip) {
                    editInput = '<input type="text" placeholder="Enter your tooltips..." value="'+ qtip +'" />';
                    editBtns = '<div class="wpsetooltip-qe-apply"><button title="Apply new tooltips"><i class="mce-ico mce-i-dashicon dashicons-editor-break"></i></button></div>';

                    $('.wpsetooltip-qe-preview').html(editInput);
                    $('.wpsetooltip-qe-btn').html(editBtns);
                    $('.wpsetooltip-qe-preview input').select();
                }

                function previewMode(qtip) {
                    previewHTML = '<p>'+ qtip +'</p>';
                    previewBtns = '<div class="wpsetooltip-qe-edit"><button title="Quick edit tooltips"><i class="mce-ico mce-i-dashicon dashicons-edit"></i></button></div>' +
                                    '<div class="wpsetooltip-qe-remove"><button title="Remove tooltips"><i class="mce-ico mce-i-dashicon dashicons-dismiss"></i></button></div>';

                    $('.wpsetooltip-qe-preview').html(previewHTML);
                    $('.wpsetooltip-qe-btn').html(previewBtns);
                    initButtons();
                }
            } else {
                delete oTooltips;
                editor.off('resizewindow scrollwindow', setPosition);
                editor.dom.unbind(editor.getWin(), 'resize scroll', setPosition);
            }

            // Set position for the panel
            function setPosition() {
                cTooltips = editor.dom.getParent(event.element, 'span.wpsetooltips');
                if (!cTooltips) return;
                //Get needed information for positioning
                var container = editor.getContainer(),
                    wpAdminbar = document.getElementById( 'wpadminbar' ),
                    mceIframe = document.getElementById( editor.id + '_ifr' ),
                    mceToolbar,
                    mceStatusbar,
                    wpStatusbar;

                if ( container ) {
                    mceToolbar = tinymce.$( '.mce-toolbar-grp', container )[0];
                    mceStatusbar = tinymce.$( '.mce-statusbar', container )[0];
                }

                if ( editor.id === 'content' ) {
                    wpStatusbar = document.getElementById( 'post-status-info' );
                }

                var scrollX = window.pageXOffset || document.documentElement.scrollLeft,
                    scrollY = window.pageYOffset || document.documentElement.scrollTop,
                    windowWidth = window.innerWidth,
                    windowHeight = window.innerHeight,
                    iframeRect = mceIframe ? mceIframe.getBoundingClientRect() : {
                        top: 0,
                        right: windowWidth,
                        bottom: windowHeight,
                        left: 0,
                        width: windowWidth,
                        height: windowHeight
                    },
                    toolbar = $('.wpsetooltip-qe-block'),
                    toolbarWidth = toolbar.outerWidth(),
                    toolbarHeight = toolbar.outerHeight(),
                    selection = cTooltips.getBoundingClientRect(),
                    selectionMiddle = ( selection.left + selection.right ) / 2,
                    buffer = 5,
                    spaceNeeded = toolbarHeight + buffer,
                    wpAdminbarBottom = wpAdminbar ? wpAdminbar.getBoundingClientRect().bottom : 0,
                    mceToolbarBottom = mceToolbar ? mceToolbar.getBoundingClientRect().bottom : 0,
                    mceStatusbarTop = mceStatusbar ? windowHeight - mceStatusbar.getBoundingClientRect().top : 0,
                    wpStatusbarTop = wpStatusbar ? windowHeight - wpStatusbar.getBoundingClientRect().top : 0,
                    blockedTop = Math.max( 0, wpAdminbarBottom, mceToolbarBottom, iframeRect.top ),
                    blockedBottom = Math.max( 0, mceStatusbarTop, wpStatusbarTop, windowHeight - iframeRect.bottom ),
                    spaceTop = selection.top + iframeRect.top - blockedTop,
                    spaceBottom = windowHeight - iframeRect.top - selection.bottom - blockedBottom,
                    editorHeight = windowHeight - blockedTop - blockedBottom,
                    className = '',
                    iosOffsetTop = 0,
                    iosOffsetBottom = 0,
                    top, left;

                editor.on( 'hide', function () {
                    toolbar.remove();
                } );

                if ( spaceTop >= editorHeight || spaceBottom >= editorHeight ) {
                    toolbar.hide();
                } else {
                    toolbar.show();
                }

                // Prepare need data for classes and position
                if ( spaceBottom >= spaceNeeded ) {
                    className = 'mce-arrow-up';
                    top = selection.bottom + iframeRect.top + scrollY - iosOffsetBottom;
                } else if ( spaceTop >= spaceNeeded ) {
                    className = 'mce-arrow-down';
                    top = selection.top + iframeRect.top + scrollY - toolbarHeight + iosOffsetTop;
                }

                if ( typeof top === 'undefined' ) {
                    top = scrollY + blockedTop + buffer + iosOffsetBottom;
                }

                left = selectionMiddle - toolbarWidth / 2 + iframeRect.left + scrollX;

                if ( selection.left < 0 || selection.right > iframeRect.width ) {
                    left = iframeRect.left + scrollX + ( iframeRect.width - toolbarWidth ) / 2;
                } else if ( toolbarWidth >= windowWidth ) {
                    className += ' mce-arrow-full';
                    left = 0;
                } else if ( ( left < 0 && selection.left + toolbarWidth > windowWidth ) || ( left + toolbarWidth > windowWidth && selection.right - toolbarWidth < 0 ) ) {
                    left = ( windowWidth - toolbarWidth ) / 2;
                } else if ( left < iframeRect.left + scrollX ) {
                    className += ' mce-arrow-left';
                    left = selection.left + iframeRect.left + scrollX;
                } else if ( left + toolbarWidth > iframeRect.width + iframeRect.left + scrollX ) {
                    className += ' mce-arrow-right';
                    left = selection.right - toolbarWidth + iframeRect.left + scrollX;
                }

                // Set style
                toolbar.removeClass('mce-arrow-up mce-arrow-down mce-arrow-left mce-arrow-right');
                toolbar.addClass(className);
                toolbar.css({
                    'top': top,
                    'left': left
                })
            }
        });
    })
})();