<?php

ob_start();
/*
Plugin Name: Affiliate Hoover
Plugin URI: http://wordpress.org/extend/plugins/affiliate-hoover/
Description: Takes affiliate feed files and parses them into posts
Version: 0.81
Author: Andy Walpole
Author URI: http://about.me/andywalpole
Author email: me@andywalpole.me
License: GPL2
*/

/**
 * Check to make sure it is PHP version 5.3 and more
 */

if (PHP_VERSION_ID < 50300 || floatval($wp_version) < 3.3) {

    require_once ABSPATH.'/wp-admin/includes/plugin.php';
    deactivate_plugins(__file__);
    wp_die("Sorry, this Wordpress module will only run on PHP versions 5.3 and above and on Wordpress version 3.3 and higher.
     If you are unsure please ask your hosting company. 
     The addon has been deactivated. 
     You are free to navigate back to the main admin area.");

} else {

    include_once ("config.php");

    // loop through the tracking files
    foreach (\Config\Configuration::tracking_files() as $file) {

        require_once ($file);

    }

    //Loop through all files in folders and include them in main file
    foreach (\Config\Configuration::total_files() as $file) {

        //if (is_admin()) { // require only in admin

            require_once ($file);

       // }

    }

    // was firing twice in __construct - moved to here
    if (is_admin()) {

        add_action('wp_ajax_nopriv_ah_update', array($tracker, 'update_tracking'), "1");
        //add_action('wp_ajax_ah_update', array($tracker, 'update_tracking'), "1");

    }
    //add_action('wp_ajax_nopriv_ah_update', array($tracker, 'update_tracking'));


} // end if (floatval(phpversion()) < 5.3 && floatval($wp_version) < 3.3) {


// delete database tables when addon is deleted
register_uninstall_hook(__FILE__, 'ah_unistall');

// recursively delete all directory contents and then the directory
function ah_deleteDir($path) {
    return is_file($path) ? @unlink($path) : array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}

function ah_unistall() {

    $sql1 = "DROP table if exists ".AH_FEED_DETAILS_TABLE;
    $sql2 = "DROP table if exists ".AH_TOTAL_FEEDS_TABLES;

    global $wpdb;

    $wpdb->query($sql1);
    $wpdb->query($sql2);

    delete_option('affiliate_hoover_plugin_options');

    $ah_uploads = wp_upload_dir();

    ah_deleteDir($ah_uploads['basedir'].DIRECTORY_SEPARATOR.'affiliate-hoover');

}
ob_flush();

?>