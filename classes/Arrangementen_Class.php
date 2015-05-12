<?php

Class Arrangementen extends Base_Class {

    private $get;
    private $result;
    private $aankomst;
    private $vertrek;
    private $faciliteiten;
    
    public $all_faciliteiten;

    final function __construct() {
        global $wp_simpelreserveren;
        parent::__construct();

        $this->sr = &$wp_simpelreserveren;
        $this->get = $_GET;

        if (!$this->get['aankomst']) {
            $cookie = json_decode(stripslashes($_COOKIE['simpelreserveren']));
            if ($cookie && isset($cookie->aankomst)) {
                $this->get['aankomst'] = $cookie->aankomst;
                $this->get['vertrek'] = $cookie->vertrek;
                $this->get['volw'] = $cookie->volw;
                $this->get['kind'] = $cookie->kind;
            } else {
                $this->get['aankomst'] = date('d-m-Y');
                $this->get['vertrek'] = date('d-m-Y', strtotime('+' . $this->sr->get_setting('std-aantal-nachten') . ') days'));
                $this->get['volw'] = 2;
                $this->get['kind'] = 0;
            }
        }
        $this->aankomst = strtotime($this->get['aankomst']);
        $this->vertrek = strtotime($this->get['vertrek']);
        $this->result = array();
        $this->faciliteiten = array('camping' => array(), 'accommodatie' => array(), 'selected' => array());

    }

    function zoek() {
        $title_field = 'title';
        if (defined('ICL_LANGUAGE_CODE')) {
            $title_field .= '_' . ICL_LANGUAGE_CODE;
        }

        // first get all the arrangementen
        $arrangementen = $this->wpdb->get_results('select * from ' . SIMPEL_DB_PREFIX . 'arrangementen where zichtbaar = "1" order by '.$title_field);

        // then loop through them to see which accommodations have them
        foreach ($arrangementen as $arr) {
            $arrangement = new Arrangement($arr->id);
            $arrangement->accos = $this->get_accommodaties($this->get['aankomst'], $this->get['vertrek'], $arrangement->id);

            $this->result[] = $arrangement;
        }


        return $this->result;
    }

    function get_accommodaties($aankomst, $vertrek, $arrangement_id)
    {
        $result = array();
        $arrangement = new Arrangement($arrangement_id);
        $accommodaties = $this->wpdb->get_results('select * from ' . SIMPEL_DB_PREFIX . 'accommodatie order by title');

        // if altijd geldig gewoon door de accommodaties lopen
        foreach ($accommodaties as $acco) {
            $accommodatie = new Accommodatie($acco->id);
            $accommodatie->prijs = $accommodatie->get_prijs($aankomst, $vertrek, $this->get['volw'], $this->get['kind'], 0, 1, $arrangement->id);
            if ($accommodatie->prijs) {
                $result[] = $accommodatie;
            }
        }

        return $result;
    }

    function get_arrangementen($aankomst, $vertrek, $accommodatie_id)
    {
        $title_field = 'title';
        if (defined('ICL_LANGUAGE_CODE')) {
            $title_field .= '_' . ICL_LANGUAGE_CODE;
        }

        $result = array();
        $accommodatie = new Accommodatie($accommodatie_id);
        $arrangementen = $this->wpdb->get_results('select * from ' . SIMPEL_DB_PREFIX . 'arrangementen order by '.$title_field);

        // if altijd geldig gewoon door de accommodaties lopen
        foreach ($arrangementen as $arr) {
            $arrangement = new Arrangement($arr->id);
            $arrangement->accommodatie = $accommodatie;
            $arrangement->accommodatie->prijs = $arrangement->accommodatie->get_prijs($aankomst, $vertrek, $this->get['volw'], $this->get['kind'], 0, 1, $arrangement->id);
            if ($arrangement->accommodatie->prijs) {
                if ($arrangement->altijd_geldig) {
                    $result[] = $arrangement;
                } else {
                    // see in which periods this arrangement is valid for this accommodation
                    $periodes = $this->wpdb->get_results($this->wpdb->prepare('select periode_id from ' . SIMPEL_DB_PREFIX . 'arrangementen_per where arrangement_id = "%d" and accommodatie_id = "%d"', $arrangement->id, $accommodatie->id));
                    foreach ($periodes as $row) {
                        if(in_array($row->periode_id, $arrangement->accommodatie->periodes)) {
                            $result[] = $arrangement;
                            break;
                        }
                    }

                }
            }
        }

        return $result;
    }

}
