<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SR_Admin_Optionspage {
    
    private $sr;

    function __construct($sr) {
        $this->sr = $sr;

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'init_settings' ) );

        add_action('admin_init', array( $this, 'ignore_errors') );

        $this->show_errors();
    }

    public function show_errors() {
        $search = get_posts(array('post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'sr-search.php'));
        $book = get_posts(array('post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'sr-book.php'));

        $errors = array();
        if(count($search) === 0){

            $errors[] = 'Er is nog geen zoekpagina gevonden.';
        }
        if(count($book) === 0){
            $errors[] = 'Er is nog geen boekingspagina.';
        }
        if(count($errors)){
            
            add_action('admin_notices', function() use ($errors) {
                global $current_user;

                if ( ! get_user_meta($current_user->ID, 'sr_ignore_error') ) {
                    ?>
                    <div class="error">
                        <strong>Simpel Reserveren</strong>
                        <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error ?></li>
                        <?php endforeach; ?>
                        </ul>
                        <?php printf(__('<a href="%1$s">Ik weet het, laat me met rust</a>'), '?sr_ignore_error=0'); ?>
                    </div>
                    <?php
                }
            });
        }
        
    }

    public function ignore_errors() {
        global $current_user;
       
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['sr_ignore_error']) && '0' == $_GET['sr_ignore_error'] ) {
            add_user_meta($current_user->ID, 'sr_ignore_error', 'true', true);
        }
    }

    public function add_admin_menu() {
        add_options_page( 'Simpel Reserveren', 'Simpel Reserveren', 'manage_options', 'dbk-sr', array($this, 'render_options_page') );
    }

    public function init_settings() {
        $options = $this->sr->options;

        add_settings_section(
            'dbk-sr_simpleres_section', 
            __( 'Algemene opties' ), 
            array($this, 'section_settings'), 
            'simpleres'
        );

        foreach($options->get_options() as $field => $value) {
            register_setting( 'simpleres', $options->get_option_key($field) );
        }
        
    }

    public function section_settings() { 
        $options = $this->sr->options;

        foreach($options->get_options() as $field => $value) {

            if($value['type'] === 'text') {
                add_settings_field( 
                    $options->get_option_key($field), 
                    $field, 
                    function() use ($field){
                        $this->render_text($field);
                    }, 
                    'simpleres', 
                    'dbk-sr_simpleres_section' 
                );
            }
            elseif($value['type'] === 'sr_page') {
                add_settings_field( 
                    $options->get_option_key($field), 
                    $field, 
                    function() use ($field){
                        $this->render_page_select($field);
                    }, 
                    'simpleres', 
                    'dbk-sr_simpleres_section' 
                );
            }
            
        }

    }

    public function render_options_page() {
        ?>
        <form action='options.php' method='post'>
            
            <h2>Simpel Reserveren Instellingen</h2>
            
            <?php
            settings_fields( 'simpleres' );
            do_settings_sections( 'simpleres' );
            submit_button();
            ?>
            
        </form>
        <?php
    }

    private function render_text($field) {
        $options = $this->sr->options;
        ?>
        <input type='text' name='<?php echo $options->get_option_key($field) ?>' value='<?php echo $options->get_option($field) ?>'>
        <?php

    }



    public function __call($name, $arguments) {

        if(strpos($name, 'render_field') !== false) {
            $field = str_replace('render_field_', '', $name);
            return $this->render_field($field);
        }
    }



}
new SR_Admin_Optionspage($this);
