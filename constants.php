<?php

/**
 * Defined constants here
 */
$ah_uploads = wp_upload_dir();
if (!file_exists($ah_uploads['basedir'].DIRECTORY_SEPARATOR.'affiliate-hoover')) {
    mkdir($ah_uploads['basedir'].DIRECTORY_SEPARATOR.'affiliate-hoover', 0755);
}
$xyze = __file__;
global $wpdb;
// string concatenation not allowed with const keyword because of php bug in version 5.3. The following method below is necessary
!defined('AH_PLUGINNAME_PATH') ? define('AH_PLUGINNAME_PATH', plugin_dir_path(__FILE__)) : die("AH_PLUGINNAME_PATH already defined: $xyze");
!defined('AH_First_MODEL') ? define('AH_First_MODEL', 'model') : die("AH_First_MODEL CONSTANT already defined: $xyze");
!defined('AH_First_CONTROLLER') ? define('AH_First_CONTROLLER', 'controller') : die("AH_First_CONTROLLER CONSTANT already defined: $xyze");
!defined('AH_First_VIEW') ? define('AH_First_VIEW', 'view') : die("AH_First_VIEW CONSTANT already defined: $xyze");
!defined('AH_First_LIBS') ? define('AH_First_LIBS', 'libs') : die("AH_First_LIBS CONSTANT already defined: $xyze");
!defined('AH_DIR_PATH') ? define('AH_DIR_PATH', AH_PLUGINNAME_PATH) : die("AH_DIR_PATH CONSTANT already defined: $xyze");
!defined('AH_FEEDS_DIR') ? define('AH_FEEDS_DIR', $ah_uploads['basedir'].DIRECTORY_SEPARATOR.
    'affiliate-hoover'.DIRECTORY_SEPARATOR) : die("AH_FEEDS_DIR already defined: $xyze");
!defined('AH_FEED_DETAILS_TABLE') ? define('AH_FEED_DETAILS_TABLE', $wpdb->prefix."ah_feed_details") :
    die("AH_FEED_DETAILS_TABLE already defined: $xyze");
!defined('AH_TOTAL_FEEDS_TABLES') ? define('AH_TOTAL_FEEDS_TABLES', $wpdb->prefix."ah_total_feeds") :
    die("AH_FEED_DETAILS_TABLE already defined: $xyze");
!defined('AH_DS') ? define('AH_DS', DIRECTORY_SEPARATOR) : die("AH_DS already defined: $xyze");
!defined('AH_BR') ? define('AH_BR', "<br />") : die("AH_BR already defined: $xyze");
