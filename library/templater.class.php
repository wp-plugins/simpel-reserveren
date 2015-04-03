<?php
class SR_Templater
{

        public $sr;

         /**
          * A Unique Identifier
          */
         protected $plugin_slug;

        /**
         * The array of templates that this plugin tracks.
         */
        protected $templates;

        /**
         * Initializes the plugin by setting filters and administration functions.
         */
        public function __construct($sr)
        {
            $this->sr = $sr;

            add_action('init', array( $this, 'init' ));
        }

    public function init()
    {
        $this->templates = array();

                // Add a filter to the attributes metabox to inject template into the cache.
                add_filter(
                    'page_attributes_dropdown_pages_args',
                     array( $this, 'register_project_templates' )
                );

                // Add a filter to the save post to inject out template into the page cache
                add_filter(
                    'wp_insert_post_data',
                    array( $this, 'register_project_templates' )
                );

                // Add a filter to the template include to determine if the page has our
                // template assigned and return it's path
                add_filter(
                    'template_include',
                    array( $this, 'view_project_template')
                );

                // Add your templates to this array.
                $this->templates = array(
                        'sr-search.php'    => 'Simpel Reserveren zoeken template',
                        'sr-book.php'      => 'Simpel Reserveren boeken template',
                );

                // intercept /?boekenplattegrond=1
                add_action('template_redirect', array($this, 'template_redirect_intercept'));
        add_filter('query_vars', array($this, 'add_query_vars'));
    }

    public function add_query_vars($qvars)
    {
        $qvars[] = 'boekenplattegrond';

        return $qvars;
    }

    public function template_redirect_intercept()
    {
        global $wp_query;
        if ($wp_query->get('boekenplattegrond')) {
            $file = 'sr-map.php';
            $plugin_file = plugin_dir_path(__FILE__).'../templates/'.$file;
            $theme_file = get_template_directory().'/simpel-reserveren/'.$file;

            add_filter('wp_title', function ($title) {
                    $title = 'Boeken op plattegrond';

                    return $title;
                }, 999);

                // Just to be safe, we check if the file exist first
                if (file_exists($theme_file)) {
                    include $theme_file;
                }
            if (file_exists($plugin_file)) {
                include $plugin_file;
            }

            die;
        }
    }

        /**
         * Adds our template to the pages cache in order to trick WordPress
         * into thinking the template file exists where it doens't really exist.
         *
         */
        public function register_project_templates($atts)
        {

                // Create the key used for the themes cache
                $cache_key = 'page_templates-'.md5(get_theme_root().'/'.get_stylesheet());

                // Retrieve the cache list.
                // If it doesn't exist, or it's empty prepare an array
                $templates = wp_get_theme()->get_page_templates();
            if (empty($templates)) {
                $templates = array();
            }

                // New cache, therefore remove the old one
                wp_cache_delete($cache_key, 'themes');

                // Now add our template to the list of templates by merging our templates
                // with the existing templates array from the cache.
                $templates = array_merge($templates, $this->templates);

                // Add the modified cache to allow WordPress to pick it up for listing
                // available templates
                wp_cache_add($cache_key, $templates, 'themes', 1800);

            return $atts;
        }

        /**
         * Checks if the template is assigned to the page
         */
        public function view_project_template($template)
        {
            global $post;

            if (!isset($this->templates[get_post_meta($post->ID, '_wp_page_template', true)])) {
                return $template;
            }

            $file = get_post_meta($post->ID, '_wp_page_template', true);

            $plugin_file = plugin_dir_path(__FILE__).'../templates/'.$file;
            $theme_file = get_template_directory().'/simpel-reserveren/'.$file;

            // add full width class
            add_filter('body_class', function ($classes = '') {
                $classes[] = 'full-width';

                return $classes;
            });

            // $this->add_scripts();
            // $this->add_styles();

            // Just to be safe, we check if the file exist first
            $new_template = locate_template(array('simpel-reserveren/'.$file));
            if ($new_template != '') {
                return $new_template;
            }
            if (file_exists($theme_file)) {
                return $file;
            }
            
            if (file_exists($plugin_file)) {
                return $plugin_file;
            }

            return $template;
        }
}
