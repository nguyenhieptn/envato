jQuery(function($) {
    "use strict";

    var _em_actions = $('.em-actions');

    /* click get new items. */
    $('.tablenav').on('click', '.em-get-new-items', function(){

        var _category = $(this).parent().find('select.em-category').val();

        if(_category == '' || _category == undefined){
            em_console_log('error : category null', 'error');
        } else {
            em_console_log('check new items from category : ' + _category);
            em_get_new_items(_category);
        }
    });

    /* click get items. */
    $('.tablenav').on('click', '.em-get-items', function(){

        var _page = $(this).parent().find('input.em-page').val();
        var _items = $(this).parent().find('input.em-items').val();
        var _category = $(this).parent().find('select.em-category').val();

        if(_page == '' || _page == undefined){
            em_console_log('error : page null', 'error');
            return;
        }

        if(_items == '' || _items == undefined){
            em_console_log('error : items null', 'error');
            return;
        }

        if((parseInt(_page) < 1 || parseInt(_page) > 60) || parseInt(_items) < 1 ){
            em_console_log('error : number page and items >= 1, page <= 60', 'error');
            return;
        }

        em_console_log('get ' + _items + ' items from page ' + _page + ' and category is ' + _category);

        em_get_items(_page, _items, _category);
    });

    /* click get item. */
    $('.tablenav').on('click', '.em-get-item', function(){

        var _item_id = $(this).parent().find('input.em-item-id').val();

        if(_item_id == '' || _item_id == undefined){
            em_console_log('error : id null', 'error');
        } else {
            em_console_log('get item id : ' + _item_id);
            em_get_item(_item_id);
        }
    });

    /* click show console log */
    $('.em-console-header').on('click', function () {
        if($('.em-console-content').hasClass('active')){
            $('.em-console-content').removeClass('active');
            $(this).find('span.right').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
        } else {
            $('.em-console-content').addClass('active');
            $(this).find('span.right').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
        }
    });

    function em_get_new_items(category) {

        em_loading();

        $.post(ajaxurl, {
            'action': 'em_get_new_items',
            'category': category,
            'type' : typenow
        }, function (response) {
            em_loaded();
            if(response.error != undefined){
                em_console_log('error : ' + response.error_description, 'error');
            } else if(response.length == 0) {
                em_console_log('0 new items');
            } else {
                em_console_log(response.length + ' new items');
            }
        });
    }

    function em_get_items(page, items, category) {

        em_loading();

        $.post(ajaxurl, {
            'action': 'em_get_items',
            'page' : page,
            'items' : items,
            'category' : category,
            'type' : typenow
        }, function (response) {
            em_loaded();
            if(response.error != undefined){
                em_console_log('error : ' + (response.error_description != undefined ? response.error_description : response.description), 'error');
            } else {
                $.each(response, function (_index, _val) {
                    if(_val.error != undefined){
                        em_console_log(_val.error_description != undefined ? _val.error_description : _val.description, 'error');
                    } else {
                        em_console_log('completed : ' + _val.title);
                    };
                });
            };
        });
    }

    function em_get_item(item_id){
        em_loading();

        $.post(ajaxurl, {
            'action': 'em_get_item',
            'item_id' : item_id,
            'type' : typenow
        }, function (response) {
            em_loaded();
            if(response.error != undefined){
                em_console_log('error : ' + (response.description != undefined ? response.description : response.error_description), 'error');
            } else {
                em_console_log('completed : ' + response.title);
            };
        });
    }

    /**
     * add to console log.
     * @param log
     */
    function em_console_log(log, type) {

        var _log = log;

        if(!$('.em-console-content').hasClass('active')){
            $('.em-console-content').addClass('active');
        }

        if(type == 'error'){
            _log = '<span style="color: red;">' + log + '</span>';
        }

        $('.em-console-content > div').append(' - ' + _log + '</br>');
        $('.em-console-header #em-console-title').html(log);
    }

    function em_loading(){
        _em_actions.find('input,select').attr('disabled','disabled');
        _em_actions.find('span.spinner').css('display', 'block');
    }

    function em_loaded(){
        _em_actions.find('input,select').removeAttr('disabled');
        _em_actions.find('span.spinner').css('display', 'none');
    }
});