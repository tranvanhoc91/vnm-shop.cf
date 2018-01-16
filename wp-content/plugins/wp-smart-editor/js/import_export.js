jQuery(document).ready(function ($) {
    $('.wrap .page-title-action').after('<button class="im_ex_wpse_settings" type="button">Import/Export Profiles</button>')
        .after('<style>.im_ex_wpse_settings{\n' +
            '   border: 1px solid #ccc;\n' +
            '   padding: 4px 8px;\n' +
            '   margin-left: 5px;\n' +
            '   position: relative;\n' +
            '   top: -3px;\n' +
            '   color: #0073aa;\n' +
            '   background: #f7f7f7;\n' +
            '   font-weight: 600;\n' +
            '   cursor: pointer;\n}' +
            '.im_ex_wpse_settings:hover{' +
            '   background: #008EC2;' +
            '   border-color: #008EC2;' +
            '   color: #fff}' +
            '</style>');
    $('.im_ex_wpse_settings').unbind('click').click(function () {
        tb_show('Import/Export', 'admin.php?page=wpse-import-export&view=profiles&noheader=1TB_iframe=1&width=550&height=400');
    });

    $('#profile-check-all').unbind('click').click(function () {
        $('input[name="profile-id[]"]').prop('checked', this.checked);
    });

    $('input[name="profile-id[]"]').unbind('click').click(function () {
        if (!this.checked) {
            $('#profile-check-all').prop('checked', false);
        }
    });
});