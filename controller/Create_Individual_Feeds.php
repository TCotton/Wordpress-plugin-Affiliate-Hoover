<?php namespace controller;
use File_CSV_DataSource;
use ArrayIterator;
use DateTime;

/**
 * Create_Individual_Feeds
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

class Create_Individual_Feeds extends \model\Database {

    protected static $read_write;

    protected static $csv;

    /**
     * Create_Individual_Feeds::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();

        if (!(self::$read_write instanceof \model\Write_Read_Files)) {
            self::$read_write = new \model\Write_Read_Files();
        }

        if (!(self::$csv instanceof \model\Handle_Files)) {
            self::$csv = new \model\Handle_Files();
        }

    } // end construct

    /**
     * Create_Individual_Feed::create_post_items()
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

    protected function create_indiviual_feeds($var, $bol = TRUE) {

        $item = $this->db_select_all($var);

        $file_here = $item->fileName;

        // get user id of admin
        $user_info = get_userdata(1);
        // this is user id --> $user_info->ID

        // PARSE AND SAVE CSV DETAILS HERE

        $headers = unserialize($item->header_array);

        $new_post = array(
            'post_title' => NULL,
            'post_content' => NULL,
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_type' => 'post',
            'post_author' => $user_info->ID,
            'tags_input' => NULL,
            'post_category' => NULL,
            'post_status' => 'publish',
            );

        $post_meta = $this->db_get_post_meta();

        $total_csv = self::$csv->count_csv_rows($item->fileName);

        // CSV

        if (pathinfo($file_here, PATHINFO_EXTENSION) == 'csv') {

            $csv = new File_CSV_DataSource;

            if ($csv->load(AH_FEEDS_DIR.$file_here)) {

                $csv->symmetrize();

                $total = new ArrayIterator($csv->getrawArray());

                foreach ($total as $result => $value) {

                    if ($item->min_rows != FALSE && $item->max_rows != FALSE) {

                        if ($result > $item->max_rows) continue;
                        if ($result < $item->min_rows) continue;

                    }

                    if ($result == 0) continue;

                    $total_val = count($value);

                    foreach ($value as $key => $row_value) {

                        if ($key == 0) {

                            $post_title = $item->form_title;
                            $post_content = $item->form_body;
                            $tags_input = $item->form_tags;
                            $post_category = $item->form_categories;
                            $post_cat_array = array();
                            $post_type = $item->post_type;
                            $post_voc = $item->form_vocabulary;
                            $stagger = $item->form_staggered;

                        }

                        // Post title

                        if (mb_stristr($item->form_title, "[#$key#]") !== FALSE) {

                            $post_title = str_replace("[#$key#]", $row_value, $post_title);

                        }

                        // Post content

                        if (mb_stristr($item->form_body, "[#$key#]") !== FALSE) {

                            $post_content = str_replace("[#$key#]", $row_value, $post_content);

                        }

                        // Form tags

                        if (mb_stristr($item->form_tags, "[#$key#]") !== FALSE) {

                            $tags_input = str_replace("[#$key#]", $row_value, $tags_input);

                        }

                        // form cats

                        if (mb_stristr($item->form_categories, "[#$key#]") !== FALSE) {

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

                        if ($item->form_body_nofollow == TRUE) {
                            $nofollow = TRUE;
                        } else {
                            $nofollow = FALSE;
                        }


                        if ($item->form_categories_parent != '') {

                            $id = term_exists($item->form_categories_parent, $post_voc);

                            if ($id) {

                                $form_categories_parent = (int)$id['term_id'];

                            } else {

                                $form_categories_parent = NULL;

                            }

                        } else {

                            $form_categories_parent = NULL;

                        }

                        if ($item->post_status != '0') {
                            $new_post['post_status'] = $item->post_status;
                        } else {
                            $new_post['post_status'] = 'publish';
                        }

                        static $i = 1;
                        static $x = 1;

                        if ($key == ($total_val - 1)) {
                            // Need to make sure title doesn't start with digits
                            $new_post['post_title'] = stripslashes_deep(ah_check_utf($post_title));
                            $new_post['post_content'] = stripslashes_deep($this->add_tracking_link(ah_check_utf
                                ($post_content)));

                            if ($nofollow) {
                                $new_post['post_content'] = $this->dont_follow_links($new_post['post_content']);
                            }

                            $new_post['comment_status'] = $comment_status;
                            $new_post['ping_status'] = $ping_status;
                            $new_post['post_type'] = $post_type;

                            // Don't create tags that are digits
                            if (!preg_match('/[0-9]/', $tags_input)) {
                                $new_post['tags_input'] = stripslashes_deep(ah_check_utf($tags_input));
                            }

                            $duplicate = FALSE;

                            foreach ($post_meta as $result) {

                                if ((int)$result->meta_value == hexdec(mb_substr(md5($post_title), 0,
                                    7))) {

                                    if ($bol === FALSE) continue;

                                    // this is set to FALSE if the update button is clicked
                                    // that way no old posts will not be updated

                                    // If the two values are the same then the post already exists

                                    $update = TRUE;

                                    if ($item->form_title_contains != '') {

                                        $form_title_contains = explode(',', $item->
                                            form_title_contains);
                                        $update = FALSE;

                                        foreach ($form_title_contains as $new_result) {

                                            if (mb_stristr($new_post['post_title'], trim($new_result))) {
                                                $update = TRUE;
                                            }

                                        }

                                    }

                                    if ($item->form_body_contains != '') {

                                        $form_body_contains = explode(',', $item->
                                            form_body_contains);
                                        $update = FALSE;

                                        foreach ($form_body_contains as $new_result) {

                                            if (mb_stristr($new_post['post_content'], trim($new_result))) {
                                                $update = TRUE;
                                            }

                                        }

                                    }

                                    // If the title and body DO contain keywords then publish items OR publish if NO keywords set

                                    if ($update === TRUE) {

                                        $new_post['ID'] = (int)$result->post_id;

                                        if ($item->form_categories != '') {

                                            $cat_array = explode(',', $post_category);

                                            foreach ($cat_array as $result) {

                                                if ($result == '') continue;

                                                $result = stripslashes_deep(ah_check_utf($result));

                                                // make sure that the user doesn't accidently add numbers
                                                if (!preg_match('/^[0-9]/', $result)) {

                                                    $id = term_exists(ah_check_utf(mb_substr(trim($result),
                                                        0, 200)), $post_voc, $form_categories_parent);

                                                    if (!$id) {

                                                        $id = wp_insert_term(ah_check_utf(mb_substr
                                                            (trim($result), 0, 200)), // the term
                                                            $post_voc, // the taxonomy
                                                            array('parent' => $form_categories_parent));

                                                    }

                                                }

                                                $post_cat_array[] = $id['term_id'];

                                            } // end foreach

                                        }

                                        $new_post['post_category'] = $post_cat_array;

                                        $total_cats = $i++;

                                        wp_update_post($new_post);

                                        // If already exists then update item rather than create a new one.

                                        $duplicate = TRUE;
                                        break;

                                    } // end publish equals TRUE

                                } // end  if ((int)$result->meta_value == hexdec(substr(md5($post_title), 0, 7))) {

                                static $t = 1;

                                if ($t++ == 2) break;

                            } // end foreach loop


                            if ($duplicate === FALSE) {

                                // Here create variables for the keyword filtering on creating new post
                                $publish = TRUE;

                                if ($item->form_title_contains != '') {

                                    $form_title_contains = explode(',', $item->form_title_contains);
                                    $publish = FALSE;

                                    foreach ($form_title_contains as $new_result) {

                                        if (mb_stristr($new_post['post_title'], trim($new_result))) {
                                            $publish = TRUE;
                                        }

                                    }

                                }

                                if ($item->form_body_contains != '') {

                                    $form_body_contains = explode(',', $item->form_body_contains);
                                    $publish = FALSE;

                                    foreach ($form_body_contains as $new_result) {

                                        if (mb_stristr($new_post['post_content'], trim($new_result))) {
                                            $publish = TRUE;
                                        }

                                    }

                                }

                                // If the title and body DO contain keywords then publish items OR publish if NO keywords set

                                if ($publish === TRUE) {

                                    // Check that item has never been published
                                    // This is to prevent duplicate content if the item has been previously deleted

                                    $post_title = hexdec(mb_substr(md5($post_title), 0, 7));

                                    if ($this->db_select_total_feeds($post_title)) continue;

                                    // Categories

                                    // Create the categories in the database here and use the IDs in the insert_post() function

                                    if ($item->form_categories != '') {

                                        $cat_array = explode(',', $post_category);

                                        foreach ($cat_array as $result) {

                                            if ($result == '') continue;

                                            $result = stripslashes_deep(ah_check_utf($result));

                                            // make sure that the user doesn't accidently add numbers
                                            if (!preg_match('/^[0-9]/', $result)) {

                                                $id = term_exists(ah_check_utf(mb_substr(trim($result),
                                                    0, 200)), $post_voc, $form_categories_parent);

                                                if (!$id) {

                                                    $id = wp_insert_term(ah_check_utf(mb_substr(trim
                                                        ($result), 0, 200)), // the term
                                                        $post_voc, // the taxonomy
                                                        array('parent' => $form_categories_parent));

                                                }

                                            }

                                            $post_cat_array[] = $id['term_id'];

                                        } // end foreach

                                    }

                                    $new_post['post_category'] = $post_cat_array;

                                    // create staggered cteation date here

                                    if ((int)$stagger == 1) {

                                        if (session_id() == '') {
                                            session_start();
                                            session_regenerate_id(true);
                                        } else {
                                            session_regenerate_id(true);
                                        }

                                        // create staggered dates here

                                        static $y = 1;

                                        if ($y++ % 5 == 0) {

                                            if (isset($_SESSION['post_time']) && $_SESSION['post_time'] !=
                                                date('Y-m-d H:i:s')) {

                                                $current_date = new DateTime($_SESSION['post_time']);

                                            } else {

                                                $current_date = new DateTime();

                                            }

                                            $_SESSION['post_time'] = date_format($current_date->
                                                modify('+ 10 minutes'), 'Y-m-d H:i:s');

                                            $new_post['post_date'] = $_SESSION['post_time'];

                                        }

                                    } else {

                                        $new_post['post_date'] = date('Y-m-d H:i:s');

                                    }

                                    $new_post['post_content'] = force_balance_tags($new_post['post_content']);

                                    $new_post['ID'] = NULL;

                                    $total_new = $x++;

                                    $id = wp_insert_post($new_post);

                                    add_post_meta($id, '_unique_post', $post_title, TRUE);

                                    add_post_meta($id, '_cat_num', $item->id, TRUE);

                                    $this->db_insert_total_feeds($post_title, $item->id);

                                }

                            }

                        } // end if statement

                    } // end foreach

                } // end foreach

            } // end if ($csv->load(AH_FEEDS_DIR.$file_here)) {

        } // end if($this->get_file_extension($item->fileName) == "csv") {

        // write to file

        if (isset($total_cats)) {
            self::$read_write->write_file($total_cats." posts from ".$item->name.
                " were updated on ".date('h:i:s A, l jS \of F Y'));
        }

        if (isset($total_new)) {
            if (isset($_SESSION['post_time'])) {
                unset($_SESSION['post_time']);
                session_destroy();
            }
            self::$read_write->write_file($total_new." posts from ".$item->name." were created on ".
                date('h:i:s A, l jS \of F Y'));
        }

    }


    //http://stackoverflow.com/a/3257169/315350
    // function adds nofollow to links
    /**
     * Create_Individual_Feeds::dont_follow_links()
     * 
     * @param string $html
     * @return
     */
    protected function dont_follow_links($html) {
        // follow these websites only!
        $follow_list = array($_SERVER['HTTP_HOST']);
        return preg_replace('%(<a\s*(?!.*\brel=)[^>]*)(href="https?://)((?!(?:(?:www\.)?'.implode('|(?:www\.)?',
            $follow_list).'))[^"]+)"((?!.*\brel=)[^>]*)(?:[^>]*)>%', '$1$2$3"$4 rel="nofollow">', $html);
    }

    // adds class of ah_link to outbound links,
    // This is intended as for use with tracking clicks on outbound links
    /**
     * Create_Individual_Feeds::add_tracking_link()
     * 
     * @param string $html
     * @return
     */
    protected function add_tracking_link($html) {
        // follow these websites only!
        $follow_list = array($_SERVER['HTTP_HOST']);
        $out = preg_replace('/(<a[^>]*?)(class\s*\=\s*\"[^\"]*?\")([^>]*?>)/', '$1$3', $html);
        return preg_replace('%(<a\s*(?!.*\brel=)[^>]*)(href="https?://)((?!(?:(?:www.)?'.implode('|(?:www\.)?',
            $follow_list).'))[^"]+)"((?!.*\brel=)[^>]*)(?:[^>]*)>%', '$1$2$3"$4 class="ah_link ">',
            $out);

    }


    /**
     * Create_Individual_Feeds::create_individual_feeds_facade()
     * 
     * @param array $var
     * @param bool $bol
     * @return
     */
    public function create_individual_feeds_facade($var, $bol = TRUE) {

        $this->create_indiviual_feeds($var, $bol = TRUE);

    }

}
