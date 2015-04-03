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
 * @package DBK_SR
 *
 * @wordpress-plugin
 * Plugin Name: Simpel Reserveren
 * Plugin URI: http://simpelreserveren.nl
 * Description: De Simpel Reserveren plugin maakt het mogelijk dat uw bezoekers reserveringen vanuit uw WordPress website kunnen voltooien.
 * Version: 4.0.10
 * Author: DBK
 * Author URI: http://dbk.nl/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: dbk-simpleres
 * Domain Path: /languages
 */
if (!class_exists('DBK_SR')):

require_once 'library/autoloader.php';

class DBK_SR
{
    public static $entity_post_type = 'sr_entity';

    public $options;

    protected $plugin_name;
    protected $settings;

    protected static $_instance;
    protected static $_options;

    public function __construct()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = $this;
        }

        $this->plugin_name = 'dbk-sr';
        $this->version = '4.0.10';
        $this->settings = array(
            'path' => trailingslashit(dirname(__FILE__)),
            'url' => plugins_url(basename(__DIR__)),
        );

        $this->setup_autoload();

        $this->options = new SR_Options('dbk_sr_');

        $this->options->add_option('token',                 false,                      'API Key');
        $this->options->add_option('terms_url',             '#',                        'URL Algemene voorwaarden');
        $this->options->add_option('entity_title',          'Accommodaties',            'Objecten naam');
        $this->options->add_option('entity_title_singular', 'Accommodatie',             'Objecten naam enkelvoud');
        $this->options->add_option('entity_slug',           'accommodaties',            'Objecten slug');
        $this->options->add_option('book_on_map',           '0',                        'Boeken op plattegrond',    '',     'checkbox');
        $this->options->add_option('test_mode',             'live',                     'Test modus',               '',     'select',   array('live', 'dev', 'local'));
        $this->options->add_option('from_date',             '',                         'Vanaf datum');

        // options for what to show where
        // prefix and country can not go in step 3 since maxxton will choke
        $this->options->add_option('show_prefix',           'step2',                    'Aanhef tonen',             '',     'select', array('none', 'step2'));
        $this->options->add_option('show_firstname',        'step2',                    'Voornaam tonen',           '',     'select', array('none', 'step2', 'step3'));
        $this->options->add_option('show_address',          'step2',                    'Adres tonen',              '',     'select', array('none', 'step2', 'step3'));
        $this->options->add_option('show_postcode',         'step2',                    'Postcode tonen',           '',     'select', array('none', 'step2', 'step3'));
        $this->options->add_option('show_city',             'step2',                    'Stad tonen',               '',     'select', array('none', 'step2', 'step3'));
        $this->options->add_option('show_phone',            'step2',                    'Telefoonnummer tonen',     '',     'select', array('none', 'step2', 'step3'));
        $this->options->add_option('show_date_of_birth',    'step2',                    'Geboortedatum tonen',      '',     'select', array('none', 'step2', 'step3'));
        $this->options->add_option('show_country',          'step2',                    'Land tonen',               '',     'select', array('none', 'step2'));

        //Cache entities import
        $this->options->add_option('_entities');
        $this->options->add_option('_entitytypes');
        $this->options->add_option('_bookgroups');

        //Cache page urls
        $this->options->add_option('_book_url');
        $this->options->add_option('_search_url');

        self::$_options = $this->options;

        $this->setup_locale();

        $this->setup_enviroment();

        add_action('plugins_loaded', array( $this, 'load_textdomain' ));
        add_action('init', array( $this, 'init' ), 0);
        add_action('init', array( $this, 'register_post_types'));
        add_action('init', array( $this, 'register_sidebars'));

        add_action('widgets_init', array( $this, 'register_widgets'));
        if (!is_admin()) {
            add_action('init', array( $this, 'enqueue_style'));
            add_action('init', array( $this, 'enqueue_scripts'));
        }

        add_action('after_setup_theme', array( $this, 'after_setup_theme' ));
        add_filter('single_template', array( $this, 'entity_template'));

        add_action('generate_rewrite_rules', array($this, 'generate_rewrite_rules'));
        add_action('template_redirect', array($this, 'template_redirect'));
        add_filter('query_vars', array($this, 'query_vars'));

        register_activation_hook(__FILE__, array( $this, 'activate'));
        register_deactivation_hook(__FILE__, array( $this, 'deactivate'));
    }

    public function init()
    {
        $templater = new SR_Templater($this);
        SR_Activator::activate();
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('dbk-sr', false, dirname(plugin_basename(__FILE__)).'/languages');
    }

    public function register_post_types()
    {
        include 'includes/post-types/entity.php';
    }

    public function register_sidebars()
    {
        $sidebar_search = array(
            'name'          => __('SR: Zoeken'),
            'id'            => 'sr-sidebar-search',
            'description'   => '',
            'class'         => '',
            'before_widget' => '<div id="%1$s" class="sr widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="widgettitle">',
            'after_title'   => '</h4>',
        );
        register_sidebar($sidebar_search);
    }

    public function register_widgets()
    {
        include 'includes/widgets/bookform.php';
    }

    public function after_setup_theme()
    {
        //add image sizes etc
        add_image_size('sr-large', 1024, 768, false);
        add_image_size('sr-thumb', 500, 300, true);
        add_image_size('sr-small', 200, 100, true);
    }

    public function generate_rewrite_rules($wp_rewrite) {
        $new_rules = array(
            'sr-tunnel/(.*)$' => 'index.php?sr_tunnel=1&route=' . $wp_rewrite->preg_index(1)
        );

        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    }

    public function query_vars($qvars) {
        $qvars[] = 'sr_tunnel';
        $qvars[] = 'route';
        return $qvars;
    }

    function template_redirect()
    {
        global $wp_query;

        if ($wp_query->get('sr_tunnel')) {
            header('Content-Type: application/json');
            $url = $this->get_endpoint() . $wp_query->get('route') . '?key=' . $this->options->get_option('token') . '&' . http_build_query($_GET);
            echo file_get_contents($url);
            die;
        }
    }

    public function entity_template($single)
    {
        global $wp_query, $post;

        /* Checks for single template by post type */
        if ($post->post_type == self::$entity_post_type) {
            // add full width class
            add_filter('body_class', function ($classes = '') {
                $classes[] = 'full-width';

                return $classes;
            });

            $theme_file     = get_template_directory().'/simpel-reserveren/sr-entity.php';
            $plugin_file    = dirname(__FILE__).'/templates/sr-entity.php';
            if (file_exists($theme_file)) {
                return $theme_file;
            }
            if (file_exists($plugin_file)) {
                return $plugin_file;
            }
        }

        return $single;
    }

    public function enqueue_scripts()
    {
        add_action('wp_head', array($this, 'head_script'));

        wp_enqueue_script('jquery', $this->get_plugin_url().'/scripts/lib/jquery-1.11.2.min.js', array(), '1.11.2', true);
        wp_enqueue_script('angular', $this->get_plugin_url().'/scripts/lib/angular.min.js', array(), '1.2.26', true);
        wp_enqueue_script('angular-route', $this->get_plugin_url().'/scripts/lib/angular-route.min.js', 'angular', '1.2.26', true);
        wp_enqueue_script('angular-animate', $this->get_plugin_url().'/scripts/lib/angular-animate.min.js', 'angular', '1.2.26', true);
        wp_enqueue_script('bootstrap', $this->get_plugin_url().'/scripts/lib/bootstrap.min.js', array(), '3.2.0', true);

        if (WP_DEBUG) {
            wp_enqueue_script('datepicker', $this->get_plugin_url().'/scripts/plugins/datepicker.js', 'jquery', '1.0', true);
            wp_enqueue_script('lightbox', $this->get_plugin_url().'/scripts/plugins/ekko-lightbox.min.js', 'jquery', '1.0', true);
            wp_enqueue_script('jquery.cookie', $this->get_plugin_url().'/scripts/plugins/jquery.cookie.js', 'jquery', '1.0', true);
            wp_enqueue_script('dateFormat', $this->get_plugin_url().'/scripts/plugins/dateFormat.js', 'jquery', '1.0', true);
            wp_enqueue_script('leaflet', $this->get_plugin_url().'/scripts/plugins/leaflet.js', 'jquery', '1.0', true);
            wp_enqueue_script('leaflet.MakiMarkers', $this->get_plugin_url().'/scripts/plugins/leaflet.MakiMarkers.js', 'leaflet', '1.0', true);
            wp_enqueue_script('angular-leaflet-directive', $this->get_plugin_url().'/scripts/plugins/leaflet.angular.directive.js', 'leaflet', '1.0', true);
            wp_enqueue_script('slick', $this->get_plugin_url().'/scripts/plugins/slick.js', 'jquery', '1.0', true);

            wp_enqueue_script('sr-app', $this->get_plugin_url().'/scripts/app.js', 'angular', '1.0', true);
            wp_enqueue_script('sr-services', $this->get_plugin_url().'/scripts/app/services.js', 'angular', '1.0', true);
            wp_enqueue_script('sr-search', $this->get_plugin_url().'/scripts/app/controllers/search.js', 'angular', '1.0', true);
            wp_enqueue_script('sr-entity', $this->get_plugin_url().'/scripts/app/controllers/entity.js', 'angular', '1.0', true);
            wp_enqueue_script('sr-book', $this->get_plugin_url().'/scripts/app/controllers/book.js', 'angular', '1.0', true);
            wp_enqueue_script('sr-map', $this->get_plugin_url().'/scripts/app/controllers/map.js', 'angular', '1.0', true);
            wp_enqueue_script('sr-bookform', $this->get_plugin_url().'/scripts/app/bookform.js', 'jquery', '1.0', true);
        } else {
            wp_enqueue_script('sr-plugins', $this->get_plugin_url().'/dist/js/plugins.min.js?v='.$this->version, 'angular', $this->version, true);
            wp_enqueue_script('sr-app', $this->get_plugin_url().'/scripts/app.js?v='.$this->version, 'sr-plugins', $this->version, true);
            wp_enqueue_script('sr-app-plugins', $this->get_plugin_url().'/dist/js/app-plugins.min.js?v='.$this->version, 'sr-app', $this->version, true);
        }

        wp_localize_script('sr-app', 'sr_labels', array(
            'loading'  => __('Loading...', 'dbk-sr'),
            'submit' => __('Submit', 'dbk-sr'),
            'period' => __('Period', 'dbk-sr'),
            'type' => __('Type', 'dbk-sr'),
            'party' => __('Travelling party', 'dbk-sr'),
            'filter' => __('Filter', 'dbk-sr'),
            'total' => __('Total', 'dbk-sr'),
        ));
    }

    public function head_script()
    {
        $static_url = ($this->options->get_option('test_mode') == 'local' ? '//sr4.dev/' : '//static.simpelreserveren.nl/');
        ?>
        <script>
            var dbk_sr = {
                bookgroups: <?php echo json_encode($this->options->get_option('_bookgroups'));  ?>,
                entitytypes: <?php echo json_encode($this->options->get_option('_entitytypes')); ?>,
                base_url: '<?=$this->get_endpoint() ?>',
                tunnel_url: '<?=home_url('sr-tunnel/') ?>',
                plugin_url: '<?= $this->get_plugin_url() ?>',
                search_url: '<?=$this->options->get_option('_search_url')?>',
                book_url: '<?=$this->options->get_option('_book_url')?>',
                ajax_url: '<?=admin_url('admin-ajax.php')?>',
                static_url: '<?= $static_url ?>',
                terms_url: '<?= $this->options->get_option('terms_url') ?>',
                from_date: '<?= $this->options->get_option('from_date') ?>',
                version: '<?= $this->version ?>',
                show_prefix: '<?= $this->options->get_option('show_prefix') ?>',
                show_firstname: '<?= $this->options->get_option('show_firstname') ?>',
                show_address: '<?= $this->options->get_option('show_address') ?>',
                show_postcode: '<?= $this->options->get_option('show_postcode') ?>',
                show_city: '<?= $this->options->get_option('show_city') ?>',
                show_phone: '<?= $this->options->get_option('show_phone') ?>',
                show_date_of_birth: '<?= $this->options->get_option('show_date_of_birth') ?>',
                show_country: '<?= $this->options->get_option('show_country') ?>',
            }
        </script>
        <?php

    }

    public function enqueue_style()
    {
        //wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
        wp_enqueue_style('font-awesome', $this->get_plugin_url().'/dist/css/font-awesome.min.css');
        //wp_enqueue_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css');
        wp_enqueue_style('sr-main', $this->get_plugin_url().'/dist/css/main.min.css?v='.$this->version, null, $this->version);

        $custom_css = get_template_directory().'/simpel-reserveren/custom-sr-theme.css';
        if (file_exists($custom_css)) {
            wp_enqueue_style('sr-theme', get_template_directory_uri().'/simpel-reserveren/custom-sr-theme.css?v='.$this->version, null, $this->version);
        } else {
            wp_enqueue_style('sr-theme', $this->get_plugin_url().'/sr-theme.css?v='.$this->version, null, $this->version);
        }
    }

    private function setup_enviroment()
    {

        //Sync
        include 'includes/admin/sync.php';

        //Ajax
        include 'includes/ajax/entities.php';
        include 'includes/ajax/booking.php';

        //Shortcodes
        include 'includes/shortcodes/entities.php';
        include 'includes/admin/metabox/entity_alternative.php';

        //Admin
        if (is_admin()) {
            //Metaboxes
            include 'includes/admin/metabox/gallery.php';
            include 'includes/admin/metabox/entity.php';

            //Options page
            include 'includes/admin/optionspage.php';

            //Hook on save_post
            include 'includes/admin/page.php';
        }
    }

    private function setup_locale()
    {
        load_plugin_textdomain($this->get_plugin_name(), false, $this->get_plugin_path().'/languages');
    }

    public function activate()
    {
        $admin = get_role('administrator');
        $admin->add_cap('sr_settings');
        $admin->add_cap('sr_entities'); 
    }

    public function deactivate()
    {
        
    }

    private function setup_autoload()
    {
        new SR_Autoloader('SR/', 'SR');
    }

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    public function get_plugin_path()
    {
        return $this->settings['path'];
    }

    public function get_plugin_url()
    {
        return $this->settings['url'];
    }

    public function get_endpoint()
    {
        if ($this->options->get_option('test_mode') === 'local') {
            return '//api.simpelreserveren.app/';
        } elseif ($this->options->get_option('test_mode') === 'dev') {
            return 'https://api-dev.simpelreserveren.nl/';
        } else {
            return 'https://api.simpelreserveren.nl/';
        }
    }

    public static function get_option($name)
    {
        return self::$_options->get_option($name);
    }

    public static function get_instance()
    {
        return self::$_instance;
    }

    public function includeTemplate($template, $vars)
    {
        extract($vars);
        $theme_template_file = get_template_directory() . '/simpel-reserveren/'.$template;
        if (file_exists($theme_template_file)) {
            include($theme_template_file);
            return true;
        }

        $plugin_template_file = dirname(__FILE__) . '/templates/' . $template;
        if (file_exists($plugin_template_file)) {
            include($plugin_template_file);
           return true;
        }
        return false;
    }
}

function DBK_SR_init()
{
    global $DBK_SR;

    if (!isset($DBK_SR)) {
        $DBK_SR = new DBK_SR();
    }

    return $DBK_SR;
}

DBK_SR_init();

endif;
