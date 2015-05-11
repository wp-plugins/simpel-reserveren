<?php

class Arrangement extends Base_Class
{

    private $sr;

    function __construct($id = null)
    {
        global $wp_simpelreserveren;
        parent::__construct('simpelreserveren_arrangementen', $id);

        if (defined('ICL_LANGUAGE_CODE')) {
            $language_fields = array('title', 'omschrijving', 'overview', 'terms');
            foreach ($language_fields as $field) {
                $lang_field = $field . '_' . ICL_LANGUAGE_CODE;
                if (!empty($this->$lang_field)) {
                    $this->$field = $this->$lang_field;
                }
            }
        }

        $this->sr = &$wp_simpelreserveren;
        if (empty($this->samenvatting)) {
            $this->samenvatting = truncate_string($this->omschrijving, 150, '...');
        }

        $this->overview = apply_filters('the_content', $this->overview);
        $this->terms = apply_filters('the_content', $this->terms);

        $this->url = (defined('ICL_LANGUAGE_CODE') ? trim(icl_get_home_url(), '/') : home_url()) . '/arrangement/' . $this->name . '/';
    }


    function __get($name)
    {
        switch ($name) {
            case 'img':
            case 'afbeelding':
                if (!$this->afbeelding_id && isset($this->afbeelding)) {
                    return $this->afbeelding;
                }
                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) {
                    $this->sr->switch_to_blog($this->camping_id);
                }
                $img = wp_get_attachment_image_src($this->afbeelding_id, 'large');
                $this->afbeelding = $img[0];
                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) {
                    restore_current_blog();
                }

                return $this->afbeelding;
                break;
            case 'resized_img':
                if (isset($this->resized_img)) {
                    return $this->resized_img;
                }
                if (!$this->afbeelding_id) {
                    return;
                }

                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) {
                    $this->sr->switch_to_blog($this->camping_id);
                }
                $img = wp_get_attachment_image_src($this->afbeelding_id, 'sr-thumb-368');
                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) {
                    restore_current_blog();
                }

                $this->resized_img = $img[0];

                return $this->resized_img;
                break;

            case 'afbeeldingen':
                if (isset($this->afbeeldingen)) {
                    return $this->afbeeldingen;
                }
                $this->afbeeldingen = $this->thumbs = $this->afbeeldingen_large = $this->img_ids = array();

                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) {
                    $this->sr->switch_to_blog($this->camping_id);
                }
                $results = $this->wpdb->get_results($this->wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'arrangement_foto where arrangement_id = "%d"', $this->id));
                foreach ($results as $row) {
                    $img = wp_get_attachment_image_src($row->foto_id, 'sr-thumb-368');
                    $thumb = wp_get_attachment_image_src($row->foto_id, 'thumbnail');
                    $large = wp_get_attachment_image_src($row->foto_id, 'large');

                    $this->thumbs[] = $thumb[0];
                    $this->afbeeldingen[] = $img[0];
                    $this->afbeeldingen_large[] = $large[0];
                    $this->img_ids[] = $row->foto_id;
                }
                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) {
                    restore_current_blog();
                }

                return $this->afbeeldingen;
                break;
            case 'beschikbaarheid':
                // kijk of de beschikbaarheid al eerder geset is, dan hoeft het niet opnieuw
                if (isset($this->beschikbaarheid)) {
                    return $this->beschikbaarheid;
                }

                $this->beschikbaarheid = array();
                $accommodaties = $this->get_possible_accommodaties();

                // create the combined availability of all the
                foreach ($accommodaties as $accommodatie) {
                    foreach ($accommodatie->beschikbaarheid as $year => $dagen) {
                        if (!isset($this->beschikbaarheid[$year])) {
                            $this->beschikbaarheid[$year] = $dagen;
                        } else {
                            for ($i=0; $i<strlen($dagen); $i++) {
                                if ($this->beschikbaarheid[$year][$i] != 'X' && $dagen[$i] == 'X') {
                                    $this->beschikbaarheid[$year][$i] = 'X';
                                } elseif ($this->beschikbaarheid[$year][$i] != 'A' && $dagen[$i] == 'A') {
                                    $this->beschikbaarheid[$year][$i] = 'A';
                                }
                            }
                        }
                    }
                }

                return $this->beschikbaarheid;

            break;


        }
    }

    /**
     * Get all the possible accomodaties for current arrangement, without period constraint
     *
     * @return array of Accommodatie classes
     */
    function get_possible_accommodaties()
    {
        if ($this->altijd_geldig) {
            // get all accommodaties
            $accommodaties = $this->wpdb->get_results('select id from ' . SIMPEL_DB_PREFIX . 'accommodatie order by title');
        } else {
            $accommodatie_ids = $this->wpdb->get_results('select accommodatie_id from ' . SIMPEL_DB_PREFIX . 'arrangementen_per where arrangement_id = "' . $this->id . '"');
            $ids = array();
            foreach ($accommodatie_ids as $acco) {
                if (!in_array($acco->accommodatie_id, $ids)) {
                    $ids[] = $acco->accommodatie_id;
                }
            }
            $accommodaties = $this->wpdb->get_results('select id from ' . SIMPEL_DB_PREFIX . 'accommodatie where id in (' . implode(',', $ids) . ') order by title');
        }
        $result = array();
        foreach ($accommodaties as $acco) {
            $result[] = new Accommodatie($acco->id);
        }

        return $result;
    }

}
