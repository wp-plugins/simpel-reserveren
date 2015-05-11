<?php
/*
  Plugin Name: Simpel Reserveren 3
  Plugin URI: http://www.simpelreserveren.nl
  Description: Boeking module om hotels, vakantieparken, jachthavens & campings te beheren
  Version: 3.5.2
  Author: Sietze Keuning
  Author URI: http://www.kingwebsites.nl
  License: Creative Common
 */
global $wpdb;

// Zorg ervoor dat er maar 1x de tabellen worden aangemaakt in geval van multisite
if (defined('SIMPEL_MULTIPLE') && SIMPEL_MULTIPLE) {
    define('SIMPEL_DB_PREFIX', 'wp_simpelreserveren_');
} else {
    define('SIMPEL_DB_PREFIX', $wpdb->prefix . 'simpelreserveren_');
}
if (defined('WP_CONTENT_URL')) {
    define('SIMPEL_PLUGIN_URL', WP_CONTENT_URL . '/plugins/simpelreserveren-3.0/');
} else {
    define('SIMPEL_PLUGIN_URL', plugins_url() . '/simpelreserveren-3.0/');
}
include_once(dirname(__FILE__) . '/functions.php');
include_once(dirname(__FILE__) . '/admin.php');
include_once('classes/Base_Class.php');
include_once('classes/Camping_Class.php');
include_once('classes/Zoek_Class.php');
include_once('classes/Accommodatie_Class.php');
include_once('classes/Arrangement_Class.php');
include_once('classes/Arrangementen_Class.php');
include_once('classes/Prijsberekening_Class.php');
include_once('classes/Customizer_Class.php');
include_once('classes/Widgets_Class.php');
include_once('classes/Landingpage_Class.php');
include_once('classes/Update_Class.php');
include_once('classes/Xls_Class.php');
include_once('classes/Tag_Manager_Class.php');

class WPSimpel_Reserveren
{

    public $hide_footer = 0;
    public $theme_settings;
	public $prijsberekening;
	public $version = '3.5.2';

	private $wpdb;
    private $plugin_db_prefix = 'simpelreserveren_';
    private $db_prefix;
    private $db_suffix;
    private $camping_rights;
    private $multiple_campings;
    private $laatste_stap;

    function __construct()
    {
        // standaard instellingen als deze niet in wp-config.php staan
        if (!defined('SIMPEL_MULTIPLE')) {
            define('SIMPEL_MULTIPLE', 0);
        }
        if (!defined('SIMPEL_CAMPING_SLUG')) {
            define('SIMPEL_CAMPING_SLUG', 'camping');
        }
        if (!defined('SIMPEL_ACCOMMODATIE_SLUG')) {
            define('SIMPEL_ACCOMMODATIE_SLUG', 'accommodatie');
        }
        if (!defined('SIMPEL_FACEBOOK_ID')) {
            define('SIMPEL_FACEBOOK_ID', '');
        }
        if (!defined('SIMPEL_KLANT_TYPE')) {
            define('SIMPEL_KLANT_TYPE', 'camping');
        } // kiezen uit camping, vakantiepark, hotel, bootverhuur
        if (!defined('SIMPEL_BOOTSTRAP')) {
            define('SIMPEL_BOOTSTRAP', '1');
        }
        if (!defined('SIMPEL_AFRONDEN')) {
            define('SIMPEL_AFRONDEN', '1');
        }

        add_action('acf/register_fields', array(&$this, 'acf_register_extra_field'));
        add_action('init', array(&$this, 'init'), 0);

        $admin = new Simpel_Reserveren_Admin($this);
        add_action('init', array($admin, 'init'), 0);

        add_action('widgets_init', array('SimpelReserveren_Widgets', 'register'));
    }

    function home_url()
    {
        if (function_exists('icl_get_home_url')) {
            return icl_get_home_url();
        }

        return site_url('/');
    }

    function acf_register_extra_field($val, $attr = array())
    {
        include_once(dirname(__FILE__) . '/acf-fields/sr_object.php');
    }

    function init()
    {
        global $wp_rewrite;
        global $wpdb;

        $this->wpdb = &$wpdb;
        $this->db_prefix = $this->wpdb->prefix . $this->plugin_db_prefix;

        if (SIMPEL_MULTIPLE) {
            $main_user_id = $this->wpdb->get_var('select user_id from wp_usermeta where meta_key = "primary_blog" and meta_value = "' . get_current_blog_id() . '"');
            $camping_id = get_user_meta($main_user_id, 'camping', true);
            if ($main_user_id && $camping_id) {
                define('SIMPEL_SHOW_CAMPING', $camping_id);
                add_filter('body_class', function ($classes) {
                        $classes[] = 'sub-site';

                        return $classes;
                    });
            } else {
                define('SIMPEL_SHOW_CAMPING', '');
            }
        } else {
            define('SIMPEL_SHOW_CAMPING', '');
        }

        $this->plugin_url = SIMPEL_PLUGIN_URL;
        $this->prijsberekening = new Prijsberekening();
        $this->laatste_stap = 3;
        $this->theme_settings = get_option('simpelreserveren');

        load_plugin_textdomain('simpelreserveren', false, dirname(plugin_basename(__FILE__)) . '/languages/');


        add_action('wp_ajax_get_kassabon', array(&$this, 'get_kassabon'));
        add_action('wp_ajax_nopriv_get_kassabon', array(&$this, 'get_kassabon'));
        add_action('wp_ajax_check_beschikbaarheid', array(&$this, 'check_beschikbaarheid'));
        add_action('wp_ajax_nopriv_check_beschikbaarheid', array(&$this, 'check_beschikbaarheid'));
        add_action('wp_ajax_opslaan-naw', array(&$this, 'form_save_naw'));
        add_action('wp_ajax_nopriv_opslaan-naw', array(&$this, 'form_save_naw'));
        add_action('wp_ajax_get_arr_accommodaties', array(&$this, 'ajax_get_arr_accommodaties'));
        add_action('wp_ajax_nopriv_get_arr_accommodaties', array(&$this, 'ajax_get_arr_accommodaties'));

        add_action('wp_ajax_get_accommodatie_arrangementen', array(&$this, 'ajax_get_accommodatie_arrangementen'));
        add_action('wp_ajax_nopriv_get_accommodatie_arrangementen', array(&$this, 'ajax_get_accommodatie_arrangementen'));
        add_action('wp_ajax_get_accommodatie_alternatieve_prijzen', array(&$this, 'ajax_get_accommodatie_alternatieve_prijzen'));
        add_action('wp_ajax_nopriv_get_accommodatie_alternatieve_prijzen', array(&$this, 'ajax_get_accommodatie_alternatieve_prijzen'));

        add_action('wp_ajax_get_prices', array(&$this, 'get_prices'));
        add_action('wp_ajax_nopriv_get_prices', array(&$this, 'get_prices'));


        add_image_size('sr-thumb-368', 368, 245, true);
        add_image_size('sr-thumb-150', 150, 120, true);

        if (SIMPEL_MULTIPLE) {
            // voeg camping toe aan gebruikers, zodat deze ook kunnen worden gekoppeld
            add_action('show_user_profile', array(&$this, 'booking_show_user_camping'), 10);
            add_action('edit_user_profile', array(&$this, 'booking_show_user_camping'), 10);
            add_action('personal_options_update', array(&$this, 'save_user_camping'));
            add_action('edit_user_profile_update', array(&$this, 'save_user_camping'));
        }


        add_action('generate_rewrite_rules', array(&$this, 'add_rewrite_rules'));
        add_action('template_redirect', array(&$this, 'template_redirect_intercept'));

        add_filter('query_vars', array(&$this, 'add_query_vars'));

        // zoek en boek venster
        add_shortcode('sr-search-block', array(&$this, 'search_block'));
        add_shortcode('sr-camping-block', array(&$this, 'camping_block'));
        add_shortcode('sr-lastminutes-block', array(&$this, 'lastminutes_block'));
        add_shortcode('sr-arrangementen-block', array(&$this, 'arrangementen_block'));
        add_shortcode('sr-simple-sitemap', array(&$this, 'sitemap'));

    }

    function set_title($title)
    {
        $this->title = $title;
        add_filter('body_class', array(&$this, 'add_body_class'));
        add_filter('wpseo_title', array(&$this, 'add_title'));
    }

    function add_body_class($classes)
    {
        //$classes[] = 'simpel-reserveren';
        return $classes;
    }

    function add_title($title)
    {
        $title = $this->title;

        return $title;
    }

    function update_cookie(Array $values)
    {
        $cookie = json_decode(stripslashes($_COOKIE['simpelreserveren']));
        if (!$cookie) {
            $cookie = new stdClass;
        }
        foreach ($values as $key => $val) {
            $cookie->$key = $val;
        }

        $cookie = (json_encode($cookie));
        $expire = time() + (60 * 60 * 24 * 30);
        setcookie('simpelreserveren', $cookie, $expire, '/');
    }

    function add_rewrite_rules($wp_rewrite)
    {
        $new_rules = array(
            'boeken/([0-9]+)/stap([0-9]+)' => 'index.php?boeken=1&id=' . $wp_rewrite->preg_index(1) . '&stap=' . $wp_rewrite->preg_index(2),
            SIMPEL_ACCOMMODATIE_SLUG . '/([0-9]*)/(.*)' => 'index.php?accommodatie=1&id=' . $wp_rewrite->preg_index(1),
            SIMPEL_ACCOMMODATIE_SLUG . '/(.*)$' => 'index.php?accommodatie=1&name=' . $wp_rewrite->preg_index(1),
            'arrangement/(.*)$' => 'index.php?arrangement=1&name=' . $wp_rewrite->preg_index(1),
            'jaaroverzicht/([0-9]+)' => 'index.php?jaaroverzicht=1&id=' . $wp_rewrite->preg_index(1),
            'zoeken' => 'index.php?zoeken=1',
            'arrangementen' => 'index.php?arrangementen=1',
        );

        if (SIMPEL_MULTIPLE) {
            $new_rules[SIMPEL_CAMPING_SLUG . '/([0-9]*)/(.*)$'] = 'index.php?camping=1&id=' . $wp_rewrite->preg_index(1);
            $new_rules[SIMPEL_CAMPING_SLUG . '/(.*)$'] = 'index.php?camping=1&name=' . $wp_rewrite->preg_index(1);
        }

        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;

        if (defined('ICL_LANGUAGE_CODE')) {
            $this->db_suffix = '_' . ICL_LANGUAGE_CODE;
        } else {
            $this->db_suffix = '';
        }
    }

    function add_query_vars($qvars)
    {
        $qvars[] = 'accommodatie';
        $qvars[] = 'camping';
        $qvars[] = 'jaaroverzicht';
        $qvars[] = 'zoeken';
        $qvars[] = 'arrangement';
        $qvars[] = 'arrangementen';
        $qvars[] = 'boeken';
        $qvars[] = 'name';
        $qvars[] = 'id';
        $qvars[] = 'stap';

        return $qvars;
    }

    function next_step()
    {
        $this->_volgende_stap();
    }

    function template_redirect_intercept()
    {
        global $wp_query;
        wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', false, '2.0.3', true);
        if (SIMPEL_BOOTSTRAP) {
            wp_enqueue_script('bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js', 'jquery', '3.0.0', true);
        }
        if ($wp_query->get('accommodatie') || $wp_query->get('camping') || $wp_query->get('arrangement')) {
            wp_enqueue_script('datepick', $this->plugin_url . 'js/jquery.datepick.js', 'jquery', 1, true);
            wp_enqueue_script('datepick-nl', $this->plugin_url . 'js/jquery.datepick-nl.js', 'datepick', 1, true);
        }
        wp_enqueue_script('waypoints', $this->plugin_url . 'js/waypoints.min.js', 'jquery', '1.6.2', true);
        wp_enqueue_script('jqueryui', $this->plugin_url . 'js/jquery-ui.min.js', 'jquery', '1.11.4', true);

        wp_enqueue_script('fancybox', $this->plugin_url . 'js/jquery.fancybox.js', 'jquery', '2.0', true);
        wp_enqueue_script('jquery.cookie', $this->plugin_url . 'js/jquery.cookie.js', 'jquery', '1.4.1', true);
        wp_enqueue_script('sr-script', $this->plugin_url . 'js/simpel-reserveren.js?v='.$this->version, 'waypoints', $this->version, true);
        wp_localize_script('sr-script', 'sr_translations', array(
            'wait_while_processing' => __('Please wait a moment while your booking is being processed', 'simpelreserveren'),
            'wait_while_saving' => __('Please wait while your data is being saved', 'simpelreserveren'),
            'error_terms' => __('You have to agree to the terms and conditions', 'simpelreserveren'),
            'lang' => (defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'nl'),
            'js_dir' => plugins_url() . '/' . $this->plugin_url . '/js/',
            'admin_ajax' => admin_url('admin-ajax.php' . (defined('ICL_LANGUAGE_CODE') ? '?lang=' . ICL_LANGUAGE_CODE : '')),
            'loading' => __('loading...', 'simpelreserveren'),
            'root' => home_url(),
            'std_aantal_nachten' => $this->get_setting('std-aantal-nachten'),
            'vanaf_datum' => $this->get_setting('vanaf-datum'),
        ));
        if ($wp_query->get('boeken')) {
            wp_enqueue_script('validation-script', $this->plugin_url . 'js/jquery.validationEngine.js', 'jquery', '1', true);
            wp_enqueue_script('zoom', $this->plugin_url . 'js/jquery.smoothZoom.min.js', 'jquery', '1', true);
        }


        wp_enqueue_style('jquery-ui', 'http://code.jquery.com/ui/1.9.1/themes/cupertino/jquery-ui.css');
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css');
        if (SIMPEL_BOOTSTRAP) {
            wp_enqueue_style('sr-bootstrap', $this->plugin_url . 'css/simpel-reserveren-bootstrap.css');
        }
        wp_enqueue_style('booking-style', $this->plugin_url . 'css/simpel-reserveren.css', false, '1.1', 'all');
        wp_enqueue_style('wp-jquery-fancybox', $this->plugin_url . 'css/jquery.fancybox.css', false, '1.1', 'all');

        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/custom.css';
        if (file_exists($theme_file)) {
            wp_enqueue_style('booking-style-custom', get_bloginfo('template_url') . '/simpel-reserveren/custom.css', false, '1.1', 'all');
        }
        wp_enqueue_style('datepick', $this->plugin_url . 'css/jquery.datepick.css');

        if ($wp_query->get('boeken')) {
            add_action('wp_head', array(&$this, 'meta_noindex'));
            $this->hide_footer = 1;
            $this->show_boeken();
            $this->_exit();
        } elseif ($wp_query->get('zoeken')) {
            add_action('wp_head', array(&$this, 'meta_noindex'));
            $this->set_title(__('Search', 'simpelreserveren'));
            $this->hide_footer = 1;
            $this->show_zoeken();
            $this->_exit();
        } elseif ($wp_query->get('arrangementen')) {
            add_action('wp_head', array(&$this, 'meta_noindex'));
            $this->set_title(__('Packages', 'simpelreserveren'));
            $this->hide_footer = 1;
            $this->show_arrangementen();
            $this->_exit();
        } elseif ($wp_query->get('accommodatie')) {
            if ($wp_query->get('id')) {
                $acco = new Accommodatie($wp_query->get('id'));
                wp_redirect($acco->url);
                exit;
            }
            $this->id = $this->_get_accommodatie_id($wp_query->get('name'));
            if ($this->id) {
                $this->hide_footer = 1;
                status_header(200);
                $this->show_accommodatie();
                $this->_exit();
            }
        } elseif ($wp_query->get('arrangement')) {
            $this->id = $this->_get_arrangement_id($wp_query->get('name'));
            if ($this->id) {
                $this->hide_footer = 1;
                status_header(200);
                $this->show_arrangement();
                $this->_exit();
            }
        } elseif ($wp_query->get('camping')) {
            if ($wp_query->get('id')) {
                $camping = new Camping($wp_query->get('id'));
                wp_redirect($camping->url);
                exit;
            }
            $this->id = $this->_get_camping_id($wp_query->get('name'));
            if ($this->id) {
                $this->hide_footer = 1;
                status_header(200);
                $this->show_camping();
                $this->_exit();
            }
        } elseif ($wp_query->get('jaaroverzicht')) {
            $accommodatie = $this->accommodatie = new accommodatie($wp_query->get('id'));
            include('templates/jaaroverzicht.php');
            $this->_exit();
        }
    }

    function meta_noindex()
    {
        echo '<meta name="robots" content="noindex">';
    }

    function _exit($msg = '')
    {
        if ($msg) {
            echo $msg;
        }
        do_action('shutdown');
        exit;
    }

    private function _get_accommodatie_id($name)
    {
        $sql = $this->wpdb->prepare('select id from ' . SIMPEL_DB_PREFIX . 'accommodatie where name = "%s"', $name);

        return $this->wpdb->get_var($sql);
    }

    private function _get_arrangement_id($name)
    {
        $sql = $this->wpdb->prepare('select id from ' . SIMPEL_DB_PREFIX . 'arrangementen where name = "%s"', $name);

        return $this->wpdb->get_var($sql);
    }

    private function _get_camping_id($name)
    {
        $sql = $this->wpdb->prepare('select id from ' . SIMPEL_DB_PREFIX . 'camping where name = "%s"', $name);

        return $this->wpdb->get_var($sql);
    }

    function show_accommodatie()
    {

        // update bekeken met +1
        $sql = $this->wpdb->prepare('update ' . SIMPEL_DB_PREFIX . 'accommodatie set bekeken = bekeken + 1 where id = "%d"', $this->id);
        $this->wpdb->query($sql);

        $accommodatie = $this->accommodatie = new Accommodatie($this->id);
        if (!$accommodatie) {
            return;
        }
        $this->set_title($accommodatie->title);

        // voeg enhanced e-commerce analytics toe
        add_action('wp_head', array('Tag_Manager', 'view'));

        if (version_compare(phpversion(), '5.4.0', '>=')) {
            add_filter('wpseo_canonical', function () {
                    return $this->accommodatie->url;
                });
            add_filter('wpseo_metadesc', function () {
                    return $this->accommodatie->samenvatting;
                });
            add_action('wp_head', function () {
                ?>
                <meta property="og:type" content="product"/>
                <meta property="og:title" content="<?= $this->accommodatie->title ?>"/>
                <meta property="og:url" content="<?= $this->accommodatie->url ?>"/>
                <meta property="og:description" content="<?= $this->accommodatie->samenvatting ?>"/>
                <meta property="og:image" content="<?= $this->accommodatie->afbeelding ?>"/>
            <?php
            });
        }

        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/accommodatie.php';
        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/accommodatie.php');
        }
    }

    function show_arrangement()
    {

        $arrangement = $this->arrangement = new Arrangement($this->id);
        if (!$arrangement) {
            return;
        }
        $this->set_title(utf8_decode($arrangement->title));

        // voeg enhanced e-commerce analytics toe
        add_action('wp_head', array('Tag_Manager', 'view'));

        if (version_compare(phpversion(), '5.4.0', '>=')) {
            add_filter('wpseo_canonical', function () {
                    return $this->arrangement->url;
                });
            add_filter('wpseo_metadesc', function () {
                    return $this->arrangement->samenvatting;
                });
            add_action('wp_head', function () {
                ?>
                <meta property="og:type" content="product"/>
                <meta property="og:title" content="<?= $this->arrangement->title ?>"/>
                <meta property="og:url" content="<?= $this->arrangement->url ?>"/>
                <meta property="og:description" content="<?= $this->arrangement->samenvatting ?>"/>
                <meta property="og:image" content="<?= $this->arrangement->afbeelding ?>"/>
            <?php
            });
        }

        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/arrangement.php';
        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/arrangement.php');
        }
    }

    function show_camping()
    {
        $camping = $this->camping = new Camping($this->id);
        if (!$camping) {
            return;
        }
        $this->set_title($camping->title);

        if (version_compare(phpversion(), '5.4.0', '>=')) {
            add_filter('wpseo_canonical', function () {
                    return $this->camping->url;
                });
            add_filter('wpseo_metadesc', function () {
                    return $this->camping->samenvatting;
                });
            add_action('wp_head', function () {
                ?>
                <meta property="og:type" content="product"/>
                <meta property="og:title" content="<?= $this->camping->title ?>"/>
                <meta property="og:url" content="<?= $this->camping->url ?>"/>
                <meta property="og:description" content="<?= $this->camping->samenvatting ?>"/>
                <meta property="og:image" content="<?= $this->camping->afbeelding ?>"/>
            <?php
            });
        }

        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/camping.php';
        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/camping.php');
        }
    }

    function show_zoeken()
    {
        $zoek = new Zoek();
        $results = $this->results = $zoek->go();

        // zoek types en faciliteiten
        $sql = 'select * from ' . $this->db_prefix . 'acco_type order by title';
        $accommodatie_types = $this->wpdb->get_results($sql);
        $this->types = $this->wpdb->get_results('select * from ' . $this->db_prefix . 'acco_type');
        if (defined('ICL_LANGUAGE_CODE')) {
            foreach ($this->types as $type) {
                $lang_field = 'title_' . ICL_LANGUAGE_CODE;
                if (!empty($type->$lang_field)) {
                    $type->title = $type->$lang_field;
                }
            }
        }

        // voeg enhanced e-commerce analytics toe
        add_action('wp_head', array('Tag_Manager', 'search'));


        $faciliteiten = $zoek->all_faciliteiten;
        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/zoeken.php';

        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/zoeken.php');
        }
    }

    function show_arrangementen()
    {
        $arrangementen = new Arrangementen();
        $results = $this->results = $arrangementen->zoek();


        // voeg enhanced e-commerce analytics toe
        add_action('wp_head', array('Tag_Manager', 'search'));


        $faciliteiten = $zoek->all_faciliteiten;
        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/zoek-arrangementen.php';

        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/zoek-arrangementen.php');
        }
    }

    function show_accommodaties($camping_id)
    {
        $camping = new camping($camping_id);
        foreach ($camping->accommodaties as $acco) {
            $html .= '<div class="acco"><h4><a href="' . $acco->url . '">' . $acco->title . '</a></h4>';
            if ($acco->thumb) {
                $html .= '<img src="' . $acco->thumb . '" alt="' . $acco->title . '" />';
            }
            $html .= '</div>';
        }
        echo $html;
    }

    function show_boeken()
    {
        if (!session_id()) {
            session_start();
        }
        define('NO_CACHE', 1);
        $cookie = json_decode(stripslashes($_COOKIE['simpelreserveren']));

        if (!isset($_GET['id'])) {
            global $wp_query;
            $_GET['id'] = $wp_query->get('id');
            $_GET['stap'] = $stap = $wp_query->get('stap');
        }

        if (isset($_POST['next_step'])) {
            $this->_volgende_stap();
        }

        if (isset($_GET['aankomst'])) {
            $_SESSION['boeken']['aankomst'] = $_GET['aankomst'];
            $_SESSION['boeken']['vertrek'] = $_GET['vertrek'];
            if ($cookie->volw) {
                $_SESSION['boeken']['volw'] = $cookie->volw;
            } elseif (!isset($_SESSION['boeken']['volw'])) {
                $_SESSION['boeken']['volw'] = 2;
            }
        } elseif (!isset($_SESSION['boeken']['aankomst'])) {
            $_SESSION['boeken']['aankomst'] = date('d-m-Y');
            $_SESSION['boeken']['vertrek'] = date('d-m-Y', strtotime('+7 days'));
            //$_SESSION['boeken']['volw'] = 2;
        }
        if (!isset($_SESSION['boeken']['volw'])) {
            //$_SESSION['boeken']['volw'] = 2;
        }
        if (!isset($_SESSION['boeken']['youth'])) {
            $_SESSION['boeken']['youth'] = 0;
        }
        if (!isset($_SESSION['boeken']['baby'])) {
            $_SESSION['boeken']['baby'] = 0;
        }

        if ($_SESSION['boeken']['youth'] || (isset($_SESSION['boeken']['kind']) && $_SESSION['boeken']['kind']) || $_SESSION['boeken']['baby']) {
            $_SESSION['boeken']['has_children'] = 1;
        } else {
            $_SESSION['boeken']['has_children'] = 0;
        }

        $boeken = $_SESSION['boeken'];
        $accommodatie = $this->accommodatie = new Accommodatie($_GET['id']);
        $toeslagen = $this->accommodatie->get_toeslagen($_SESSION['boeken']['aankomst'], $_SESSION['boeken']['vertrek'], $_SESSION['boeken']['volw'] + $_SESSION['boeken']['youth'] + (isset($_SESSION['boeken']['kind']) ? $_SESSION['boeken']['kind'] : 0));

        $this->set_title(__('Book', 'simpelreserveren') . ' ' . $accommodatie->title);


        $stap = $this->stap = $_GET['stap'];
        $hash = '';
        if (isset($_GET['hash'])) {
            $check = sha1(AUTH_SALT . sha1($_GET['boekid']));
            if ($check == $_GET['hash']) {
                $sql = $this->wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'boeking where id = "%d"', $_GET['boekid']);
                $boeken = $this->wpdb->get_row($sql);
                if ($boeken->id) {
                    $_SESSION['boeken']['boeken_id'] = $boeken->id;
                    $hash = 1;
                }
            }
        }
        if (($this->stap < 0 || $this->stap > $this->laatste_stap || ($this->stap > 1 && !$_SESSION['boeken']['totaal']) || ($this->stap == $this->laatste_stap && !$_SESSION['boeken']['email'])) && !$hash) {
            echo "<script>document.location = '" . $accommodatie->boek_url . (isset($_GET['refer']) ? '&refer=' . htmlentities($_GET['refer']) : '') . "';</script>";
            $this->_exit();
        }


        if ($this->stap == $this->laatste_stap && !$hash) {
            $this->accommodatie = &$accommodatie;
            $boek_result = $this->_do_boek();

            $boeken = $this->boeken = $_SESSION['boeken'];
        }

        if ($hash) {
            $kassabon_html = '';
        } else {
            $kassabon_html = $this->_get_kassabon();
        }

        if ($this->stap == 1 || $this->stap == 2) {
            add_action('wp_head', array('Tag_Manager', 'checkout'));
        } elseif ($this->stap == $this->laatste_stap && !$hash) {
            add_action('wp_head', array('Tag_Manager', 'booking'));
        }

        if ($stap == 1) {
            $volgende_stap = $accommodatie->boek_url_2;
        } elseif ($stap == 2) {
            $vorige_stap = $accommodatie->boek_url;
            $volgende_stap = $accommodatie->boek_url_3;
        } elseif ($stap == 3) {
            $vorige_stap = $accommodatie->boek_url_2;
        }
        if ($stap == $this->laatste_stap) {
            $action = site_url() . '/wp-admin/admin-ajax.php';
        } else {
            $action = $volgende_stap;
        }

        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('google-analytics-for-wordpress/googleanalytics.php')) {
            $options = get_option('Yoast_Google_Analytics');
            if (isset($options['uastring']) || $options['uastring'] != '') {
                $ua_code = $options['uastring'];
            }
        }

        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/boeken.php';
        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/boeken.php');
        }

        if ($this->stap == $this->laatste_stap) {
            unset($_SESSION['boeken']);
        }
    }


    function show_accommodatie_afbeeldingen($accommodatie_id)
    {
        $acco = new accommodatie($_GET['id']);
        $html = '<link href="' . $this->plugin_url . 'css/simpel-reserveren.css" type="text/css" rel="stylesheet"/>';
        $html .= '<div id="tabs" class="accommodatie"><h2>' . $acco->title . '</h2>';
        if ($acco->img) {
            $html .= '<img src="' . $acco->img . '" style="float:left;width:300px;" alt="' . $acco->title . '" />';
        }
        $html .= nl2br($acco->omschrijving);
        $html .= '<div class="clear"></div>';
        if (count($acco->afbeeldingen)) {
            $html .= '<h3>Bilder</h3>';
            foreach ($acco->thumbs as $i => $thumb) {
                $html .= '<div class="thumb"><a href="' . $acco->afbeeldingen[$i] . '" class="fancybox"><img src="' . $thumb . '" alt=""/></a></div>';
            }
        }
        $html .= '</div>';
        echo $html;
    }

    function booking_show_user_camping($user)
    {
        ?>
        <table class="form-table">
            <tr>
                <th><label for="camping">Gebruiker heeft recht om de volgende campings te wijzigen:</label></th>
                <td>
                    <select name="camping" id="camping" style="width:300px">
                        <option value="0">Alle campings</option>
                        <?php
                        $campings = $this->wpdb->get_results('select * from ' . $this->db_prefix . 'camping order by title');
                        foreach ($campings as $camping) {
                            ?>
                            <option value="<?php echo $camping->id ?>" <?php echo(get_the_author_meta('camping', $user->ID) == $camping->id ? 'selected' : '') ?>><?php echo $camping->title ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        </table>
    <?php
    }

    function save_user_camping($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        update_usermeta($user_id, 'camping', $_POST['camping']);
    }

    function get_datum(&$aankomst, &$vertrek)
    {
        if (isset($_SESSION['boeken']['aankomst'])) {
            $aankomst = $_SESSION['boeken']['aankomst'];
            $vertrek = $_SESSION['boeken']['vertrek'];
        } else {
            $aankomst = date('d-m-Y');
            $vertrek = date('d-m-Y', strtotime('+7 days'));
        }
    }

    function show_prices($accommodatie_id = null, $camping_id = null, $arrangement_id = null)
    {
        $this->kalender_action = 'get_prices';

        if ($accommodatie_id != null) {
            $accommodatie = new Accommodatie($accommodatie_id);
        } elseif ($camping_id != null) {
            $camping = new Camping($camping_id);
            if (!$camping->id) {
                return;
            }
            $accommodaties = $camping->accommodaties;
            $accommodatie = $accommodaties[0];
        } else {
            $arrangement = new Arrangement($arrangement_id);
        }

        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/kalender.php';
        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/kalender.php');
        }
    }

    function set_beschikbaarheid()
    {
        global $wpdb;

        $beschikbaarheid_table_name = $wpdb->prefix . $this->plugin_db_prefix . "beschikbaarheid";
        $date = strtotime($_POST['date']);
        $dagen = $wpdb->get_var($wpdb->prepare("select dagen from $beschikbaarheid_table_name where jaar = %d and accommodatie_id = %d", date("Y", $date), $_POST['id']));
        if (!$dagen) {
            $dagen = "";
            for ($i = 0; $i < 366; $i++) {
                $dagen .= "A";
            }
            $insert = 1;
        }
        $dagen[date("z", $date)] = ($_POST['beschikbaar'] == 'true' ? "A" : "O");
        $fields = array(
            "accommodatie_id" => $_POST['id'],
            "dagen" => $dagen,
            "jaar" => date("Y", $date)
        );

        if ($insert) {
            $wpdb->insert($beschikbaarheid_table_name, $fields);
        } else {
            $wpdb->update($beschikbaarheid_table_name, $fields, array("accommodatie_id" => $_POST['id'], "jaar" => date("Y", $date)));
        }
        $this->_exit();
    }

    function update_cal()
    {
        $date = strtotime(sprintf("%04d-%02d-%02d", $_POST['year'], $_POST['month'], 1));
        if ($_POST['js_action'] == 'prijzen') {
            $this->kalender_action = 'get_prices';
        }
        echo $this->show_beschikbaarheid($_POST['id'], $date, $_POST['nr_months']);
        $this->_exit();
    }

    function get_prices($type = 'acco')
    {
        if (filter_input(INPUT_POST, 'show') == 'prices') {
            $acco = new Accommodatie($_POST['accommodatie_id']);
        } else
            $arrangement = new Arrangement(filter_input(INPUT_POST, 'arrangement_id'));
        if (!isset($_SESSION['boeken']['volw'])) {
            $_SESSION['boeken']['volw'] = 2;
        }
        if (!isset($_SESSION['boeken']['youth'])) {
            $_SESSION['boeken']['youth'] = 0;
        }
        if (!isset($_SESSION['boeken']['kind'])) {
            $_SESSION['boeken']['kind'] = 0;
        }
	    if (isset($_POST['volw']) && is_numeric($_POST['volw'])) {
		    $_SESSION['boeken']['volw'] = $_POST['volw'];
	    }
	    if (isset($_POST['kind']) && is_numeric($_POST['kind'])) {
		    $_SESSION['boeken']['kind'] = $_POST['kind'];
	    }

        if (isset($acco)) {
            $prijs = $acco->get_prijs($_POST['aankomst'], $_POST['vertrek'], $_SESSION['boeken']['volw'] + $_SESSION['boeken']['youth'], $_SESSION['boeken']['kind'], 0, 1, isset($_POST['arrangement_id']) ? $_POST['arrangement_id'] : 0);

            $html = '<div class="sr-boeken-prijs">';
            if ($acco->alternative && $prijs) {
                $html .= '<p class="alert alert-warning no-margin">' . __('No price for', 'simpelreserveren') . '<br/>' . date('d-m-Y', $_POST['aankomst']) . ' ' . __('till', 'simpelreserveren') . ' ' . date('d-m-Y', $_POST['vertrek']) . '</p>';
                $html .= '<p class="head">' . date('d-m-Y', strtotime($acco->van)) . ' ' . __('till', 'simpelreserveren') . ' ' . date('d-m-Y', strtotime($acco->tot)) . ':</p>';
                $_SESSION['boeken']['aankomst'] = date('d-m-Y', $_POST['aankomst']);
                $_SESSION['boeken']['vertrek'] = date('d-m-Y', $_POST['vertrek']);
                $_POST['aankomst'] = strtotime($acco->van);
                $_POST['vertrek'] = strtotime($acco->tot);
            } else {
                $_SESSION['boeken']['aankomst'] = date('d-m-Y', $_POST['aankomst']);
                $_SESSION['boeken']['vertrek'] = date('d-m-Y', $_POST['vertrek']);
                $html .= '<p class="head">' . date('d-m-Y', $_POST['aankomst']) . ' ' . __('till', 'simpelreserveren') . ' ' . date('d-m-Y', $_POST['vertrek']) . ':</p>';
            }
            if (!$prijs || $acco->error) {
                $html .= '<p>' . __('No price found.', 'simpelreserveren') . '</p></div>';
            } else {
                $html .= '<p>';
                if ($acco->korting) {
                    $html .= '<span class="sr-boeken-prijs-van">&euro; ' . number_format($prijs + $acco->korting, 2, ',', '.') . '</span> ';
                }
                $html .= '<span class="sr-boeken-prijs-voor">&euro; ' . number_format($prijs, 2, ',', '.') . '</span></p>';
                $html .= '</div>';
                $html .= '<div class="sr-boeken-button">';
                $html .= '<button class="btn sr-primary-button" onclick="document.location=\'' . $acco->boek_url . '?aankomst=' . date('d-m-Y', $_POST['aankomst']) . '&vertrek=' . date('d-m-Y',
                        $_POST['vertrek']) . ($acco->arrangement ? '&arrangement=' . $acco->arrangement->id : '') . '\'" >' . __('Book now', 'simpelreserveren') . '</button>';
                $html .= '</div>';

                if ($acco->arrangement) {
                    $html .= '<div style="clear:both"></div>';
                    $html .= '<div class="alert alert-success">' . __('Included',
                            'simpelreserveren') . ' ' . utf8_decode($acco->arrangement->title) . ' <span class="fa fa-info-circle" title="' . utf8_decode(nl2br($acco->arrangement->omschrijving)) . '"></span></div>';
                }
            }
        } else {
            // Arrangementen pagina, dus alle accommdaties tonen
            $accommodaties = $arrangement->get_possible_accommodaties();
            $html = '';
            foreach ($accommodaties as $acco) {
                $prijs = $acco->get_prijs($_POST['aankomst'], $_POST['vertrek'], $_SESSION['boeken']['volw'] + $_SESSION['boeken']['youth'], $_SESSION['boeken']['kind'], 0, 1, $arrangement->id);

                if ($prijs) {
                    $html .= '<div class="sr-boeken-prijs">';
                    $html .= '<div class="arr-acco"><a href="' . $acco->url .'?arrangement='.$arrangement->id.'">' . $acco->title . '</a></div>';
                    $html .= '<p>';
                    if ($acco->korting) {
                        $html .= '<span class="sr-boeken-prijs-van">&euro; ' . number_format($prijs + $acco->korting, 2, ',', '.') . '</span> ';
                    }
                    $html .= '<span class="sr-boeken-prijs-voor"><strong>&euro; ' . number_format($prijs, 2, ',', '.') . '</strong></span></p>';
                    $html .= '</div>';
                    $html .= '<div class="sr-boeken-button">';
                    $html .= '<button class="btn sr-primary-button" onclick="document.location=\'' . $acco->boek_url . '?aankomst=' . date('d-m-Y', $_POST['aankomst']) . '&vertrek=' . date('d-m-Y',
                            $_POST['vertrek']) . '&arrangement=' . $arrangement->id  . '\'" >' . __('Book now', 'simpelreserveren') . '</button>';
                    $html .= '</div>';

                    $html .= '<div class="clear"></div>';
                }
            }
            $html .= '</div><div class="clear"></div>';
        }



        if (isset($_POST['action']) && !isset($_POST['params'])) {
            echo $html;
            $this->_exit();
        }

        return $html;
    }

    function get_beschikbaarheid()
    {
        $accommodatie = new Accommodatie($_POST['accommodatie_id']);
        echo json_encode($accommodatie->beschikbaarheid);
        $this->_exit();
    }

    function show_prijzen($accommodatie_id, $datum = null)
    {
        $accommodatie = new Accommodatie($accommodatie_id);
        if (!$accommodatie->id) {
            return 'Accommodatie niet gevonden.';
        }
        if ($datum == null) {
            if ($_SESSION['boeken']['aankomst']) {
                $datum = $_SESSION['boeken']['aankomst'];
            } else {
                $datum = date('d-m-Y');
            }
        }
        $_POST['accommodatie_id'] = $accommodatie->id;
        $_POST['date'] = $datum;
        //$html = '<div class="price-head">Prijzen ' . $accommodatie->title . ' vanaf ' . $datum . ':</div>';
        $html .= $this->get_prices();

        return $html;
    }


    function get_kassabon()
    {
        if (isset($_GET['aankomst'])) {
            $_SESSION['boeken']['aankomst'] = $_GET['aankomst'];
            $_SESSION['boeken']['vertrek'] = $_GET['vertrek'];
            if (isset($_REQUEST['volw'])) {
                $_SESSION['boeken']['volw'] = $_REQUEST['volw'];
            }
            if (isset($_REQUEST['youth'])) {
                $_SESSION['boeken']['youth'] = $_REQUEST['youth'];
            }
            if (isset($_REQUEST['kind'])) {
                $_SESSION['boeken']['kind'] = $_REQUEST['kind'];
            }
            if (isset($_REQUEST['baby'])) {
                $_SESSION['boeken']['baby'] = $_REQUEST['baby'];
            }

        } else {
            $field = $_GET['field'];
            if ($_GET['checked'] != '') {
                $_SESSION['boeken'][$field] = ($_GET['checked'] == 'true');
            } else {
                $_SESSION['boeken'][$field] = $_GET['value'];
            }
        }
        $this->accommodatie = new Accommodatie($_GET['id']);
        $result = array(
            'html' => $this->_get_kassabon(),
            'prijs' => sprintf('%0.2f', $_SESSION['boeken']['totaal_incl_borgsom'])
        );
        echo json_encode($result);
        $this->_exit();
    }

    function _get_kassabon($mode = 'site')
    {
        if (isset($_GET['arrangement'])) {
            $_SESSION['boeken']['arrangement'] = $_GET['arrangement'];
        }

        // get accommodatie prijs
        $totaal = 0;
        $this->accommodatie->korting = 0;
        $volw = $_SESSION['boeken']['volw'];
        $kind = (isset($_SESSION['boeken']['kind']) ? $_SESSION['boeken']['kind'] : 0);
        if ($this->accommodatie->camping->youth_as_child) {
            $kind += $_SESSION['boeken']['youth'];
        } else {
            $volw += $_SESSION['boeken']['youth'];
        }
        $prijs = $this->accommodatie_prijs = $this->accommodatie->get_prijs($_SESSION['boeken']['aankomst'], $_SESSION['boeken']['vertrek'], $volw, $kind, 1, 0, isset($_SESSION['boeken']['arrangement']) ? $_SESSION['boeken']['arrangement'] : 0);
        $_SESSION['boeken']['prijs'] = $prijs + 0;
        if ($this->accommodatie->error) {
            $_SESSION['boeken']['totaal'] = 0;
            $html = '<table class="table"><tr><td><strong>Geen prijs gevonden.</strong><br/>' . $this->accommodatie->error . '</td></tr></table>';

            return $html;
        }

        $html = ($mode == 'site' ? '<table class="table">' : '');
        if ($prijs && !$this->accommodatie->prijs_melding) {
            if (isset($this->stap) && $this->stap == $this->laatste_stap && $mode == 'site') {
                $html .= '<tr><td><h3>GEBOEKT</h3></td><td class="print"><a href="javascript:print()"><span class="glyphicon glyphicon-print"></span> print</a></td></tr>';
            }
            $totaal = $prijs;
            if ($this->accommodatie->arrangement) {
                $html .= '<tr><td colspan="2">';
                $html .= '<div class="alert alert-success">' . __('Included',
                        'simpelreserveren') . ' ' . utf8_decode($this->accommodatie->arrangement->title) . ' <span class="fa fa-info-circle sr-tip" title="' . utf8_decode(nl2br($this->accommodatie->arrangement->omschrijving)) . '"></span></div>';
                $html .= '</td></tr>';
            }

            $html .= '<tr><td><div class="acco">' . $this->accommodatie->title . '</div><div class="datum">';

            // kijk of er ook arrangementen zijn geboekt
            $arrangementen = array();
            foreach ($this->accommodatie->path as $path_item) {
                if (!in_array($path_item->periode->naam, $arrangementen) && $path_item->periode->naam) {
                    $arrangementen[] = $path_item->periode->naam;
                }
            }
            if (count($arrangementen)) {
                $html .= 'Arrangement: ' . implode(', ', $arrangementen) . '<br/>';
            }
            $html .= 'van ' . $_SESSION['boeken']['aankomst'] . ' ' . $this->accommodatie->aankomst_tijd . '<br/> tot ' . $_SESSION['boeken']['vertrek'] . ' ' . $this->accommodatie->vertrek_tijd . '<br/>';
            $html .= $_SESSION['boeken']['volw'] . ' ' . __('adults', 'simpelreserveren') .
                ($_SESSION['boeken']['youth'] > 0 ? '<br/>' . $_SESSION['boeken']['youth'] . ' ' . __('youth', 'simpelreserveren') . ' (' . $this->accommodatie->camping->age_youth . ' ' . __('year', 'simpelreserveren') . ')' : '') .
                (isset($_SESSION['boeken']['kind']) && $_SESSION['boeken']['kind'] > 0 ? '<br/>' . $_SESSION['boeken']['kind'] . ' ' . __('children', 'simpelreserveren') . ' (' . $this->accommodatie->camping->age_child . ' ' . __('year',
                        'simpelreserveren') . ')' : '') .
                ($_SESSION['boeken']['baby'] > 0 ? '<br/>' . $_SESSION['boeken']['baby'] . ' ' . __('babies', 'simpelreserveren') . ' (' . $this->accommodatie->camping->age_baby . ' ' . __('year', 'simpelreserveren') . ')' : '') .
                '</div></td><td class="euro" valign="top" width="90">';
            if ($this->accommodatie->korting) {
                $html .= '<div class="sr-boeken-prijs-van">&euro; ' . number_format($this->accommodatie->korting + $prijs, 2, ',',
                        '.') . '</div><div class="sr-discount omschrijving">' . (isset($this->accommodatie->aanbieding->omschrijving) ? $this->accommodatie->aanbieding->omschrijving : '') . '</div>';
            }
            $html .= '<span class="sr-boeken-prijs-voor">&euro; ' . number_format($prijs, 2, ',', '.') . '</span></td></tr>';

            $ter_plaatse_html = $borgsom_html = '';
            $ter_plaatse_totaal = $borgsom_totaal = 0;

            $this->toeslagen = array();
            $toeslagen = $this->accommodatie->get_toeslagen($_SESSION['boeken']['aankomst'], $_SESSION['boeken']['vertrek'], $_SESSION['boeken']['volw'] + $_SESSION['boeken']['youth'] + (isset($_SESSION['boeken']['kind']) ? $_SESSION['boeken']['kind'] : 0));
            foreach ($toeslagen as $toeslag) {
                $field = 'toeslag-' . $toeslag->id;
                if ((isset($_SESSION['boeken'][$field]) && $_SESSION['boeken'][$field]) || $toeslag->verplicht) {
                    if ($toeslag->verplicht && $this->accommodatie->incl_toeslagen) {
                        continue;
                    }
                    $prefix = '';
                    if ($toeslag->type == 'aantal') {
                        $prefix = $_SESSION['boeken'][$field] . ' x ';
                        $toeslag->aantal = $_SESSION['boeken'][$field];
                        $toeslag->totaal_prijs *= $_SESSION['boeken'][$field];
                    }
                    $line = '<tr><td><div>' . $prefix . $toeslag->title . '</div></td><td class="euro">&euro; ' . number_format($toeslag->totaal_prijs, 2, ',', '.') . '</td></tr>';
                    if ($toeslag->borgsom) {
                        $borgsom_html .= $line;
                        $borgsom_totaal += $toeslag->totaal_prijs;
                    } elseif ($toeslag->ter_plaatse_betalen) {
                        $ter_plaatse_html .= $line;
                        $ter_plaatse_totaal += $toeslag->totaal_prijs;
                    } else {
                        $html .= $line;
                        $this->toeslagen[] = $toeslag;
                        $totaal += $toeslag->totaal_prijs;
                    }
                }
            }

            $html .= '<tr class="sr-totaal"><td>' . ($borgsom_totaal ? 'Subtotaal' : 'Totaal') . '</td><td class="sr-boeken-prijs-voor">&euro; ' . number_format($totaal, 2, ',', '.') . '</td></tr>';

            if ($borgsom_html) {
                $html .= '<tr><td colspan="2"><br/><b>Borgsom:</b></td></tr>' . $borgsom_html;
                $html .= '<tr class="sr-totaal sr-boeken"><td>Totaal</td><td class="sr-boeken-prijs-voor">&euro; ' . number_format($borgsom_totaal + $totaal, 2, ',', '.') . '</td></tr>';
            }
            if ($ter_plaatse_html) {
                $html .= '<tr><td colspan="2"><br/><b>Ter plaatse betalen:</b></td></tr>' . $ter_plaatse_html;
                $html .= '<tr class="sr-totaal sr-boeken"><td>Totaal</td><td class="sr-boeken-prijs-voor">&euro; ' . number_format($ter_plaatse_totaal, 2, ',', '.') . '</td></tr>';
            }
            $_SESSION['boeken']['totaal_incl_borgsom'] = ($borgsom_totaal + $totaal);
            if ($mode == 'site') {
                $html .= '<tr><td colspan="2" class="beste-prijs"><span class="pull-right"><span class="pull-left"><img src="' . $this->plugin_url . '/images/valid.png" alt=""/></span> ' . __('Best price guarantee', 'simpelreserveren') . '</span></td></tr>';
                if (!defined('SIMPEL_HIDE_LOGO') || !SIMPEL_HIDE_LOGO) {
                    $html .= '<tr><td colspan="2" class="sr-credits">' . __('We work together with',
                            'simpelreserveren') . ' <a href="http://www.simpelreserveren.nl/" target="_blank"><img src="' . $this->plugin_url . '/images/sr-credits.png" height="16" width="183"/></a></td></tr>';
                }
            }
        } else {
            $html .= '<tr><td class="msg"><strong>Geen prijs gevonden.</strong><br/>' . $this->accommodatie->prijs_melding . '</td></tr>';
        }
        $_SESSION['boeken']['totaal'] = $totaal + 0;
        if ($mode == 'site') {
            $html .= '</table>';
        }
        if (isset($this->stap) && $this->stap == $this->laatste_stap) {
            //unset($ _ SESSION['boeken']);
        }

        return $html;
    }

    function _volgende_stap()
    {
        foreach ($_POST as $key => $val) {
            $_SESSION['boeken'][$key] = $val;
        }
        wp_redirect($_POST['next_step']);
        $this->_exit();
    }

    function form_save_naw()
    {
        $boeken = $this->wpdb->get_row($this->wpdb->prepare('select * from ' . $this->db_prefix . 'boeking where id = "%d"', $_POST['boeken-id']));
        if ($boeken->update_send) {
            return;
        }

        $this->accommodatie = $accommodatie = new Accommodatie($_POST['id']);
        $fields = array(
            'adres' => $_POST['adres'],
            'postcode' => $_POST['postcode'],
            'plaats' => $_POST['plaats'],
            'factuur_per_post' => ($_POST['factuur-per-post'] ? 1 : 0),
            'update_send' => '1',
        );
        $this->wpdb->show_errors();
        $this->wpdb->update($this->db_prefix . 'boeking', $fields, array('id' => $_POST['boeken-id']));

        $boeken = $this->wpdb->get_row($this->wpdb->prepare('select * from ' . $this->db_prefix . 'boeking where id = "%d"', $_POST['boeken-id']));
        ob_start();
        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/mail_boeken_update.php';
        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/mail_boeken_update.php');
        }

        $html = ob_get_contents();
        ob_end_clean();

        $headers = 'From: ' . $this->accommodatie->camping->title . ' <' . $this->accommodatie->camping->email . '>' . "\r\n";
        $subject = 'Update gegevens boeking ' . $fields['voornaam'] . ' ' . $fields['achternaam'];
        add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
        wp_mail($this->accommodatie->camping->email, $subject, $html, $headers);
        wp_mail($boeken->email, $subject, $html, $headers);
    }

    function _do_boek($site = '')
    {
        $kassabon = $this->_get_kassabon('mail');

        return $this->prijsberekening->do_boek($this->accommodatie, $kassabon, $site);
    }

    function _set_zoek_vars(&$accommodatie = null)
    {
        $std_aantal_nachten = $this->get_setting('std-aantal-nachten');
        if (!$std_aantal_nachten) {
            $std_aantal_nachten = 7;
        }

        if (isset($_GET['aankomst']) && !empty($_GET['aankomst'])) {
            $this->aankomst = $_GET['aankomst'];
            $this->vertrek = $_GET['vertrek'];
            $this->volw = $_GET['volw'];
            $this->kind = (isset($_GET['kind']) ? $_GET['kind'] : 0);
            $this->type = (isset($_GET['type']) ? $_GET['type'] : '');
        } elseif (isset($_SESSION['boeken']['aankomst']) && !empty($_SESSION['boeken']['aankomst'])) {
            $this->aankomst = $_SESSION['boeken']['aankomst'];
            $this->vertrek = $_SESSION['boeken']['vertrek'];
            $this->volw = $_SESSION['boeken']['volw'];
            $this->kind = $_SESSION['boeken']['kind'];
            $this->type = $_SESSION['boeken']['type'];
        } else {
            $vanaf = $this->get_setting('vanaf-datum');
            $this->aankomst = date('d-m-Y');
            $this->vertrek = date('d-m-Y', strtotime('+' . $std_aantal_nachten . ' days', time()));
            $this->volw = 2;
            $this->kind = 0;
        }

        if (strtotime($this->aankomst) < time()) {
            $this->aankomst = date('d-m-Y');
            $this->vertrek = date('d-m-Y', strtotime('+' . $std_aantal_nachten . ' days'));
        }


        if ($accommodatie != null) {
            if (strtotime($this->aankomst) < time()) {
                $this->aankomst = date('d-m-Y');
                $this->vertrek = date('d-m-Y', strtotime('+' . $this->get_setting('std-aantal-nachten') . ' days', strtotime($this->aankomst)));
            }
            $year = date('Y', strtotime($this->aankomst));
            $beschikbaarheid = $accommodatie->beschikbaarheid[$year];
            $z = date('z', strtotime($this->aankomst));
            if ($beschikbaarheid[$z] != 'X') {
                while ($beschikbaarheid[$z] != 'X') {
                    if ($z > 366) {
                        break;
                    }
                    $z++;
                }
                if ($z < 366) {
                    $this->aankomst = date('d-m-Y', strtotime('+' . $z . ' days', strtotime($year . '-01-01')));
                    $this->vertrek = date('d-m-Y', strtotime('+' . $this->get_setting('std-aantal-nachten') . ' days', strtotime($this->aankomst)));
                }
            }
        }

        $this->types = $this->wpdb->get_results('select * from ' . $this->db_prefix . 'acco_type');
        if (defined('ICL_LANGUAGE_CODE')) {
            foreach ($this->types as $type) {
                $lang_field = 'title_' . ICL_LANGUAGE_CODE;
                if (!empty($type->$lang_field)) {
                    $type->title = $type->$lang_field;
                }
            }
        }


        $_SESSION['boeken']['aankomst'] = $this->aankomst;
        $_SESSION['boeken']['vertrek'] = $this->vertrek;
        $_SESSION['boeken']['volw'] = $this->volw;
        $_SESSION['boeken']['kind'] = $this->kind;
    }

    function search_block($atts = array())
    {
        extract(shortcode_atts(array('mode' => 'horizontal'), $atts));
        $this->types = $this->wpdb->get_results('select * from ' . SIMPEL_DB_PREFIX . 'acco_type');
        if (defined('ICL_LANGUAGE_CODE')) {
            foreach ($this->types as $type) {
                $lang_field = 'title_' . ICL_LANGUAGE_CODE;
                if (!empty($type->$lang_field)) {
                    $type->title = $type->$lang_field;
                }
            }
        }
        ob_start();
        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/zoek-blok.php';
        if (file_exists($theme_file)) {
            include($theme_file);
        } elseif (file_exists(dirname(__FILE__) . '/templates/zoek-blok.php')) {
            include(dirname(__FILE__) . '/templates/zoek-blok.php');
        }
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    function camping_block($atts = array())
    {
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'camping order by rand()';
        $all = $this->wpdb->get_results($sql);
        $campings = array();
        foreach ($all as $row) {
            $campings[] = new Camping($row->id);
        }
        ob_start();
        $theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/camping-blok.php';
        if (file_exists($theme_file)) {
            include($theme_file);
        } else {
            include('templates/camping-blok.php');
        }
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    function lastminutes_block($atts = array())
    {
        if (!$atts['nr']) {
            $atts['nr'] = 3;
        }

        $sql = 'select * from ' . $this->db_prefix . 'prijs pr 
			inner join ' . $this->db_prefix . 'periode pe on pr.periode_id = pe.id 
			where pe.tot >= now() and pe.van < "' . date('Y-m-d', strtotime('+1 month')) . '" and
				(pr.nachtaanbieding > 0 or pr.weekaanbieding > 0 or pr.weekaanbieding > 0 or pr.midweekaanbieding > 0 or pr.periodeaanbieding > 0)
			group by pr.accommodatie_id
			order by rand()
			limit ' . $atts['nr'];

        $last_minutes = $this->wpdb->get_results($sql);
        $results = array();
        foreach ($last_minutes as $row) {
            $acco = new Accommodatie($row->accommodatie_id);

            if ($row->van < date('Y-m-d')) {
                $acco->van = date('Y-m-d');
            } else {
                $acco->van = $row->van;
            }

            if ($row->nachtaanbieding > 0) {
                $acco->prijs_orig = $row->nachtprijs;
                $acco->prijs = $row->nachtaanbieding;
                $acco->prijstype = 'nacht';
                $acco->melding = 'Prijs voor 1 nacht in de periode van ' . date('d-m-Y', strtotime($row->van)) . ' tot ' . date('d-m-Y', strtotime($row->tot));
                $acco->tot = date('Y-m-d', strtotime('+' . $acco->min_aantal_nacht . ' days', strtotime($acco->van)));
            } elseif ($row->weekaanbieding > 0) {
                $acco->prijs_orig = $row->weekprijs;
                $acco->prijs = $row->weekaanbieding;
                $acco->prijstype = 'week';
                $acco->melding = 'Prijs voor een week in de periode van ' . date('d-m-Y', strtotime($row->van)) . ' tot ' . date('d-m-Y', strtotime($row->tot));
                $acco->tot = date('Y-m-d', strtotime('+1 week', strtotime($acco->van)));
            } elseif ($row->weekendaanbieding > 0) {
                $acco->prijs_orig = $row->weekendprijs;
                $acco->prijs = $row->weekendaanbieding;
                $acco->prijstype = 'weekend';

                // zorg ervoor dat de startdag een vrijdag is
                while (date('w', strtotime($acco->van)) != 5) {
                    $acco->van = strtotime('+1 day', strtotime($acco->van));
                }
                $acco->tot = date('Y-m-d', strtotime('+3 days', strtotime($acco->van)));
            } elseif ($row->midweekaanbieding > 0) {
                $acco->prijs_orig = $row->midweekprijs;
                $acco->prijs = $row->midweekaanbieding;
                $acco->prijstype = 'midweek';

                // zorg ervoor dat de startdag een vrijdag is
                while (date('w', strtotime($acco->van)) != 1) {
                    $acco->van = strtotime('+1 day', strtotime($acco->van));
                }
                $acco->tot = date('Y-m-d', strtotime('+4 days', strtotime($acco->van)));
            } else {
                $acco->prijs_orig = $row->periodeprijs;
                $acco->prijs = $row->periodeaanbieding;
                $acco->prijstype = 'periode';
                $acco->melding = 'Prijs voor ' . date('d-m-Y', strtotime($row->van)) . ' tot ' . date('d-m-Y', strtotime($row->tot));
                $acco->van = $row->van;
                $acco->tot = $row->tot;
            }
            $results[] = $acco;
        }

        ob_start();
        include('templates/last-minutes.php');
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    function ajax_get_accommodatie_arrangementen()
    {
        $cookie = json_decode(stripslashes($_COOKIE['simpelreserveren']));
        $accommodatie = new Accommodatie($_POST['id']);
        $arrangementen = $accommodatie->get_arrangementen($cookie->aankomst);
        if (file_exists(dirname(__FILE__) . '/templates/accommodatie_arrangementen.php')) {
            include(dirname(__FILE__) . '/templates/accommodatie_arrangementen.php');
        } elseif (file_exists($theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/accommodatie_arrangementen.php')) {
            include($theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/accommodatie_arrangementen.php');
        }

        exit;
    }

    function ajax_get_accommodatie_alternatieve_prijzen()
    {
        if (defined('SIMPEL_HIDE_ALTERNATIVE') && SIMPEL_HIDE_ALTERNATIVE) {
            exit;
        }
        $cookie = json_decode(stripslashes($_COOKIE['simpelreserveren']));
        $accommodatie = new Accommodatie($_POST['id']);
        $alternatieve_prijzen = $accommodatie->get_alternatieve_prijzen($cookie->aankomst, $cookie->vertrek);

        if (file_exists(dirname(__FILE__) . '/templates/accommodatie_alternatieve_prijzen.php')) {
            include(dirname(__FILE__) . '/templates/accommodatie_alternatieve_prijzen.php');
        } elseif (file_exists(get_theme_root() . '/' . get_template() . '/simpel-reserveren/accommodatie_alternatieve_prijzen.php')) {
            include(get_theme_root() . '/' . get_template() . '/simpel-reserveren/ajax_get_accommodatie_alternatieve_prijzen.php');
        }

        exit;

    }

    function arrangementen_block($atts = array())
    {
        if (!$atts['nr']) {
            $atts['nr'] = 2;
        }
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'toeslagen where arrangement = 1 ' . (SIMPEL_MULTIPLE ? ' group by camping_id ' : '') . ' order by rand() limit ' . $atts['nr'];
        $arrangementen = $this->wpdb->get_results($sql);
        foreach ($arrangementen as $item) {
            $upload_dir = wp_upload_dir();
            $item->img_dir = $upload_dir['baseurl'] . '/toeslagen/' . $item->id . '/';
            if ($item->afbeelding) {
                $item->img = $item->img_dir . $item->afbeelding;
            }
            $item->prijs = $item->prijs_camping;

            $sql = 'select id from ' . SIMPEL_DB_PREFIX . 'accommodatie where camping_id = "' . $item->camping_id . '" order by rand() limit 1';
            $acco_id = $this->wpdb->get_var($sql);
            $item->accommodatie = new Accommodatie($acco_id);
        }

        ob_start();
        include('templates/arrangementen.php');
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    function check_beschikbaarheid()
    {
        $this->accommodatie = new Accommodatie($_POST['id']);
        $prijs = $this->accommodatie->get_prijs($_POST['aankomst'], $_POST['vertrek']);
        $result = ($prijs && !$accommodatie->error);
        echo json_encode(array($_POST['fieldId'], $result));
        $this->_exit();
    }


    function get_setting($field, $lang = null)
    {
        $sql = $this->wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'settings where `field` = "%s"', $field);
        $row = $this->wpdb->get_row($sql);
        if ($row && defined('ICL_LANGUAGE_CODE') && $row->multilang) {
            $field = 'value_' . ICL_LANGUAGE_CODE;
            if (isset($row->$field)) {
                return $row->$field;
            }
        }
        if ($row) {
            return $row->value;
        }
    }

    function show_message($place)
    {
        //$this->get_datum($aankomst, $vertrek);
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'meldingen where van <= "' . date('Y-m-d') . '" and tot > "' . date('Y-m-d') . '" and plaats like "%' . $place . '%"';
        $melding = $this->wpdb->get_row($sql);

        $title = utf8_decode($melding->title);
        if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != 'nl') {
            $title_fld = 'title_' . ICL_LANGUAGE_CODE;
            $title = utf8_decode($melding->$title_fld);
        }
        if ($melding && $melding->title) {
            return '<div class="simpel-melding no-margin alert ' . $melding->type . '">' . $title . '</div>';
        }
    }


    function get_campings()
    {
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'camping order by title';
        $result = $this->wpdb->get_results($sql);
        $campings = array();
        foreach ($result as $row) {
            $campings[] = new Camping($row->id);
        }

        return $campings;
    }

    function get_periodes()
    {
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'periode where tot > now() order by van';
        $results = $this->wpdb->get_results($sql);

        return $results;
    }

    function get_accommodaties($nr = 3, $order = 'random', $idkey = false)
    {
        $accommodaties = array();
        switch ($order) {
            case 'meest bekeken':
                $sql_order = 'order by bekeken desc';
                break;

            case 'random':
                $sql_order = 'order by rand()';
                break;

            case 'title':
                $sql_order = 'order by title';
                break;

            case 'seq_inner':
                $sql_order = 'order by seq_inner asc';
                break;
        }
        $where = '';
        if (SIMPEL_MULTIPLE && SIMPEL_SHOW_CAMPING) {
            $where = ' and camping_id = "' . SIMPEL_SHOW_CAMPING . '"';
        }
        if ($order == 'arrangementen') {
            $sql = 'select *
                from ' . SIMPEL_DB_PREFIX . 'nachtprijzen 
                where datum > now() and type = "periode" ' . $where . '
                order by datum';
        } elseif ($order == 'geboekt') {
            $sql = 'select accommodatie_id as id from ' . SIMPEL_DB_PREFIX . 'boeking where 1=1 ' . $where . ' order by datum_boeking DESC limit ' . $nr;
        } else {
            $sql = 'select id from ' . SIMPEL_DB_PREFIX . 'accommodatie  where 1=1 ' . $where . ' ' . $sql_order . ' limit ' . $nr;
        }
        $results = $this->wpdb->get_results($sql);
        foreach ($results as $row) {
            if ($order == 'arrangementen') {
                $found = 0;
                foreach ($accommodaties as $acco) {
                    if ($row->accommodatie_id == $acco->id) {
                        $found = 1;
                    }
                }
                if ($found) {
                    continue;
                }
                $accommodatie = new Accommodatie($row->accommodatie_id);
                $accommodatie->periode = $this->wpdb->get_row('select * from ' . SIMPEL_DB_PREFIX . 'periode where id = "' . $row->periode_id . '"');
                $accommodatie->prijs_row = $this->wpdb->get_row('select * from ' . SIMPEL_DB_PREFIX . 'prijs where periode_id = "' . $row->periode_id . '" and accommodatie_id = "' . $row->accommodatie_id . '"');
                $accommodatie->prijs = ($accommodatie->prijs_row->periodeaanbieding > 0 ? $accommodatie->prijs_row->periodeaanbieding : $accommodatie->prijs_row->periodeprijs);
            } else {
                $accommodatie = new Accommodatie($row->id);
                $accommodatie->prijs = $accommodatie->vanaf_prijs;
            }

            if ($idkey) {
                $accommodaties[$accommodatie->id] = $accommodatie->title;
            } else {
                $accommodaties[] = $accommodatie;
            }
            if (count($accommodaties) == $nr) {
                break;
            }
        }

        return $accommodaties;
    }

    function sitemap()
    {
        $html = '<h2>' . __('Campings', 'simpelreserveren') . '</h2><ul>';
        $campings = $this->get_campings();
        foreach ($campings as $camping) {
            $html .= '<li><a href="' . $camping->url . '">' . $camping->title . '</a><ul>';
            foreach ($camping->accommodaties as $acco) {
                $html .= '<li><a href="' . $acco->url . '">' . $acco->title . '</a></li>';
            }
            $html .= '</ul></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    function get_types()
    {
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'acco_type order by title';

        return $this->wpdb->get_results($sql);
    }

    function get_camping($id = null)
    {
        if (SIMPEL_MULTIPLE && SIMPEL_SHOW_CAMPING) {
            $camping = new Camping(SIMPEL_SHOW_CAMPING);
        } else {
            $camping = new Camping($id);
        }

        return $camping;
    }

    function switch_to_blog($camping_id)
    {
        $sql = 'select meta_value from wp_usermeta where meta_key = "primary_blog" and user_id = (select user_id from wp_usermeta where meta_key = "camping" and meta_value = "' . $camping_id . '")';
        $blog_id = $this->wpdb->get_var($sql);
        if ($blog_id) {
            switch_to_blog($blog_id);
        }
    }

    function ajax_get_arr_accommodaties()
    {
        $arrangement_id = filter_input(INPUT_POST, 'arrangement');
        $aankomst = filter_input(INPUT_POST, 'aankomst');
        $vertrek = filter_input(INPUT_POST, 'vertrek');
        $arrangementen = new Arrangementen();
        $accommodaties = $arrangementen->get_accommodaties($aankomst, $vertrek, $arrangement_id);
        foreach ($accommodaties as $accommodatie) : ?>
            <div class="sr-acco">
                <h3><a href="<?= $accommodatie->url ?>?arrangement=<?= $arrangement_id ?>"><?= $accommodatie->title ?></a></h3>
                <img src="<?= $accommodatie->img ?>"> <?= $accommodatie->samenvatting ?>
                <a class="btn btn-primary" href="<?= $accommodatie->boek_url ?>?aankomst=<?= $aankomst ?>&vertrek=<?= $vertrek ?>&arrangement=<?= $arrangement_id ?>"><?= __('Book now for', 'simpelreserveren') ?> &euro; <?= number_format($accommodatie->prijs,
                        2) ?></a>
            </div>

        <?php endforeach;

        exit;
    }


}

$wp_simpelreserveren = new WPSimpel_Reserveren();

