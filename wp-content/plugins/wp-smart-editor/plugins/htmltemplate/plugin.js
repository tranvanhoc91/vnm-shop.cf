// JavaScript Document
(function() {
    tinymce.PluginManager.add('htmltemplate', function (ed, url) {
        ed.addButton('htmltemplate', {
            title : 'Template Manager',
            icon: 'myicon dashicons-media-document',
            onclick: function() {
                tb_show('Template Manager', 'admin.php?page=wpse-htmltemplate&noheader=1TB_iframe=1&width=960');
            }
        });
    });
})();