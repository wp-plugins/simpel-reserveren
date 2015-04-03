<?php

class SR_Activator
{
    public static function activate()
    {
	   	$role = get_role('administrator');

       	$role->add_cap( 'sr_settings' );
    	$role->add_cap( 'sr_entities' );
    }
}
