<?php
if (! defined('ABSPATH')) {
    exit();
}

$_attr_type = ''; $_attr_values = array();

if(isset($term)):
    $_attr_type = get_term_meta($term->term_id, 'attributes-type', true);
    $_attr_values = get_term_meta($term->term_id, 'attributes-values', true);
endif;

$_types = apply_filters('attribute-types',array(
    'select'        => esc_html__('Select', 'envato-market'),
    'text'          => esc_html__('Text', 'envato-market'),
    'number'        => esc_html__('Number', 'envato-market'),
    'multiple'      => esc_html__('Multiple', 'envato-market'),
    'attributes'    => esc_html__('Attributes', 'envato-market'),
    'object'        => esc_html__('Object', 'envato-market'),
));

/* if is_edit. */
$_attr_values[] = array('title'=>'','value'=>'');

?>
<div class="form-field term-em-type-wrap">
    <label for="em-attributes-type"><?php esc_html_e('Type', 'envato-market'); ?></label>
    <select id="em-attributes-type" name="attributes-type">
        <?php foreach($_types as $key => $_type): ?>
        <option value="<?php echo esc_attr($key); ?>"<?php if($_attr_type == $key){ echo ' selected="selected"';}?>><?php echo esc_html($_type); ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="form-field term-em-attributes-wrap">
    <label><?php esc_html_e('Values', 'envato-market'); ?></label>
    <div class="term-em-attributes">
        <div id="em-select">
            <?php foreach($_attr_values as $key => $_attr_value): ?>
            <div class="em-select-values">
                <input class="em-select-title" type="text" name="attributes-values[<?php echo esc_attr($key); ?>][title]" value="<?php echo esc_html($_attr_value['title']); ?>" placeholder="<?php esc_html_e('title', 'envato-market'); ?>">
                <input class="em-select-value" type="text" name="attributes-values[<?php echo esc_attr($key); ?>][value]"  value="<?php echo esc_html($_attr_value['value']); ?>" placeholder="<?php esc_html_e('value', 'envato-market'); ?>">
                <button type="button" class="em-select-value-add button button-primary"><?php esc_html_e('Add', 'envato-market'); ?></button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <p class="description"><?php esc_html_e('add options for select or multiple attribute.', 'envato-market'); ?></p>
</div>