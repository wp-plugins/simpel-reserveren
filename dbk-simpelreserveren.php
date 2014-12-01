<?php

/**
* The plugin bootstrap file
*
* This file is read by WordPress to generate the plugin information in the plugin
* Dashboard. This file also includes all of the dependencies used by the plugin,
* registers the activation and deactivation functions, and defines a function
* that starts the plugin.
*
* @link http://simpelreserveren.nl
* @since 1.0.0
* @package DBK_WP_SimpleRes
*
* @wordpress-plugin
* Plugin Name: Simpel Reserveren
* Plugin URI: http://simpelreserveren.nl
* Description: De Simpel Reserveren plugin maakt het mogelijk dat uw bezoekers reserveringen vanuit uw WordPress website kunnen voltooien.
* Version: 0.0.1
* Author: DBK
* Author URI: http://dbk.nl/
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain: dbk-simpleres
* Domain Path: /languages
*/

if( !class_exists('DBK_SR') ):

require_once('library/autoloader.php');

class DBK_SR
{
    public static $entity_post_type = 'sr_entity';

    public $options;

    protected $plugin_name;
    protected $settings;

    protected static $_instance;

    function __construct() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = $this;
        }

        $this->plugin_name = 'dbk-sr';
        $this->settings = array(
            'path' => trailingslashit(dirname(__FILE__)),
            'url' => plugins_url(basename(__DIR__) )
        );

        $this->setup_autoload();

        $this->options = new SR_Options('dbk_sr_');

        $this->options->add_option('token',                 'API Key',                  false);
        $this->options->add_option('entity_title',          'Objecten naam',            'Accommodaties');
        $this->options->add_option('entity_title_singular', 'Objecten naam enkelvoud',  'Accommodatie');
        $this->options->add_option('entity_slug',           'Objecten slug',            'accommodaties');
        $this->options->add_option('thumb_size_w',          'Thumbnail breedte',        300);
        $this->options->add_option('thumb_size_h',          'Thumbnail hoogte',         200);

        //Cache entities import
        $this->options->add_option('_entities');

        $this->setup_locale();

        $this->setup_enviroment();

        add_action('init', array( $this, 'init' ), 0 );
        add_action('init', array( $this, 'register_post_types'));
        add_action('init', array( $this, 'register_sidebars'));

        add_action('widgets_init', array( $this, 'register_widgets')); 

        add_action('after_setup_theme', array( $this, 'after_setup_theme' ));

        add_filter('single_template', array( $this, 'entity_template'));

        register_activation_hook( __FILE__, array( $this, 'activate'));
        register_deactivation_hook( __FILE__, array( $this, 'deactivate')); 
    
        
    }
    
    function init () {
        
        $templater = new SR_Templater($this);
    }

    public function register_post_types() {
        include('includes/post-types/entity.php');
    }

    public function register_sidebars() {
        $sidebar_search = array(
            'name'          => __('SR: Zoeken'),
            'id'            => 'sr-sidebar-search',          
            'description'   => '',
            'class'         => '',
            'before_widget' => '<div id="%1$s" class="sr widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="widgettitle">',
            'after_title'   => '</h4>' 
        );
        register_sidebar($sidebar_search);
    }

    public function register_widgets() {
        include('includes/widgets/bookform.php');
    }
    

    public function after_setup_theme() {
        //add image sizes etc
        add_image_size('sr-thumb', $this->options->get_option('thumb_size_w'), $this->options->get_option('thumb_size_h'), true);
    }


    public function entity_template($single) {
        global $wp_query, $post;

        /* Checks for single template by post type */
        if ($post->post_type == self::$entity_post_type){
            $theme_file     = get_template_directory() . '/sr/sr-entity.php';
            $plugin_file    = dirname(__FILE__). '/templates/sr-entity.php';
            if(file_exists($theme_file))
                return $theme_file;
            if(file_exists($plugin_file))
                return $plugin_file;
        }
        return $single;
    }
    
    private function setup_enviroment() {

        //Ajax
        include('includes/ajax/entities.php');
        include('includes/ajax/booking.php');

        //Shortcodes
        include('includes/shortcodes/entities.php');

        //Admin
        if(is_admin()) {
            //Metaboxes
            include('includes/admin/metabox/gallery.php');

            //Options page
            include('includes/admin/optionspage.php');

            //Sync
            include('includes/admin/sync.php');
        }
    }

    private function setup_locale() {  
        load_plugin_textdomain( $this->get_plugin_name(), false, $this->get_plugin_path() . '/languages' );
    }

    private function activate() {
        DBK_SR_Activator::deactivate();
    }

    private function deactivate() {
        DBK_SR_Deactivator::deactivate();
    }

    private function setup_autoload() {        
        new SR_Autoloader('SR/', 'SR');
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_plugin_path() {
        return $this->settings['path'];
    }

    public function get_plugin_url() {
        return $this->settings['url'];
    }

    public static function get_instance() {
        return self::$_instance;
    }
    
    
}

function DBK_SR_init() {
    global $DBK_SR;
    
    if( !isset($DBK_SR) ) {
        $DBK_SR = new DBK_SR();
    }
    
    return $DBK_SR;
}

DBK_SR_init();

endif;