<?php

Class Zoek extends Base_Class {

    private $get;
    private $result;
    private $aankomst;
    private $vertrek;
    private $faciliteiten;
    
    public $all_faciliteiten;

    final function __construct() {
        global $wp_simpelreserveren;
        parent::__construct();

        $this->get = $_GET;
        if (!$this->get['aankomst']) {
            $this->get['aankomst'] = date('d-m-Y');
            $this->get['vertrek'] = date('d-m-Y', strtotime('+7 days'));
            $this->get['volw'] = 2;
            $this->get['kind'] = 0;
        }
        $this->aankomst = strtotime($this->get['aankomst']);
        $this->vertrek = strtotime($this->get['vertrek']);
        $this->result = array();
        $this->faciliteiten = array('camping' => array(), 'accommodatie' => array(), 'selected' => array());
        $this->_get_faciliteiten();
        $this->alternative_parameters = array();
        $this->sr = &$wp_simpelreserveren;
    }

    function go() {
        $this->create_result();

        if (!count($this->result)) { // verander parameters als de zoekopdracht niets oplevert 
            // pas het aantal personen aan
            if ($this->get['volw'] + $this->get['kind'] > 2) {
                $this->get['volw'] = 2;
                $this->get['kind'] = 0;

                $this->alternative_parameters[] = __('Number of people changed to 2 adults and 0 children', 'simpelreserveren');
                return $this->go();
            }

            // kijk of er faciliteiten aangevinkt zijn en vink desnoods uit
            $found = 0;
            foreach ($this->faciliteiten as $types) {
                foreach ($types as $type => &$facil) {
                    if (isset($this->get['facil' . $facil->id]) && $this->get['facil' . $facil->id]) {
                        $found = 1;
                        $facil->selected = false;
                        unset($this->get['facil' . $facil->id]);
                    }
                }
            }
            if ($found) {
                $this->alternative_parameters[] = __('Filter on facilities disabled', 'simpelreserveren');
                return $this->go();
            }

            // zoek op alle types
            if ($this->get['type']) {
                unset($this->get['type']);
                $this->alternative_parameters[] = __('All accommodation types shown', 'simpelreserveren');
                return $this->go();
            }

            // als al het bovenstaand geprobeerd is, dan verander de aankomst / vertrek data
            // probeer eerst een week later
            if (!isset($this->alternative_parameters['period1'])) {

                $this->aankomst = strtotime('+1 week', $this->aankomst);
                $this->vertrek = strtotime('+1 week', $this->vertrek);
                $this->get['aankomst'] = $_GET['aankomst'] = date('d-m-Y', $this->aankomst);
                $this->get['vertrek'] = $_GET['vertrek'] = date('d-m-Y', $this->vertrek);
                $this->alternative_parameters['period1'] = __('Period changed into', 'simpelreserveren') . date(' d-m-Y ', $this->aankomst) . __('until', 'simpelreserveren') . date(' d-m-Y', $this->vertrek);

                $this->sr->update_cookie( array('aankomst' => $this->get['aankomst'], 'vertrek' => $this->get['vertrek'], 'force' => '1' ) );
                return $this->go();
            }

            // probeer anders een week eerder of 2 week later
            if (!isset($this->alternative_parameters['period2'])) {
                $this->aankomst = strtotime('-2 week', $this->aankomst);
                $this->vertrek = strtotime('-2 week', $this->vertrek);
                if($this->aankomst < strtotime(date('Y-m-d'))) {
                    $this->aankomst = strtotime('+3 week', $this->aankomst);
                    $this->vertrek = strtotime('+3 week', $this->vertrek);
                }
                $this->get['aankomst'] = $_GET['aankomst'] = date('d-m-Y', $this->aankomst);
                $this->get['vertrek'] = $_GET['vertrek'] = date('d-m-Y', $this->vertrek);
                $this->alternative_parameters['period2'] = __('Period changed into', 'simpelreserveren') . date(' d-m-Y ', $this->aankomst) . __('until', 'simpelreserveren') . date(' d-m-Y', $this->vertrek);

                $this->sr->update_cookie( array('aankomst' => $this->get['aankomst'], 'vertrek' => $this->get['vertrek'], 'force' => '1' ) );
                return $this->go();
            }
        } else { // als er wel resultaten zijn
            if(isset($this->alternative_parameters['period2'])) { // dan de eerste aanpassing weghalen
                unset($this->alternative_parameters['period1']);
            }
        }

        return $this->result;
    }

    function sort_by_date($a, $b) {
        return ($a->datum < $b->datum) ? -1 : 1;
    }

    private function _randomize_accommodaties() {
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie_randomized where datum = "' . date('Y-m-d') . '"';
        $all = $this->wpdb->get_results($sql);
        if (count($all))
            return;

        $sql = 'update ' . SIMPEL_DB_PREFIX . 'accommodatie set seq = floor(rand() * 1000)';
        $this->wpdb->query($sql);

        $sql = 'update ' . SIMPEL_DB_PREFIX . 'camping set seq = floor(rand() * 1000)';
        $this->wpdb->query($sql);

        $sql = 'insert into ' . SIMPEL_DB_PREFIX . 'accommodatie_randomized (datum) values( now() )';
        $this->wpdb->query($sql);
    }

    private function create_result() {
        $this->_randomize_accommodaties();
        $aantal_dagen = round(($this->vertrek - $this->aankomst) / (60 * 60 * 24));
        $dag_nummer = date('z', $this->aankomst);
        $jaar = date('Y', $this->aankomst);
        $beschikbaarheids_string = '';
        for ($i = 0; $i < $aantal_dagen; $i++)
            $beschikbaarheids_string .= 'A';

        $sql = 'select a.id 
			from ' . SIMPEL_DB_PREFIX . 'accommodatie a
			left join ' . SIMPEL_DB_PREFIX . 'beschikbaarheid b 
				on a.id = b.accommodatie_id
					and ((SUBSTRING(b.dagen, ' . ($dag_nummer + 1) . ', ' . $aantal_dagen . ') = "' . $beschikbaarheids_string . '" 
					and b.jaar = ' . $jaar . ') 
					or b.dagen is null)
			where 1=1 ';
        if (isset($this->get['type']) && $this->get['type'] > 0) {
            $sql .= $this->wpdb->prepare(' and a.acco_type_id = "%d"', $this->get['type']);
        }
        if (isset($this->get['volw']) && $this->get['volw'] > 0) {
            $sql .= $this->wpdb->prepare(' and a.aantal_personen >= "%d"', ($this->get['volw'] + (isset($this->get['kind']) ? $this->get['kind'] : 0)));
        }
        if (SIMPEL_SHOW_CAMPING) {
            $sql .= ' and a.camping_id = "' . SIMPEL_SHOW_CAMPING . '"';
        }
        $sql .= ' order by seq_inner';
        $this->wpdb->show_errors();
        $result = $this->wpdb->get_results($sql);



        $this->result = array();
        // zorg ervoor dat alle campings in een eerlijke volgorde komen
        if (SIMPEL_MULTIPLE) {
            $campings = $this->wpdb->get_results('select id from ' . SIMPEL_DB_PREFIX . 'camping order by seq');
            for ($i = 0; $i < count($result); $i++) {
                foreach ($campings as $camping) {
                    $this->result[] = $camping->id;
                }
            }
        }

        foreach ($result as $row) {
            $accommodatie = new Accommodatie($row->id);
            $accommodatie->prijs = $accommodatie->get_prijs($this->get['aankomst'], $this->get['vertrek'], $this->get['volw'], (isset($this->get['kind']) ? $this->get['kind'] : 0), 0, 1, (isset($this->get['arrangement']) ? $this->get['arrangement'] : 0));
            if (($accommodatie->prijs > 0 || (!isset($this->get['arrangement']) && defined('SIMPEL_ONLY_ARRANGEMENT') && SIMPEL_ONLY_ARRANGEMENT))&& $this->_check_faciliteiten($accommodatie) && !$accommodatie->error) {
                // zoek een plekje om de accommodatie in te stoppen
                if (SIMPEL_MULTIPLE) {
                    foreach ($this->result as $i => $item) {
                        if (is_numeric($item) && $item == $accommodatie->camping_id) {
                            $this->result[$i] = $accommodatie;
                            break;
                        }
                    }
                } else {
                    $this->result[] = $accommodatie;
                }
            }
        }

        // zorg dat alle lege plekken worden verwijderd
        if (SIMPEL_MULTIPLE) {
            foreach ($this->result as $i => $item) {
                if (is_numeric($item))
                    unset($this->result[$i]);
            }
        }

        $this->all_faciliteiten = array_merge($this->faciliteiten['camping'], $this->faciliteiten['accommodatie']);
    }

    private function _check_faciliteiten(&$accommodatie) {
        $result = true;
        foreach ($this->faciliteiten['accommodatie'] as &$facil) {
            if ($facil->selected && !in_array($facil->id, array_keys($accommodatie->faciliteiten))) {
                $result = false;
            } elseif (in_array($facil->id, array_keys($accommodatie->faciliteiten))) {
                $facil->accos[$accommodatie->id] = 1;
            }
        }
        foreach ($this->faciliteiten['camping'] as &$facil) {
            if ($facil->selected && !in_array($facil->id, array_keys($accommodatie->camping->faciliteiten))) {
                $result = false;
            } elseif (in_array($facil->id, array_keys($accommodatie->camping->faciliteiten))) {
                $facil->accos[$accommodatie->id] = 1;
            }
        }

        if (!$result) {
            foreach ($this->faciliteiten['camping'] as &$facil) {
                unset($facil->accos[$accommodatie->id]);
            }
            foreach ($this->faciliteiten['accommodatie'] as &$facil) {
                unset($facil->accos[$accommodatie->id]);
            }
        }
        return $result;
    }

    private function _get_faciliteiten() {
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'faciliteiten where is_filter = 1';
        $all = $this->wpdb->get_results($sql);
        foreach ($all as $facil) {
            // kijk of de faciliteit als filter wordt gebruikt
            $facil->selected = isset($this->get['facil' . $facil->id]) && ($this->get['facil' . $facil->id] == 1);
            if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != 'nl') {
                $field = 'title_' . ICL_LANGUAGE_CODE;
                $facil->title = $facil->$field;
            }

            if ($facil->is_camping) {
                $this->faciliteiten['camping'][] = $facil;
            } else {
                $this->faciliteiten['accommodatie'][] = $facil;
            }

            if ($facil->selected) {
                $this->faciliteiten['selected'][] = $facil;
            }
        }
    }

}
