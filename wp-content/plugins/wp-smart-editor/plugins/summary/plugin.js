(function () {
    tinyMCE.PluginManager.add('summary', function(editor, url) {
        var myButton = null;
        editor.addButton('summary', {
            title: 'Summary',
            type: 'splitbutton',
            icon: 'myicon dashicons-clipboard',
            onclick: summaryCommand,
            menu: [{
                text: ' Insert Summary',
                icon: 'myicon dashicons-clipboard',
                onclick: summaryCommand
            }, {
                text: ' Update Summary',
                icon: 'myicon dashicons-update',
                onPostRender: function () {
                    myButton = this;
                },
                onclick: updateSummaryCommand
            }]
        });

        editor.on('NodeChange', function(event) {
            if(myButton) {
                myButton.disabled(!(editor.dom.getParent(event.element, 'div.tableOfContent')));
            }
        });

        function summaryCommand() {
            var element = editor.selection.getNode();
            if (element.nodeName === 'BODY') {
                editor.focus();
                element = editor.selection.getNode();
            }
            var par = editor.dom.getParents(element);
            for (i = 0; i < par.length; i++) {
                if (par[i].tagName === 'BODY') {
                    element = par[i-1];
                    break;
                }
            }
            var body = editor.dom.getParent(element, 'body');
            var wrapper = document.createElement('div');
            var toc = '<div class="tableOfContent">' + buildTableOfContent() + '</div>';
            wrapper.innerHTML = toc;
            body.insertBefore(wrapper.firstChild, element);
            editor.undoManager.add();
        }

        function updateSummaryCommand() {
            var element = editor.selection.getNode();
            var toc = editor.dom.getParent(element, 'div.tableOfContent');
            if (toc !== null) {
                toc.innerHTML = buildTableOfContent();
            }
            else {
                return;
            }
            editor.undoManager.add();
        }

        function buildTableOfContent() {
            var myTree = (editor.dom.select('h1,h2,h3,h4'));
            var myTreehtml = '<ul class="tableOfContent">';

            for (var i = 0; i < myTree.length; i++) {
                var cNode = myTree[i];
                cTag = cNode.tagName;
                cLevel = parseInt(cTag.replace('H', ''));
                if(cNode.textContent === '') continue;

                anchorID = getAnchorID(cNode,'toc');

                if (i < myTree.length - 1) {
                    nNode = myTree[i + 1];
                    nTag = nNode.tagName;
                    nLevel = parseInt(nTag.replace('H', ''));
                } else {
                    nLevel = cLevel;
                }

                myTreehtml += '<li class="toc-level'+cLevel+'">';
                myTreehtml += '<a style="cursor: pointer" onclick="location.hash=\'#'+anchorID+'\';" >' + cNode.textContent + '</a>';
                if (cLevel !== nLevel) {
                    if (nLevel > cLevel) {
                        for (var j = 0; j < (nLevel - cLevel - 1); j++) {
                            myTreehtml += '<ul class="tableOfContent"><li class="toc-level'+(cLevel+j+1)+'">';
                        }
                        myTreehtml += '<ul class="tableOfContent">';
                    } else {
                        // The next item is shallower.
                        myTreehtml += '</li>';
                        for (var j = 0; j < (cLevel - nLevel); j++) {
                            myTreehtml += '</ul></li>';
                        }
                    }
                } else {
                    myTreehtml += '</li>';
                }

            }
            myTreehtml += '</ul>';

            return myTreehtml;
        }

        verboseIdCache = {};
        function getAnchorID(heading, prefix) {
            var candidateId =  heading.textContent.replace(/[^a-z0-9]/ig, ' ').replace(/\s+/g, '-').toLowerCase();
            if (verboseIdCache[candidateId]) {
                var j = 2;

                while(verboseIdCache[candidateId + j]) {
                    j++;
                }
                candidateId = candidateId + '-' + j;

            }
            verboseIdCache[candidateId] = true;
            heading.id = prefix + '-' + candidateId;

            return prefix + '-' + candidateId;
        }
    });
})();