<?php @ini_set('output_buffering', 'on');

ob_start();
/*
Plugin Name: Affiliate Hoover
Plugin URI: http://wordpress.org/extend/plugins/affiliate-hoover/
Description: Takes affiliate feed files and parses them into posts. IMPORTANT! This module requires PHP 5.3+. If you have an error such as 'Parse error: syntax error, unexpected T_STRING' this is why. Ask your hosting provider to upgrade.
Version: 1.5
Author: Andy Walpole
Author URI: http://about.me/andywalpole
Author email: me@andywalpole.me
License: GPL2
*/

/**
 * Check to make sure it is PHP version 5.3 and more
 */

global $wp_version;

if (floatval(phpversion()) < 5.3 || floatval($wp_version) < 3.3) {

    require_once ABSPATH.'/wp-admin/includes/plugin.php';
    deactivate_plugins(__file__);
    wp_die('Sorry, this Wordpress module will only run on PHP versions 5.3 and above and on Wordpress version 3.3 and higher.
     If you are unsure please ask your hosting company. 
     The addon has been deactivated. 
     You are free to navigate back to the main admin area.');

} else {

    include_once ('config.php');

} // end if (floatval(phpversion()) < 5.3 && floatval($wp_version) < 3.3) {

register_activation_hook(__FILE__, 'ah_create_table');

function ah_create_table() {

    extract(\model\Initialise::$form);

    $sql = "CREATE TABLE IF NOT EXISTS ".AH_FEED_DETAILS_TABLE." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(200) NOT NULL,
  `URL` text NULL,
  `fileName` text NOT NULL,
  `header_array` text NOT NULL,
  `header_array_amend` text NOT NULL,
  `num_rows` int(11) NULL,
  `min_rows` int(11) NULL,
  `max_rows` int(11) NULL,
  `post_status` char(15) NULL,
  `form_title` text NULL,
  `form_title_contains` char(250) NULL,
  `form_title_not_contains` char(250) NULL,
  `form_body` text NULL,
  `form_body_contains` char(250) NULL,
  `form_body_not_contains` char(250) NULL,
  `form_body_nofollow` TINYINT(1) NULL,
  `form_vocabulary` char(250) NULL,
  `form_categories` char(250) NULL,
  `form_categories_contains` char(250) NULL,
  `form_categories_not_contains` char(250) NULL,
  `form_categories_parent` char(250) NULL,
  `form_tags` char(250) NULL,
  `form_cron` char(30) NULL,
  `post_type` char(200) NULL,
  `form_staggered` TINYINT(1) NULL,
  `form_allow_comments` TINYINT(1) NULL,
  `form_allow_trackback` TINYINT(1) NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci";

    $sql2 = "CREATE TABLE IF NOT EXISTS ".AH_TOTAL_FEEDS_TABLES." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `post_title_id` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci";

    $sql3 = "CREATE TABLE IF NOT EXISTS ".AH_TRACKING_TABLE."(
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ip` char(40) NOT NULL,
        `post_id` int(11) NOT NULL,
        `date` datetime NOT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci";

    // add column
    function ah_find_missing_tables($database, $column, $details) {

        $exists = FALSE;

        global $wpdb;

        foreach ($database as $result) {

            if ($result->Field == $column) {
                $exists = TRUE;
                break;
            }
        }

        if ($exists === FALSE) {
            $wpdb->query("ALTER TABLE `".AH_FEED_DETAILS_TABLE."` ADD `".$column."` ".$details);
        }

    }

    // alter column
    function ah_alter_columns($database, $column, $details, $charset) {

        $exists = FALSE;

        foreach ($database as $result) {

            if ($result->Field == $column) {
                if ($result->Type == $charset) {
                    $exists = TRUE;
                    break;
                }
            } // end if
        } // end foreach

        if ($exists === TRUE) {
            $wpdb->query("ALTER TABLE `".AH_FEED_DETAILS_TABLE."` MODIFY `".$column."` ".$details);
        }

    }

    require_once (ABSPATH.'wp-admin/includes/upgrade.php');

    dbDelta($sql);
    dbDelta($sql2);
    dbDelta($sql3);

    global $wpdb;

    // Find all columns in the feed details table and save them to memory
    $query = "SHOW COLUMNS FROM `".AH_FEED_DETAILS_TABLE."`";
    $columns = $wpdb->get_results($query);

    ah_alter_columns($columns, 'form_body', 'text', 'char(250)');
    ah_find_missing_tables($columns, 'form_categories_parent', ' char(250) NULL ');
    ah_find_missing_tables($columns, 'form_body_nofollow', ' TINYINT(1) NULL ');
    ah_find_missing_tables($columns, 'form_vocabulary', ' char(250) NULL ');
    ah_find_missing_tables($columns, 'form_staggered', ' TINYINT(1) NULL ');

}


// delete database tables when addon is deleted
register_uninstall_hook(__FILE__, 'ah_unistall');

// recursively delete all directory contents and then the directory
function ah_deleteDir($path) {
    return is_file($path) ? @unlink($path) : array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}

function ah_unistall() {

    $sql1 = 'DROP table if exists '.AH_FEED_DETAILS_TABLE;
    $sql2 = 'DROP table if exists '.AH_TOTAL_FEEDS_TABLES;
    $sql3 = 'DROP table if exists '.AH_TRACKING_TABLE;

    global $wpdb;

    $wpdb->query($sql1);
    $wpdb->query($sql2);
    $wpdb->query($sql3);

    delete_option('affiliate_hoover_plugin_options');

    $ah_uploads = wp_upload_dir();

    ah_deleteDir($ah_uploads['basedir'].DIRECTORY_SEPARATOR.'affiliate-hoover');

}
