(function ($) {
    $(document).ready(function () {
        if (edt.modes === 'js') {
            edt.modes = 'javascript';
        }
        if (edt.modes === 'html') {
            edt.modes = 'xml';
        }
        var themeArea = document.getElementById('newcontent');
        height = themeArea.offsetHeight;
        template = document.getElementById('template');
        wrapper = document.createElement('div');

        themeEditor = CodeMirror.fromTextArea(themeArea, {
            mode: edt.modes,
            lineNumbers: true,
            extraKeys: {"Ctrl-Space": "autocomplete"}
        });

        wrapper.className = 'wpse-theme-editor';
        wrapper.appendChild(themeEditor.getWrapperElement());
        template.parentNode.insertBefore(wrapper, template);

        themeEditor.setSize(null, height);
        themeEditor.refresh();
    });
})(jQuery);