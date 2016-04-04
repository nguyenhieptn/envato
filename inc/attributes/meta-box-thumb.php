<?php
if (!defined('ABSPATH')) {
    exit();
}

$_thumbnail_url = '';

$_thumbnail_id = get_post_meta($post->ID, '_em_thumbnail', true);

if($_thumbnail_id)
    $_thumbnail_url = wp_get_attachment_image_url($_thumbnail_id, 'large');

?>
<script>

    var file_frame;

    jQuery( window ).on( 'load', function() {
        if (!jQuery('#_em_thumbnail').val()) {
            jQuery('#set-post-thumb').css('display', 'block');
            jQuery('#remove-post-thumb').css('display', 'none');
        } else {
            jQuery('#set-post-thumb').css('display', 'none');
            jQuery('#remove-post-thumb').css('display', 'block');
        }
    });

    jQuery( document ).on( 'click', '#set-post-thumb', function( event ) {

        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.downloadable_file = wp.media({
            title: '<?php _e( "Choose an image", "envato-market" ); ?>',
            button: {
                text: '<?php _e( "Use image", "envato-market" ); ?>'
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            var attachment = file_frame.state().get( 'selection' ).first().toJSON();

            jQuery( '#_em_thumbnail' ).val( attachment.id );
            jQuery( '#_em_thumbnail_img' ).attr( 'src', attachment.url );
            jQuery( '#set-post-thumb').css('display','none');
            jQuery( '#remove-post-thumb').css('display','block');
        });

        // Finally, open the modal.
        file_frame.open();
    });

    jQuery( document ).on( 'click', '#remove-post-thumb', function() {
        jQuery( '#_em_thumbnail_img' ).attr( 'src', '' );
        jQuery( '#_em_thumbnail' ).val( '' );
        jQuery( '#set-post-thumb').css('display','block');
        jQuery( '#remove-post-thumb').css('display','none');
    });
</script>
<div style="text-align: center"><img id="_em_thumbnail_img" width="80px" height="80px" src="<?php echo esc_url($_thumbnail_url); ?>"></div>
<p class="hide-if-no-js">
    <a href="javascript:void(0)" id="set-post-thumb"><?php esc_html_e('Set featured image', 'envato-market'); ?></a>
</p>
<p class="hide-if-no-js">
    <a href="javascript:void(0)" id="remove-post-thumb" style="display: none"><?php esc_html_e('Remove thumbnail image', 'envato-market'); ?></a>
</p>
<input id="_em_thumbnail" type="hidden" name="_em_thumbnail" value="<?php echo $_thumbnail_id; ?>">
