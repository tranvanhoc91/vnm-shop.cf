(function () {
    tinyMCE.PluginManager.add('wpsebutton', function (editor, url) {
        editor.addButton('wpsebutton', {
            text: 'Button',
            title: 'Add Button',
            classes: 'mytext',
            onclick: function () {
                tb_show('Insert Button', 'admin.php?page=wpse-button&noheader=1TB_iframe=1&width=960');
            }
        });

        // Do some dirty jobs ^^
        editor.on('BeforeSetContent', function (event) {
            if (event.content.indexOf('title="&lt;style&gt;"') !== -1) {
                event.content = event.content.replace(/<img*[\s\S]*?(\/>|>)/g, function (match, tag) {
                    text = match.match(/data-wp-preserve="*[\s\S]*?\"/g);
                    check = match.match(/title="&lt;style&gt;"/g);
                    if (text && check) {
                        text = text[0];
                        text = text.replace(/data-wp-preserve="/g, '');
                        text = text.replace(/"/g, '');
                        text = decodeURIComponent(text);

                        return text;
                    } else {
                        return match;
                    }
                });
            }
        })
    })
})();