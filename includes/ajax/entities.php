<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class SR_Ajax_Entities
{
    private $sr;
    private $cache_file;

    public function __construct($sr)
    {
        $this->sr = $sr;
        $upload_dir = wp_upload_dir();
        $this->cache_file = $upload_dir['basedir'] . '/sr-cache/entities.json';

        add_action('wp_ajax_sr_entities', array($this, 'getEntitiesJson' ));
        add_action('wp_ajax_nopriv_sr_entities', array($this, 'getEntitiesJson' ));

        add_action('save_post', array($this, 'deleteCacheFile'));
    }

    public function getEntitiesJson($return = true)
    {
        if (file_exists($this->cache_file)) {
            echo file_get_contents($this->cache_file);
            die;
        }
        $q = SR_PostType::query(DBK_SR::$entity_post_type, array('posts_per_page' => -1));

        $data = array();
        while ($q->have_posts()) {
            $q->the_post();
            $entity_id = get_post_meta(get_the_id(), '_entity_id', true);
            $thumb = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_id()), 'sr-thumb');

            $data[] = array(
                'id' => get_the_id(),
                'entity_id' => $entity_id,
                'permalink' => get_permalink(get_the_id()),
                'title' => get_the_title(),
                'excerpt' => SR_PostType_Entity::get_excerpt(),
                'stars' => SR_PostType_Entity::get_stars(),
                'thumbnail' => str_replace('http://', '//', $thumb[0]),
                'images' => SR_PostType_Entity::get_gallery_images(get_the_id(), 3),
            );
        }

        ob_clean();
        $result = json_encode($data);

        if (!file_exists(dirname($this->cache_file))) {
            mkdir(dirname($this->cache_file));
        }
        file_put_contents($this->cache_file, $result);
        
        if ($return) {
            echo $result;
            die();
        }
    }

    public function deleteCacheFile($post_id)
    {
        $post = get_post($post_id);

        if ($post->post_type == 'sr_entity') {
            unlink($this->cache_file);
            $this->getEntitiesJson(false);
        }
    }
}

new SR_Ajax_Entities($this);
