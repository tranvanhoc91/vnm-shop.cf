jQuery(document).ready(function ($) {
    // Show tips
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

    $('.submit-profiles').click(function(e) {
        e.preventDefault();
        $('#publish').click();
    });

    // Take order of the toolbars
    $('#current-toolbars .toolbar-rows').each(function () {
        $(this).attr('id', 'toolbar'+($(this).index()+1));
    });
    $('#unused-toolbars .toolbar-rows').each(function () {
        $(this).attr('id', 'unused_toolbar'+($(this).index()+1));
    });

    $('.toolbar-rows').sortable({
        connectWith: '.toolbar-rows',
        placeholder: 'btns-holder',
        distance: 10,
        cursor: 'move',
        cursorAt: {
            right: 30,
            bottom: 30
        },
        start: function () {

        },
        stop: function (e, ui) {
            // Assign data
            buttons_data();
        }
    });

    // Assign value by default
    function buttons_data() {
        // Get list of all buttons
        toolbar1_btns = $('#toolbar1').sortable('toArray').length ? $('#toolbar1').sortable('toArray').toString() : '';
        toolbar2_btns = $('#toolbar2').sortable('toArray').length ? $('#toolbar2').sortable('toArray').toString() : '';
        toolbar3_btns = $('#toolbar3').sortable('toArray').length ? $('#toolbar3').sortable('toArray').toString() : '';
        toolbar4_btns = $('#toolbar4').sortable('toArray').length ? $('#toolbar4').sortable('toArray').toString() : '';
        unused_toolbar1_btns = $('#unused_toolbar1').sortable('toArray').length ? $('#unused_toolbar1').sortable('toArray').toString() : '';
        unused_toolbar2_btns = $('#unused_toolbar2').sortable('toArray').length ? $('#unused_toolbar2').sortable('toArray').toString() : '';
        unused_toolbar3_btns = $('#unused_toolbar3').sortable('toArray').length ? $('#unused_toolbar3').sortable('toArray').toString() : '';

        // Join them into 1 large string
        all_toolbars_btns = '*toolbar1:'+toolbar1_btns+'*toolbar2:'+toolbar2_btns+'*toolbar3:'+toolbar3_btns+'*toolbar4:'+toolbar4_btns+'*unused_toolbar1:'+unused_toolbar1_btns+'*unused_toolbar2:'+unused_toolbar2_btns+'*unused_toolbar3:'+unused_toolbar3_btns;

        // Add this string to submit
        $('.get_list_buttons').val(all_toolbars_btns);
    }
    buttons_data();

    // Sortable toolbars row
    $('.toolbar-blocks').sortable({
        connectWith: '.toolbar-blocks',
        placeholder: 'toolbars-holder',
        distance: 20,
        cursor: 'move',
        start: function () {

        },
        stop: function (e, ui) {
            //Reorder the toolbars if they have changed
            $('#current-toolbars .toolbar-rows').each(function () {
                $(this).attr('id', 'toolbar'+($(this).index()+1));
                var check = $(this).find('div#unused').length;
                if (check) {
                    $(this).find('#unused').remove();
                }
            });
            $('#unused-toolbars .toolbar-rows').each(function () {
                $(this).attr('id', 'unused_toolbar'+($(this).index()+1));
                var check = $(this).find('div#unused').length;
                if (!check) {
                    $(this).append("<div id='unused' class='no-display'></div>")
                }
            });

            // Assign data
            buttons_data();
        }
    });

    // Ajax for displaying users list
    $('#user-search-input').bind('searchUsers', function (e) {
        var searchKey = $(this).val();
        var roleKey = $('#wpse-roles-filter').val();
        var post_id = $('#post_ID').val();
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'wpse_get_users',
                search: searchKey,
                role: roleKey
            },
            success: function (res) {
                $('#wpse-users-body').html(res.users_list);
                $('#pagination').html(res.pages_list);
                selectedUsers();
                switchPage();
            }
        })
    });
    $('#user-search-input').on('keypress keyup', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).trigger('searchUsers');
        }
    });

    $('#wpse-roles-filter').change(function (e) {
        var roleKey = $(this).val();
        var searchKey = $('#user-search-input').val();
        var post_id = $('#post_ID').val();
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'wpse_get_users',
                search: searchKey,
                role: roleKey
            },
            success: function (res) {
                $('#wpse-users-body').html(res.users_list);
                $('#pagination').html(res.pages_list);
                selectedUsers();
                switchPage();
            }
        })
    });

    $('#wpse-clear-btn').click(function () {
        $('#user-search-input').val('');
        $('#wpse-roles-filter').val('');
        var post_id = $('#post_ID').val();
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'wpse_get_users'
            },
            success: function (res) {
                $('#wpse-users-body').html(res.users_list);
                $('#pagination').html(res.pages_list);
                selectedUsers();
                switchPage();
            }
        })
    });
    
    // Check all buttons
    $('#wpse-users-checkall').click(function () {
        $('#wpse-users-body').find(':checkbox').attr('checked', this.checked);
    });

    // Switch page
    function switchPage() {
        $('.switch-page').unbind('click').click(function () {
            var paged = $(this).text();
            paged = parseInt(paged);
            getPagination(paged);
        });
        $('#pagination a#first-page').unbind('click').click(function () {
            var paged = 'first';
            getPagination(paged);
        });
        $('#pagination a#last-page').unbind('click').click(function () {
            var paged = 'last';
            getPagination(paged);
        });
    }
    switchPage();

    // Ajax for pagination
    function getPagination(page_num) {
        var post_id = $('#post_ID').val();
        var searchKey = $('#user-search-input').val();
        var roleKey = $('#wpse-roles-filter').val();
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'wpse_get_users',
                search: searchKey,
                role: roleKey,
                paged: page_num
            },
            success: function (res) {
                $('#wpse-users-body').html(res.users_list);
                $('#pagination').html(res.pages_list);
                selectedUsers();
                switchPage();
            }
        })
    }

    // Function for selecting users
    function selectedUsers() {
        $('#wpse-users-body :checkbox').change(function () {
            if (this.checked) {
                // Action when checked
                var val = $(this).val();
                $("#wpse-users-access-list").val($("#wpse-users-access-list").val() + " " + val);
            } else {
                // Action  when unchecked
                var vals = $(this).val();
                var split_val = $('#wpse-users-access-list').val().split(' ');
                split_val.splice($.inArray(vals, split_val),1);
                var final_val = split_val.join(' ');
                $('#wpse-users-access-list').val(final_val);
            }
        });

        var split_vals = $('#wpse-users-access-list').val().split(' ');
        $('#wpse-users-body :checkbox').each(function (e) {
            var val = $(this).val();
            var checked = $.inArray(val, split_vals);
            //Check if users is checked
            if (checked !== -1) {
                $(this).attr('checked', 'checked');
            }
        })
    }
    selectedUsers();
});