<?php

namespace OptionModel;
use OptionModelSub;
use File_CSV_DataSource;
use XML_Serializer;
use XML_Unserializer;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;
use DOMDocument;

/**
 * Form_Model b
 * 
 * @package Affiliate Hoover
 * @author Andy Walpole
 * @copyright Andy Walpole
 * @link http://andywalpole.me/
 * @version development
 * @access public
 * @license GPLv2: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Wordpress functions:
 * 
 * dbDelta()
 * http://codex.wordpress.org/Creating_Tables_with_Plugins
 * 
 * $wpdb->query()
 * $wpdb->prepare()
 * $wpdb->get_row()
 * $wpdb->get_results()
 * http://codex.wordpress.org/Class_Reference/wpdb
 * 
 * stripslashes_deep()
 * http://codex.wordpress.org/Function_Reference/stripslashes_deep
 * 
 * wp_create_category()http://codex.wordpress.org/Function_Reference/wp_create_category
 * 
 * wp_update_post()
 * http://codex.wordpress.org/Function_Reference/wp_update_post
 * 
 * wp_insert_post()
 * http://codex.wordpress.org/Function_Reference/wp_insert_post
 * 
 * add_post_meta()
 * http://codex.wordpress.org/Function_Reference/add_post_meta
 * 
 * wp_delete_post()
 * http://codex.wordpress.org/Function_Reference/wp_delete_post
 * 
 * update_option()
 * http://codex.wordpress.org/Function_Reference/update_option
 * 
 * wp_redirect()
 * http://codex.wordpress.org/Function_Reference/wp_redirect
 * 
 * get_option()
 * http://codex.wordpress.org/Function_Reference/get_option
 * 
 * wp_verify_nonce()
 * http://codex.wordpress.org/Function_Reference/wp_verify_nonce
 * 
 * force_balance_tags()
 * http://codex.wordpress.org/Function_Reference/force_balance_tags
 *
 */

class Form_Model extends OptionModelSub\Form_Model_Sub {


    function __construct() {

    } // end construct


    /**
     * Form_Model::duplicate_entries()
     * 
     * Checks to make sure all array values are unique
     * 
     * For an explanation of this code read my blog post here: http://www.suburban-glory.com/blog?page=152
     * 
     * @param array $array
     * @return boolean
     */

    protected function duplicate_entries($array) {

        extract(static::$form);

        $tmp = array();

        foreach ($array[$option_name] as $key => $value) {

            // root out radio buttons
            if (preg_match("/zyxwv/", $key)) continue;

            // remove checkboxes from the loop
            if (preg_match("/zqxjk/", $key)) continue;

            // remove select options
            if (preg_match("/kvbpy/", $key)) continue;

            if (is_string($key) && !empty($value)) {
                $tmp[] = $value;
            }

        } // end foreach

        if (count($tmp) !== count(array_unique($tmp))) {
            return FALSE;
        }

    }


    /**
     * Form_Model::empty_radio_butts()
     * 
     * Checks for empty radio buttons
     * Has to be a separate form due to issues around how php handles the name attribute 
     * 
     * @param array $array
     * @return boolean
     */


    protected function empty_radio_butts($form_output) {

        // essential
        extract(static::$form);
        $result = FALSE;
        $database = get_option($option_name);

        if (static::check_options_table() && $dynamic_output && !empty($database[$option_name])) {

            // space here if necessary for dynamic forms

        } else {

            /**
             * Run through the name inputs
             * If the name imput is never present for the specified radio button
             * This means that none of them have been checked
             * One radio button has to be checked to allow submission
             */

            foreach ($form_output[$option_name] as $key => $value) {

                if (preg_match("/zyxwv/", $key)) {
                    $result = TRUE;
                }

            }

            if (!$result) {
                return FALSE;
            }

        }

    }

    /**
     * Form_Model::eempty_checkboxess()
     * 
     * Checks for empty checkboxes if used for validation 
     * 
     * @param array $form_output
     * @return boolean
     */


    protected function empty_checkboxes($form_output, $digit = 1) {

        // essential
        extract(static::$form);
        $result = FALSE;
        $database = get_option($option_name);
        $total_checkboxes = 0;


        if (static::check_options_table() && $dynamic_output && !empty($database[$option_name])) {

            // space here if necessary for dynamic forms

            // find the total number of checkboxes
            foreach ($form_output[$option_name] as $key => $value) {

                if (is_string($value)) continue;
                if (!isset($value['checkbox_number'])) continue;
                if ($value['checkbox_number'] === "") continue;

                $total_checkboxes = (int)$value['checkbox_number'];
                break;

            } // end foreach

            // find the total number of individual form blocks - does not include original form
            foreach ($form_output[$option_name] as $key => $value) {

                if (is_integer($key)) continue;
                // remove delete checkbox
                if (preg_match('/xyz/', $key)) continue;
                // only checkboxes with digits on the end
                if (!preg_match('/zqxjk([0-9]+)$/', $key)) continue;

                $number = $key;

            } // end foreach

            preg_match('/([0-9]+)$/', $number, $match);
            $match = (int)array_pop($match);

            $error = array();

            foreach ($form_output[$option_name] as $key => $value) {

                // remove delete checkbox
                if (preg_match('/xyz/', $key)) continue;
                if (is_array($value)) continue;
                // only checkboxes
                if (!preg_match('/zqxjk/', $key)) continue;

                static $x = 1;

                if ($value === "") {
                    $error[] = $x++;

                }

            } // end foreach

            // total 0 values minus original empty form
            $total_NULL = (array_pop($error) - $total_checkboxes);

            // find total number of checkboxes minus the original empty form
            $total_checks = ($match * $total_checkboxes);

            // now do the maths and work out which
            if ($total_NULL > ($total_checks - $digit)) {
                return FALSE;
            }

        } else {

            /**
             * Run through the name inputs
             * If the name imput is never present for the specified radio button
             * This means that none of them have been checked
             * One radio button has to be checked to allow submission
             */

            $result = array();
            $total = array();

            foreach ($form_output[$option_name] as $key => $value) {

                static $x = 1;
                static $y = 1;

                if (preg_match("/zqxjk/", $key)) {

                    if ($value !== "") {
                        $result[] = $y++;
                    } // end if

                }

            } //foreach

            if ((array_pop($result)) < (int)$digit) {
                return FALSE;
            }

        }

    }


    /**
     * Form_Model::empty_value()
     * 
     * Checks if form fields are empty
     * 
     * Will only work form arrays that only include input or textarea
     * Radio, select and checkboxs have to be invididually processed with a string value
     * 
     * @param mixed $form_output
     * @return boolean
     */
    protected function empty_value($form_output, $single = NULL) {

        extract(static::$form);

        $database = static::check_options_table();
        $output = (int)$form_output['total_user_fields'];
        $data = get_option($option_name);

        $total_inputs = array();
        $total_arrays = array();
        $total_checkboxes = 0;
        $total_radio_buttons = 0;

        // This is a repeat of code. Refactor it

        // if new form without option database created yet make sure ALL fields are not empty
        foreach ($form_output[$option_name] as $n_key => $n_value) {

            // find the total amount of individual checkboxes per form block
            if (is_array($n_value) && isset($n_value['checkbox_number']) && $n_value['checkbox_number']
                !== "") {
                $total_checkboxes = (int)$n_value['checkbox_number'];
            }

            // find the total amount of individual radio buttons per form block
            if (is_array($n_value) && isset($n_value['radio_number']) && $n_value['radio_number']
                !== "") {
                $total_radio_buttons = (int)$n_value['radio_number'];
            }

            static $x = 1;
            static $z = 0;

            if (is_string($n_value)) {

                // remove delete checkbox
                if (preg_match('/xyz/', $n_key)) continue;
                // remove regular checkboxes
                if (preg_match('/zqxjk/', $n_key)) continue;
                // remove radio buttons
                if (preg_match('/zyxwv/', $n_key)) continue;

                // the total inputs are fluid depending if the user has checked the delete box
                // The only TRUE way to determine the number is to access it here
                $total_inputs[] = $x++;
            } // end is_string

            if (is_array($n_value)) {

                // the total inputs are fluid depending if the user has checked the delete box
                // The only TRUE way to determine the number is to access it here
                $total_arrays[] = $z++;
            } // end is_string

        } // end foreach loop

        $total = (array_pop($total_inputs) - ($output - $total_checkboxes - $total_radio_buttons));

        if ($dynamic_output) {

            if ($database && !empty($data[$option_name])) {

                if ($single == NULL) {
                    // if entire form is entered

                    // if new form without option database created yet make sure ALL fields are not empty
                    foreach ($form_output[$option_name] as $n_key => $n_value) {

                        // remove delete checkbox
                        if (preg_match('/xyz/', $n_key)) continue;
                        // remove regular checkboxes
                        if (preg_match('/zqxjk/', $n_key)) continue;
                        // remove radio buttons
                        if (preg_match('/zyxwv/', $n_key)) continue;

                        if (is_string($n_value)) {

                            //This is to prevent checking for empty the bottom form
                            static $c = 0;

                            if ($c++ < $total) {

                                if (is_string($n_value)) {

                                    if (empty($n_value)) {
                                        return FALSE;
                                    } // end if

                                } // end is_string

                            } // end if

                        } // end if

                    } // end foreach loop

                } else {

                    // if only single form input entered
                    foreach ($form_output[$option_name] as $key => $n_value) {

                        if (preg_match("/$single/i", $key)) {

                            if (empty($n_value)) {
                                return FALSE;
                            } // end if

                        } // end if

                    } // end foreach

                } // end if single

            } else {

                if ($this->empty_non_dynamic($form_output, $single) === FALSE) {
                    return FALSE;
                }

            } // end if $database

            // if $dynamic_output is set to FALSE
        } else {

            if ($this->empty_non_dynamic($form_output, $single) === FALSE) {
                return FALSE;
            }

        } // end if $dynamic_output

    }


    /**
     * Form_Model::empty_non_dynamic()
     * 
     * Checks if form fields are empty if no dynamic is set
     * 
     * A private method to work with empty_value() only
     * 
     * @param array $form_output
     * @param single $single
     * @return boolean
     */
    private function empty_non_dynamic($form_output, $single) {

        extract(static::$form);

        if ($single === NULL) {

            // if new form without option database created yet make sure ALL fields are not empty
            foreach ($form_output[$option_name] as $n_key => $n_value) {

                // remove delete checkbox
                if (preg_match('/xyz/', $n_key)) continue;
                // remove regular checkboxes
                if (preg_match('/zqxjk/', $n_key)) continue;
                // remove radio buttons
                if (preg_match('/zyxwv/', $n_key)) continue;

                if (is_string($n_value)) {

                    if (empty($n_value)) {
                        return FALSE;
                    } // end if

                } // end is_string

            } // end foreach loop

        } else {

            foreach ($form_output[$option_name] as $key => $n_value) {

                if (preg_match("/$single/i", $key)) {

                    if (empty($n_value)) {
                        return FALSE;
                    } // end if

                } // end if

            } // end foreach

        } // end if not single

    }


    /**
     * Form_Model::create_table()
     * 
     * Creates database table when addon is enabled
     * Creates two different tables
     * The central database and one to record ALL posts
     * 
     */

    protected function create_table() {

        extract(static::$form);

        //add_option($option_name);

        global $wpdb;

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
  `form_title` char(250) NULL,
  `form_title_contains` char(250) NULL,
  `form_title_not_contains` char(250) NULL,
  `form_body` char(250) NULL,
  `form_body_contains` char(250) NULL,
  `form_body_not_contains` char(250) NULL,
  `form_categories` char(250) NULL,
  `form_categories_contains` char(250) NULL,
  `form_categories_not_contains` char(250) NULL,
  `form_tags` char(250) NULL,
  `form_cron` char(30) NULL,
  `post_type` char(200) NULL,
  `form_allow_comments` TINYINT(1) NULL,
  `form_allow_trackback` TINYINT(1) NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $sql2 = "CREATE TABLE IF NOT EXISTS ".AH_TOTAL_FEEDS_TABLES." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `post_title_id` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        require_once (ABSPATH.'wp-admin/includes/upgrade.php');

        dbDelta($sql);
        dbDelta($sql2);

        //$wpdb->print_error();

    }

    /**
     * Form_Model::create_post_items()
     * 
     * Updates the main datbase table
     * 
     * Very imporant function
     * This take the data from the CSV file and the user-generated settings from the database 
     * and then creates the post
     * 
     * @param array $var
     * @param boolean TRUE
     * 
     */

    protected function create_post_items($var, $bol = TRUE) {

        $item = $this->select_all($var);

        $file_here = $item->fileName;

        // get user id of admin
        $user_info = get_userdata(1);
        // this is user id --> $user_info->ID

        // PARSE AND SAVE CSV DETAILS HERE

        $headers = unserialize($item->header_array);

        $new_post = array(
            'post_title' => NULL,
            'post_content' => NULL,
            'post_status' => 'open',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_type' => 'post',
            'post_author' => $user_info->ID,
            'tags_input' => NULL,
            'post_category' => NULL,
            'post_status' => 'publish');

        $post_meta = $this->get_post_meta();

        // CSV

        if ($this->get_file_extension($file_here) === "csv") {

            $csv = new File_CSV_DataSource;

            if ($csv->load(AH_FEEDS_DIR.$file_here)) {

                //var_dump($csv->isSymmetric());

                $csv->symmetrize();

                $total = $csv->getrawArray();

                foreach ($total as $result => $value) {

                    if ($item->min_rows != FALSE && $item->max_rows != FALSE) {

                        if ($result > $item->max_rows) continue;
                        if ($result < $item->min_rows) continue;

                    }

                    if ($result == 0) continue;

                    $total_val = count($value);

                    foreach ($value as $key => $row_value) {

                        if ($key === 0) {

                            $post_title = $item->form_title;
                            $post_content = $item->form_body;
                            $tags_input = $item->form_tags;
                            $post_category = $item->form_categories;
                            $post_cat_array = array();
                            $post_type = $item->post_type;

                        }

                        // Post title

                        if (stristr($item->form_title, "[#$key#]") !== FALSE) {

                            $post_title = str_replace("[#$key#]", $row_value, $post_title);

                        }

                        // Post content

                        if (stristr($item->form_body, "[#$key#]") !== FALSE) {

                            $post_content = str_replace("[#$key#]", $row_value, $post_content);

                        }

                        // Form tags

                        if (stristr($item->form_tags, "[#$key#]") !== FALSE) {

                            $tags_input = str_replace("[#$key#]", $row_value, $tags_input);

                        }

                        // form cats

                        if (stristr($item->form_categories, "[#$key#]") !== FALSE) {

                            $post_category = str_replace("[#$key#]", $row_value, $post_category);

                        }

                        // Allow comments

                        if ($item->form_allow_comments == TRUE) {
                            $comment_status = 'open';
                        } else {
                            $comment_status = 'closed';
                        }

                        // Allow trackback

                        if ($item->form_allow_trackback == TRUE) {
                            $ping_status = 'open';
                        } else {
                            $ping_status = 'closed';
                        }


                        //testing

                        static $i = 0;
                        static $x = 0;

                        if ($key === ($total_val - 1)) {
                            // Need to make sure title doesn't start with digits
                            $new_post['post_title'] = stripslashes_deep($this->check_utf($post_title));
                            $new_post['post_content'] = stripslashes_deep($this->check_utf($post_content));
                            $new_post['comment_status'] = $comment_status;
                            $new_post['ping_status'] = $ping_status;
                            $new_post['post_type'] = $post_type;

                            // Don't create tags that are digits
                            if (!preg_match("/[0-9]/", $tags_input)) {
                                $new_post['tags_input'] = stripslashes_deep($this->check_utf($tags_input));
                            }

                            if ($item->post_status == NULL) {
                                $new_post['post_status'] = $item->post_status;
                            }

                            $duplicate = FALSE;

                            foreach ($post_meta as $result) {

                                if ((int)$result->meta_value === hexdec(substr(md5($post_title), 0,
                                    7))) {

                                    if ($bol === FALSE) continue;

                                    // this is set to FALSE if the update button is clicked
                                    // that way no old posts will not be updated

                                    // If the two values are the same then the post already exists

                                    $update = TRUE;

                                    if ($item->form_title_contains != "") {

                                        $form_title_contains = explode(",", $item->
                                            form_title_contains);
                                        $update = FALSE;

                                        foreach ($form_title_contains as $new_result) {

                                            if (stristr($new_post['post_title'], trim($new_result))) {
                                                $update = TRUE;
                                            }

                                        }

                                    }

                                    if ($item->form_body_contains != "") {

                                        $form_body_contains = explode(",", $item->
                                            form_body_contains);
                                        $update = FALSE;

                                        foreach ($form_body_contains as $new_result) {

                                            if (stristr($new_post['post_content'], trim($new_result))) {
                                                $update = TRUE;
                                            }

                                        }

                                    }

                                    // If the title and body DO contain keywords then publish items OR publish if NO keywords set

                                    if ($update === TRUE) {

                                        $new_post['ID'] = (int)$result->post_id;

                                        if ($item->form_categories != "") {

                                            $cat_array = explode(",", $post_category);

                                            foreach ($cat_array as $result) {

                                                $result = stripslashes_deep($this->check_utf($result));

                                                // make sure that the user doesn't accidently add numbers
                                                if (!preg_match("/^[0-9]/", $result)) {
                                                    // cut down string length if longer that 200 characters
                                                    // This is to prevent problems if the user hasn't inputed the correct code
                                                    if (mb_strlen($result) > 200) {
                                                        $id = wp_create_category($this->check_utf(mb_substr
                                                            ($result, 0, 200)));
                                                    } else {
                                                        $id = wp_create_category($this->check_utf($result));
                                                    }

                                                }

                                                $post_cat_array[] = $id;

                                            }

                                        }

                                        $new_post['post_category'] = $post_cat_array;

                                        $total_cats = $i++;

                                        wp_update_post($new_post);
                                        
                                        //var_dump("Update: " );
                                        //var_dump($new_post);

                                        // If already exists then update item rather than create a new one.

                                        $duplicate = TRUE;
                                        break;

                                    } // end publish equals TRUE

                                } // end  if ((int)$result->meta_value === hexdec(substr(md5($post_title), 0, 7))) {

                            } // end foreach loop


                            if ($duplicate === FALSE) {

                                // Here create variables for the keyword filtering on creating new post
                                $publish = TRUE;

                                if ($item->form_title_contains != "") {

                                    $form_title_contains = explode(",", $item->form_title_contains);
                                    $publish = FALSE;

                                    foreach ($form_title_contains as $new_result) {

                                        if (stristr($new_post['post_title'], trim($new_result))) {
                                            $publish = TRUE;
                                        }

                                    }

                                }

                                if ($item->form_body_contains != "") {

                                    $form_body_contains = explode(",", $item->form_body_contains);
                                    $publish = FALSE;

                                    foreach ($form_body_contains as $new_result) {

                                        if (stristr($new_post['post_content'], trim($new_result))) {
                                            $publish = TRUE;
                                        }

                                    }

                                }

                                // If the title and body DO contain keywords then publish items OR publish if NO keywords set

                                if ($publish === TRUE) {

                                    // Check that item has never been published
                                    // This is to prevent duplicate content if the item has been previously deleted

                                    $post_title = hexdec(substr(md5($post_title), 0, 7));

                                    if ($this->select_total_feeds($post_title)) continue;

                                    // Categories

                                    // Create the categories in the database here and use the IDs in the insert_post() function

                                    if ($item->form_categories != "") {

                                        $cat_array = explode(",", $post_category);

                                        foreach ($cat_array as $result) {

                                            $result = stripslashes_deep($this->check_utf($result));

                                            // make sure that the user doesn't accidently add numbers
                                            if (!preg_match("/^[0-9]/", $result)) {

                                                // cut down string length if longer that 200 characters
                                                // This is to prevent problems if the user hasn't inputed the correct code
                                                if (mb_strlen($result) > 200) {
                                                    $id = wp_create_category($this->check_utf(mb_substr
                                                        ($result, 0, 200)));
                                                } else {
                                                    $id = wp_create_category($this->check_utf($result));
                                                }

                                            }

                                            $post_cat_array[] = $id;

                                        }

                                    }

                                    $new_post['post_category'] = $post_cat_array;
                                    $new_post['post_date'] = date('Y-m-d H:i:s');
                                    $new_post['post_content'] = force_balance_tags($new_post['post_content']);
                                    $new_post['ID'] = NULL;
                                  

                                    $total_new = $x++;
                                    
                                    //var_dump("New Post:");
                                    //var_dump($new_post);

                                    $id = wp_insert_post($new_post);

                                    add_post_meta($id, '_unique_post', $post_title, TRUE);

                                    add_post_meta($id, '_cat_num', $item->id, TRUE);

                                    $this->insert_total_feeds($post_title, $item->id);

                                }

                            }

                        } // end if statement

                    } // end foreach

                } // end foreach

            } // end if ($csv->load(AH_FEEDS_DIR.$file_here)) {

        } // end if($this->get_file_extension($item->fileName) === "csv") {


        //var_dump("total cats: ".$total_cats);
        //var_dump("total new: ".$total_new);

    }

    /*

    private function recursive_array_find_key_digit($old_array, $new_array = array()) {

    foreach ($old_array as $key => $value) {

    if (!is_int($key)) {
    $new_array = $this->recursive_array_find_key_digit($value, $new_array);

    } else {
    $new_array[] = $value;

    }

    }
    return $new_array;
    }


    private function recursive_array_find_total_entries($old_array, $new_array = array()) {

    foreach ($old_array as $key => $value) {

    if (!is_int($key)) {
    $new_array = $this->recursive_array_find_total_entries($value, $new_array);

    } else {
    $new_array[] = $key;

    }

    }
    return $new_array;
    }
    
    */


    /**
     * Form_Model::check_utf()
     * 
     * If not UTF-8 if then encodes the string
     * 
     * @param string $str
     * @return string
     */


    private function check_utf($str) {

        if (mb_detect_encoding($str, 'UTF-8', TRUE) == FALSE) {
            return utf8_encode($str);
        } else {
            return $str;
        }

    }

    /**
     * Form_Model::synchronize_feeds()
     * 
     * Compares the remote and file and database, deletign database entries if not equal
     * 
     * This is really fucking messy major refacture
     * 
     * @return string
     */

    protected function synchronize_feeds($var) {

        // firstly find the name of the feed to be updated

        $id = $this->find_feed_details_id($_GET['unique_form']);

        // now use that id to find details of all existing published items for that category

        $cat_array = $this->find_meta_cat($id->id);

        // Now find the title of the posts

        foreach ($cat_array as $result) {

            $total_posts[] = $this->get_post_meta_id($result->post_id);

        }

        // the total posts for this category are no in the $total_posts array

        // Now find the filename so as to access the CSV file

        $filename = $this->find_filename_feed_details($_GET['unique_form']);

        $csv = new File_CSV_DataSource;

        $total_titles = array();

        // Load CSV file

        if ($csv->load(AH_FEEDS_DIR.$filename->fileName)) {

            $csv->symmetrize();

            $total = $csv->getrawArray();

            $total_meta = array();

            foreach ($total_posts as $meta_result) {

                $total_meta[] = $meta_result->meta_value;

            }

            // the total_meta array now has the titles for every post in this category

            // loop through CSV total contents and get an array of every title

            ###

            foreach ($total as $result => $value) {

                if ($result == 0) continue;

                foreach ($value as $key => $row_value) {

                    if (stristr((string )$var, (string )$key)) {
                        $total_titles[] = hexdec(substr(md5($row_value), 0, 7));

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

            $postid = $this->find_meta_id($result);
            wp_delete_post($postid->post_id);

        }
    }

    private function insert_total_feeds($id, $cat_id) {

        global $wpdb;

        return $wpdb->query($wpdb->prepare("
		INSERT INTO ".AH_TOTAL_FEEDS_TABLES."(post_title_id, cat_id) VALUES (%d, %d)", $id, $cat_id));

    }

    private function select_total_feeds($id) {

        global $wpdb;

        return $wpdb->get_row("SELECT post_title_id FROM ".AH_TOTAL_FEEDS_TABLES.
            " WHERE post_title_id = $id");

    }

    private function find_meta_id($var) {

        global $wpdb;

        return $wpdb->get_row("SELECT post_id FROM ".$wpdb->prefix."postmeta WHERE meta_value  = '".
            $var."'");

    }

    private function find_meta_cat($var) {

        global $wpdb;

        return $wpdb->get_results("SELECT post_id FROM ".$wpdb->prefix.
            "postmeta WHERE meta_key = '_cat_num' AND meta_value = $var");

    }

    private function find_feed_details_id($var) {

        global $wpdb;

        return $wpdb->get_row("SELECT id FROM ".AH_FEED_DETAILS_TABLE." WHERE name = '".$var."'");


    }

    private function find_filename_feed_details($var) {

        global $wpdb;

        return $wpdb->get_row("SELECT fileName, header_array FROM ".AH_FEED_DETAILS_TABLE.
            " WHERE name = '".$var."'");

    }

    private function find_all_posts_meta() {

        global $wpdb;

        return $wpdb->get_results("SELECT post_id FROM ".$wpdb->prefix.
            "postmeta WHERE meta_key = '_unique_post'");


    }

    private function find_post_title($var) {

        global $wpdb;

        return $wpdb->get_row("SELECT post_title FROM ".$wpdb->prefix."posts WHERE ID = $var");


    }

    private function find_post_revisions($var) {

        global $wpdb;

        return $wpdb->get_row("SELECT ID FROM ".$wpdb->prefix."posts WHERE post_name REGEXP '^$var' AND post_type = 'revision'");

    }

    protected function find_all_post_cats() {

        global $wpdb;

        return $wpdb->get_results("SELECT id, name FROM ".AH_FEED_DETAILS_TABLE);

    }

    protected function delete_total_feeds($var) {

        global $wpdb;

        $wpdb->query($wpdb->prepare("DELETE FROM ".AH_TOTAL_FEEDS_TABLES."
		 WHERE cat_id = %d", $var));

    }


    /**
     * Form_Model::check_table()
     * 
     * Select just name from feed details table
     * 
     * @return string
     */

    protected function check_table($var) {

        global $wpdb; // ADD PREPARE STATEMENT WITH SQL

        return $wpdb->get_row("SELECT name FROM ".AH_FEED_DETAILS_TABLE." WHERE name = '".$var."'");
    }

    protected function get_all_feed_names() {

        global $wpdb;
        return $wpdb->get_results("SELECT name FROM ".AH_FEED_DETAILS_TABLE);
    }

    private function get_post_meta_id($var) {

        global $wpdb;
        return $wpdb->get_row("SELECT post_id, meta_value FROM ".$wpdb->prefix.
            "postmeta WHERE meta_key = '_unique_post' AND post_id = $var");
    }

    private function get_post_meta() {

        global $wpdb;

        return $wpdb->get_results("SELECT post_id, meta_value FROM ".$wpdb->prefix.
            "postmeta WHERE meta_key = '_unique_post'");
    }

    protected function find_file_name() {

        global $wpdb;

        return $wpdb->get_results("SELECT fileName FROM ".AH_FEED_DETAILS_TABLE);

    }

    /**
     * Form_Model::select_all() 
     * 
     * Select all from the feed details table
     * 
     * @return string
     */

    protected function select_all($var) {

        global $wpdb; // ADD PREPARE STATEMENT WITH SQL

        return $wpdb->get_row("SELECT * FROM ".AH_FEED_DETAILS_TABLE." WHERE name = '".$var."'");

    }

    private function select_postid_get_post_meta($var) {

        global $wpdb;

        return $wpdb->get_results("SELECT post_id FROM ".$wpdb->prefix.
            "postmeta WHERE meta_key = '_cat_num' AND meta_value = ".$var);

    }

    protected function delete_all_feed_posts($var) {

        $id = $this->select_postid_get_post_meta($var);

        foreach ($id as $result) {

            if ($result->post_id !== null) {
                wp_delete_post((int)$result->post_id, TRUE);
            }
        }
    }

    protected function delete_revisions() {

        // buid array of total posts

        global $wpdb;

        $all_posts = $this->find_all_posts_meta();

        $title_array = array();

        foreach ($all_posts as $result) {

            $id = $this->find_post_title($result->post_id);

            if ($id !== NULL) {
                $title_array[] = $id;
            } // end

        }

        if (!empty($title_array)) {

            foreach ($title_array as $result) {

                $wpdb->query($wpdb->prepare("DELETE a,b,c
FROM wp_posts a
LEFT JOIN wp_term_relationships b ON (a.ID = b.object_id)
LEFT JOIN wp_postmeta c ON (a.ID = c.post_id)
WHERE a.post_type = 'revision' AND post_title = %s", $result->post_title));

            } // end foreach

        } // end if(!empty($title_array)) {

    }


    /**
     * Form_Model::insert_table() 
     * 
     * Select all from the feed details table
     * 
     * @param string $name
     * @param string $url
     * @param string $fileName
     * @return boolean
     */

    protected function insert_table($name, $url, $fileName, $header_array, $header_array_amend, $num_rows) {

        global $wpdb;
        if ($this->check_table($name) === NULL) {

            return $wpdb->query($wpdb->prepare("
		INSERT INTO ".AH_FEED_DETAILS_TABLE."
		( name, URL, fileName, header_array, header_array_amend,  num_rows)
		VALUES ( %s, %s, %s, %s, %s,  %d )
	", $name, $url, $fileName, $header_array, $header_array_amend, $num_rows));
        } else {

            // here compare data between relevant database table and form
            // if they are different then update table and delete old file in feeds folder

            $select_all = $this->select_all($name);
            if ($select_all->URL !== $url || $select_all->fileName !== $fileName) {

                $file = AH_FEEDS_DIR.$select_all->fileName;
                unlink($file);
                return $wpdb->query($wpdb->prepare("UPDATE ".AH_FEED_DETAILS_TABLE.
                    " SET URL = %s, fileName = %s, header_array = %s, header_array_amend = %s, num_rows = %d WHERE name = %s",
                    $url, $fileName, $header_array, $header_array_amend, $num_rows, $name));
            }
            //$wpdb->print_error();
        }

    }


    /**
     * Form_Model::update_option()
     * 
     * Updates databass. Includes important remove_empty() method
     * 
     * @param array $form
     * @return boolean
     */
    protected function update_option($form) {
        //essential
        extract(static::$form);
        $this->remove_empty($form);
        $this->delete($form);
        $this->check_feed_details_table($form); //var_dump($form);

        if (update_option($option_name, $form)) {
            return $this->success_message("You have successfully updated the form");
        }

    }

    /**
     * Form_Model::check_feed_details_table()
     * 
     * Remove associated data from the feed details table and the feeds folder
     * when the title is deleted from the options tables filed
     * 
     * @param array $form
     * @return boolean
     */

    private function check_feed_details_table($form) {
        //essential
        extract(static::$form);
        global $wpdb;
        $table_name = AH_FEED_DETAILS_TABLE;
        $fields = array();
        foreach ($form[$option_name] as $key2 => $value2) {

            if (is_array($value2)) continue;
            if ($value2 === "") continue;
            $fields[] = $value2;
        }

        $all_feeds = $wpdb->get_results("SELECT name, fileName FROM $table_name");
        foreach ($all_feeds as $key => $value) {

            if (in_array($value->name, $fields)) continue;
            $this->delete_record($value->name, $value->fileName);

        }

    }

    /**
     * Form_Model::delete_record()
     * 
     * Remove associated data from the feed details table and the feeds folder
     * when the title is deleted from the options tables filed
     * 
     * @param string $name
     * @param string $filename
     * 
     */

    private function delete_record($name, $filename) {

        global $wpdb;
        $file = AH_FEEDS_DIR.$filename;
        unlink($file);

        // delete stuff here

        $id = $this->find_feed_details_id($name);

        $this->delete_total_feeds($id->id);

        $wpdb->query($wpdb->prepare("DELETE FROM ".AH_FEED_DETAILS_TABLE."
		 WHERE name = %s
		", $name));
    }

    /**
     * Form_Model::update_ind_form())
     * 
     * Remove associated data from the feed details table and the feeds folder
     * when the title is deleted from the options tables filed
     * 
     * @param boolean $form
     * @param string $form_name
     * 
     */

    protected function update_ind_form($form, $form_name) {

        //essential
        extract(static::$form);

        $form_categories = NULL;
        $form_tags = NULL;
        foreach ($form[$option_name] as $key => $value) {

            if ($key === "formTitle") {
                $form_title = $value;
            }

            if ($key === "TitleContains") {
                if ($value != "") {
                    $form_title_contains = $value;
                } else {
                    $form_title_contains = NULL;
                }
            }

            if ($key === "formBody") {
                $form_body = $value;
            }

            if ($key === "BodyContains") {
                if ($value != "") {
                    $form_body_contains = $value;
                } else {
                    $form_body_contains = NULL;
                }

            }

            if ($key === "formCategories") {

                if ($value != "") {
                    $form_categories = $value;
                } else {
                    $form_categories = NULL;
                }

            }

            if ($key === "formMinRows") {
                if ($value != "") {
                    $form_min_rows = (int)$value;
                } else {
                    $form_min_rows = "NULL";
                }

            }

            if ($key === "formMaxRows") {
                if ($value != "") {
                    $form_max_rows = (int)$value;
                } else {
                    $form_max_rows = NULL;
                }

            }

            if ($key === "formPostStatus") {
                if ($value != "") {
                    $form_post_status = $value;
                } else {
                    $form_post_status = 'published';
                }

            }

            if ($key === "formTags") {

                if ($value != "") {
                    $form_tags = $value;
                } else {
                    $form_tags = NULL;
                }

            }

            if (preg_match("/^formAllowComments/", $key)) {
                $form_allow_comments = (integer)$value;
            }

            if (preg_match("/^formAllowTrackbacks/", $key)) {
                $form_allow_trackback = (integer)$value;
            }

            if ($key === "formPostType") {

                if ($value != "") {
                    $form_posttype = $value;
                } else {
                    $form_posttype = "post";
                }

            }


        }


        if ($this->update_feed_details($form_title, $form_title_contains, $form_body, $form_body_contains,
            $form_categories, $form_tags, $form_allow_comments, $form_allow_trackback, $form_name, $form_min_rows,
            $form_max_rows, $form_post_status, $form_posttype)) {

            // Once the feed_details table has been updated then process the feed:

            return TRUE; //return $this->success_message("You have successfully updated the form");
            wp_redirect(admin_url("/options-general.php?page=".$page_url));
            exit;
        }


    }


    /**
     * Form_Model::update_feed_details()
     * 
     * Updates the main datbase table
     * 
     */

    protected function update_feed_details($form_title, $form_title_contains, $form_body, $form_body_contains,
        $form_categories, $form_tags, $form_allow_comments, $form_allow_trackback, $form_name, $form_min_rows,
        $form_max_rows, $form_post_status, $form_posttype) {

        global $wpdb;
        return $wpdb->query($wpdb->prepare("UPDATE ".AH_FEED_DETAILS_TABLE.
            " SET form_title = %s, form_title_contains = %s, form_body = %s, form_body_contains = %s, form_categories = %s, form_tags = %s, form_allow_comments = %d, form_allow_trackback = %d, min_rows = %d, max_rows = %d, post_status = %s, post_type = %s WHERE name = %s",
            $form_title, $form_title_contains, $form_body, $form_body_contains, $form_categories, $form_tags,
            $form_allow_comments, $form_allow_trackback, $form_min_rows, $form_max_rows, $form_post_status,
            $form_posttype, $form_name));
    }


    /**
     * Form_Model::update_record()
     * 
     * This downloads files from URL or from uploading
     * After this the filename is then used to create a row in the feed_details table
     * 
     * @param array $form
     * 
     */


    protected function update_record($form) {

        //essential
        extract(static::$form);
        $feed_url = $form[$option_name]['feedURL'];
        $fileName = NULL;
        if ($this->check_file_empty($_FILES[$option_name], 'feedFile') === FALSE) {

            // download and covert feeds to
            $fileName = $this->parse_feeds_loop($form, 'feedURL');
        } else {

            $fileName = $this->move_file($_FILES[$option_name], 'feedFile');
        }

        if ($this->get_file_extension($fileName) === "xml") {

            // Here convert XML file to CSV

            $xml_file = AH_FEEDS_DIR.$fileName; // Use XML_Serializer to get the data into a useable array

            $data = $this->xml_helper($xml_file);

            $header_array = $this->recursive_array($data); //

            // Find ZML filename without the extension

            $csv_file = $this->get_file_filename($fileName).'.csv'; // Use that for the name of the CSV file

            $csv_full = AH_FEEDS_DIR.$csv_file;
            $fp = fopen($csv_full, 'w');
            // The first line in the CSV line is the header infor

            fputcsv($fp, $header_array); // Find all the values in the XML file

            $total = $this->recursive_array_values($data); //

            // Here I reset the array so the first key in the index is one not zero

            //$keys = range(1, count($form));
            //$values = array_values($form);
            //$form = array_combine($keys, $values);

            $this->reset_array($total);

            static $i = 1;
            $end = array();
            foreach ($total as $key => $value) {

                // Now loop through the values in the XML file adding them underneath the CSV headers
                // If a value has no info use empty instead

                if (strlen($value) === 0) {
                    $value = "Empty";
                }

                $end[] = $value;
                if ($i++ % count($header_array) == 0) {
                    fputcsv($fp, $end);
                    unset($end);
                }

            }

            // Done. Close the CSV file and destroy the used XML file
            fclose($fp);
            unlink($xml_file);
            $fileName = $csv_file;
            $header_array = $this->parse_csv_head(AH_FEEDS_DIR.$fileName); //var_dump($header_array); //correct!

            $header_array_amend = $header_array; //count total number of entries
            $num_rows = $this->count_csv_rows(AH_FEEDS_DIR.$fileName);
            foreach ($header_array_amend as $key => $value) {
                $header_array_amend['[#'.$key.'#]'] = $value;
                unset($header_array_amend[$key]);
            } // end foreach

        }

        if ($this->get_file_extension($fileName) === "csv") {

            $header_array = $this->parse_csv_head(AH_FEEDS_DIR.$fileName);
            $header_array_amend = $header_array;
            //count total number of entries
            $num_rows = $this->count_csv_rows(AH_FEEDS_DIR.$fileName);
            foreach ($header_array_amend as $key => $value) {
                $header_array_amend['[#'.$key.'#]'] = $value;
                unset($header_array_amend[$key]);
            } // end foreach

        } // end if

        if ($fileName != NULL) {

            if ($this->insert_table($form['indName'], $feed_url, $fileName, serialize($header_array),
                serialize($header_array_amend), $num_rows)) {
                wp_redirect(admin_url("/options-general.php?page=".$page_url));
                exit;
            }

        }

    }

    /**
     * Form_Model::recursive_array()
     * 
     * Uses SPL to find header array
     * 
     * @param array $old_array
     * @return array
     * 
     */


    private function recursive_array($old_array) {

        $riter = new RecursiveIteratorIterator(New RecursiveArrayIterator($old_array));
        $output = array();
        foreach ($riter as $key => $value) {
            static $i = 1;
            if ($riter->getDepth() > 2) {

                $index = $i++;
                if ($index === 1) {
                    $index_key = $key;
                }

                if ($key === $index_key && $index > 1) {
                    break;
                }

                $output[] = $key;
            }
        }

        return $output;

    }

    /**
     * Form_Model::recursive_array()
     * 
     * Uses SPL to find all array info
     * 
     * @param array $old_array
     * @return array
     * 
     */

    private function recursive_array_values($old_array, $new_array = array()) {

        $riter = new RecursiveIteratorIterator(New RecursiveArrayIterator($old_array));
        foreach ($riter as $key => $value) {

            static $x = 0;

            if ($riter->getDepth() > 2) {
                $new_array[] = $value;
            }
        }

        return $new_array;
    }


    /**
     * Form_Model::xml_helper()
     * 
     * Uses SPL to find all array info
     * 
     * Uses XML_Serializer and XML_Unserializer objects to get the XXM data into a useable array
     * 
     * @param array $file
     * @return array
     * 
     */


    private function xml_helper($file) {

        // keep these functions for future use!!!

        /*

        //http://stackoverflow.com/a/8287944/315350
        function array_clean(array $haystack) {
        foreach ($haystack as $key => $value) {
        if (is_array($value)) {
        $haystack[$key] = array_clean($value);
        } elseif (is_string($value)) {
        $value = trim($value);
        }

        if (!$value) {
        unset($haystack[$key]);
        }
        }

        return $haystack;
        }


        function removeRecursive($haystack, $needle) {
        if (is_array($haystack)) {
        foreach ($haystack as $k => $value) {
        if ($k === $needle) {
        foreach ($value as $key => $result) {
        unset($value[$key]);
        }

        unset($haystack[$k]);

        } else {
        $haystack[$k] = removeRecursive($value, $needle);

        }
        }
        }

        return $haystack;
        }
        
        */

        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

        $options = array(XML_SERIALIZER_OPTION_INDENT => '    ', XML_SERIALIZER_OPTION_RETURN_RESULT => TRUE);

        $serializer = &new XML_Serializer($options);

        $result = $serializer->serialize($xml);

        $options = array(XML_UNSERIALIZER_OPTION_COMPLEXTYPE => 'array',
                XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE => FALSE);
        $unserializer = &new XML_Unserializer($xml);
        // userialize the document
        $unserializer->unserialize($result, FALSE);
        $data = $unserializer->getUnserializedData();

        return $data;


    }


    /**
     * Form_Model::success_message()
     * 
     * @param mixed $message
     * @return
     */
    protected function success_message($message) {

        //essential
        extract(static::$form); // necessary for javascript form values zero to work
        if ($dynamic_output) {
            setcookie("_multi_cov", $option_name, time() + 60);
        }

        $html = '<div id="message" class="updated">';
        if (is_array($message)) {
            foreach ($message as $line) {
                $html .= '<p><strong>'.$line.'</strong></p>';
            }
        } else {
            $html .= '<p><strong>'.$message.'</strong></p>';
        } // end if

        $html .= '</div>';
        return $html;
    }

    /**
     * Form_Model::failure_message()
     * 
     * @param mixed $message
     * @return
     */
    protected function failure_message($message) {

        //essential
        extract(static::$form);
        $html = '<div id="message" class="error">';
        if (is_array($message)) {
            foreach ($message as $line) {
                $html .= '<p><strong>'.$line.'</strong></p>';
            }
        } else {
            $html .= '<p><strong>'.$message.'</strong></p>';
        } // end if

        $html .= '</div>';
        return $html;
    }


    /**
     * Form_Model::remove_empty()
     * 
     * Necessary for not including empty HTML fields in the database update if dynamic options is set to TRUE
     * If this method is not used then unnessecary fields will become part of the option database field
     * 
     * The reason this is complicated code is because the array is an irregular mix of strings and nested arrays
     * There needs a method to delete both from the array before it is submitted to the database
     * 
     * @param array $form_output
     * @return array
     */
    protected function remove_empty(&$form_output) {

        extract(static::$form);
        $database = get_option($option_name);
        $output = (int)$form_output['total_user_fields'];
        $fields = count($form_output[$option_name]);
        $unset = FALSE;
        $new_key = array();
        $total_inputs = array();
        $total_arrays = array();
        $radio = FALSE;
        if (static::check_options_table() && $dynamic_output && !empty($database[$option_name])) {

            // if new form without option database created yet make sure ALL fields are not empty
            foreach ($form_output[$option_name] as $n_key => $n_value) {

                // need to take into caculations whether radio buttons are used
                // the extra non-checked radio buttons need to be added the to the final totals
                // Only one radio button will ever be checked, so the remained are left
                if (preg_match("/zyxwv/", $n_key)) {
                    $form_output['radio_buttons'] = TRUE;
                    $radio[] = TRUE;
                }

                static $x = 0;
                static $z = 0;
                if (is_string($n_value)) {

                    $x++; // the total inputs are fluid depending if the user has checked the delete box
                    // The only TRUE way to determine the number is to access it here
                    $total_inputs[] = $x;
                } // end is_string

                if (is_array($n_value)) {
                    $z++; // the total inputs are fluid depending if the user has checked the delete box
                    // The only TRUE way to determine the number is to access it here
                    $total_arrays[] = $z;
                } // end is_string

            } // end foreach loop

            // previously was total_inputs
            $total = array_pop($total_arrays) - $output;
            $total_minus = array_pop($total_arrays) - $output;
            if (!empty($radio)) {

                foreach ($form_output[$option_name] as $key => $value) {

                    var_dump($key);
                    if (is_string($value)) continue;
                    if (!isset($value['radio_number'])) continue;
                    if ($value['radio_number'] == NULL) continue;
                    if (preg_match("/^\d$/", $key)) continue;
                    $number = (int)$value['radio_number'];
                    break;
                } // end foreach

                $form_output[$option_name] = array_reverse($form_output[$option_name]);
                $total_empties = array();
                $t = NULL;
                foreach ($form_output[$option_name] as $key => $value) {

                    if (!is_array($value)) {

                        static $t = 1;
                        if ($value !== "") {
                            // if values are not as above then they have content
                            // if they are as above or in the case of radio buttons not set at all
                            // then that means
                            static $n = 1;
                            $total_empties[] = $n++;
                        }

                    }

                    if ($t++ === $output) break;
                } // end foreach

                if (empty($total_empties)) {
                    // if the array is empty then all the input fields including radion buttons are empty
                    $unset = TRUE;
                }

                if ($unset === TRUE) {

                    // total invididual number of arrays to be deleted are
                    // total number of individual radio button fields
                    // plus the complete field arrays minus above * 2 remainder.
                    // This is because every non-radio button has two arrays associated with it
                    $total_ars = $number + (($output - $number) * 2); // remove empty form from entire array
                    array_splice($form_output[$option_name], 0, $total_ars, NULL);
                    // on successful completion rearrange array to previous order but without unwanted fields
                    $form_output[$option_name] = array_reverse($form_output[$option_name]);
                    return $form_output;
                } else {

                    // if not TRUE then put the array back to how it was before;
                    $form_output[$option_name] = array_reverse($form_output[$option_name]);
                    return $form_output;
                }
                // beginning of if not $radio - no radio buttons in the form submit process
            } elseif (empty($radio)) {

                foreach ($form_output[$option_name] as $n_key => $n_value) {

                    static $i = 1;
                    static $y = 0;
                    static $b = 0;
                    if (is_string($n_value)) {


                        // don't allow checkboxes to be submitted
                        //if(preg_match('/zqxjk/', $n_key)) continue;

                        if ($i++ > $total) {

                            if (empty($n_value)) {
                                $y++;
                                $new_key[] = $y;
                            }

                            //var_dump($new_key);

                            if (array_pop($new_key) === $output) {
                                $unset = TRUE;
                            } // end if

                        } // end if

                    } // end if

                } // end foreach

                // if unset then make sure the unwanted arrays and strings are removed from the parent array before submission to the database
                if ($unset === TRUE) {

                    foreach ($form_output[$option_name] as $n_key => $n_value) {

                        static $c = 0;
                        static $f = 0;
                        if (is_string($n_value)) {

                            if ($c++ >= $total) {
                                unset($form_output[$option_name][$n_key]);
                            } // end if

                        } // end if

                        if (is_array($n_value)) {

                            if ($f++ >= $total_minus) {
                                unset($form_output[$option_name][$n_key]);
                            } // end if

                        }

                    } // end foreach

                } // end if unset

                return $form_output;
            } // if not $radio

        } else {

            return $form_output;
        } // end if ($dynamic_output)

    }

    /**
     * Form_Model::delete()
     * 
     * Deletes data before submission to database if the checkbox is checked
     * Unsets array items and then rebuilds array with fresh index
     * 
     * @param array $form
     * @return array $form
     */

    protected function delete(&$form_output) {

        // essential.
        extract(static::$form);
        $database = get_option($option_name);
        if (static::check_options_table() && $dynamic_output && !empty($database[$option_name])) {

            $delete = NULL;
            $output = (int)$form_output['total_user_fields'];
            $total_arrays = array();
            $delete = array();
            $radio = array();
            // if new form without option database created yet make sure ALL fields are not empty
            foreach ($form_output[$option_name] as $n_key => $n_value) {

                if (preg_match("/zyxwv/", $n_key)) {
                    $radio[] = TRUE;
                }

                static $x = 0;
                $x++;
                $total_arrays[] = $x;
            } // end foreach loop

            $total_elements = array_pop($total_arrays);
            $this->reset_array($form_output[$option_name]);
            // Find any button
            foreach ($form_output[$option_name] as $result => $value) {

                if ($value === "1") {
                    $delete[] = $result;
                }

            } // end foreach

            if ($radio && (isset($form_output['radio_buttons']) && $form_output['radio_buttons'] === TRUE)) {

                if ($delete) {

                    // find the total number of radio buttons in the form
                    foreach ($form_output[$option_name] as $key => $value) {

                        if (is_string($value)) continue;
                        if (!isset($value['radio_number'])) continue;
                        if ($value['radio_number'] == NULL) continue;
                        $number = (int)$value['radio_number'];
                        break;
                    } // end foreach

                    foreach ($delete as $n_delete) {

                        //Work out max top and bottom keys to delete
                        $y_delete = $n_delete + 1;
                        $t_element = (int)$y_delete;
                        $b_element = $t_element - (int)(($output * 2) + 2) + ($number - 1); // include missing radio buttons in calcs

                        // use slice to remove unwanted forms from parent array
                        array_splice($form_output[$option_name], $b_element, $t_element, NULL);
                    } // end foreach

                }

            }


            if (!$radio) {

                if ($delete) {

                    foreach ($delete as $n_delete) {

                        //Work out max top and bottom keys to delete
                        $y_delete = $n_delete + 1;
                        $t_element = (int)$y_delete;
                        $b_element = $t_element - (int)(($output * 2) + 2); // use slice to remove unwanted forms from parent array
                        array_splice($form_output[$option_name], $b_element, $t_element, NULL);
                    } // end foreach

                } // end if delete

            } // end if not radio

            if (!empty($form_output[$option_name])) {
                $this->reset_array($form_output[$option_name]);
            }

            return $form_output;
        } else {

            // if not dynamic
            return $form_output;
        } // end dynamic output


    }

    /**
     * Form_Model::security_check()
     * 
     * ESSENTIAL! Must include this
     * Removes non-relevant HTML form fields before database update
     * 
     * @param array $array
     * @return array
     */
    protected function security_check($array) {

        if (!wp_verify_nonce($array['_wpnonce_options_cov'], "options_form_cov")) die("Security check failed");
        if ($_SERVER['REQUEST_URI'] !== $array['_wp_http_referer']) die("Security check failed");
        // The values below need to be removed before further validation and database entry

        unset($array['option_page']);
        unset($array['_wpnonce_options_cov']);
        unset($array['_wp_http_referer']);
        unset($array['submit']); //$form['unset_all'] = FALSE;

        return $array;
    }


    /**
     * Form_Model::find_url()
     * 
     * Need to find the full URI for the admin area pages.
     * Is there a suitable Wordpress function for this purpose? I couldn't find one
     * 
     * @return string
     */
    public static function find_url() {

        $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            return $pageURL;
        }

    }


    /**
     * Form_Model::hex2bin()
     * 
     * Alternative function because hex2bin is not native in PHP until version 5.4!
     * 
     * @param array $form
     * @return string
     */
    protected function hex2bin($data) {
        $bin = "";
        $i = 0;
        do {
            $bin .= chr(hexdec($data{$i}.$data{($i + 1)}));
            $i += 2;
        } while ($i < strlen($data));
        return $bin;
    }


    /**
     * Form_Model::reset_array()
     * 
     * Alternative function because hex2bin is not native in PHP until version 5.4!
     * 
     * @param array $form
     * @return string
     */
    protected function reset_array(&$form) {

        $keys = range(1, count($form));
        $values = array_values($form);
        $form = array_combine($keys, $values);
        return $form;
    }


}
