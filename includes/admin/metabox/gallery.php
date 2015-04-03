<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly


class SR_Metabox_Gallery
{

    /**
     * Output the metabox
     */
    public static function output($post)
    {
        ?>
        <div id="product_images_container">
            <ul class="product_images">
                <?php
                    if (metadata_exists('post', $post->ID, '_product_image_gallery')) {
                        $product_image_gallery = get_post_meta($post->ID, '_product_image_gallery', true);
                    } else {
                        // Backwards compat
                        $attachment_ids = get_posts('post_parent='.$post->ID.'&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids');
                        $attachment_ids = array_diff($attachment_ids, array( get_post_thumbnail_id() ));
                        $product_image_gallery = implode(',', $attachment_ids);
                    }

        $attachments = array_filter(explode(',', $product_image_gallery));

        if ($attachments) {
            foreach ($attachments as $attachment_id) {
                echo '<li class="image" data-attachment_id="'.esc_attr($attachment_id).'">
                                '.wp_get_attachment_image($attachment_id, 'thumbnail').'
                                <ul class="actions">
                                    <li><a href="#" class="delete tips" data-tip="'.'Verwijder afbeelding'.'">'.'Verwijderen'.'</a></li>
                                </ul>
                            </li>';
            }
        }
        ?>
            </ul>

            <input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php echo esc_attr($product_image_gallery);
        ?>" />

        </div>
        <p class="add_product_images hide-if-no-js">
            <a href="#" data-choose="Kies afbeeldingen" data-text="">Voeg afbeelding toe</a>
        </p>
        <script>
        jQuery(document).ready(function($) {
        // Product gallery file uploads
        var product_gallery_frame;
        var $image_gallery_ids = $('#product_image_gallery');
        var $product_images = $('#product_images_container ul.product_images');

        jQuery('.add_product_images').on( 'click', 'a', function( event ) {
            var $el = $(this);
            var attachment_ids = $image_gallery_ids.val();

            event.preventDefault();

            // If the media frame already exists, reopen it.
            if ( product_gallery_frame ) {
                product_gallery_frame.open();
                return;
            }

            // Create the media frame.
            product_gallery_frame = wp.media.frames.product_gallery = wp.media({
                // Set the title of the modal.
                title: $el.data('choose'),
                button: {
                    text: $el.data('update'),
                },
                states : [
                    new wp.media.controller.Library({
                        title: $el.data('choose'),
                        filterable :    'all',
                        multiple: true,
                    })
                ]
            });

            // When an image is selected, run a callback.
            product_gallery_frame.on( 'select', function() {

                var selection = product_gallery_frame.state().get('selection');

                selection.map( function( attachment ) {

                    attachment = attachment.toJSON();

                    if ( attachment.id ) {
                        attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

                        $product_images.append('\
                            <li class="image" data-attachment_id="' + attachment.id + '">\
                                <img src="' + attachment.url + '" width="150" height="150" />\
                                <ul class="actions">\
                                    <li><a href="#" class="delete" title="' + $el.data('delete') + '">' + $el.data('text') + '</a></li>\
                                </ul>\
                            </li>');
                        }

                    });

                    $image_gallery_ids.val( attachment_ids );
                });

                // Finally, open the modal.
                product_gallery_frame.open();
            });

            // Image ordering
            $product_images.sortable({
                items: 'li.image',
                cursor: 'move',
                scrollSensitivity:40,
                forcePlaceholderSize: true,
                forceHelperSize: false,
                helper: 'clone',
                opacity: 0.65,
                placeholder: 'wc-metabox-sortable-placeholder',
                start:function(event,ui){
                    ui.item.css('background-color','#f6f6f6');
                },
                stop:function(event,ui){
                    ui.item.removeAttr('style');
                },
                update: function(event, ui) {
                    var attachment_ids = '';

                    $('#product_images_container ul li.image').css('cursor','default').each(function() {
                        var attachment_id = jQuery(this).attr( 'data-attachment_id' );
                        attachment_ids = attachment_ids + attachment_id + ',';
                    });

                    $image_gallery_ids.val( attachment_ids );
                }
            });

            // Remove images
            $('#product_images_container').on( 'click', 'a.delete', function() {
                $(this).closest('li.image').remove();

                var attachment_ids = '';

                $('#product_images_container ul li.image').css('cursor','default').each(function() {
                    var attachment_id = jQuery(this).attr( 'data-attachment_id' );
                    attachment_ids = attachment_ids + attachment_id + ',';
                });

                $image_gallery_ids.val( attachment_ids );

                runTipTip();

                return false;
            });


        });
        </script>
        <?php

    }
}
