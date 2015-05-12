<?php

class acf_field_sr_object extends acf_field {
    /*
     *  __construct
     *
     *  Set name / label needed for actions / filters
     *
     *  @since	3.6
     *  @date	23/01/13
     */

    function __construct() {
        // vars
        $this->name = 'sr_object';
        $this->label = __("Simpel Reserveren Object", 'acf');
        $this->category = __("Relational", 'acf');
        $this->defaults = array(
            'post_type' => array('all'),
            'taxonomy' => array('all'),
            'multiple' => 0,
            'allow_null' => 1,
        );


        // do not delete!
        parent::__construct();
    }

    /*
     *  load_field()
     *  
     *  This filter is appied to the $field after it is loaded from the database
     *  
     *  @type filter
     *  @since 3.6
     *  @date 23/01/13
     *  
     *  @param $field - the field array holding all the field options
     *  
     *  @return $field - the field array holding all the field options
     */

    function load_field($field) {
        // validate post_type
        //if( !$field['post_type'] || !is_array($field['post_type']) || in_array('', $field['post_type']) )
        //{
        $field['post_type'] = array('all');
        //}
        // validate taxonomy
        //if( !$field['taxonomy'] || !is_array($field['taxonomy']) || in_array('', $field['taxonomy']) )
        //{
        $field['taxonomy'] = array('all');
        //}
        // return
        return $field;
    }

    /*
     *  create_field()
     *
     *  Create the HTML interface for your field
     *
     *  @param	$field - an array holding all the field's data
     *
     *  @type	action
     *  @since	3.6
     *  @date	23/01/13
     */

    function create_field($field) {
        global $wp_simpelreserveren;

        // Change Field into a select
        $field['type'] = 'select';
        $field['choices'] = array();

        $accommodaties = $wp_simpelreserveren->get_accommodaties(9999, 'title');

        foreach ($accommodaties as $acco) {
            $field['choices'][$acco->id] = $acco->title;
        }
        // foreach( $field['post_type'] as $post_type )
        // create field
        do_action('acf/create_field', $field);
    }

    /*
     *  create_options()
     *
     *  Create extra options for your field. This is rendered when editing a field.
     *  The value of $field['name'] can be used (like bellow) to save extra data to the $field
     *
     *  @type	action
     *  @since	3.6
     *  @date	23/01/13
     *
     *  @param	$field	- an array holding all the field's data
     */

    function create_options($field) {
        // vars
        $key = $field['name'];
        ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label for=""><?php _e("Post Type", 'acf'); ?></label>
            </td>
            <td>
        <?php
        $choices = array(
            'all' => __("All", 'acf')
        );
        $choices = apply_filters('acf/get_post_types', $choices);


        do_action('acf/create_field', array(
            'type' => 'select',
            'name' => 'fields[' . $key . '][post_type]',
            'value' => $field['post_type'],
            'choices' => $choices,
            'multiple' => 1,
        ));
        ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e("Filter from Taxonomy", 'acf'); ?></label>
            </td>
            <td>
                <?php
                $choices = array(
                    '' => array(
                        'all' => __("All", 'acf')
                    )
                );
                $simple_value = false;
                $choices = apply_filters('acf/get_taxonomies_for_select', $choices, $simple_value);

                do_action('acf/create_field', array(
                    'type' => 'select',
                    'name' => 'fields[' . $key . '][taxonomy]',
                    'value' => $field['taxonomy'],
                    'choices' => $choices,
                    'multiple' => 1,
                ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e("Allow Null?", 'acf'); ?></label>
            </td>
            <td>
                <?php
                do_action('acf/create_field', array(
                    'type' => 'radio',
                    'name' => 'fields[' . $key . '][allow_null]',
                    'value' => $field['allow_null'],
                    'choices' => array(
                        1 => __("Yes", 'acf'),
                        0 => __("No", 'acf'),
                    ),
                    'layout' => 'horizontal',
                ));
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e("Select multiple values?", 'acf'); ?></label>
            </td>
            <td>
        <?php
        do_action('acf/create_field', array(
            'type' => 'radio',
            'name' => 'fields[' . $key . '][multiple]',
            'value' => $field['multiple'],
            'choices' => array(
                1 => __("Yes", 'acf'),
                0 => __("No", 'acf'),
            ),
            'layout' => 'horizontal',
        ));
        ?>
            </td>
        </tr>
                <?php
            }

            /*
             *  format_value()
             *
             *  This filter is appied to the $value after it is loaded from the db and before it is passed to the create_field action
             *
             *  @type	filter
             *  @since	3.6
             *  @date	23/01/13
             *
             *  @param	$value	- the value which was loaded from the database
             *  @param	$post_id - the $post_id from which the value was loaded
             *  @param	$field	- the field array holding all the field options
             *
             *  @return	$value	- the modified value
             */

            function format_value($value, $post_id, $field) {
                // empty?
                if (!empty($value)) {
                    // convert to integers
                    if (is_array($value)) {
                        $value = array_map('intval', $value);
                    } else {
                        $value = intval($value);
                    }
                }


                // return value
                return $value;
            }

            /*
             *  format_value_for_api()
             *
             *  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
             *
             *  @type	filter
             *  @since	3.6
             *  @date	23/01/13
             *
             *  @param	$value	- the value which was loaded from the database
             *  @param	$post_id - the $post_id from which the value was loaded
             *  @param	$field	- the field array holding all the field options
             *
             *  @return	$value	- the modified value
             */

            function format_value_for_api($value, $post_id, $field) {
                //echo 'val: ' . $value . ' post: ' . $post_id . '<br>';
                // return the value
                return $value;
            }

            /*
             *  update_value()
             *
             *  This filter is appied to the $value before it is updated in the db
             *
             *  @type	filter
             *  @since	3.6
             *  @date	23/01/13
             *
             *  @param	$value - the value which will be saved in the database
             *  @param	$post_id - the $post_id of which the value will be saved
             *  @param	$field - the field array holding all the field options
             *
             *  @return	$value - the modified value
             */

            function update_value($value, $post_id, $field) {
                // validate
                if (empty($value)) {
                    return $value;
                }


                if (is_object($value) && isset($value->ID)) {
                    // object
                    $value = $value->ID;
                } elseif (is_array($value)) {
                    // array
                    foreach ($value as $k => $v) {

                        // object?
                        if (is_object($v) && isset($v->ID)) {
                            $value[$k] = $v->ID;
                        }
                    }

                    // save value as strings, so we can clearly search for them in SQL LIKE statements
                    $value = array_map('strval', $value);
                }

                return $value;
            }

        }

        new acf_field_sr_object();
        ?>