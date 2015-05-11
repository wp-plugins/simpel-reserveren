<?php

class Base_Class {

    protected $wpdb;
    private $table;
    private $columns;
    private $columns_info;

    function __construct($table = null, $id = null) {
        global $wpdb;

        $this->wpdb = &$wpdb;
        if(SIMPEL_MULTIPLE) {
            $this->table = 'wp_' . $table;
        } else {
            $this->table = $this->wpdb->prefix . $table;
        }

        if ($table == null)
            return;


        $sql = 'show columns from ' . $this->table;
        $columns = $this->wpdb->get_results($sql);

        if ($id && is_numeric($id)) {
            $sql = 'select * from ' . $this->table . ' where id = "' . ($id) . '"';
            $values = $this->wpdb->get_row($sql);
        }

        foreach ($columns as $column) {
            $field = $column->Field;
            $this->columns[] = $field;
            $this->columns_info[$field] = $column;
            if ($id && is_numeric($id)) {
                $this->$field = $values->$field;
            }
        }
    }

}
