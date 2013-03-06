<?php namespace model;
use File_CSV_DataSource;
use ArrayIterator;

/**
 * Upload_Form_Processing
 * 
 * @package Affiliate Hoover
 * @author Andy Walpole
 * @copyright Andy Walpole
 * @link http://andywalpole.me/
 * @access public
 * @license GPLv2: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * 
 */

class Synchronize_Feeds extends \model\Database {

    protected static $read_write;

    function __construct() {

        parent::__construct();

        if (!(self::$read_write instanceof \model\Write_Read_Files)) {
            self::$read_write = new \model\Write_Read_Files();
        }

    } // end construct

    /**
     * Form_Model::synchronize_feeds()
     * 
     * Compares the remote and file and database, delets database entries if not equal
     * 
     * This is useful as it deletes affiliate items no longer offered by the company
     *
     * @return string
     */
    protected function synchronize_feeds($var) {

        // firstly find the name of the feed to be updated

        $id = $this->db_find_feed_details_id($_GET['unique_form']);

        // now use that id to find details of all existing published items for that category

        $cat_array = $this > db_find_meta_cat($id->id);

        // Now find the title of the posts

        foreach ($cat_array as $result) {

            $total_posts[] = $this->db_get_post_meta_id($result->post_id);

        }

        // the total posts for this category are no in the $total_posts array
        // Now find the filename so as to access the CSV file

        $filename = $this->db_find_filename_feed_details($_GET['unique_form']);

        $csv = new File_CSV_DataSource;

        $total_titles = array();

        // Load CSV file

        if ($csv->load(AH_FEEDS_DIR.$filename->fileName)) {

            $csv->symmetrize();

            $total = new ArrayIterator($csv->getrawArray());

            $total_meta = array();

            foreach ($total_posts as $meta_result) {

                $total_meta[] = $meta_result->meta_value;

            }

            // the total_meta array now has the titles for every post in this category

            // loop through CSV total contents and get an array of every title

            foreach ($total as $result => $value) {

                if ($result == 0) continue;

                foreach ($value as $key => $row_value) {

                    if (mb_stristr((string )$var, (string )$key)) {
                        $total_titles[] = hexdec(mb_substr(md5($row_value), 0, 7));

                    }

                } // end foreach

            } // end foreach

        } // end if ($csv->load(AH_FEEDS_DIR.$filename)) {

        // now we have an array of every title in the CVS and every publish post in that category
        // lets compare them
        // if a published post is not in the CVS file, lets delete it from the database

        $difference = array_diff($total_meta, $total_titles);

        //in the $difference array there is now the titles that need to be deleted

        foreach ($difference as $result) {

            static $x = 1;

            $postid = $this->db_find_meta_id($result);
            wp_delete_post($postid->post_id);
            $total_delete = $x++;

        }

        if (isset($total_delete)) {
            self::$read_write->write_file($total_delete." posts from ".$_GET['unique_form'].
                " were sent to trash after synchronization on ".date('h:i:s A, l jS \of F Y'));
        }

    }


    public function synchronise_feeds_facade($var) {

        $this->synchronize_feeds($var);


    }


}
