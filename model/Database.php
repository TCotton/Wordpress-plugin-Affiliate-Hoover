<?php namespace model;

/**
 * Database
 * 
 * @package Affiliate Hoover
 * @author Andy Walpole
 * @copyright Andy Walpole
 * @link http://andywalpole.me/
 * @version development
 * @access public
 * @license GPLv2: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Wordpress functions used:
 * 
 * 
 * 
 * 
 */
class Database extends \model\Initialise {

    /**
     * Database::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();

    } // end __construct

    /**
     * Database::db_insert_total_feeds()
     * 
     * @param mixed $id
     * @param mixed $cat_id
     * @return
     */
    protected function db_insert_total_feeds($id, $cat_id) {

        return static::$wpdb->query(static::$wpdb->prepare("
		INSERT INTO ".AH_TOTAL_FEEDS_TABLES."(post_title_id, cat_id) VALUES (%d, %d)", $id, $cat_id));

    }

    /**
     * Database::db_select_total_feeds()
     * 
     * @param mixed $id
     * @return
     */
    protected function db_select_total_feeds($id) {

        return static::$wpdb->get_row("SELECT post_title_id FROM ".AH_TOTAL_FEEDS_TABLES.
            " WHERE post_title_id = $id");

    }

    /**
     * Database::db_find_meta_id()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_meta_id($var) {

        return static::$wpdb->get_row("SELECT post_id FROM ".static::$wpdb->prefix.
            "postmeta WHERE meta_value  = '".$var."'");

    }

    /**
     * Database::db_find_meta_cat()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_meta_cat($var) {

        return static::$wpdb->get_results("SELECT post_id FROM ".static::$wpdb->prefix.
            "postmeta WHERE meta_key = '_cat_num' AND meta_value = $var");

    }

    /**
     * Database::db_find_feed_details_id()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_feed_details_id($var) {

        return static::$wpdb->get_row("SELECT id FROM ".AH_FEED_DETAILS_TABLE." WHERE name = '".$var.
            "'");

    }

    /**
     * Database::db_find_filename_feed_details()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_filename_feed_details($var) {

        return static::$wpdb->get_row("SELECT fileName, header_array FROM ".AH_FEED_DETAILS_TABLE.
            " WHERE name = '".$var."'");

    }

    /**
     * Database::db_find_all_posts_meta()
     * 
     * @return
     */
    protected function db_find_all_posts_meta() {

        return static::$wpdb->get_results("SELECT post_id FROM ".static::$wpdb->prefix.
            "postmeta WHERE meta_key = '_unique_post'");

    }

    /**
     * Database::db_find_post_title()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_post_title($var) {

        return static::$wpdb->get_row("SELECT post_title FROM ".static::$wpdb->prefix.
            "posts WHERE ID = $var");

    }

    /**
     * Database::db_find_post_revisions()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_post_revisions($var) {

        return static::$wpdb->get_row("SELECT ID FROM ".static::$wpdb->prefix.
            "posts WHERE post_name REGEXP '^$var' AND post_type = 'revision'");

    }

    /**
     * Database::db_find_all_post_cats()
     * 
     * @return
     */
    protected function db_find_all_post_cats() {

        return static::$wpdb->get_results("SELECT id, name FROM ".AH_FEED_DETAILS_TABLE);

    }

    /**
     * Database::db_delete_total_feeds()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_delete_total_feeds($var) {

        static::$wpdb->query(static::$wpdb->prepare("DELETE FROM ".AH_TOTAL_FEEDS_TABLES."
		 WHERE cat_id = %d", $var));

    }

    /**
     * Database::db_find_post_types()
     * 
     * @return
     */
    protected static function db_find_post_types() {

        $exists = static::$wpdb->get_row("SHOW TABLES LIKE '".AH_FEED_DETAILS_TABLE."'", ARRAY_A);

        if ($exists) {

            return static::$wpdb->get_results("SELECT DISTINCT post_type FROM ".
                AH_FEED_DETAILS_TABLE, ARRAY_A);

        }

    }

    /**
     * Database::db_find_feed_name_from_post_id()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_feed_name_from_post_id($var) {

        $result = static::$wpdb->get_row("SELECT meta_value FROM ".static::$wpdb->prefix.
            "postmeta WHERE meta_key = '_cat_num' AND post_id = $var");

        if (isset($result->meta_value)) {

            $name = static::$wpdb->get_row("SELECT name FROM ".AH_FEED_DETAILS_TABLE." WHERE id = ".
                (int)$result->meta_value);

            return $name->name;

        } else {

            return NULL;

        }

    }

    /**
     * Database::db_find_tracking_details_from_post_id()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_tracking_details_from_post_id($var) {

        return static::$wpdb->get_results("SELECT * FROM ".AH_TRACKING_TABLE." WHERE post_id = ".(int)
            $var, ARRAY_A);

    }

    /**
     * Database::db_delete_all_tracking()
     * 
     * @return
     */
    protected function db_delete_all_tracking() {

        return static::$wpdb->query('DELETE FROM '.AH_TRACKING_TABLE);

    }


    /**
     * Form_Model::check_table()
     * 
     * Select just name from feed details table
     * 
     * @return string
     */
    protected function db_check_table($var) {

        return static::$wpdb->get_row("SELECT name FROM ".AH_FEED_DETAILS_TABLE." WHERE name = '".$var.
            "'");

    }

    /**
     * Database::db_get_all_feed_names()
     * 
     * @return
     */
    protected function db_get_all_feed_names() {

        return static::$wpdb->get_results("SELECT name FROM ".AH_FEED_DETAILS_TABLE);

    }

    /**
     * Database::db_get_post_meta_id()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_get_post_meta_id($var) {

        return static::$wpdb->get_row("SELECT post_id, meta_value FROM ".static::$wpdb->prefix.
            "postmeta WHERE meta_key = '_unique_post' AND post_id = $var");

    }

    /**
     * Database::db_get_post_meta()
     * 
     * @return
     */
    protected function db_get_post_meta() {

        return static::$wpdb->get_results("SELECT post_id, meta_value FROM ".static::$wpdb->prefix.
            "postmeta WHERE meta_key = '_unique_post'");

    }

    /**
     * Database::db_find_file_name()
     * 
     * @return
     */
    protected function db_find_file_name() {

        return static::$wpdb->get_results("SELECT fileName FROM ".AH_FEED_DETAILS_TABLE.
            " WHERE name <> '".$_GET['unique_name']."'");

    }

    /**
     * Database::db_select_all()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_select_all($var) {

        return static::$wpdb->get_row("SELECT * FROM ".AH_FEED_DETAILS_TABLE." WHERE name = '".$var.
            "'");

    }

    /**
     * Database::db_select_postid_get_post_meta()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_select_postid_get_post_meta($var) {

        return static::$wpdb->get_results("SELECT post_id FROM ".static::$wpdb->prefix.
            "postmeta WHERE meta_key = '_cat_num' AND meta_value = ".$var);

    }

    /**
     * Database::db_delete_all_feed_posts()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_delete_all_feed_posts($var) {

        $id = $this->db_select_postid_get_post_meta($var);

        foreach ($id as $result) {

            if ($result->post_id !== null) {
                wp_delete_post((int)$result->post_id, TRUE);
            }
        }
    }


    /**
     * Database::db_delete_revisions()
     * 
     * @return
     */
    protected function db_delete_revisions() {

        // buid array of total posts

        $all_posts = $this->db_find_all_posts_meta();

        $title_array = array();

        foreach ($all_posts as $result) {

            $id = $this->db_find_post_title($result->post_id);

            if ($id !== NULL) {
                $title_array[] = $id;
            } // end

        }

        if (!empty($title_array)) {

            foreach ($title_array as $result) {

                static::$wpdb->query(static::$wpdb->prepare("DELETE a,b,c
FROM wp_posts a
LEFT JOIN wp_term_relationships b ON (a.ID = b.object_id)
LEFT JOIN wp_postmeta c ON (a.ID = c.post_id)
WHERE a.post_type = 'revision' AND post_title = %s", $result->post_title));

            } // end foreach

        } // end if(!empty($title_array)) {

    }

    /**
     * Database::db_delete_record()
     * 
     * @param mixed $name
     * @param mixed $filename
     * @return
     */
    protected function db_delete_record($name, $filename) {

        $file = AH_FEEDS_DIR.$filename;
        unlink($file);

        // delete stuff here

        $id = $this->db_find_feed_details_id($name);

        $this->db_delete_total_feeds($id->id);

        static::$wpdb->query(static::$wpdb->prepare("DELETE FROM ".AH_FEED_DETAILS_TABLE."
		 WHERE name = %s
		", $name));
    }

    /**
     * Database::db_find_cat_id()
     * 
     * @param mixed $var
     * @return
     */
    protected function db_find_cat_id($var) {

        return static::$wpdb->get_row("SELECT term_id FROM ".static::$wpdb->prefix.
            "terms WHERE name  = '".$var."'");

    }

    /**
     * Database::db_update_feed_details()
     * 
     * @param mixed $form_title
     * @param mixed $form_title_contains
     * @param mixed $form_body
     * @param mixed $form_body_contains
     * @param mixed $form_categories
     * @param mixed $form_tags
     * @param mixed $form_allow_comments
     * @param mixed $form_allow_trackback
     * @param mixed $form_name
     * @param mixed $form_min_rows
     * @param mixed $form_max_rows
     * @param mixed $form_post_status
     * @param mixed $form_posttype
     * @param mixed $form_nofollow
     * @param mixed $form_cat_parents
     * @param mixed $form_tax
     * @param mixed $form_stagger
     * @return
     */
    protected function db_update_feed_details($form_title, $form_title_contains, $form_body, $form_body_contains,
        $form_categories, $form_tags, $form_allow_comments, $form_allow_trackback, $form_name, $form_min_rows,
        $form_max_rows, $form_post_status, $form_posttype, $form_nofollow, $form_cat_parents, $form_tax,
        $form_stagger) {

        return static::$wpdb->query(static::$wpdb->prepare("UPDATE ".AH_FEED_DETAILS_TABLE.
            " SET form_title = %s, form_title_contains = %s, form_body = %s, form_body_contains = %s, form_categories = %s, form_tags = %s, form_allow_comments = %d, form_allow_trackback = %d, min_rows = %s, max_rows = %d, post_status = %s, post_type = %s, form_body_nofollow = %d, form_categories_parent = %s, form_vocabulary = %s, form_staggered = %s WHERE name = %s",
            $form_title, $form_title_contains, $form_body, $form_body_contains, $form_categories, $form_tags,
            $form_allow_comments, $form_allow_trackback, $form_min_rows, $form_max_rows, $form_post_status,
            $form_posttype, $form_nofollow, $form_cat_parents, $form_tax, $form_stagger, $form_name));

    }

    /**
     * Database::db_insert_table()
     * 
     * @param mixed $name
     * @param mixed $url
     * @param mixed $fileName
     * @param mixed $header_array
     * @param mixed $header_array_amend
     * @param mixed $num_rows
     * @return
     */
    protected function db_insert_table($name, $url, $fileName, $header_array, $header_array_amend, $num_rows) {

        if ($this->db_check_table($name) === NULL) {

            return static::$wpdb->query(static::$wpdb->prepare("
		INSERT INTO ".AH_FEED_DETAILS_TABLE."
		( name, URL, fileName, header_array, header_array_amend,  num_rows)
		VALUES ( %s, %s, %s, %s, %s,  %d )
	", $name, $url, $fileName, $header_array, $header_array_amend, $num_rows));
        } else {

            // here compare data between relevant database table and form
            // if they are different then update table and delete old file in feeds folder

            $select_all = $this->db_select_all($name);

            if ($select_all->fileName != $fileName) {
                $file = AH_FEEDS_DIR.$select_all->fileName;
                unlink($file);
            }

            return static::$wpdb->query(static::$wpdb->prepare("UPDATE ".AH_FEED_DETAILS_TABLE.
                " SET URL = %s, fileName = %s, header_array = %s, header_array_amend = %s, num_rows = %d WHERE name = %s",
                $url, $fileName, $header_array, $header_array_amend, $num_rows, $name));

        }

    }


    /**
     * Tracking_Model::update_tracking()
     * Used for AJAX updates tracking table when user clicks on an affiliate link
     * 
     * @return no return or the AJAX fucks up
     */

    public static function db_update_tracking() {

        if (!wp_verify_nonce($_REQUEST['nonce'], 'myajax-nonce')) die('Busted!');

        $digit = preg_replace('/[^0-9]|,[0-9]*$/', '', $_REQUEST['post']);

        $digit = (int)$digit;

        $meta = get_post_meta($digit, '_tracking');

        if ($meta) {

            // if post exists then add to previous total
            $new_total = (int)$meta + 1;

            update_post_meta($digit, '_tracking', (string )$new_total);

        } else {

            // if it doesn't exist then create one
            add_post_meta($digit, '_tracking', "1");

        }

        global $wpdb;

        $wpdb->query($wpdb->prepare("INSERT INTO ".AH_TRACKING_TABLE.
            " (date, post_id, ip) VALUES (now(), %d, %s)", $digit, (string )$_SERVER['REMOTE_ADDR']));

        die();

    }


    /**
     * Tracking_Model::get_all()
     * 
     * @param mixed $limit - for pagination
     * @return
     */
    protected function db_get_all($limit) {

        return static::$wpdb->get_results("SELECT ip,post_id,date FROM ".AH_TRACKING_TABLE.
            " ORDER BY date DESC ".$limit);
    }


    /**
     * Tracking_Model::total_entries()
     * Finds total entries of posts in ah_tracking table and that which are currently still live
     * 
     * @return
     */

    protected function db_total_tracking_entries() {

        $total = static::$wpdb->get_results("SELECT id, post_id FROM ".AH_TRACKING_TABLE);
        static $i = 1;
        $new_total = NULL;
        foreach ($total as $result) {

            $post = (int)$result->post_id;
            $post = get_post($post, OBJECT);
            if ($post === NULL) continue;
            $new_total = $i++;
        }

        return $new_total;
    }


    /**
     * Tracking_Model::update_options()
     * 
     * Changes option field for ah_tracking
     * 
     * @param mixed $var
     */

    protected function db_update_options($var) {

        if (isset($var) && $var === TRUE) {

            $option = get_option('ah_tracking');

            if ($option) {

                update_option('ah_tracking', TRUE);

            } else {

                update_option('ah_tracking', TRUE);

            }

        } else {

            $option = get_option('ah_tracking');

            if ($option) {

                update_option('ah_tracking', FALSE);

            } else {

                update_option('ah_tracking', FALSE);

            }

        } // end if

    }


    /**
     * Database::db_delete_feed_leftovers()
     * 
     * @param mixed $form
     * @return
     */
    protected function db_delete_feed_leftovers($form) {

        foreach ($form as $key => $result) {

            if ($key == 'total_user_fields') continue;
            if ($key == 'submitDeleteFeed') continue;

            if ($result != '') {
                $this->db_delete_total_feeds((int)$result);
            }
        }
    }

    /**
     * Database::db_delete_posts()
     * 
     * @param mixed $form
     * @return
     */
    protected function db_delete_posts($form) {

        foreach ($form as $key => $result) {

            if ($key == 'total_user_fields') continue;
            if ($key == 'deleteFeedPosts') continue;

            if ($result != '') {

                $this->db_delete_all_feed_posts((int)$result);
            }

        }

    }

}
