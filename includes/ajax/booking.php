<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class SR_Ajax_Booking
{
    private $sr;

    public function __construct($sr)
    {
        $this->sr = $sr;

        $sr_sync = new SR_Sync($sr);

        add_action('wp_ajax_sr_booking', array($sr_sync, 'save_booking' ));
        add_action('wp_ajax_nopriv_sr_booking', array($sr_sync, 'save_booking' ));

        add_action('wp_ajax_sr_address', array($sr_sync, 'save_address' ));
        add_action('wp_ajax_nopriv_sr_address', array($sr_sync, 'save_address' ));
    }
}

new SR_Ajax_Booking($this);
