<?php

class Accommodatie extends Base_Class
{

    private $prijsberekening;
    private $sr;

    function __construct($id = null)
    {
        global $wp_simpelreserveren;
        parent::__construct('simpelreserveren_accommodatie', $id);

        if (defined('ICL_LANGUAGE_CODE')) {
            $language_fields = array('samenvatting', 'omschrijving', 'button_tekst');
            foreach ($language_fields as $field) {
                $lang_field = $field . '_' . ICL_LANGUAGE_CODE;
                if (!empty($this->$lang_field)) {
                    $this->$field = ($this->$lang_field);
                }
            }
            $language_fields = array('title');
            foreach ($language_fields as $field) {
                $lang_field = $field . '_' . ICL_LANGUAGE_CODE;
                if (!empty($this->$lang_field)) {
                    $this->$field = utf8_decode($this->$lang_field);
                }
            }
        }

        $this->sr = &$wp_simpelreserveren;
        //add_filter( 'excerpt_length', function(){ return 10; }, 999 );
        if (empty($this->samenvatting)) {
            $this->samenvatting = truncate_string($this->omschrijving, 150, '...');
        }

        $this->omschrijving = apply_filters('the_content', $this->omschrijving);

        $this->url_extern = $this->url;

        $this->url = (defined('ICL_LANGUAGE_CODE') ? trim(icl_get_home_url(), '/') : home_url()) . '/' . SIMPEL_ACCOMMODATIE_SLUG . '/' . $this->name . '/';
        $this->boek_url = $this->boek_url();

        preg_match('/(.*)\(/i', $this->title, $title);
        $this->title_trimmed = @$title[1];
        $this->huisdieren = ($this->huisdieren_toegestaan ? __('pets ok', 'simpelreserveren') : __('no pets allowed', 'simpelreserveren'));

    }

    function boek_url($stap = 1, $boek_id = 0)
    {
        $url = (defined('ICL_LANGUAGE_CODE') ? trim(icl_get_home_url(), '/') : home_url()) . '/boeken/' . $this->id . '/stap' . $stap . '/' . str_replace('+', '-',
                urlencode(str_replace(array('(', ')', '/'), '', strtolower($this->title)))) . '/' . (isset($_GET['refer']) ? '?refer=' . $_GET['refer'] : '');
        if ($boek_id) {
            $boeken = $this->wpdb->get_row($this->wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'boeking where id = "%d"', $boek_id));
            $url .= '?boekid=' . $boeken->id;
            $hash = sha1(AUTH_SALT . sha1($boeken->id));
            $url .= '&hash=' . $hash;
        }

        return $url;
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
            case 'boek_url_2':
                if (!isset($this->boek_url_2)) {
                    $this->boek_url_2 = $this->boek_url(2);
                }

                return $this->boek_url_2;
                break;
            case 'boek_url_3':
                if (!isset($this->boek_url_3)) {
                    $this->boek_url_3 = $this->boek_url(3);
                }

                return $this->boek_url_3;
                break;
            case 'prijsberekening':
                if (isset($this->prijsberekening)) {
                    return $prijsberekening;
                }

                $this->prijsberekening = new Prijsberekening();

                return $this->prijsberekening;

                break;
            case 'arrangementen':
                if (!isset($this->arrangementen)) {
                    $this->arrangementen = $this->get_arrangementen();
                }

                return $this->arrangementen;
                break;
            case 'toeslagen':
                if (!isset($this->toeslagen)) {
                    $this->toeslagen = $this->get_toeslagen();
                }

                return $this->toeslagen;
                break;
            case 'type':
                if (isset($this->type)) {
                    return $this->type;
                }
                $this->type = $this->wpdb->get_row('select * from ' . SIMPEL_DB_PREFIX . 'acco_type where id = "' . $this->acco_type_id . '"');

                return $this->type;
                break;
            case 'camping':
                if (isset($this->camping)) {
                    return $this->camping;
                }
                $this->camping = new Camping($this->camping_id);

                return $this->camping;
                break;
            case 'afbeeldingen':
                if (isset($this->afbeeldingen)) {
                    return $this->afbeeldingen;
                }
                $this->afbeeldingen = $this->thumbs = $this->afbeeldingen_large = $this->img_ids = array();

                if (SIMPEL_MULTIPLE && function_exists('restore_current_blog')) {
                    $this->sr->switch_to_blog($this->camping_id);
                }
                $results = $this->wpdb->get_results($this->wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'accommodatie_foto where accommodatie_id = "%d"', $this->id));
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
            case 'alternatieve_prijzen':
                if (isset($this->alternatieve_prijzen)) {
                    return $this->alternatieve_prijzen;
                }

                return $this->get_alternatieve_prijzen();
                break;
            case 'faciliteiten':
                if (isset($this->faciliteiten)) {
                    return $this->faciliteiten;
                }
                $this->faciliteiten = array();
                $sql = 'select f.* from ' . SIMPEL_DB_PREFIX . 'faciliteiten f 
					inner join ' . SIMPEL_DB_PREFIX . 'faciliteiten_per p on f.id = p.faciliteit_id
					where p.accommodatie_id = "' . $this->id . '"';
                $all = $this->wpdb->get_results($sql);
                foreach ($all as $row) {
                    if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != 'nl') {
                        $field = 'title_' . ICL_LANGUAGE_CODE;
                        $row->title = $row->$field;
                    }
                    $this->faciliteiten[$row->id] = $row;
                }

                return $this->faciliteiten;
            case 'beschikbaarheid':
                // kijk of de beschikbaarheid al eerder geset is, dan hoeft het niet opnieuw
                if (isset($this->beschikbaarheid)) {
                    return $this->beschikbaarheid;
                }

                $this->beschikbaarheid = array();
                if (defined('SIMPEL_AANTALLEN') && SIMPEL_AANTALLEN) {
                    for ($jaar = date('Y'); $jaar <= date('Y') + 1; $jaar++) {
                        $this->beschikbaarheid[$jaar] = 'O';
                        for ($i = 0; $i < 366; $i++) {
                            $this->beschikbaarheid[$jaar][$i] = 'O';
                        }
                    }
                    $rows = $this->wpdb->get_results('select datum, nr from ' . SIMPEL_DB_PREFIX . 'available where datum >= now() and accommodatie_id = "' . $this->id . '"');
                    foreach ($rows as $row) {
                        if ($row->nr > 0) {
                            $time = strtotime($row->datum);
                            $this->beschikbaarheid[date('Y', $time)][date('z', $time)] = 'A';
                        }
                    }

                } else {
                    for ($jaar = date('Y'); $jaar <= date('Y') + 1; $jaar++) {
                        $sql = 'select dagen from ' . SIMPEL_DB_PREFIX . 'beschikbaarheid where jaar = "' . $jaar . '" and accommodatie_id = "' . $this->id . '"';
                        $dagen = $this->wpdb->get_var($sql);
                        if ($dagen != '') {
                            $this->beschikbaarheid[$jaar] = $dagen;
                        } else {
                            $this->beschikbaarheid[$jaar] = 'A';
                            for ($i = 0; $i < 366; $i++) {
                                $this->beschikbaarheid[$jaar][$i] = 'A';
                            }
                        }
                    }
                }
                if ($this->mode == 'manage') {
                    return $this->beschikbaarheid;
                }

                // loop alle periodes bij langs om te kijken wat geldige aankomstdagen zijn
                $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'periode where camping_id = "' . $this->camping_id . '" and acco_type_id = "' . $this->acco_type_id . '" and tot > now() order by van';
                $all = $this->wpdb->get_results($sql);

                $opties = array('week', 'weekend', 'midweek', 'periode');
                $dagen = array('zo', 'ma', 'di', 'wo', 'do', 'vr', 'za');
                foreach ($all as $periode) {
                    $dag = strtotime($periode->van);
                    $einddag = strtotime($periode->tot);
                    if (isset($this->beschikbaarheid[date('Y', $einddag)][date('z', $einddag)]) && $this->beschikbaarheid[date('Y', $einddag)][date('z', $einddag)] == 'A') {
                        $this->beschikbaarheid[date('Y', $einddag)][date('z', $einddag)] = 'X';
                    }

                    // kijk per periode welke prijzen er mogelijk zijn		
                    $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'prijs where periode_id = ' . $periode->id . ' and accommodatie_id = ' . $this->id;
                    $prijzen = $this->wpdb->get_row($sql);
                    // als er een prijs voor de hele periode is, dan is dit altijd een aankomstdag
                    if (is_object($prijzen) && $prijzen->periodeprijs > 0 && $this->beschikbaarheid[date('Y', $dag)][date('z', $dag)] != 'O') {
                        $i = $dag;

                        if (isset($this->beschikbaarheid[date('Y', $dag)][date('z', $dag)]) && $this->beschikbaarheid[date('Y', $dag)][date('z', $dag)] == 'A') {
                            $this->beschikbaarheid[date('Y', $dag)][date('z', $dag)] = 'X';
                        }

                        /* while($i <= strtotime($periode->tot))
                          {
                          $i = strtotime('+1 day', $i);
                          } */
                    }

                    while (date('Y-m-d', $dag) <= $periode->tot) {
                        if (isset($this->beschikbaarheid[date('Y', $dag)][date('z', $dag)]) && $this->beschikbaarheid[date('Y', $dag)][date('z', $dag)] == 'A') {
                            $letter = 'X';
                            // als er een midweekprijs is en de dag maandag is
                            if (date('w', $dag) == 1 && $prijzen && $prijzen->midweekprijs > 0) {
                                // kijk of de hele midweek vrij is
                                $this->beschikbaarheid[date('Y', $dag)][date('z', $dag)] = 'X';
                                $einddag = strtotime('+4 days', $dag);
                                if ($this->beschikbaarheid[date('Y', $einddag)][date('z', $einddag)] == 'A') {
                                    $this->beschikbaarheid[date('Y', $einddag)][date('z', $einddag)] = 'X';
                                }

                                // als er een weekendprijs is en de dag vrijdag is
                            } elseif (date('w', $dag) == 5 && $prijzen && $prijzen->weekendprijs > 0) {
                                // kijk of het hele weekend vrij is
                                $this->beschikbaarheid[date('Y', $dag)][date('z', $dag)] = 'X';
                                $einddag = strtotime('+3 days', $dag);
                                if ($this->beschikbaarheid[date('Y', $einddag)][date('z', $einddag)] == 'A') {
                                    $this->beschikbaarheid[date('Y', $einddag)][date('z', $einddag)] = 'X';
                                }

                                // als er een weekprijs is, kijk dan naar de mogelijke aankomstdagen
                            }

                            if (is_object($prijzen) && $prijzen->weekprijs > 0) {
                                $weekdag = date('w', $dag);
                                $periode_weekdag = $dagen[$weekdag];
                                if ($periode->$periode_weekdag) {
                                    // kijk of de hele week vrij is
                                    $this->beschikbaarheid[date('Y', $dag)][date('z', $dag)] = 'X';

                                    if ($letter == 'X') { // als de hele week beschikbaar is, ook de laatste dag op 'X' zetten, zodat deze als einddatum aan kan worden geklikt
                                        $einddag = strtotime('+7 days', $dag);
                                        //$this->beschikbaarheid[ date('Y', $einddag) ][ date('z', $einddag) ] = 'X';
                                    }
                                }
                            }
                            if (is_object($prijzen) && $prijzen->nachtprijs > 0) {
                                $weekdag = date('w', $dag);
                                $periode_weekdag = $dagen[$weekdag];
                                if ($periode->$periode_weekdag) {
                                    $this->beschikbaarheid[date('Y', $dag)][date('z', $dag)] = 'X';
                                }
                            }
                        }
                        $dag = strtotime('+1 day', $dag);
                    }
                }

                return $this->beschikbaarheid;
                break;
        }
    }

    function is_beschikbaar($van, $tot)
    {
        if (!is_numeric($van)) {
            $van = strtotime($van);
        }
        if (!is_numeric($tot)) {
            $tot = strtotime($tot);
        }

        $i = $van;
        $beschikbaar = true;
        while ($i < $tot) {
            $dag = $this->beschikbaarheid[date('Y', $i)][date('z', $i)];
            if ($dag == 'O') {
                $beschikbaar = false;
            }
            $i = strtotime('+1 day', $i);
        }

        return $beschikbaar;
    }

    function get_alternatieve_prijzen()
    {

        if (isset($_GET['aankomst'])) {
            $van = $_GET['aankomst'];
            $tot = $_GET['vertrek'];
            $volw = $_GET['volw'];
            $kind = isset($_GET['kind']) ? $_GET['kind'] : 0;
        } elseif (isset($_SESSION['aankomst'])) {
            $van = $_SESSION['aankomst'];
            $tot = $_SESSION['vertrek'];
            $volw = $_SESSION['volw'];
            $kind = isset($_SESSION['kind']) ? $_SESSION['kind'] : 0;
        } else {
            $van = date('Y-m-d');
            $tot = date('Y-m-d', strtotime("+" . $this->min_aantal_nachten . " days"));
            $volw = 2;
            $kind = 0;
        }
        if (is_numeric($van)) {
            $van = date('Y-m-d', $van);
        }
        if (is_numeric($tot)) {
            $tot = date('Y-m-d', $tot);
        }

        if (empty($van)) {
            $van = date('Y-m-d');
        }
        if (empty($tot)) {
            $tot = date('Y-m-d', strtotime("+" . $this->min_aantal_nachten . " days"));
        }
        $time_van = strtotime($van);
        $time_tot = strtotime($tot);
        $nachten = round(($time_tot - $time_van) / (60 * 60 * 24));

        $prijzen = array();

        // arrangement
        $arrangementen = $this->get_arrangementen($van);
        foreach ($arrangementen as $arrangement) {
            if ($arrangement->van == date('Y-m-d', strtotime($van)) && $arrangement->tot == date('Y-m-d', strtotime($tot))) {
                continue;
            }
            if (!$arrangement->naam) {
                continue;
            }
            if ($this->is_beschikbaar($arrangement->van, $arrangement->tot)) {

                $prijs = $this->get_prijs($arrangement->van, $arrangement->tot, $volw, $kind, 1, 1, $_GET['arrangement']);

                $prijzen[strtolower($arrangement->naam)] = array(
                    'prijs' => $prijs,
                    'korting' => $this->korting,
                    'aanbieding' => $arrangement->omschrijving,
                    'van' => date('d-m-Y', strtotime($arrangement->van)),
                    'tot' => date('d-m-Y', strtotime($arrangement->tot)),
                    'url' => $arrangement->url
                );
                break;
            }
        }

        // weekend
        if (date('w', $time_van) != 5 || $nachten != 3) {
            $vrijdag = $time_van;
            while (date('w', $vrijdag) != 5) {
                $vrijdag = strtotime('+1 day', $vrijdag);
            }

            if ($this->is_beschikbaar($vrijdag, strtotime('+3 days', $vrijdag))) {
                $prijs = $this->get_prijs($vrijdag, strtotime('+3 days', $vrijdag), $volw, $kind, 1, 1, isset($_GET['arrangement']) ? $_GET['arrangement'] : 0);
                if ($prijs) {
                    $prijzen[__('weekend', 'simpelreserveren')] = array(
                        'prijs' => $prijs,
                        'korting' => $this->korting,
                        'aanbieding' => (isset($this->aanbieding) && isset($this->aanbieding->omschrijving) ? $this->aanbieding->omschrijving : ''),
                        'van' => date('d-m-Y', $vrijdag),
                        'tot' => date('d-m-Y', strtotime('+3 days', $vrijdag)),
                        'url' => $this->boek_url . '?aankomst=' . date('d-m-Y', $vrijdag) . '&vertrek=' . date('d-m-Y', strtotime('+3 days', $vrijdag))
                    );
                }
            }
        }

        // midweek
        if (date('w', $time_van) != 1 || $nachten != 4) {
            $maandag = $time_van;
            while (date('w', $maandag) != 1) {
                $maandag = strtotime('+1 day', $maandag);
            }
            if ($this->is_beschikbaar($maandag, strtotime('+4 days', $maandag))) {
                $prijs = $this->get_prijs($maandag, strtotime('+4 days', $maandag), $volw, $kind, 1, 1, isset($_GET['arrangement']) ? $_GET['arrangement'] : 0);
                if ($prijs) {
                    $prijzen[__('midweek', 'simpelreserveren')] = array(
                        'prijs' => $prijs,
                        'korting' => $this->korting,
                        'aanbieding' => (isset($this->aanbieding) && isset($this->aanbieding->omschrijving) ? $this->aanbieding->omschrijving : ''),
                        'van' => date('d-m-Y', $maandag),
                        'tot' => date('d-m-Y', strtotime('+4 days', $maandag)),
                        'url' => $this->boek_url . '?aankomst=' . date('d-m-Y', $maandag) . '&vertrek=' . date('d-m-Y', strtotime('+4 days', $maandag))
                    );
                }
            }
        }

        // week
        if ($nachten != 7 || $this->beschikbaarheid[date('Y', $time_van)][date('z', $time_van)] != 'X') {
            $i = 0;
            while ($this->beschikbaarheid[date('Y', $time_van)][date('z', $time_van)] != 'X' && $i++ < 7) {
                $time_van = strtotime('+1 day', $time_van);
            }
            if ($this->is_beschikbaar($time_van, strtotime('+7 days', $time_van))) {
                $prijs = $this->get_prijs($time_van, strtotime('+7 days', $time_van), $volw, $kind, 1, 1, isset($_GET['arrangement']) ? $_GET['arrangement'] : 0);
                if ($prijs) {
                    $prijzen[__('week', 'simpelreserveren')] = array(
                        'prijs' => $prijs,
                        'korting' => $this->korting,
                        'aanbieding' => ((is_object($this->aanbieding)) ? $this->aanbieding->omschrijving : ''),
                        'van' => date('d-m-Y', $time_van),
                        'tot' => date('d-m-Y', strtotime('+7 days', $time_van)),
                        'url' => $this->boek_url . '?aankomst=' . date('d-m-Y', $time_van) . '&vertrek=' . date('d-m-Y', strtotime('+7 days', $time_van))
                    );
                }
            }
        }

        $this->alternatieve_prijzen = $prijzen;

        return $this->alternatieve_prijzen;
    }

    function get_prijs($van, $tot, $volw, $kind, $force = 0, $inclusief_toeslagen = 1, $arrangement_id = 0)
    {
        $start = microtime(true);
        if (($volw + $kind) > $this->aantal_personen) {
            return;
        }

        if (defined('SIMPEL_ONLY_ARRANGEMENT') && SIMPEL_ONLY_ARRANGEMENT && !$arrangement_id) {
            return;
        }

        if (!isset($this->prijsberekening)) {
            $this->prijsberekening = new Prijsberekening();
        }
        if (is_numeric($van)) {
            $van = date('Y-m-d', $van);
        }
        if (is_numeric($tot)) {
            $tot = date('Y-m-d', $tot);
        }

        $check_day = $time_van = strtotime($van);
        $time_tot = strtotime($tot);
        $nachten = round(($time_tot - $time_van) / (60 * 60 * 24));

        if ($nachten < $this->min_aantal_nachten) {
            if (!$force) {

                $this->alternative = 1;
                $this->van = $van;
                $this->tot = date('Y-m-d', strtotime('+' . $this->min_aantal_nachten . ' days', $time_van));

                return $this->get_prijs($this->van, $this->tot, $volw, $kind, 1);
            }

            return;
        }

        $this->__get('beschikbaarheid');
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'periode where camping_id = "' . $this->camping->id . '" and acco_type_id = "' . $this->acco_type_id . '" and tot = "' . date('Y-m-d', strtotime($tot)) . '"';

        $periodes = $this->wpdb->get_results($sql);

        if (!isset($this->beschikbaarheid[date('Y', $time_van)])) {
            return;
        }
        $een_na_laatste_dag = strtotime('-1 day', $time_tot);

        if ($this->beschikbaarheid[date('Y', $time_van)][date('z', $time_van)] != 'X' ||
            ($this->beschikbaarheid[date('Y', $time_tot)][date('z', $time_tot)] != 'X' && !count($periodes) && $this->beschikbaarheid[date('Y', $een_na_laatste_dag)][date('z', $een_na_laatste_dag)] == 'O')
        ) {
            if (!$force) {
                $this->alternative = 1;
                $this->van = date('Y-m-d', $time_van);
                for ($i = 0; $i < 10; $i++) {
                    $day = strtotime('+' . $i . ' days', $time_van);
                    if ($this->beschikbaarheid[date('Y', $day)][date('z', $day)] == 'X') {
                        $this->van = date('Y-m-d', $day);
                        break;
                    }
                }
                $this->tot = date('Y-m-d', $time_tot);
                for ($i = 0; $i < 10; $i++) {
                    $day = strtotime('+' . $i . ' days', $time_tot);
                    if ($this->beschikbaarheid[date('Y', $day)][date('z', $day)] == 'X') {
                        $this->tot = date('Y-m-d', $day);
                        break;
                    }
                }
                $prijs = $this->get_prijs($this->van, $this->tot, $volw, $kind, 1);

                if ($prijs) {
                    return $prijs;
                } else { // als er geen datum in de buurt is, kan het zijn dat er een periode met alleen een periodeprijs in de buurt is, dan deze pakken
                    $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'periode 
						where acco_type_id = "' . $this->acco_type_id . '" and van >= "' . date('Y-m-d', $time_van) . '" and van < "' . date('Y-m-d', $time_tot) . '"
						order by van 
						limit 1';

                    $row = $this->wpdb->get_row($sql);
                    if ($row && $row->id) {
                        $this->alternative = 1;

                        return $this->get_prijs($row->van, $row->tot, $volw, $kind, 1);
                    }

                    return;
                }
            }

            $this->error = 'Aankomst- of vertrekdag niet geldig';

            return;
        }

        while (date('Ymd', $check_day) < date('Ymd', $time_tot)) {
            $this->debug .= 'beschikbaar ' . date('d-m-Y', $check_day) . ': ' . $this->beschikbaarheid[date('Y', $check_day)][date('z', $check_day)] . '<br>';
            //echo 'beschikbaar ' . date('d-m-Y', $check_day) . ': ' . $this->beschikbaarheid[date('Y', $check_day)][date('z', $check_day)] . '<br>';
            if ($this->beschikbaarheid[date('Y', $check_day)][date('z', $check_day)] == 'O') {
                $this->error = 'Deze periode is bezet';

                return;
            }
            $check_day = strtotime('+1 day', $check_day);
        }

        $prijs = $this->prijsberekening->bereken($this->id, $van, $tot, $volw, $kind);

        // period title and period ids
        $this->path = $prijs->path;
        $this->periode_title = $prijs->periode_title;
        $this->periodes = array();
        if (is_array($this->path)) {
	        foreach ( $this->path as $path ) {
		        $this->periodes[] = $path->periode->id;
	        }
        }

        if ((!$prijs || !$prijs->prijs) && !$force) {
            $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'periode 
				where acco_type_id = "' . $this->acco_type_id . '" and van >= "' . date('Y-m-d', $time_van) . '" and van < "' . date('Y-m-d', $time_tot) . '"
				order by van 
				limit 1';

            $row = $this->wpdb->get_row($sql);
            if ($row && $row->id) {
                $this->alternative = 1;
                $this->van = $row->van;
                $this->tot = $row->tot;

                return $this->get_prijs($row->van, $row->tot, $volw, $kind, 1);
            }
            $this->alternative = 1;
            $this->van = $van;
            $this->tot = date('Y-m-d', strtotime('+' . (7 * ceil($nachten / 7)) . ' days', strtotime($van)));

            return $this->get_prijs($this->van, $this->tot, $volw, $kind, 1);
        }


        //echo $this->id . ' => ' . (microtime(true) - $start) . 'sec<br/>';
        if ($this->sr->get_setting('prijs-incl-verpl-toeslagen') && $prijs && $inclusief_toeslagen) {
	        $this->_bereken_toeslagen($prijs, ($volw + $kind), $van, $tot);
        }

        if ($prijs) {
            if ($arrangement_id) {
                $this->arrangement = $this->get_arrangement($arrangement_id);

                // check if is valid in this period
                if (!$this->arrangement->altijd_geldig) {
                    $periodes = $this->wpdb->get_results($this->wpdb->prepare('select periode_id from ' . SIMPEL_DB_PREFIX . 'arrangementen_per where arrangement_id = "%d" and accommodatie_id = "%d"', $this->arrangement->id, $this->id));
                    $arrangement_periodes = array();
                    foreach ($periodes as $row) {
                        if (!in_array($row->periode_id, $arrangement_periodes)) {
                            $arrangement_periodes[] = $row->periode_id;
                        }
                    }

                    foreach ($this->periodes as $periode_id) {
                        if (!in_array($periode_id, $arrangement_periodes)) {
                            return;
                        }
                    }
                }


                // calculate price
                if ($this->arrangement->max_nights && ($this->arrangement->max_nights < $nachten)) {
	                unset($this->arrangement);
                } elseif ($this->arrangement->min_nights && ($this->arrangement->min_nights > $nachten)) {
	                unset($this->arrangement);
                } else {
                    if ($this->arrangement) {
                        $arrangement_prijs = $this->arrangement->prijs;

	                    // kijk of er een aparte prijs is voor dit arrangement
	                    $row = $this->wpdb->get_row( $this->wpdb->prepare(
		                    'select * from ' . SIMPEL_DB_PREFIX . 'arrangementen_prijzen where accommodatie_id = "%d" and arrangement_id = "%d"',
		                    $this->id, $arrangement_id ) );
	                    if ($row) {
		                    $arrangement_prijs = $row->prijs;
		                    $arrangement_prijs += ($volw * $row->meerprijs_volw);
		                    $arrangement_prijs += ($kind * $row->meerprijs_kind);
	                    }

                        if ($this->arrangement->per == 'person') {
                            $arrangement_prijs *= $volw;
                        } elseif ($this->arrangement->per == 'person_night') {
                            $arrangement_prijs *= $volw * $nachten;
                        } elseif ($this->arrangement->per == 'night') {
                            $arrangement_prijs *= $nachten;
                        }

	                    if($this->arrangement->overwrite) {
		                    $prijs->prijs = $arrangement_prijs;
	                    } else {
		                    $prijs->prijs += $arrangement_prijs;
	                    }
                    }
                }
            }
            $this->korting = $prijs->korting;
            $this->aanbieding = isset($prijs->aanbieding) ? $prijs->aanbieding : 0;

            return $prijs->prijs;
        }
    }

    private function get_arrangement($arrangement_id)
    {
        $arrangement = $this->wpdb->get_row($this->wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'arrangementen where id = "%d"', $arrangement_id));
        if (defined('ICL_LANGUAGE_CODE')) {
            $fields = array('title', 'omschrijving');
            foreach ($fields as $field) {
                $lang_field = $field . '_' . ICL_LANGUAGE_CODE;
                $arrangement->$field = $arrangement->$lang_field;
            }
        }

        return $arrangement;
    }

    private function _bereken_toeslagen(&$prijs, $personen, $van, $tot)
    {
        $toeslagen = $this->get_toeslagen($van, $tot, $personen, $prijs->prijs);
        foreach ($toeslagen as $toeslag) {
            if ($toeslag->verplicht && !$toeslag->borgsom && !$toeslag->ter_plaatse_betalen) {
                $prijs->prijs += $toeslag->totaal_prijs;
            }
        }
    }

    function get_overige_prijzen($aankomst, $vertrek, $volw = 2, $kind = 0, $inclusief_toeslagen = 0)
    {
        if (!is_numeric($aankomst)) {
            $aankomst = strtotime($aankomst);
            $vertrek = strtotime($vertrek);
        }
        $nachten = round(($vertrek - $aankomst) / (60 * 60 * 24));

        $prijzen = array();
        // als het niet exact al een midweek is, de prijs van de midweek opzoeken
        if (date('w', $aankomst) != 1 || $nachten != 4) {
            $dag = $aankomst;
            while (date('w', $dag) != 1) {
                $dag = strtotime('+1 day', $dag);
            }
            $prijs_midweek = $this->get_prijs($dag, strtotime('+4 days', $dag), $volw, $kind, 1, 1);
            if ($prijs_midweek) {
                $prijzen['midweek'] = array('prijs' => $prijs_midweek, 'van' => $dag, 'tot' => strtotime('+4 days', $dag));
            }
        }

        // als het niet exact een weekend is, de prijs van het eerst komende weekend opzoeken
        if (date('w', $aankomst) != 5 || $nachten != 3) {
            $dag = $aankomst;
            while (date('w', $dag) != 5) {
                $dag = strtotime('+1 day', $dag);
            }
            $prijs_weekend = $this->get_prijs($dag, strtotime('+3 days', $dag), $volw, $kind, 1, 1);
            if ($prijs_weekend) {
                $prijzen['weekend'] = array('prijs' => $prijs_weekend, 'van' => $dag, 'tot' => strtotime('+3 days', $dag));
            }
        }

        // probeer voor alle komende dagen een weekprijs te berekenen
        if ($nachten != 7) {
            for ($i = 0; $i < 7; $i++) {
                $dag = strtotime('+' . $i . ' days', $aankomst);
                $prijs_week = $this->get_prijs($dag, strtotime('+7 days', $dag), $volw, $kind, 1, 1);
                if ($prijs_week) {
                    $prijzen['week'] = array('prijs' => $prijs_week, 'van' => $dag, 'tot' => strtotime('+7 days', $dag));
                    break;
                }
            }
        }

        return $prijzen;
    }

    function get_all_aanbiedingen()
    {
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'aanbiedingen_per ap
			inner join ' . SIMPEL_DB_PREFIX . 'periode p on ap.periode_id = p.id 
			where tot > now() and accommodatie_id = "' . $this->id . '" order by van';
        $aanbiedingen = $this->wpdb->get_results($sql);
        $result = array();
        foreach ($aanbiedingen as $row) {
            $aanbieding = $row->aanbieding = $this->wpdb->get_row('select * from ' . SIMPEL_DB_PREFIX . 'aanbiedingen where id = "' . $row->aanbieding_id . '"');
            if (!$aanbieding) {
                continue;
            }
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

    function sort_aanbiedingen($a, $b)
    {
        return ($a->omschrijving < $b->omschrijving) ? -1 : 1;
    }

    function get_arrangementen($aankomst = null)
    {
        if (!$aankomst) {
            $aankomst = date('Y-m-d');
        }
        $aankomst = date('Y-m-d', strtotime($aankomst));

        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'periode p
			inner join ' . SIMPEL_DB_PREFIX . 'prijs pr on p.id = pr.periode_id
			where accommodatie_id = "' . $this->id . '" and periodeprijs > 0 and tot > now() and ABS(datediff(van, "' . $aankomst . '")) < 21 order by ABS(datediff(van, "' . $aankomst . '"))';
        $periodes = $this->wpdb->get_results($sql);
        foreach ($periodes as $periode) {
            $periode->url = $this->boek_url . '?aankomst=' . date('d-m-Y', strtotime($periode->van)) . '&vertrek=' . date('d-m-Y', strtotime($periode->tot));
            if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != 'nl') {
                $fields = array('naam' => 'naam_' . ICL_LANGUAGE_CODE, 'omschrijving' => 'omschrijving_' . ICL_LANGUAGE_CODE);
                foreach ($fields as $old_field => $new_field) {
                    if (!empty($periode->$new_field)) {
                        $periode->$old_field = $periode->$new_field;
                    }
                }
            }
        }

        return $periodes;
    }

    public function get_toeslagen($aankomst = null, $vertrek = null, $personen = 2, $prijs_accommodatie = 0)
    {
        $toeslagen = array();
        if (!$prijs_accommodatie) {
            if (!isset($_SESSION['volw'])) {
                $_SESSION['volw'] = 2;
            }
            if (!isset($_SESSION['kind'])) {
                $_SESSION['kind'] = 0;
            }
            if (!isset($_SESSION['klein_kind'])) {
                $_SESSION['klein_kind'] = 0;
            }
            $prijs_accommodatie = $this->get_prijs($aankomst, $vertrek, $_SESSION['volw'] + $_SESSION['kind'], $_SESSION['klein_kind'], 1);
        }

        $nachten = round((strtotime($vertrek) - strtotime($aankomst)) / (60 * 60 * 24));

        // voeg de toeslagen toe
        $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'toeslagen where camping_id = "' . $this->camping->id . '" order by seq, title';
        $all = $this->wpdb->get_results($sql);
        foreach ($all as $row) {
            // vertaal de titel voor anders dan nederlands
            if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != 'nl') {
                $field = 'title_' . ICL_LANGUAGE_CODE;
                $row->title = $row->$field;
            }

            // kijk of de toeslag alleen in bepaalde periodes geldig is
            if ($row->periodes) {
                $periodes = explode(',', $row->periodes);
                $found = 0;
                foreach ($periodes as $periode_id) {
                    if (is_array($this->periodes) && in_array($periode_id, $this->periodes)) {
                        $found = 1;
                    }
                }
                if (!$found) {
                    continue;
                }
            }

            // kijk of de toeslag alleen voor bepaalde arrangementen geldig is
            if ($row->arrangementen) {
                $arrangementen = explode(',', $row->arrangementen);
                $found = 0;
                if (isset($_GET['arrangement'])) {
                    if (in_array($_GET['arrangement'], $arrangementen)) {
                        $found = 1;
                    }
                } elseif (isset($_SESSION['boeken']['arrangement'])) {
	                if (in_array($_SESSION['boeken']['arrangement'], $arrangementen)) {
		                $found = 1;
	                }
                }
                if (!$found) {
                    continue;
                }
            }

            $row->prijs = $row->prijs_camping;
            $sql = 'select prijs from ' . SIMPEL_DB_PREFIX . 'toeslagen_per where accommodatie_id = "' . $this->id . '" and toeslag_id = "' . $row->id . '"';
            $prijs = $this->wpdb->get_var($sql);
            if ($prijs != 0) {
                $row->prijs = $prijs;
            } else {
                $prijs = $row->prijs;
            }

            switch ($row->per) {
                case 'p.persoon p.nacht':
                    $prijs *= $nachten * $personen;
                    break;
                case 'persoon':
                    $prijs *= $personen;
                    break;
                case 'nacht':
                    $prijs *= $nachten;
                    break;
                case 'aantal':
                    $field = 'toeslag-' . $row->id;
                    if (isset($_SESSION[$field])) {
                        $prijs *= $_SESSION[$field];
                    }
                    break;
                default:
                    // prijs per verblijf blijft hetzelfde
                    break;
            }
	        $row->totaal_prijs = $prijs;

            if ($row->percentage > 0) {
                $row->totaal_prijs += ($prijs_accommodatie / 100 * $row->percentage);
            }
            if ($row->totaal_prijs != 0 || $row->per == 'aantal') {
                $toeslagen[] = $row;
            }
        }

        return $toeslagen;
    }

    function get_new_arrangementen($aankomst, $vertrek)
    {
        $arrangementen = new Arrangementen();

        return $arrangementen->get_arrangementen($aankomst, $vertrek, $this->id);
    }

}
