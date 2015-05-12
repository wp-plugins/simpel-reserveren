<?php

class SimpelReserveren_Customizer {

    public static function register($wp_customize) {
        $wp_customize->add_section('simpelreserveren', array(
            'title' => 'Simpel Reserveren',
            'description' => 'Instellingen voor Simpel Reserveren',
        ));

        $wp_customize->add_setting('simpelreserveren[zoek_bg]', array(
            'default' => '#ffffff',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_zoek_bg', array(
            'label' => 'Zoek achtergrond',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[zoek_bg]',
            'priority' => 10,
        )));

        $wp_customize->add_setting('simpelreserveren[zoek_color]', array(
            'default' => '#333333',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_zoek_color', array(
            'label' => 'Zoek tekst kleur',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[zoek_color]',
            'priority' => 20,
        )));

        $wp_customize->add_setting('simpelreserveren[filter_bg]', array(
            'default' => '#eeeeee',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_filter_bg', array(
            'label' => 'Filter achtergrond',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[filter_bg]',
            'priority' => 30,
        )));

        $wp_customize->add_setting('simpelreserveren[primary_button_bg]', array(
            'default' => '#ffa01a',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_primary_button_bg', array(
            'label' => 'Primaire button achtergrond',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[primary_button_bg]',
            'priority' => 40,
        )));

        $wp_customize->add_setting('simpelreserveren[primary_button_color]', array(
            'default' => '#ffffff',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_primary_button_color', array(
            'label' => 'Primaire button kleur',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[primary_button_color]',
            'priority' => 50,
        )));

        $wp_customize->add_setting('simpelreserveren[primary_container_bg]', array(
            'default' => '#E4F5FD',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_primary_container_bg', array(
            'label' => 'Primaire container achtergrond',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[primary_container_bg]',
            'priority' => 60,
        )));

        $wp_customize->add_setting('simpelreserveren[secondary_button_bg]', array(
            'default' => '#ffa01a',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_secondary_button_bg', array(
            'label' => 'Secondaire button achtergrond',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[secondary_button_bg]',
            'priority' => 70,
        )));

        $wp_customize->add_setting('simpelreserveren[secondary_button_color]', array(
            'default' => '#ffffff',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_secondary_button_color', array(
            'label' => 'Secondaire button kleur',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[secondary_button_color]',
            'priority' => 80,
        )));

        $wp_customize->add_setting('simpelreserveren[secondary_container_bg]', array(
            'default' => '#E4F5FD',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_secondary_container_bg', array(
            'label' => 'Secondaire container achtergrond',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[secondary_container_bg]',
            'priority' => 90,
        )));

        $wp_customize->add_setting('simpelreserveren[prijs_color]', array(
            'default' => '#ff0000',
            'type' => 'option',
            'sanitize_callback' => 'sanitize_hex_color_no_hash',
            'sanitize_js_callback' => 'maybe_hash_hex_color',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'simpelreserveren_prijs_color', array(
            'label' => 'Prijs kleur',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[prijs_color]',
            'priority' => 100,
        )));

        $wp_customize->add_setting('simpelreserveren[zoek_blok_position]', array(
            'default' => 'static',
            'type' => 'option',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control('simpelreserveren_zoek_blok_position', array(
            'label' => 'Positie zoek blok',
            'section' => 'simpelreserveren',
            'settings' => 'simpelreserveren[zoek_blok_position]',
            'type' => 'select',
            'choices' => array(
                'static' => 'Statisch',
                'fixed' => 'Gefixeerd',
            ),
            'priority' => 110,
        ));

        //4. We can also change built-in settings by modifying properties. For instance, let's make some stuff use live preview JS...
        $wp_customize->get_setting('blogname')->transport = 'postMessage';
        $wp_customize->get_setting('blogdescription')->transport = 'postMessage';
    }

    /**
     * This will output the custom WordPress settings to the live theme's WP head.
     * 
     * Used by hook: 'wp_head'
     * 
     * @see add_action('wp_head',$func)
     * @since MyTheme 1.0
     */
    public static function header_output() {
        ?>
        <!--Customizer CSS--> 
        <style type="text/css">
        <?php
        $settings = get_option('simpelreserveren');
        if (empty($settings['zoek_bg'])) {
            $settings = array(
                'zoek_bg' => 'ffffff',
                'zoek_color' => '333333',
                'filter_bg' => 'eeeeee',
                'primary_button_bg' => 'ffa01a',
                'primary_button_color' => 'ffffff',
                'primary_container_bg' => 'E4F5FD',
                'prijs_color' => 'ff0000',
                'secondary_button_bg' => 'ffa01a',
                'secondary_button_color' => 'ffffff',
                'secondary_container_bg' => 'E4F5FD',
            );
        }
        ?>
        <?php //self::generate_css('.sr-order-box', 'background-color', 'simpelreserveren[zoek_bg]');  ?>
            .simpel-reserveren.sr-order-box,
            .simpel-reserveren .sr-order-box{ background-color: #<?php echo $settings['zoek_bg'] ?>; color: #<?php echo $settings['zoek_color'] ?>;}
            .simpel-reserveren.sr-order-box h3{color: #<?php echo $settings['zoek_color'] ?>; }
            .simpel-reserveren .sr-filter-box{ background-color: #<?php echo $settings['filter_bg'] ?>;}

            .simpel-reserveren .sr-primary-button{ background-color: #<?php echo $settings['primary_button_bg'] ?> ; color: #<?php echo $settings['primary_button_color'] ?> ; }
            .simpel-reserveren .sr-primary-button:hover{ background-color: #<?php echo self::darken_color($settings['primary_button_bg'], 1.15) ?>; color: #<?php echo $settings['primary_button_color'] ?> ; }
            .simpel-reserveren .sr-boeken{ background-color: #<?php echo $settings['primary_container_bg'] ?>;}
            .simpel-reserveren .sr-boeken-prijs-voor{ color: #<?php echo $settings['prijs_color'] ?> !important;}

            .simpel-reserveren .sr-secondary-button{ background-color: #<?php echo $settings['secondary_button_bg'] ?> ; color: #<?php echo $settings['secondary_button_color'] ?> ; }
            .simpel-reserveren .sr-secondary-button:hover{ background-color: #<?php echo self::darken_color($settings['secondary_button_bg'], 1.15) ?>; color: #<?php echo $settings['secondary_button_color'] ?> ; }
            .simpel-reserveren .sr-secondary-container{ background-color: #<?php echo $settings['secondary_container_bg'] ?>; }

            .simpel-reserveren.prijstabel h3{ background-color: #<?php echo $settings['zoek_bg'] ?>; color: #<?php echo $settings['zoek_color'] ?>; }
            .simpel-reserveren.prijstabel thead th{ background-color: #<?php echo self::colourBrightness($settings['zoek_bg'], .2) ?>; }
            .simpel-reserveren.prijstabel .periode{ background-color: #<?php echo $settings['primary_container_bg'] ?>; }
                   
            .simpel-reserveren.prijstabel tr.evenrow:hover,
            .simpel-reserveren.prijstabel tr.oddrow:hover,
            .simpel-reserveren.prijstabel thead th{ background-color: #<?php echo $settings['primary_button_bg'] ?>; color: #<?php echo $settings['primary_button_color'] ?> ;}
            .simpel-reserveren.prijstabel tr.oddrow { background-color: #<?php echo $settings['primary_container_bg'] ?>; }
        </style> 
        <!--/Customizer CSS-->
        <?php
    }

    /**
     * This outputs the javascript needed to automate the live settings preview.
     * Also keep in mind that this function isn't necessary unless your settings 
     * are using 'transport'=>'postMessage' instead of the default 'transport'
     * => 'refresh'
     * 
     * Used by hook: 'customize_preview_init'
     * 
     * @see add_action('customize_preview_init',$func)
     * @since MyTheme 1.0
     */
    public static function live_preview() {
        /* echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>';
          echo '<script src="/wp-includes/js/customize-preview.js"></script>';
          echo '<script src="' . SIMPEL_PLUGIN_URL.'/js/theme-customizer.js"></script>';
         */
        wp_enqueue_script(
                'simpelreserveren-customize', // Give the script a unique ID
                SIMPEL_PLUGIN_URL . '/js/theme-customizer.js', // Define the path to the JS file
                array('customize-preview'), // Define dependencies
                '', // Define a version (optional) 
                true // Specify whether to put in footer (leave this true)
        );
    }

    /**
     * This will generate a line of CSS for use in header output. If the setting
     * ($mod_name) has no defined value, the CSS will not be output.
     * 
     * @uses get_theme_mod()
     * @param string $selector CSS selector
     * @param string $style The name of the CSS *property* to modify
     * @param string $mod_name The name of the 'theme_mod' option to fetch
     * @param string $prefix Optional. Anything that needs to be output before the CSS property
     * @param string $postfix Optional. Anything that needs to be output after the CSS property
     * @param bool $echo Optional. Whether to print directly to the page (default: true).
     * @return string Returns a single line of CSS with selectors and a property.
     * @since MyTheme 1.0
     */
    public static function generate_css($selector, $style, $mod_name, $prefix = '', $postfix = '', $echo = true) {
        $return = '';
        $mod = get_theme_mod($mod_name, '#ffffff');
        if (!empty($mod)) {
            $return = sprintf('%s { %s:%s; }', $selector, $style, $prefix . $mod . $postfix
            );
            if ($echo) {
                echo $return;
            }
        }
        return $return;
    }

    public static function darken_color($rgb, $darker = 2) {

        $hash = (strpos($rgb, '#') !== false) ? '#' : '';
        $rgb = (strlen($rgb) == 7) ? str_replace('#', '', $rgb) : ((strlen($rgb) == 6) ? $rgb : false);
        if (strlen($rgb) != 6)
            return $hash . '000000';
        $darker = ($darker > 1) ? $darker : 1;

        list($R16, $G16, $B16) = str_split($rgb, 2);

        $R = sprintf("%02X", floor(hexdec($R16) / $darker));
        $G = sprintf("%02X", floor(hexdec($G16) / $darker));
        $B = sprintf("%02X", floor(hexdec($B16) / $darker));

        return $hash . $R . $G . $B;
    }

    public static function colourBrightness($hex, $percent) {
        // Work out if hash given
        $hash = '';
        if (stristr($hex, '#')) {
            $hex = str_replace('#', '', $hex);
            $hash = '#';
        }
        /// HEX TO RGB
        $rgb = array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        //// CALCULATE 
        for ($i = 0; $i < 3; $i++) {
            // See if brighter or darker
            if ($percent > 0) {
                // Lighter
                $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
            } else {
                // Darker
                $positivePercent = $percent - ($percent * 2);
                $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1 - $positivePercent));
            }
            // In case rounding up causes us to go to 256
            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }
        //// RBG to Hex
        $hex = '';
        for ($i = 0; $i < 3; $i++) {
            // Convert the decimal digit to hex
            $hexDigit = dechex($rgb[$i]);
            // Add a leading zero if necessary
            if (strlen($hexDigit) == 1) {
                $hexDigit = "0" . $hexDigit;
            }
            // Append to the hex string
            $hex .= $hexDigit;
        }
        return $hash . $hex;
    }

}
