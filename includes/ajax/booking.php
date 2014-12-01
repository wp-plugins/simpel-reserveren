<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SR_Ajax_Booking {
    private $sr;

    public function __construct($sr) {
        $this->sr = $sr;

        add_action( 'wp_ajax_sr_booking', array('SR_Sync', 'save_booking' ) );
        add_action( 'wp_ajax_nopriv_sr_booking', array('SR_Sync', 'save_booking' ) );
    }


}

new SR_Ajax_Booking($this);