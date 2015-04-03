<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class SR_Admin_Optionspage
{

    private $sr;

    public function __construct($sr)
    {
        $this->sr = $sr;

        add_action('admin_menu', array( $this, 'add_admin_menu' ));
        add_action('admin_init', array( $this, 'init_settings' ));

        add_action('admin_init', array( $this, 'ignore_errors'));

        $this->show_errors();
    }

    public function show_errors()
    {
        $search = get_posts(array('post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'sr-search.php'));
        $book = get_posts(array('post_type' => 'page', 'meta_key' => '_wp_page_template', 'meta_value' => 'sr-book.php'));

        $errors = array();
        if (count($search) === 0) {
            $errors[] = 'Er is nog geen zoekpagina gevonden.';
        }
        if (count($book) === 0) {
            $errors[] = 'Er is nog geen boekingspagina.';
        }
        if (count($errors)) {
            add_action('admin_notices', function () use ($errors) {
                global $current_user;

                if (! get_user_meta($current_user->ID, 'sr_ignore_error')) {
                    ?>
                    <div class="error">
                        <strong>Simpel Reserveren</strong>
                        <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error ?></li>
                        <?php endforeach;
                    ?>
                        </ul>
                        <a href="?sr_ignore_error=0">Ik weet het, laat me met rust</a>
                    </div>
                    <?php

                }
            });
        }
    }

    public function ignore_errors()
    {
        global $current_user;

        /* If user clicks to ignore the notice, add that to their user meta */
        if (isset($_GET['sr_ignore_error']) && '0' == $_GET['sr_ignore_error']) {
            add_user_meta($current_user->ID, 'sr_ignore_error', 'true', true);
        }
    }

    public function add_admin_menu()
    {
        add_options_page('Simpel Reserveren', 'Simpel Reserveren', 'sr_settings', 'dbk-sr', array($this, 'render_options_page'));
    }

    public function init_settings()
    {
        $options = $this->sr->options;

        add_settings_section(
            'dbk-sr_simpleres_section',
            'Algemene opties',
            array($this, 'section_settings'),
            'simpleres'
        );

        foreach ($options->get_options() as $field => $value) {
            register_setting('simpleres', $options->get_option_key($field));
        }
    }

    public function section_settings()
    {
        $options = $this->sr->options;

        foreach ($options->get_options() as $field => $value) {
            if ($value['type'] === 'text') {
                add_settings_field(
                    $options->get_option_key($field),
                    $value['title'],
                    function () use ($field) {
                        $this->render_text($field);
                    },
                    'simpleres',
                    'dbk-sr_simpleres_section'
                );
            } elseif ($value['type'] === 'select') {
                add_settings_field(
                    $options->get_option_key($field),
                    $value['title'],
                    function () use ($field) {
                        $this->render_select_field($field);
                    },
                    'simpleres',
                    'dbk-sr_simpleres_section'
                );
            } elseif ($value['type'] === 'checkbox') {
                add_settings_field(
                    $options->get_option_key($field),
                    $value['title'],
                    function () use ($field) {
                        $this->render_checkbox($field);
                    },
                    'simpleres',
                    'dbk-sr_simpleres_section'
                );
            } elseif ($value['type'] === 'sr_page') {
                add_settings_field(
                    $options->get_option_key($field),
                    $value['title'],
                    function () use ($field) {
                        $this->render_page_select($field);
                    },
                    'simpleres',
                    'dbk-sr_simpleres_section'
                );
            }
        }
    }

    public function render_options_page()
    {
        ?>
        <form action='options.php' method='post'>

            <h2>Simpel Reserveren Instellingen</h2>

            <?php
            settings_fields('simpleres');
        do_settings_sections('simpleres');
        submit_button();
        ?>

        </form>
        <?php

    }

    private function render_text($field)
    {
        $options = $this->sr->options;
        ?>
        <input type='text' name='<?php echo $options->get_option_key($field) ?>' value='<?php echo $options->get_option($field) ?>' size="50">
        <?php

    }

    private function render_checkbox($field)
    {
        $options = $this->sr->options;
        ?>
        <input type='checkbox' name='<?php echo $options->get_option_key($field) ?>' value='1' <?= checked(1, $options->get_option($field), false) ?>>
        <?php

    }

    private function render_select_field($field)
    {
        $options = $this->sr->options;

        $select = $options->options[$field]['options'];
        ?>

        <select type='text' name='<?php echo $options->get_option_key($field) ?>' value='<?php echo $options->get_option($field) ?>'>
        <?php foreach ($select as $option) : ?>
            <option <?= ($options->get_option($field) == $option ? 'selected' : '') ?> ><?= $option ?></option>
        <?php endforeach;
        ?>
        </select>
        <?php

    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'render_field') !== false) {
            $field = str_replace('render_field_', '', $name);

            return $this->render_field($field);
        }
    }
}
new SR_Admin_Optionspage($this);
