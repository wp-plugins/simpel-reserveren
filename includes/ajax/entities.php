<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SR_Ajax_Entities {
    private $sr;

    public function __construct($sr) {
        $this->sr = $sr;

        add_action( 'wp_ajax_sr_entities', array($this, 'get_entities_json' ) );
        add_action( 'wp_ajax_nopriv_sr_entities', array($this, 'get_entities_json' ) );
    }

    public function get_entities_json() {

        $q = SR_PostType::query(DBK_SR::$entity_post_type, array('posts_per_page' => -1));

        $data = array();
        while($q->have_posts()) { $q->the_post();
            $entity_id = get_post_meta(get_the_id(), '_entity_id', true);
            $thumb = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_id()), 'sr-thumb' );

            $data[] = array(
                'id' => get_the_id(),
                'entity_id' => $entity_id,
                'permalink' => get_permalink(),
                'title' => get_the_title(),
                'excerpt' => get_the_excerpt(),
                'thumbnail' => $thumb[0],
                'gallery' => SR_PostType_Entity::get_gallery_images(get_the_id())
            );
        }

        ob_clean();
        echo json_encode($data);
        die();
    }
}

new SR_Ajax_Entities($this);