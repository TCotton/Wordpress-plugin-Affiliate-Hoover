<?php

/*
Plugin Name: Affiliate Hoover
Plugin URI: //
Description: Takes affiliate CSV feeds and integrates them into Wordpress posts on cron run
Version: Alpha
Author: Andy Walpole
Author URI: http://andywalpole.me
Author email: me@andywalpole.me
License: GPL2
*/

/**
 * Check to make sure it is PHP version 5.3 and more
 */

if (floatval(phpversion()) < 5.3) {

    require_once ABSPATH.'/wp-admin/includes/plugin.php';
    deactivate_plugins(__file__);
    wp_die("Sorry, this Wordpress module will only run on PHP versions 5.3 and above.
     If you are unsure please ask your hosting company. 
     The addon has been deactivated. 
     Navigate back to the main admin area.");

}

include_once ("config.php");

//Loop through all files in folders and include them in main file
foreach (\Config\Configuration::total_files() as $file) {

    if (is_admin()) { // require only in admin

        require_once ($file);

    }

}

if (ini_get('safe_mode')) {

}

set_time_limit(120);
