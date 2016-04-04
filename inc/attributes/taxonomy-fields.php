<?php
if (! defined('ABSPATH')) {
    exit();
}

$_title_template = $_default_tag = '';

if(isset($term)) {
    $_title_template = get_term_meta($term->term_id, 'em-taxonomy-title-template', true);
    $_default_tag = get_term_meta($term->term_id, 'em-taxonomy-default-tag', true);
}
?>
<div class="form-field term-em-taxonomy-title-template-wrap" style="display: none;">
    <label for="em-taxonomy-title-template"><?php esc_html_e('Title Template', 'envato-market'); ?></label>
    <input id="em-taxonomy-title-template" name="em-taxonomy-title-template" type="text" value="<?php echo esc_attr($_title_template); ?>">
    <p class="description"><?php esc_html_e('Title template (Download | %s).', 'envato-market'); ?></p>
</div>
<div class="form-field term-em-taxonomy-default-tag-wrap" style="display: none;">
    <label for="em-taxonomy-default-tag"><?php esc_html_e('Default Tags', 'envato-market'); ?></label>
    <input id="em-taxonomy-default-tag" name="em-taxonomy-default-tag" type="text" value="<?php echo esc_attr($_default_tag); ?>">
    <p class="description"><?php esc_html_e('Auto add tags to items in category (tag a, tag b, tag c, ...).', 'envato-market'); ?></p>
</div>
<script>
    jQuery(window).on('load', function(){
        em_custom_fields(jQuery('#parent').val());
    });

    jQuery('#parent').on('change',function(){
        em_custom_fields(jQuery(this).val());
    });

    function em_custom_fields(_parent){
        if(_parent == '-1'){
            jQuery('.term-em-taxonomy-title-template-wrap, .term-em-taxonomy-default-tag-wrap').css('display','block');
        } else {
            jQuery('.term-em-taxonomy-title-template-wrap, .term-em-taxonomy-default-tag-wrap').css('display','none');
        }
    }
</script>
