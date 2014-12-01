<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SR_PostType_Entity {

    public $sr;
    public $slug;
    public $plural_name;
    public $singular_name;

    public function __construct($sr) {
        $this->sr = $sr;

        $slug = $this->sr->options->get_option('entity_slug');
        $plural = $this->sr->options->get_option('entity_title');
        $name = $this->sr->options->get_option('entity_title_singular');

        $labels = array(
            'name' => $plural,
            'singular_name' => $name,
            'add_new' => _x( 'Add New', strtolower( $name ) ),
            'add_new_item' => __( 'Add New ' . $name ),
            'edit_item' => __( 'Edit ' . $name ),
            'new_item' => __( 'New ' . $name ),
            'all_items' => __( 'All ' . $plural ),
            'view_item' => __( 'View ' . $name ),
            'search_items' => __( 'Search ' . $plural ),
            'not_found' => __( 'No ' . strtolower( $plural ) . ' found'),
            'not_found_in_trash' => __( 'No ' . strtolower( $plural ) . ' found in Trash'), 
            'parent_item_colon' => '',
            'menu_name' => $plural
        );

        SR_PostType::register(DBK_SR::$entity_post_type, array(
          'rewrite' => array('slug' => $slug),
          'hierarchical' => false,
          'exclude_from_search' => true,
          'publicly_queryable' => true
        ), $labels);

        if(is_admin()) {
            add_action('add_meta_boxes', array( $this, 'add_metaboxes'));
            add_action('save_post', array( $this, 'save' ) );
        }

    }

    public function add_metaboxes() {
        add_meta_box('dbk-sr-gallery', __('Gallery'), array('SR_Metabox_Gallery', 'output'), DBK_SR::$entity_post_type, 'side');
    }

    public function save() {

    }
    public static function get_gallery_images($post_id, $size='sr-thumb') {
        
        if ( metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
            $product_image_gallery = get_post_meta( $post_id, '_product_image_gallery', true );
        } else {
            // Backwards compat
            $attachment_ids = get_posts( 'post_parent=' . $post_id . '&numberposts=-1&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids' );
            $attachment_ids = array_diff( $attachment_ids, array( get_post_thumbnail_id() ) );
            $product_image_gallery = implode( ',', $attachment_ids );
        }

        $attachments = array_filter( explode( ',', $product_image_gallery ) );

        $images = array();
        foreach($attachments as $attachment_id) {
            $image = wp_get_attachment_image_src( $attachment_id, $size);
            $images[] = $image[0];
        }

        return $images;
    }
    
}
new SR_PostType_Entity($this);