<?php
class SR_Templater {


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
        public function __construct($sr) {
            $this->sr = $sr;

            add_action( 'init', array( $this, 'init' ) );

                
        } 


        public function init() {

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
        }

        /**
         * Adds our template to the pages cache in order to trick WordPress
         * into thinking the template file exists where it doens't really exist.
         *
         */

        public function register_project_templates( $atts ) {

                // Create the key used for the themes cache
                $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

                // Retrieve the cache list. 
                // If it doesn't exist, or it's empty prepare an array
                $templates = wp_get_theme()->get_page_templates();
                if ( empty( $templates ) ) {
                        $templates = array();
                } 

                // New cache, therefore remove the old one
                wp_cache_delete( $cache_key , 'themes');

                // Now add our template to the list of templates by merging our templates
                // with the existing templates array from the cache.
                $templates = array_merge( $templates, $this->templates );

                // Add the modified cache to allow WordPress to pick it up for listing
                // available templates
                wp_cache_add( $cache_key, $templates, 'themes', 1800 );

                return $atts;

        } 

        /**
         * Checks if the template is assigned to the page
         */
        public function view_project_template( $template ) {

                global $post;

                if (!isset($this->templates[get_post_meta( $post->ID, '_wp_page_template', true )] ) ) {
                    return $template;    
                }

                $file = get_post_meta( $post->ID, '_wp_page_template', true );

                $plugin_file = plugin_dir_path(__FILE__). '../templates/' . $file;
                $theme_file = get_template_directory() . '/sr/'. $file;

                // add full width class
                add_filter('body_class', function($classes = '') {
                    $classes[] = 'full-width';

                    return $classes;
                });

                $this->add_scripts();
                $this->add_styles();
                
                // Just to be safe, we check if the file exist first
                if( file_exists( $theme_file ) ) {
                    return $file;
                } 
                if( file_exists( $plugin_file ) ) {
                    return $plugin_file;
                }

                return $template;

        } 

        private function add_scripts() {
                wp_enqueue_script( 'jquery', '//code.jquery.com/jquery-1.10.2.min.js', array(), '1.10.2', true);
                wp_enqueue_script( 'angular', '//ajax.googleapis.com/ajax/libs/angularjs/1.2.26/angular.min.js', array(), '1.2.26', true);
                wp_enqueue_script( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js', array(), '3.2.0', true);
                
                wp_enqueue_script( 'ripples', $this->sr->get_plugin_url() . '/dist/js/ripples.min.js', 'jquery', '1.0', true);
                wp_enqueue_script( 'material', $this->sr->get_plugin_url() . '/dist/js/material.min.js', 'jquery', '1.0', true);
                wp_enqueue_script( 'datepicker', $this->sr->get_plugin_url() . '/dist/js/datepicker.js', 'jquery', '1.0', true);
                wp_enqueue_script( 'jquery.cookie', $this->sr->get_plugin_url() . '/scripts/jquery.cookie.js', 'jquery', '1.0', true);
                wp_enqueue_script( 'dateFormat', $this->sr->get_plugin_url() . '/scripts/dateFormat.js', 'jquery', '1.0', true);
                wp_enqueue_script( 'leaflet', $this->sr->get_plugin_url() . '/scripts/plugins/leaflet.js', 'jquery', '1.0', true);
                wp_enqueue_script( 'leaflet.MakiMarkers', $this->sr->get_plugin_url() . '/scripts/plugins/leaflet.MakiMarkers.js', 'jquery', '1.0', true);
                wp_enqueue_script( 'angular-leaflet-directive', $this->sr->get_plugin_url() . '/scripts/plugins/angular-leaflet-directive.js', 'jquery', '1.0', true);

                wp_enqueue_script( 'sr-app', $this->sr->get_plugin_url() . '/scripts/app/app.js', 'angular', '1.0', true);
                wp_enqueue_script( 'sr-services', $this->sr->get_plugin_url() . '/scripts/app/services.js', 'angular', '1.0', true);
                wp_enqueue_script( 'sr-search', $this->sr->get_plugin_url() . '/scripts/app/search.js', 'angular', '1.0', true);
                wp_enqueue_script( 'sr-map', $this->sr->get_plugin_url() . '/scripts/app/map.js', 'angular', '1.0', true);
                wp_enqueue_script( 'sr-temp', $this->sr->get_plugin_url() . '/scripts/app/temp.js', 'angular', '1.0', true);
        }

        private function add_styles() {
            //wp_enqueue_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css');
            wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
            wp_enqueue_style( 'ripples', $this->sr->get_plugin_url() . '/dist/css/ripples.min.css');
            wp_enqueue_style( 'sr-main', $this->sr->get_plugin_url() . '/dist/css/main.min.css');
            //wp_enqueue_style( 'wfont', plugins_url() . '/dist/css/material-wfont.min.css');


        }

       


} 
