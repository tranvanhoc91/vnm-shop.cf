/**
 * WP File Download
 *
 * @package WP File Download
 * @author Joomunited
 * @version 1.0
 */

jQuery(document).ready(function($) {
    var sourcefiles   = $("#wpfd-template-files").html();
    var sourcecategories   = $("#wpfd-template-categories").html();
    var default_hash = window.location.hash;
    var tree = $('.wpfd-foldertree-default');
    var tree_source_cat = $('.wpfd-content-default.wpfd-content-multi').data('category');
    var cParents = {};
    
    $(".wpfd-content-default").each(function(index ){
        var topCat = $(this).data('category');
        cParents[topCat] = {parent:0, term_id: topCat,name: $(this).find("h2").text()};
        $(this).find(".wpfdcategory.catlink").each(function(index ){
            var tempidCat = $(this).data('idcat');
            cParents[tempidCat]= {parent:topCat, term_id:tempidCat,name: $(this).text()};
        })
    })           

    Handlebars.registerHelper('bytesToSize', function(bytes) {
        return bytes == 'n/a' ? bytes : bytesToSize(parseInt(bytes));
    });

    function default_initClick(){

        $('.wpfd-content-default .catlink').click(function(e){
            default_load($(this).parents('.wpfd-content-default.wpfd-content-multi').data('category'), $(this).data('idcat'));
            return false;
        });
    }
    default_initClick();
    default_hash = default_hash.replace('#','');
    if (default_hash != '') {
        var hasha = default_hash.split('-');
        var re = new RegExp("^(p[0-9]+)$");
        var page = null;
        var stringpage = hasha.pop();

        if (re.test(stringpage)) {
            page = stringpage.replace('p','');
        }

        var hash_category_id = hasha[0];
        if(!parseInt(hash_category_id)){
            return;
        }
        setTimeout(function () {
            default_load($('.wpfd-content-default.wpfd-content-multi').data('category'), hash_category_id, page);
        },100)
    }


    function default_load(sourcecat,category, page){

        var pathname = window.location.pathname;

        $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]").find('#current_category').val(category);
        $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]").next('.wpfd-pagination').remove();
        $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]  .wpfd-container-default").empty();        
        $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]  .wpfd-container-default").html($('#wpfd-loading-wrap').html());
        //Get categories
        $.ajax({
            url: wpfdparams.ajaxurl+"?action=wpfd&task=categories.display&view=categories&id="+category+"&top="+sourcecat,
            dataType : "json"
        }).done(function(categories) {

            if (page !=null) {
                window.history.pushState('', document.title, pathname + '#'+category+'-'+categories.category.slug+'-p' + page );
            } else {
                window.history.pushState('', document.title, pathname + '#'+category+'-'+categories.category.slug );
            }

            $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]").find('#current_category_slug').val(categories.category.slug);

            var template = Handlebars.compile(sourcecategories);
            var html = template(categories);
            $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"] .wpfd-container-default").prepend(html);

            for(i=0;i< categories.categories.length;i++) {
                cParents[categories.categories[i].term_id]= categories.categories[i];
            }

            default_breadcrum(sourcecat,category);
            default_initClick();

            if (tree.length) {

                tree.find('li').removeClass('selected');
                tree.find('i.md').removeClass('md-folder-open').addClass("md-folder");

                tree.jaofiletree('open', category);

                var el = tree.find('a[data-file="'+category+'"]').parent();
                el.find(' > i.md').removeClass("md-folder").addClass("md-folder-open");

                if (!el.hasClass('selected') ) {
                    el.addClass('selected');
                }

            }

        });

        //Get files
        $.ajax({
            url: wpfdparams.ajaxurl+"?action=wpfd&task=files.display&view=files&id="+category+"&rootcat="+tree_source_cat+"&page=" + page,
            dataType : "json"
        }).done(function(content) {

            $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]").after(content.pagination);
            delete content.pagination;
            var template = Handlebars.compile(sourcefiles);
            var html = template(content);
            html = $('<textarea/>').html(html).val();
            $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"] .wpfd-container-default").append(html);

            if (typeof wpfdColorboxInit !='undefined') {
                wpfdColorboxInit();
            }

            wpfdTrackDownload();

            default_init_pagination($('.wpfd-pagination'));
            wpfd_remove_loading($(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]  .wpfd-container-default"));
        });

    }

    function default_breadcrum(sourcecat, catid) {
        var links = [];        
        current_Cat = cParents[catid];
        if(!current_Cat){
            return false;
        }
        links.unshift(current_Cat);
        if (current_Cat.parent !=  0) {
            while(cParents[current_Cat.parent]) {
                current_Cat = cParents[current_Cat.parent];
                links.unshift(current_Cat);
            };
        }

        html = '';
        for(i=0;i<links.length;i++) {
            if(i< links.length-1) {
                html += '<li><a class="catlink" data-idcat="'+links[i].term_id+'" href="javascript:void(0)">'+links[i].name+'</a><span class="divider"> &gt; </span></li>';
            }else {
                html += '<li><span>'+links[i].name+'</span></li>';
            }
        }
        $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]  .wpfd-breadcrumbs-default").html(html);
    }

    if (tree.length) {
        tree.each(function( index ) {
            var topCat = $(this).parents('.wpfd-content-default.wpfd-content-multi').data('category');         
            $(this).jaofiletree({
                script  : wpfdparams.ajaxurl+'?action=wpfd&task=categories.getSubs',
                usecheckboxes : false,
                root: topCat,
                showroot : cParents[topCat].name,
                onclick: function(elem,file){

                    if (topCat != file) {

                        $(elem).parents('.directory').each(function() {
                            var $this = $(this);
                            var category = $this.find(' > a');
                            var parent = $this.find('.icon-open-close');
                            if (parent.length > 0) {
                                if (typeof cParents[category.data('file')] =='undefined') {
                                    cParents[category.data('file')] = {parent: parent.data('parent_id'),term_id:category.data('file'),name: category.text()};
                                }
                            }
                        });

                    }

                    default_load(topCat,file);
                }
            });
        })       

    }

    $('.wpfd-pagination').each(function() {
        var $this  = $(this);
        default_init_pagination($this);
    })

    function default_init_pagination($this) {

        var number = $this.find('a:not(.current)');

        var wrap = $this.prev('.wpfd-content-default');

        var current_category = wrap.find('#current_category').val();
        var sourcecat = wrap.data('category');

        number.unbind('click').bind('click', function () {
            var page_number = $(this).attr('data-page');
            if (typeof page_number != 'undefined') {
                var pathname = window.location.pathname;
                var category = $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]").find('#current_category').val();
                var category_slug = $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]").find('#current_category_slug').val();

                window.history.pushState('', document.title, pathname + '#'+category+'-'+category_slug+'-p'+page_number );

                $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]  .wpfd-container-default div.file").remove();
                $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]  .wpfd-container-default").append($('#wpfd-loading-wrap').html());
                //Get files
                $.ajax({
                    url: wpfdparams.ajaxurl+"?action=wpfd&task=files.display&view=files&id="+current_category+"&page=" + page_number,
                    dataType : "json"
                }).done(function(content) {

                    delete content.category;
                    wrap.next('.wpfd-pagination').remove();
                    wrap.after(content.pagination);
                    delete content.pagination;
                    var template = Handlebars.compile(sourcefiles);
                    var html = template(content);

                    $(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"] .wpfd-container-default").append(html);


                    if (typeof wpfdColorboxInit != 'undefined') {
                        wpfdColorboxInit();
                    }
                    default_init_pagination($('.wpfd-pagination'));
                    wpfd_remove_loading($(".wpfd-content-default.wpfd-content-multi[data-category="+sourcecat+"]  .wpfd-container-default"));
                });
            }

        });

    }

});