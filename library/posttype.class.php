<?php
class SR_PostType
{

    public $post_type_name;
    public $post_type_args;
    public $post_type_labels;

    public function __construct($name, $args = array(), $labels = array())
    {
        $this->post_type_name = $name;
        $this->post_type_args = $args;
        $this->post_type_labels = $labels;

        if (!post_type_exists($this->post_type_name)) {
            $this->register_post_type();
        }

        $this->init();
    }

    public function init()
    {
    }

    public static function register($name, $args = array(), $labels = array())
    {
        $posttype = new self($name, $args, $labels);

        return $posttype;
    }

    public function register_post_type()
    {
        $name = ucwords(str_replace('_', ' ', $this->post_type_name));
        $plural = $name.'s';

        $labels = array_merge(
            array(
                'menu_name' => $plural,
            ),
            $this->post_type_labels
        );

        $args = array_merge(
            array(
                'labels' => $labels,
                'public' => true,
                'exclude_from_search' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => current_user_can('sr_entities'),
                'show_in_admin_bar' => false,
                'menu_position' => 20,
                'menu_icon' => null,
                'capability_type' => 'page',
                'hierarchical' => true,
                'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'),
                'has_archive' => false,
                'rewrite' => false,
                'query_var' => true,
                'can_export' => true,
            ),
            $this->post_type_args
        );

        register_post_type($this->post_type_name, $args);
    }

    public function add_taxonomy($name, $options = array(), $labels = array())
    {
        $post_type_name = $this->post_type_name;

        if (!taxonomy_exists($name)) {
            $menu_name = ucwords(str_replace('_', ' ', $name));
            $plural = $menu_name;

            $labels = array_merge(
                array(
                    'menu_name' => __($menu_name),
                ),
                $labels
            );

            $options = array_merge(
                array(
                    'label' => $plural,
                    'labels' => $labels,
                    'public' => true,
                    'show_ui' => true,
                    'show_in_nav_menus' => true,
                    '_builtin' => false,
                ),
                $options
            );

            add_action('init', function () use ($name, $post_type_name, $options) {
                register_taxonomy($name, $post_type_name, $options);
            });
        } else {
            add_action('init', function () use ($name, $post_type_name) {
                register_taxonomy_for_object_type($name, $post_type_name);
            });
        }
    }

    public function add_custom_field_group()
    {
    }

    public static function query($post_type = 'page', $options = array())
    {
        /* query posts */
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array_merge(array(
                'post_type' => $post_type,
                'posts_per_page' => get_option('posts_per_page'),
                'paged' => $paged,
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ),
            (array) $options
        );

        return new WP_Query($args);
    }
}
