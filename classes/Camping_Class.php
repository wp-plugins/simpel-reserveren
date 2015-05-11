<?php

class Camping extends Base_Class {

    public $id;
    public $title;
    private $accommodaties;
    private $sr;

    function __construct($id) {
        global $wp_simpelreserveren;
        parent::__construct('simpelreserveren_camping', $id);

        $fields = array('email_header', 'email_footer', 'booking_footer', 'confirm_tekst', 'txt_camping', 'txt_omgeving');
        if (defined('ICL_LANGUAGE_CODE')) {
            foreach ($fields as $field) {
                $lang_field = $field . '_' . ICL_LANGUAGE_CODE;
                $this->$field = $this->$lang_field;
            }
            $language_fields = array('title');
            foreach ($language_fields as $field) {
                $lang_field = $field . '_' . ICL_LANGUAGE_CODE;
                if (!empty($this->$lang_field)) {
                    $this->$field = utf8_decode($this->$lang_field);
                }
            }
        }

        $this->samenvatting = truncate_string($this->txt_camping, 150, '...');
        $this->omschrijving = apply_filters('the_content', $this->txt_camping);

        $this->sr = &$wp_simpelreserveren;
        $this->url = (defined('ICL_LANGUAGE_CODE') ? trim(icl_get_home_url(), '/') : home_url() ) . '/' . SIMPEL_CAMPING_SLUG . '/' . $this->name . '/';
    }

    public function __get($name) {
        
        switch ($name) {
            case 'logo_medium':
            case 'logo_url':
                if(isset($this->logo_medium)) return $this->logo_medium;

                $img = wp_get_attachment_image_src( $this->logo_id, 'medium' );

                $this->logo_medium = $img[0];
                return $this->logo_medium;
            break;
            case 'plattegrond_thumb':
                if(isset($this->plattegrond_thumb)) return $this->plattegrond_thumb;

                $img = wp_get_attachment_image_src( $this->plattegrond_id, 'thumbnail' );

                $this->plattegrond_thumb = $img[0];
                return $this->plattegrond_thumb;
            break;
            case 'plattegrond_full':
                if(isset($this->plattegrond_full)) return $this->plattegrond_full;

                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) $this->sr->switch_to_blog($this->id);
                $img = wp_get_attachment_image_src( $this->plattegrond_id, 'full' );
                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) restore_current_blog();

                $this->plattegrond_full = $img[0];
                return $this->plattegrond_full;
            break;
            case 'accommodaties':

                if ($this->accommodaties == null) {
                    $this->accommodaties = array();
                    $accommodaties = $this->wpdb->get_results($this->wpdb->prepare('select a.id from ' . SIMPEL_DB_PREFIX . 'accommodatie a inner join ' . SIMPEL_DB_PREFIX . 'acco_type t on a.acco_type_id = t.id where camping_id = "%d" order by seq_inner, t.title, a.title', $this->id));
                    foreach ($accommodaties as $acco) {
                        $this->accommodaties[] = new Accommodatie($acco->id);
                    }
                }
                return $this->accommodaties;
                break;

            case 'faciliteiten':
                if (isset($this->faciliteiten))
                    return $this->faciliteiten;
                $this->faciliteiten = array();
                $sql = 'select f.* from ' . SIMPEL_DB_PREFIX . 'faciliteiten f 
					inner join ' . SIMPEL_DB_PREFIX . 'faciliteiten_per p on f.id = p.faciliteit_id
					where p.camping_id = "' . $this->id . '"';
                $all = $this->wpdb->get_results($sql);
                foreach ($all as $row) {
                    if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != 'nl') {
                        $field = 'title_' . ICL_LANGUAGE_CODE;
                        if (!empty($row->$field)) {
                            $row->title = $row->$field;
                        }
                    }
                    $this->faciliteiten[$row->id] = $row;
                }
                return $this->faciliteiten;

            case 'laatste_boeking':
                $maanden = array('', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
                $dagen = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
                $this->wpdb->show_errors();
                $laatste_boeking = $this->wpdb->get_var('select datum_boeking from ' . SIMPEL_DB_PREFIX . 'boeking where camping_id = "' . $this->id . '" order by datum_boeking desc limit 1');
                if (round((time() - strtotime($laatste_boeking)) / (60 * 60 * 24)) > 5) {
                    $laatste_boeking = $this->wpdb->get_var('select datum from ' . SIMPEL_DB_PREFIX . 'boeking_fake where camping_id = "' . $this->id . '" order by datum desc limit 1');
                    if (round((time() - strtotime($laatste_boeking)) / (60 * 60 * 24)) > 5) {
                        $this->wpdb->insert(SIMPEL_DB_PREFIX . 'boeking_fake', array('camping_id' => $this->id, 'datum' => date('Y-m-d')));
                        $laatste_boeking = date('Y-m-d');
                    }
                }
                $laatste_boeking = strtotime($laatste_boeking);
                return sprintf('%s %d %s', $dagen[date('w', $laatste_boeking)], date('d', $laatste_boeking), $maanden[date('n', $laatste_boeking)]);
                break;

            case 'afbeeldingen':
                if (isset($this->afbeeldingen))
                    return $this->afbeeldingen;
                $this->afbeeldingen = $this->thumbs = $this->img_ids = array();
                
                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) $this->sr->switch_to_blog($this->id);
                $results = $this->wpdb->get_results($this->wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'camping_foto where camping_id = "%d"', $this->id));
                foreach ($results as $row) {
                    $img = wp_get_attachment_image_src( $row->foto_id, 'medium' );
                    $thumb = wp_get_attachment_image_src( $row->foto_id, 'thumbnail' );

                    $this->thumbs[]         = $thumb[0];
                    $this->afbeeldingen[]   = $img[0];
                    $this->img_ids[]        = $row->foto_id;
                }
                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) restore_current_blog();

                return $this->afbeeldingen;
                break;
        }
    }

    function get_all_aanbiedingen() {
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'aanbiedingen_per ap
			inner join ' . SIMPEL_DB_PREFIX . 'periode p on ap.periode_id = p.id 
			inner join ' . SIMPEL_DB_PREFIX . 'accommodatie a on ap.accommodatie_id = a.id
			where tot > now() and accommodatie_id = "' . $this->id . '" order by van';
        $aanbiedingen = $this->wpdb->get_results($sql);
        $result = array();
        foreach ($aanbiedingen as $row) {
            $aanbieding = $row->aanbieding = $this->wpdb->get_row('select * from ' . SIMPEL_DB_PREFIX . 'aanbiedingen where id = "' . $row->aanbieding_id . '"');
            if (!isset($result[$aanbieding->id])) {
                $aanbieding->periodes = array($row);
                $result[$aanbieding->id] = $aanbieding;
            } else {
                $result[$aanbieding->id]->periodes[] = $row;
            }
        }
        foreach ($result as &$aanbieding) {
            $aanbieding->geldig = 'Geldig in de volgende periodes: ';
            foreach ($aanbieding->periodes as $periode) {
                $aanbieding->geldig .= date('d-m-Y', strtotime($periode->van)) . ' tot ' . date('d-m-Y', strtotime($periode->tot)) . ',';
            }
            $aanbieding->geldig = trim($aanbieding->geldig, ',') . '. Bij een verblijf van tenminste ' . $aanbieding->min_nachten . ' nachten';
        }
        usort($result, array(&$this, 'sort_aanbiedingen'));
        return $result;
    }

    function sort_aanbiedingen($a, $b) {
        return ($a->omschrijving < $b->omschrijving) ? -1 : 1;
    }

}
