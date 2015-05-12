<?php

Class SimpelReserveren_Widgets extends Base_Class {

    private $sr;

    function __construct(&$sr) {
        parent::__construct();
        $this->sr = &$sr;
    }

    public static function register() {
        register_widget('SimpelReserveren_Widget_Zoeken');
        register_widget('SimpelReserveren_Widget_Random');
        register_widget('SimpelReserveren_Widget_Most_Viewed');
        register_widget('SimpelReserveren_Widget_Lastminutes');
        register_widget('SimpelReserveren_Widget_Last_Booked');
    }

}

class SimpelReserveren_Widget_Zoeken extends WP_Widget {

    private $sr;

    function __construct() {
        global $wp_simpelreserveren;
        $this->sr = &$wp_simpelreserveren;

        $options = array('classname' => 'widget_sr_zoeken', 'description' => 'A widget om het Simpel Reserveren zoek blok te tonen');
        $this->WP_Widget('simpelreserveren_widget_zoeken', 'Simpel Reserveren Zoek blok', $options);
    }

    function widget($atts, $instance) {
        extract($atts, EXTR_SKIP);
        echo $before_widget;

        echo $this->sr->search_block(array('mode' => 'vertical'));


        echo $after_widget;
    }

}

class SimpelReserveren_Widget_Most_Viewed extends WP_Widget {

    private $sr;

    function __construct() {
        global $wp_simpelreserveren;
        $this->sr = &$wp_simpelreserveren;

        $options = array('classname' => 'widget_sr_most_viewed', 'description' => 'Toont de meest bekeken accommodaties van Simpel Reserveren');
        $this->WP_Widget('simpelreserveren_widget_most_viewed', 'Simpel Reserveren Meest bekeken', $options);
    }

    function widget($atts, $instance) {
        extract($atts, EXTR_SKIP);
        echo $before_widget;

        $accommodaties = $this->sr->get_accommodaties(4, 'meest bekeken');
        echo '<div class="row">';
        foreach ($accommodaties as $i => $accommodatie) {
            ?>
            <div class="col-sm-3">
                <div class="laatst-geboekt-widget" onclick="go('<?php echo $accommodatie->url ?>')">
                    <span class="prijs"><small><?php echo __('From ', 'simpelreserveren') ?></small> &euro;<?php echo $accommodatie->prijs ?> <?php echo __('per week', 'simpelreserveren') ?></span>
                    <div class="afbeeldingen">
                        <div class="row no-space">
                            <? $imgs = $accommodatie->afbeeldingen ?>
                            <img alt="<?php echo $accommodatie->title ?>" src="<?php echo $imgs[0] ?>" />
                            <img alt="<?php echo $accommodatie->title ?>" class="second" src="<?php echo $imgs[1] ?>" />
                        </div>
                    </div>
                    <span class="titel"><?php echo $accommodatie->title ?></span>
                    <p class="omschrijving">
                        <?php echo $accommodatie->aantal_personen ?> <?php echo __('persons', 'simpelreserveren') ?>
                    </p>
                </div>
            </div>
            <?php
        }
        echo '</div>';

        echo $after_widget;
    }

}

class SimpelReserveren_Widget_Lastminutes extends WP_Widget {

    private $sr;

    function __construct() {
        global $wp_simpelreserveren;
        $this->sr = &$wp_simpelreserveren;

        $options = array('classname' => 'widget_sr_lastminutes', 'description' => 'Toont de lastminute accommodaties van Simpel Reserveren');
        $this->WP_Widget('simpelreserveren_widget_lastminutes', 'Simpel Reserveren Lastminutes', $options);
    }

    function widget($atts, $instance) {
        extract($atts, EXTR_SKIP);
        echo $before_widget;

        $accommodaties = $this->sr->get_accommodaties(1, 'arrangementen');
        foreach ($accommodaties as $i => $accommodatie) {
            echo '<div class="column one lastMinute" onclick="document.location=\'' . $accommodatie->url . '\'">
				<h2>' . __('Last-minute', 'simpelreserveren') . '</h2>
				<div class="accommodatie random">
					<a href="' . $accommodatie->url . '" title="Accommodatie ' . $accommodatie->title . '" class="opacityHover">
						<img src="' . $accommodatie->img . '" alt="' . $accommodatie->title . ' - Letlandvakantie.nl" width="300" height="140" />
					</a>
					<h3>' . (SIMPEL_MULTIPLE ? $accommodatie->camping->title . ': ' : '') . $accommodatie->title . '</h3>
					<span class="rating fiveStar"></span>
					<p style="margin-bottom:10px;">' . $accommodatie->samenvatting . '</p>
					<span class="persons">' . $accommodatie->aantal_personen . ' ' . __('persons', 'simpelreserveren') . '</span>
					<span class="huisdieren">' . $accommodatie->huisdieren . '</span>
					<span class="price">' . __('Minimum weekprice', 'simpelreserveren') . ':: &nbsp;<em>&euro; ' . $accommodatie->prijs . '</em></span>
					<a href="' . $accommodatie->boek_url . '" title="Boek deze accommodatie direct!" class="blueCta">' . __('Book accommodation right now!', 'simpelreserveren') . '</a>
				</div>
			</div>';
        }
        echo $after_widget;
    }

}

class SimpelReserveren_Widget_Last_Booked extends WP_Widget {

    private $sr;

    function __construct() {
        global $wp_simpelreserveren;
        $this->sr = &$wp_simpelreserveren;

        $options = array('classname' => 'widget_sr_lastbooked', 'description' => 'Toont de laatst geboekte accommodatie van Simpel Reserveren');
        $this->WP_Widget('simpelreserveren_widget_lastbooked', 'Simpel Reserveren Laatst geboekt', $options);
    }

    function widget($atts, $instance) {
        extract($atts, EXTR_SKIP);
        echo $before_widget;

        $accommodaties = $this->sr->get_accommodaties(1, 'geboekt');
        foreach ($accommodaties as $i => $accommodatie) {
            ?>
            <h3><?php echo __('Last booking', 'simpelreserveren') ?></h3>
            <div class="laatst-geboekt-widget" onclick="go('<?php echo $accommodatie->url ?>')">
                <span class="prijs"><small><?php echo __('From ', 'simpelreserveren') ?></small> &euro;<?php echo $accommodatie->prijs ?> <?php echo __('per week', 'simpelreserveren') ?></span>
                <div class="afbeeldingen">
                    <div class="row no-space">
                        <? $imgs = $accommodatie->afbeeldingen ?>
                        <img alt="<?php echo $accommodatie->title ?>" src="<?php echo $imgs[0] ?>" />
                        <img alt="<?php echo $accommodatie->title ?>" class="second" src="<?php echo $imgs[1] ?>" />
                    </div>
                </div>
                <span class="titel"><?php echo $accommodatie->title ?></span>
                <p class="omschrijving">
                    <?php echo $accommodatie->samenvatting ?>
                </p>
            </div> 
            <?php
        }
        echo $after_widget;
    }

}

class SimpelReserveren_Widget_Random extends WP_Widget {

    private $sr;

    function __construct() {
        global $wp_simpelreserveren;
        $this->sr = &$wp_simpelreserveren;

        $options = array('classname' => 'widget_sr_random', 'description' => 'A widget om willekeurig accommodaties van Simpel Reserveren te tonen');
        $this->WP_Widget('simpelreserveren_widget_random', 'Simpel Reserveren Random accommodaties', $options);
    }

    function widget($atts, $instance) {
        extract($atts, EXTR_SKIP);
        echo $before_widget;

        $accommodaties = $this->sr->get_accommodaties(1, 'random');
        foreach ($accommodaties as $i => $accommodatie) {
            ?><div class="column one random" onclick="document.location = '<?php echo $accommodatie->url ?>'">
                <h2><?php echo __('Holidays we have to offer', 'simpelreserveren') ?></h2>
                <div class="accommodatie random">
                    <a href="<?php echo $accommodatie->url ?>" title="Accommodatie <?php echo $accommodatie->title ?>" class="opacityHover">
                        <img src="<?php echo $accommodatie->img ?>" alt="<?php echo $accommodatie->title ?>" height="140" />
                    </a>
                    <h3><?php echo (SIMPEL_MULTIPLE ? $accommodatie->camping->title . ': ' : '') . $accommodatie->title ?></h3>
                    <span class="rating fiveStar"></span>
                    <p style="margin-bottom:10px;"><?php echo $accommodatie->samenvatting ?></p>
                    <span class="persons"><?php echo $accommodatie->aantal_personen . ' ' . __('persons', 'simpelreserveren') ?></span>
                    <span class="huisdieren"><?php echo $accommodatie->huisdieren ?></span>
                    <span class="price"><?php echo __('Minimum weekprice', 'simpelreserveren') ?>: &nbsp;<em>&euro; <?php echo $accommodatie->prijs ?></em></span>
                    <a href="<?php echo $accommodatie->boek_url ?>" title="Boek deze accommodatie direct!" class="blueCta"><?php echo __('Book accommodation right now!', 'simpelreserveren') ?></a>
                </div>
            </div>
            <?php
        }


        echo $after_widget;
    }

}
