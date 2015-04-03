<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class SR_Shortcode_Entities
{

    public function __construct($sr)
    {
        add_shortcode('sr-entities', array($this, 'shortcode_handler'));
    }

    public function shortcode_handler($atts, $content = null)
    {
        $a = shortcode_atts(array(
            'entities' => '',
        ), $atts);

        $q = SR_PostType::query(DBK_SR::$entity_post_type, array('posts_per_page' => -1));

        ob_start();
        ?>
        <div class="sr sr-entities-sc">
        <?php while ($q->have_posts()): $q->the_post();
        ?>
            <div class="sr-entity">
                <h3 class="sr-entity-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h3>
                <div class="sr-entity-image">
                    <a href="<?php the_permalink() ?>"><?php the_post_thumbnail('sr-thumb') ?></a>
                </div>
                <div class="sr-entity-excerpt">
                    <?php the_excerpt() ?>
                </div>
            </div>

        <?php endwhile;
        ?>
        </div>
        <?php
        wp_reset_query();

        return ob_get_clean();
    }
}
new SR_Shortcode_Entities($this);
