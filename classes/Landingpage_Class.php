<?php

Class SimpelReserveren_Landingpage {

    private $sr;
    private $html;

    final function __construct() {
        //parent::__construct();

        global $wp_simpelreserveren;
        global $post;

        $this->sr = &$wp_simpelreserveren;
    }

    function add_content($post) {
        $show = get_post_meta($post->ID, 'sr_show_prices', true);
        if($show) {
            $this->_get_prijzen($post->ID);
            $html = $prijstabel = $this->get_front_html($post->ID);
        }
               

        if( is_singular() && is_main_query() && !defined('SR_LANDED') && $show) {
            $post->post_content .= $html;
            define('SR_LANDED', 1);
        }
        return $post;

    }

    function create_post_meta_box() {
        add_meta_box('my-meta-box', 'Simpel Reserveren', array(&$this, 'post_meta_box'), 'page', 'normal', 'high');
    }

    function _get_prijzen($post_id) {
        $post_accommodaties = get_post_meta($post_id, 'sr_accommodaties', true);
        $this->van = date('d-m-Y', strtotime(get_post_meta($post_id, 'sr_periode_van', true)));
        $this->tot = date('d-m-Y', strtotime(get_post_meta($post_id, 'sr_periode_tot', true)));

        if (count($post_accommodaties) && is_array($post_accommodaties)) {
            $accommodaties = array();
            foreach ($post_accommodaties as $acco_id => $checked) {
                $accommodaties[] = new Accommodatie($acco_id);
            }
        } else {
            $accommodaties = $this->sr->get_accommodaties(999, 'title');
        }

        $cols = array();
        foreach ($accommodaties as $i_acco => $acco) {
            $acco->prijzen = array();

            // haal de periode prijs op die exact in dezelfde periode ligt
            $prijs_periode = $acco->get_prijs($this->van, $this->tot, 2, 0, 1);
            if ($prijs_periode) {
                $acco->prijzen['periode'] = array('van' => $this->van, 'tot' => $this->tot, 'prijs' => $prijs_periode);
                $cols['periode'] = 1;
            }

            // haal de weekprijs op, probeer voor elke mogelijke dag
            for ($i = 0; $i < 7; $i++) {
                $van = date('d-m-Y', strtotime('+' . $i . ' days', strtotime($this->van)));
                $tot = date('d-m-Y', strtotime('+7 days', strtotime($van)));
                // einddag moet wel in de periode blijven vallen
                if (strtotime($tot) > strtotime($this->tot))
                    break;

                $prijs_week = $acco->get_prijs($van, $tot, 2, 0, 1);
                if ($prijs_week) {
                    $acco->prijzen['week'] = array('van' => $van, 'tot' => $tot, 'prijs' => $prijs_week);
                    $cols['week'] = 1;
                }
            }

            // haal de midweekprijs op, pak de eerste maandag en kijk van daaruit of dit mogelijk is.
            $dag = $this->van;
            while (date('w', strtotime($dag)) != 1) {
                $dag = date('d-m-Y', strtotime('+1 day', strtotime($dag)));
            }
            $tot = date('d-m-Y', strtotime('4 days', strtotime($dag)));
            if (strtotime($dag) <= strtotime($this->tot) && strtotime($tot) <= strtotime($this->tot)) {
                $prijs_midweek = $acco->get_prijs($dag, $tot, 2, 0, 1);
                if ($prijs_midweek) {
                    $acco->prijzen['midweek'] = array('van' => $dag, 'tot' => $tot, 'prijs' => $prijs_midweek);
                    $cols['midweek'] = 1;
                }
            }

            // haal de weekendprijs op, pak de eerste vrijdag en kijk van daaruit of dit mogelijk is.
            $dag = $this->van;
            while (date('w', strtotime($dag)) != 5) {
                $dag = date('d-m-Y', strtotime('+1 day', strtotime($dag)));
            }
            $tot = date('d-m-Y', strtotime('3 days', strtotime($dag)));
            if (strtotime($dag) <= strtotime($this->tot) && strtotime($tot) <= strtotime($this->tot)) {
                $prijs_weekend = $acco->get_prijs($dag, $tot, 2, 0, 1);
                if ($prijs_weekend) {
                    $acco->prijzen['weekend'] = array('van' => $dag, 'tot' => $tot, 'prijs' => $prijs_weekend);
                    $cols['weekend'] = 1;
                }
            }

            // laat de accommodaties zonder prijzen niet zien
            if (!count($acco->prijzen)) {
                unset($accommodaties[$i_acco]);
            }
        }

        $this->options = array();
        $options = array('periode', 'week', 'midweek', 'weekend');
        foreach ($options as $option) {
            if ($cols[$option])
                $this->options[] = $option;
        }

        $this->accommodaties = $accommodaties;
    }

    function post_meta_box($object, $box) {
        global $wp_simpelreserveren;
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">Aan/uit</th>
                <td>
                    <label for="sr_show_prices">
                        <input type="checkbox" value="1" name="sr_show_prices" id="sr_show_prices" <?php echo (get_post_meta($object->ID, 'sr_show_prices', true) == '1' ? 'checked="checked"' : '') ?>/>
                        Laat prijzen van een bepaalde periode zien op deze pagina
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">Titel periode</th>
                <td>
                    <input type="text" class="regular-text" name="sr_periode_title" id="sr_periode_title" value="<?php echo get_post_meta($object->ID, 'sr_periode_title', true) ?>"/>
                </td>
            </tr>
            <tr>
                <th scope="row">Periode vanaf</th>
                <td>
                    <input type="text" class="regular-text" name="sr_periode_van" id="aankomst" value="<?php echo get_post_meta($object->ID, 'sr_periode_van', true) ?>"/>
                </td>
            </tr>
            <tr>
                <th scope="row">Periode tot</th>
                <td>
                    <input type="text" class="regular-text" name="sr_periode_tot" id="vertrek" value="<?php echo get_post_meta($object->ID, 'sr_periode_tot', true) ?>"/>
                </td>
            </tr>
        <?php /*
          <tr>
          <th scope="row">Specifieke periode</th>
          <td>
          <p class="description">Of kies een specifieke periode van de reeds ingevulde periodes</p>
          <?php $periode_id = get_post_meta( $object->ID, 'sr_periode_id', true ) ?>
          <select name="sr_periode_id" id="sr_periode_id">
          <?php $periodes = $this->sr->get_periodes(); ?>
          <?php foreach($periodes as $periode) : ?>
          <option value="<?php echo $periode->id ?>" <?php echo ($periode_id == $periode->id ? 'selected="selected"' : '') ?>><?php echo date('d-m-Y', strtotime($periode->van)) ?> tot <?php echo date('d-m-Y', strtotime($periode->tot)) ?> <?php echo ($periode->naam ? '('.$periode->naam.')' : '') ?></option>
          <?php endforeach; ?>
          </select>
          </td>
          </tr>
         */ ?>
            <tr>
                <th></th>
                <td><p class="description">Vink hieronder aan welke accommodaties je wilt tonen, vink niks aan om alles te tonen</p></td>
            </tr>
            <tr>
                <th scope="row">Accommodaties</th>
                <td>
        <?php $accommodaties = $wp_simpelreserveren->get_accommodaties(999, 'title'); ?>
                    <?php $accos = get_post_meta($object->ID, 'sr_accommodaties', true); ?>
                    <?php foreach ($accommodaties as $acco) : ?>
                        <label for="sr_accommodatie_<?php echo $acco->id ?>">
                            <input type="checkbox" value="1" name="sr_accommodaties[<?php echo $acco->id ?>]" id="sr_accommodatie_<?php echo $acco->id ?>" <?php echo ($accos[$acco->id] == '1' ? 'checked="checked"' : '') ?>/>
            <?php echo $acco->title ?><br/>
                        </label>
                        <?php endforeach; ?>
                </td>
            </tr>

        </table>

        <input type="hidden" name="sr_meta_box_nonce" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />
    <?php
    }

    function save_post_meta_box($post_id, $post) {

        if (!isset($_POST['sr_meta_box_nonce']) || !wp_verify_nonce($_POST['sr_meta_box_nonce'], plugin_basename(__FILE__)))
            return $post_id;

        if (!current_user_can('edit_post', $post_id))
            return $post_id;


        $fields = array('sr_show_prices', 'sr_periode_title', 'sr_periode_van', 'sr_periode_tot');
        foreach ($fields as $field) {
            $meta_value = get_post_meta($post_id, $field, true);
            $new_meta_value = (isset($_POST[$field]) ? stripslashes($_POST[$field]) : '');
//			echo $field . ' : ' . $meta_value . ' => ' . $new_meta_value . '<br>';

            if ($new_meta_value && '' == $meta_value)
                add_post_meta($post_id, $field, $new_meta_value, true);

            elseif ('' == $new_meta_value && $meta_value)
                delete_post_meta($post_id, $field, $meta_value);

            elseif ($new_meta_value != $meta_value)
                update_post_meta($post_id, $field, $new_meta_value);
        }

        $meta_value = get_post_meta($post_id, 'sr_accommodaties', true);
        $new_meta_value = $_POST['sr_accommodaties'];

        if ($new_meta_value && '' == $meta_value)
            add_post_meta($post_id, 'sr_accommodaties', $new_meta_value);

        elseif ($new_meta_value != $meta_value)
            update_post_meta($post_id, 'sr_accommodaties', $new_meta_value);

        elseif ('' == $new_meta_value && $meta_value)
            delete_post_meta($post_id, 'sr_accommodaties', $meta_value);
    }

    function get_front_html($post_id) {
        
        ob_start();
        ?>
        <div class="clearfix"></div>
        <div class="prijstabel simpel-reserveren">
            <h3><?php echo get_post_meta($post_id, 'sr_periode_title', true); ?></h3>
            <div class="periode">
        <?php echo $this->van ?> <?php echo __('till', 'simpelreserveren') ?> <?php echo $this->tot ?>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th><?php echo __('Choose your accommodation', 'simpelreserveren') ?></th>
                <?php foreach ($this->options as $option) : ?>
                            <th><?php echo __($option, 'simpelreserveren') ?></th>
        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>

                        <?php foreach ($this->accommodaties as $i_row => $acco) : ?>
                        <tr data-toggle="collapse" class="prijsrow <?php echo ($i_row % 2 ? 'oddrow' : 'evenrow') ?>" data-target="#row<?php echo $i_row ?>">
                            <td><img src="<?php echo $acco->img ?>" style="width: 90px; margin-right: 10px;" alt=""><?php echo $acco->title ?></td>
            <?php foreach ($this->options as $option) : ?>
                <?php $prijs = $acco->prijzen[$option]['prijs']; ?>
                                <td><?php echo ($prijs ? '&euro; ' . $prijs : '') ?></td>
                        <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td colspan="<?php echo (count($acco->prijzen) + 1) ?>" class="hiddenRow">
                                <div class="accommodatie collapse" id="row<?php echo $i_row ?>">
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="sr-images">
                                                <img src="<?php echo $acco->img ?>" alt="<?php echo $acco->title ?>" title="<?php echo $acco->title ?>" class="main-img">
                                                <div class="thumbs clearfix">
            <?php if (count($acco->afbeeldingen)) : ?>
                <?php foreach ($acco->thumbs as $i => $thumb) : ?>
                                                            <img src="<?php echo $thumb ?>" alt="<?php echo $acco->title ?>">
                    <?php if ($i >= 3) break; ?>
                <?php endforeach; ?>
                                                    <?php endif; ?>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-7">
                                            <div class="sr-text">
                                                <h2><a href="<?php echo $acco->url ?>"><?php echo $acco->title ?></a></h2>
                                                <p><?php echo $acco->samenvatting ?><a href="<?php echo $acco->url ?>">meer info</a></p>
                                                <p class="boekenbuttons">
            <?php foreach ($acco->prijzen as $period => $prijs) : ?>
                                                        <a href="<?php echo $acco->boek_url ?>?aankomst=<?php echo $prijs['van'] ?>&amp;vertrek=<?php echo $prijs['tot'] ?>" class="btn sr-primary-button"><?php echo __('Book', 'simpelreserveren') ?> <?php echo __($period, 'simpelreserveren') ?></a>
            <?php endforeach; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

        <?php endforeach; ?>

                </tbody>
            </table>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

}
