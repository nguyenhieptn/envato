<?php
if (! defined('ABSPATH')) {
    exit();
}

$_attributes = get_terms("em-{$post->post_type}-atts", array('hide_empty'=>false));
?>
<div class="em-attributes">
    <?php if(!isset($_attributes->errors)): ?>

    <table>
        <tbody>

        <?php foreach($_attributes as $_attr): ?>

            <?php $_attr_parent = $this->get_parent_taxonomy($_attr->parent, "em-{$post->post_type}-atts"); ?>

            <?php $_attr_name  = '_em_' . ($_attr_parent ? $_attr_parent . "[{$_attr->slug}]" : $_attr->slug); ?>

            <?php $_attr_value = $this->get_meta_value($post->ID, $_attr_parent, $_attr->slug); ?>

            <?php $_attr_type = get_term_meta($_attr->term_id, 'attributes-type', true); ?>

            <?php if($_attr_type != 'attributes' && $_attr_type != 'object'): ?>

            <tr>
                <th><?php echo esc_html($_attr->name); ?> : </th>
                <td>
                    <?php if ($_attr_type == 'select'): ?>

                        <select name="<?php echo esc_attr($_attr_name); ?>"<?php if($_attr_type == 'multiple'){ echo ' multiple="multiple"';} ?>>

                            <?php $_options = get_term_meta($_attr->term_id, 'attributes-values', true); ?>

                            <?php foreach($_options as $_opt): ?>

                                <option value="<?php echo esc_html($_opt['value']); ?>"<?php if($_attr_value == $_opt['value']){ echo ' selected="selected"';}?>><?php echo esc_html($_opt['title']); ?></option>

                            <?php endforeach; ?>

                        </select>

                    <?php elseif($_attr_type == 'multiple'): ?>

                        <select name="<?php echo esc_attr($_attr_name); ?>[]" multiple="multiple">

                            <?php $_options = get_term_meta($_attr->term_id, 'attributes-values', true);?>

                            <?php foreach($_options as $_opt): ?>

                                <option value="<?php echo esc_html($_opt['value']); ?>"<?php if(in_array($_opt['value'], (array)$_attr_value)){ echo ' selected="selected"';}?>><?php echo esc_html($_opt['title']); ?></option>

                            <?php endforeach; ?>

                        </select>

                    <?php elseif($_attr_type == 'text' || $_attr_type == 'number'): ?>

                        <input type="<?php echo esc_html($_attr_type); ?>" name="<?php echo esc_attr($_attr_name); ?>" value="<?php echo esc_attr($_attr_value); ?>">

                    <?php endif; ?>
                </td>
            </tr>

            <?php endif; ?>

        <?php endforeach; ?>

        </tbody>
    </table>

    <?php endif; ?>

</div>
