<?php

class SR_Options {

    public $options;
    public $prefix;

    function __construct($prefix, $options=array()) {
        $this->prefix = $prefix;
        $this->options = $options;
    }

    public function get_options($get_all=false) {
        if($get_all) 
            return $this->options;
        

        $options = array();
        foreach($this->options as $key => $option) {

            if(substr($key, 0, 1) !== '_') 
                $options[$key] = $option;
            
        }

        return $options;
    }

    public function add_option($key, $value=null, $title=null, $help=null, $type='text') {
        $this->options[$key] = array(
            'value' => $value,
            'title' => $title,
            'help' => $help,
            'type' => $type
        );
    }
    
    public function add_options($options) {
        $this->options = array_merge($this->options, $options);
    }

    public function get_option($name) {

        if($option = get_option($this->prefix . $name)){
            return $option;
        }
        elseif($this->options[$name]){
            return $this->options[$name]['value'];
        }

        return false;
    }

    public function save_option($name, $value) {
        if(isset($this->options[$name]))
            return update_option($this->prefix . $name, $value);
        else
            wp_die("SR: You are trying to save an option (<strong>$name</strong>) i don't know (yet). Please add it first.");
    }

    public function get_option_key($name) {
        return $this->prefix . $name;
    }
}