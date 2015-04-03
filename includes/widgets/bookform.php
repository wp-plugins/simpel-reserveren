<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class SR_Widget_Bookform extends WP_Widget
{
    protected $sr;

    public function SR_Widget_Bookform()
    {
        global $DBK_SR;

        parent::__construct(false, 'SR: Boekformulier');
        $this->sr = $DBK_SR;
    }

    public function widget($args, $instance)
    {
        // Widget output
        global $post;
        $template = get_post_meta($post->ID, '_wp_page_template', true);

        if ($template == 'sr-search.php') {
            $template_file = 'sr-search-book-widget.php';
        } else {
            $template_file = 'sr-home-book-widget.php';
        }

        $vars = ['instance' => $instance, '_template' => $template];
        if (!$this->sr->includeTemplate($template_file, $vars)) {
            $this->sr->includeTemplate('sr-book-widget.php', $vars);
        }

    }

    public function update($new_instance, $old_instance)
    {
        $instance = $new_instance;

        return $instance;
    }

    public function form($instance)
    {
        $instance = wp_parse_args(
            (array) $instance
        );
        $title = !empty($instance['title']) ? $instance['title'] : 'Zoek en Boek';
        $button_text = !empty($instance['button_text']) ? $instance['button_text'] : 'Zoeken';
        $cta_text = !empty($instance['cta_text']) ? $instance['cta_text'] : 'Zoeken';
        $filter_bookgroups = $instance['filter_bookgroups'];
        $filter_types = $instance['filter_types'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title');
        ?>">Titel</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title');
        ?>" name="<?php echo $this->get_field_name('title');
        ?>" type="text" value="<?php echo esc_attr($title);
        ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('button_text');
        ?>">Tekst zoekknop</label>
            <input class="widefat" id="<?php echo $this->get_field_id('button_text');
        ?>" name="<?php echo $this->get_field_name('button_text');
        ?>" type="text" value="<?php echo esc_attr($button_text);
        ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('cta_text');
        ?>">Call to Action tekst</label>
            <input class="widefat" id="<?php echo $this->get_field_id('cta_text');
        ?>" name="<?php echo $this->get_field_name('cta_text');
        ?>" type="text" value="<?php echo esc_attr($cta_text);
        ?>">
        </p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name('filter_types');
        ?>" id="<?php echo $this->get_field_id('filter_types');
        ?>" value="1" <?php checked($filter_types) ?> />
            <label for="<?php echo $this->get_field_id('filter_types');
        ?>">Filter op type</label>
        </p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name('filter_bookgroups');
        ?>" id="<?php echo $this->get_field_id('filter_bookgroups');
        ?>" value="1" <?php checked($filter_bookgroups) ?> />
            <label for="<?php echo $this->get_field_id('filter_bookgroups');
        ?>">Filter op reisgezelschap</label>
        </p>

        <?php

    }

    public static function register($sr)
    {
        register_widget('SR_Widget_Bookform');
    }
}

SR_Widget_Bookform::register($this);
