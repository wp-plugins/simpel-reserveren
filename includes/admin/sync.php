<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SR_Sync {

    public $sr;
    public $token;
    public $errors;

    public $endpoints = array(
        'entities' => 'http://api.simpelreserveren.nl/entities',
        'companies' => 'http://api.simpelreserveren.nl/entities',
        'bookgroups' => 'http://api.simpelreserveren.nl/bookgroups',
        'booking' => 'http://api.simpelreserveren.nl/booking'
    );

    function __construct($sr) {

        $this->sr = $sr;
        $this->token = $sr->options->get_option('token');

    }

    public static function init() {
        add_action('admin_menu', array( 'SR_Sync', 'admin_menu'));
        add_action('admin_bar_menu', array( 'SR_Sync', 'admin_bar_menu'));
    }

    public static function admin_bar_menu() {
        
        add_action('admin_bar_menu', function($wp_admin_bar) { 
            $wp_admin_bar->add_node(array(
                'id' => 'dbk-sr-pre-sync',
                'title' => __('SR: Import'),
                'href' => admin_url('admin.php?page=dbk-sr-pre-sync'),
                'meta' => array(
                    'class' => 'dbk-sr-pre-sync'
                )
            ));
        }, 110);
        
    }

    public static function admin_menu() {
        /* sync hook */
        add_submenu_page( 'dbk-sr-pre-sync', __('Sync'), __('Sync'), 'edit_posts', 'dbk-sr-pre-sync', function(){
            $sync = new SR_Sync(DBK_SR::get_instance());
            $sync->prepare_sync();
        });
        add_submenu_page( 'dbk-sr-do-sync', __('Sync'), __('Sync'), 'edit_posts', 'dbk-sr-do-sync', function(){
            $sync = new SR_Sync(DBK_SR::get_instance());
            $sync->do_sync();
        });
    }

    public function prepare_sync() {
        if($data = $this->get_data('entities')) {

            $this->sr->options->save_option('_entities', $data);

            ?>
            <h2>Simpel Reserveren importer</h2>
            We hebben <?php echo count($data) ?> objecten gevonden.
           
            <?php submit_button(
                'Importeer alle ' . count($data) . ' objecten',
                'primary',
                '',
                true,
                array('onclick' => "window.location = '" . get_admin_url() . "admin.php?page=dbk-sr-do-sync'")
            ) ?>
            
            <?php
        }
        else {
            ?>
            <p>Data kon niet worden opgehaald</p>
            <?php
        }
    }

    public function do_sync() {
        $data = $this->sr->options->get_option('_entities');

        $updates = 0;
        $inserts = 0;
        foreach($data as $entity) {
            $post = array(
              'post_type'      => DBK_SR::$entity_post_type,
              'post_content'   => '',
              'post_title'     => $entity->title,
              'post_status'    => 'publish',
              'ping_status'    => 'closed',
              'post_excerpt'   => $entity->summary,
              'comment_status' => 'closed',
            );

            $posts = get_posts(array(
                'post_type' => DBK_SR::$entity_post_type, 
                'meta_key'  => '_entity_id',
                'meta_value'=> $entity->id 
            ));
            if(count($posts)) {
                $post['ID'] = $posts[0]->ID;
                wp_update_post($post);
                $updates++;
            } else {
                $id = wp_insert_post($post, true);
                update_post_meta($id, '_entity_id', $entity->id);
                $inserts++;
            }

        }
        ?>
        <h2>Simpel Reserveren importer</h2>
        <p>Er zijn <?php echo $updates ?> object(en) geupdate. Er zijn <?php echo $inserts ?> object(en) aangemaakt.</p>
        <?php submit_button(
                'Ok',
                'primary',
                '',
                true,
                array('onclick' => "window.location = '" . get_admin_url() . "edit.php?post_type=".DBK_SR::$entity_post_type."'")
            ) ?>
        <?
    }

    private function get_data($endpoint) {
        
        if(!empty($this->token)){
            $data = json_decode(file_get_contents($this->get_endpoint_url($endpoint)));
        }
        else {
            wp_die('Geen token gevonden');
        }
        return $data;

    }

    public function get_endpoint_url($endpoint) {
        return $this->endpoints[$endpoint] . '?key=' . $this->token;
    }

    public function save_booking() {
        $json = file_get_contents('php://input');
                                                                                 
        $api_key = $this->token;
        $url = get_endpoint_url('booking');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SimpelReserveren API Agent');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);                
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($json))                                                                       
        );                                                                                                                   
         
        $result = curl_exec($ch);
        echo $result;
    }

}
SR_Sync::init();
