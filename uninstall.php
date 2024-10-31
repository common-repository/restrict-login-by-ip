<?php

/**
 * @author W-Shadow
 * @copyright 2008
 *
 * Plugin uninstallation routine. I still think it's inelegant as I can't figure out
 * how to merge it into my object-oriented framework.
 */

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') && !current_user_can('delete_plugins') )
    exit();
 
delete_option('ws_restrict_by_ip');

?>