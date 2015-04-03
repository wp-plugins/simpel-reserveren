<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class SR_PostType_Entity
{

    public $sr;
    public $slug;
    public $plural_name;
    public $singular_name;

    public function __construct($sr)
    {
        $this->sr = $sr;

        $slug = $this->sr->options->get_option('entity_slug');
        $plural = $this->sr->options->get_option('entity_title');
        $name = $this->sr->options->get_option('entity_title_singular');

        $labels = array(
            'name' => $plural,
            'singular_name' => $name,
            'add_new' => 'Nieuwe ', strtolower($name),
            'add_new_item' => 'Nieuw '.$name ,
            'edit_item' => 'Edit '.$name,
            'new_item' => 'Nieuwe '.$name,
            'all_items' => 'Alle '.$plural,
            'view_item' => 'Bekijk '.$name,
            'search_items' => 'Zoek '.$plural,
            'not_found' => 'Geen '.strtolower($plural).' gevonden',
            'not_found_in_trash' => 'Geen '.strtolower($plural).' gevonden in prullenbak',
            'parent_item_colon' => '',
            'menu_name' => $plural,
        );

        SR_PostType::register(DBK_SR::$entity_post_type, array(
          'rewrite' => array('slug' => $slug),
          'hierarchical' => false,
          'exclude_from_search' => true,
          'publicly_queryable' => true,
        ), $labels);

        if (is_admin()) {
            add_action('add_meta_boxes', array( $this, 'add_metaboxes'));
            add_action('save_post', array( $this, 'save' ));
        }
    }

    public function add_metaboxes()
    {
        add_meta_box('dbk-sr-gallery', 'Gallerij', array('SR_Metabox_Gallery', 'output'), DBK_SR::$entity_post_type, 'side');
        add_meta_box('dbk-sr-enity', 'Eigenschappen', array('SR_Metabox_Entity', 'output'), DBK_SR::$entity_post_type, 'normal');
    }

    public function save($post_id)
    {
        $attachment_ids = array_filter(explode(',', sanitize_text_field(filter_input(INPUT_POST, 'product_image_gallery'))));

        update_post_meta($post_id, '_product_image_gallery', implode(',', $attachment_ids));

        $stars = sanitize_text_field(filter_input(INPUT_POST, 'sr-entity-stars'));
        $quote = sanitize_text_field(filter_input(INPUT_POST, 'sr-entity-quote'));
        $video = filter_input(INPUT_POST, 'sr-entity-video');

        update_post_meta($post_id, '_sr_stars', $stars);
        update_post_meta($post_id, '_sr_quote', $quote);
        update_post_meta($post_id, '_sr_video', $video);
    }
    public static function get_gallery_images($post_id, $length = false)
    {
        if (metadata_exists('post', $post_id, '_product_image_gallery')) {
            $product_image_gallery = get_post_meta($post_id, '_product_image_gallery', true);
        } else {
            // Backwards compat
            $attachment_ids = get_posts('post_parent='.$post_id.'&numberposts='.$length.'&post_type=attachment&orderby=menu_order&order=ASC&post_mime_type=image&fields=ids');
            $attachment_ids = array_diff($attachment_ids, array( get_post_thumbnail_id() ));
            $product_image_gallery = implode(',', $attachment_ids);
        }
        $attachments = array_filter(explode(',', $product_image_gallery));
        $i = 0;
        $images = array();
        foreach ($attachments as $attachment_id) {
            $large = wp_get_attachment_image_src($attachment_id, 'large');
            $thumb = wp_get_attachment_image_src($attachment_id, 'sr-thumb');
            $small = wp_get_attachment_image_src($attachment_id, 'sr-small');
            $medium = wp_get_attachment_image_src($attachment_id, 'medium');
            $images[] = array(
                'thumb'     => str_replace('http://', '//', $thumb[0]),
                'small'     => str_replace('http://', '//', $small[0]),
                'large'     => str_replace('http://', '//', $large[0]),
                'medium'    => str_replace('http://', '//', $medium[0])
            );
        }
        if ($length) {
            $images = array_slice($images, 0, $length);
        }


        return $images;
    }

    public static function get_image($post_id)
    {
        $large = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'large' );
        $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'sr-thumb' );
        $small = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'sr-small' );
        return array('large' => $large[0], 'thumb' => $thumb[0], 'small' => $small[0]);
    }

    public static function get_excerpt($charlength = 300, $excerpt = null)
    {
        if(empty($excerpt)) {
            $excerpt = get_the_excerpt();
        }
        if (empty($excerpt)) {
            $excerpt = get_the_content();
        }
        $excerpt = preg_replace('/<a.*>.*<\/a>/is', '', $excerpt);
        $excerpt = strip_tags($excerpt);
        $charlength++;

        if (mb_strlen($excerpt) > $charlength) {
            $subex = mb_substr($excerpt, 0, $charlength - 5);
            $exwords = explode(' ', $subex);
            $excut = - (mb_strlen($exwords[ count($exwords) - 1 ]));
            if ($excut < 0) {
                $excerpt = mb_substr($subex, 0, $excut);
            } else {
                $excerpt = $subex;
            }
            $excerpt  = trim($excerpt).'...';
        }

        return $excerpt;
    }

    public static function get_stars()
    {
        $stars = (int) get_post_meta(get_the_id(), '_sr_stars', true);

        $starArr = array();
        for ($i = 0; $i<$stars; $i++) {
            array_push($starArr, $i);
        }

        return $starArr;
    }
    public static function get_video()
    {
        return get_post_meta(get_the_id(), '_sr_video', true);
    }

    public static function get_quote()
    {
        return get_post_meta(get_the_id(), '_sr_quote', true);
    }

    public static function query($args = array())
    {
        return SR_PostType::query(DBK_SR::$entity_post_type, $args);
    }

    public static function get_alternatives()
    {
        $alternatives = array();
        if (have_rows('entities')) {
            while (have_rows('entities')) {
                the_row();
                $alternatives[] = get_sub_field('entity');

                if (count($alternatives) >= 4) break;
            }
        } else {
            // get random posts sr_entity
            $args = array(
                'post_type' => DBK_SR::$entity_post_type,
                'posts_per_page' => 4,
                'orderby' => 'rand',
            );
            $alternatives = get_posts($args);
        }

        return $alternatives;
    }
}
new SR_PostType_Entity($this);
