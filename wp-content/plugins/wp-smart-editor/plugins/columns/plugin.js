// JavaScript Document
(function() {
    tinymce.PluginManager.add('columns', function (ed, url) {
        ed.addButton('columns', {
            title : 'Columns Manager',
            icon: 'myicon dashicons-editor-table',
            onclick: function() {
                tb_show('Insert Columns', 'admin.php?page=wpse-columns&noheader=1TB_iframe=1&width=960');
            }
        });
    });
})();