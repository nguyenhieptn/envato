jQuery(function($) {
    "use strict";

    var _attr_fields = $('#em-select');
    var _attr_type = $('#em-attributes-type');

    /** default. */
    $(window).load(function(){

        load_setting(_attr_type.val());

        var _attr_fields_input = _attr_fields.find('.em-select-values');

        var _attr_fields_count = _attr_fields_input.length - 1;

        _attr_fields_input.each(function(_index){
            if(_index < _attr_fields_count)
                $(this).find('button').removeClass('em-select-value-add').addClass('em-select-value-remove').html('Remove');
        });
    });

    /** add new */
    _attr_fields.on('click', '.em-select-value-add', function(){
        var _new_html = $(this).parent().clone();

        $(this).removeClass('em-select-value-add').addClass('em-select-value-remove').html('Remove');

        _new_html.find('input').val('');

        _attr_fields.append(_new_html);

        render_indexs();
    });

    /** remove */
    _attr_fields.on('click', '.em-select-value-remove', function(){
        $(this).parent().remove();
    });

    function render_indexs(){
        _attr_fields.find('.em-select-values').each(function(_index){

            var _title = $(this).find('.em-select-title');
            var _value = $(this).find('.em-select-value');

            if(_index >= 1) {
                _title.attr('name', _title.attr('name').replace(_index - 1, _index));
                _value.attr('name', _value.attr('name').replace(_index - 1, _index));
            }
        });
    }

    /** select type. */
    $('#em-attributes-type').on('click', function(){
        load_setting($(this).val());
    });

    /** show or hide options. */
    function load_setting(_type){
        if(_type == 'multiple' || _type == 'select') {
            $('.term-em-attributes-wrap').attr('style', '');
        } else {
            $('.term-em-attributes-wrap').css('display', 'none');
        }
    }

});