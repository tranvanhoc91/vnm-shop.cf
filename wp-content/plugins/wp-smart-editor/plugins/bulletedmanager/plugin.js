(function () {
    tinyMCE.PluginManager.add('bulletmngr', function (editor, url) {
        editor.addButton('bulletmngr', {
            title: 'Bullet Manager',
            icon: 'myicon dashicons-exerpt-view',
            onclick: function () {
                par = editor.dom.getParent(editor.selection.getStart(), 'ul');

                if (!par) {
                    editor.execCommand('InsertUnorderedList', false);
                }

                tb_show('Insert Bullets', 'admin.php?page=wpse-bullet&noheader=1TB_iframe=1&width=960');
            }
        });
    })
})();