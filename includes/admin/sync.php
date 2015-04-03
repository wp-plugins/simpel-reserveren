<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class SR_Sync
{

    public $sr;
    public $token;
    public $errors;

    public $endpoint;
    public $endpoints;

    public function __construct($sr)
    {
        $this->sr = $sr;
        $this->token = $sr->options->get_option('token');
        $this->endpoint = $this->sr->get_endpoint();

        $this->endpoints = array(
            'entities' => $this->endpoint.'entities',
            'entitytypes' => $this->endpoint.'entitytypes',
            'companies' => $this->endpoint.'entities',
            'bookgroups' => $this->endpoint.'bookgroups',
            'booking' => $this->endpoint.'book',
            'address' => $this->endpoint.'address',
        );
    }

    public static function init()
    {
        add_action('admin_menu', array( 'SR_Sync', 'admin_menu'));
        add_action('admin_bar_menu', array( 'SR_Sync', 'admin_bar_menu'));
    }

    public static function admin_bar_menu()
    {
        add_action('admin_bar_menu', function ($wp_admin_bar) {
            $wp_admin_bar->add_node(array(
                'id' => 'dbk-sr-pre-sync',
                'title' => 'SR: Import',
                'href' => admin_url('admin.php?page=dbk-sr-pre-sync'),
                'meta' => array(
                    'class' => 'dbk-sr-pre-sync',
                ),
            ));
        }, 110);
    }

    public static function admin_menu()
    {
        /* sync hook */
        add_submenu_page('dbk-sr-pre-sync', 'Sync', 'Sync', 'edit_posts', 'dbk-sr-pre-sync', function () {
            $sync = new SR_Sync(DBK_SR::get_instance());
            $sync->prepare_sync();
        });
        add_submenu_page('dbk-sr-do-sync', 'Sync', 'Sync', 'edit_posts', 'dbk-sr-do-sync', function () {
            $sync = new SR_Sync(DBK_SR::get_instance());
            $sync->do_sync();
        });
    }

    public function prepare_sync()
    {
        $entities   = $this->get_data('entities');
        $entitytypes = $this->get_data('entitytypes');
        $bookgroups = $this->get_data('bookgroups');

        if ($entities || $bookgroups || $entitytypes) {
            $this->sr->options->save_option('_entities', $entities);
            $this->sr->options->save_option('_entitytypes', $entitytypes);
            $this->sr->options->save_option('_bookgroups', $bookgroups);

            ?>
            <h2>Simpel Reserveren importer</h2>
            We hebben <?php echo count($entities) ?> objecten, <?= count($entitytypes) ?> types en <?= count($bookgroups) ?> reisgezelschappen gevonden.

            <?php submit_button(
                'Importeer alles',
                'primary',
                '',
                true,
                array('onclick' => "window.location = '".get_admin_url()."admin.php?page=dbk-sr-do-sync'")
            ) ?>

            <?php

        } else {
            ?>
            <p>Data kon niet worden opgehaald</p>
            <?php

        }
    }

    public function do_sync()
    {
        $entities   = $this->sr->options->get_option('_entities');
        $entitytypes = $this->sr->options->get_option('_entitytypes');
        $bookgroups = $this->sr->options->get_option('_bookgroups');

        $updates = 0;
        $inserts = 0;
        foreach ($entities as $entity) {
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
                'meta_value' => $entity->id,
            ));
            if (count($posts)) {
                $post['ID'] = $posts[0]->ID;
                //wp_update_post($post);
                //$updates++;
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
                array('onclick' => "window.location = '".get_admin_url()."edit.php?post_type=".DBK_SR::$entity_post_type."'")
            ) ?>
        <?php

    }

    private function get_data($endpoint)
    {
        if (!empty($this->token)) {
            $data = json_decode(file_get_contents($this->get_endpoint_url($endpoint)));
        } else {
            wp_die('Geen token gevonden');
        }

        return $data;
    }

    public function get_endpoint_url($endpoint)
    {
        $result = $this->endpoints[$endpoint].'?key='.$this->token;
        if (substr($result, 0, 2) == '//') {
            $result = 'http:' . $result;
        }
        return $result;
    }

    private function send_data($url, $data)
    {
        //$json = file_get_contents('php://input');
        $json = json_encode($data);

        $api_key = $this->token;

        if (substr($url, 0, 2) == '//') {
            $url = 'http:' . $url;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $api_key);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SimpelReserveren API Agent');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.strlen($json), )
        );
        $result = curl_exec($ch);

        //echo $result;

        $data = json_decode($result);

        header("Content-type: application/json; charset=utf-8");
        //if($data && $data->result == 'ok') {
        echo json_encode($data);
        //    return;
       // }

        //$data = array('result' => 'error', 'booking' => );

        //echo json_encode($data);
    }

    public function save_booking()
    {
        $data = $_POST;
        $bookgroups = array();
        foreach ($data['bookgroups'] as $bookgroup) {
            if ($bookgroup['nr']) {
                $bookgroups[$bookgroup['id']] = $bookgroup['nr'];
            }
        }

        $json = array(
            'bookgroups'    => $bookgroups,
            'entity_id'     => $data['entity_id'],
            'start'         => $data['start'],
            'end'           => $data['end'],
            'extra_values'  => $data['extra_values'],
            'lastname'      => $data['lastname'],
            'email'         => $data['email'],
        );

        $optional_fields = array('firstname', 'address', 'postcode', 'city', 'phone', 'entity_item_id', 'discountcode', 'remark', 'prefix', 'country', 'date_of_birth');
        foreach ($optional_fields as $field) {
            if (isset($data[$field])) {
                $json[$field] = $data[$field];
            }
        }

        $url = $this->get_endpoint_url('booking');
        $this->send_data($url, $json);

        wp_die();
    }

    public function save_address()
    {
        $url = $this->get_endpoint_url('address');

        $data = [
            'customer_id'   => filter_input(INPUT_POST, 'customer_id'),
            'entity_id'     => filter_input(INPUT_POST, 'entity_id'),
            'email'         => filter_input(INPUT_POST, 'email'),
            'firstname'     => filter_input(INPUT_POST, 'firstname'),
            'lastname'      => filter_input(INPUT_POST, 'lastname'),
            'address'       => filter_input(INPUT_POST, 'address'),
            'postcode'      => filter_input(INPUT_POST, 'postcode'),
            'city'          => filter_input(INPUT_POST, 'city'),
            'phone'         => filter_input(INPUT_POST, 'phone'),
            'prefix'        => filter_input(INPUT_POST, 'prefix'),
            'date_of_birth' => filter_input(INPUT_POST, 'date_of_birth'),
            'country'       => filter_input(INPUT_POST, 'country'),
        ];

        $this->send_data($url, $data);

        wp_die();
    }
}
SR_Sync::init();
