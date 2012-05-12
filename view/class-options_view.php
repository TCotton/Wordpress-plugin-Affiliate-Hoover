<?php

namespace OptionView;
use OptionController;
use Feed_Model;
use Feed_Controller;
use WP_Filesystem;
use File_CSV_DataSource;
/**
 * Form_View
 * 
 * @package Wordpess Options API access class
 * @author Andy Walpole
 * @copyright Andy Walpole
 * @link http://andywalpole.me/
 * @version development
 * @access public
 * @license GPLv2: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * add_action()
 * http://codex.wordpress.org/Function_Reference/add_action
 * 
 * add_options_page()
 * http://codex.wordpress.org/Function_Reference/add_options_page
 * 
 * screen_icon()
 * http://codex.wordpress.org/Function_Reference/screen_icon
 * 
 * wp_die()
 * http://codex.wordpress.org/Function_Reference/wp_die 
 * 
 * wp_enqueue_script()
 * http://codex.wordpress.org/Function_Reference/wp_enqueue_script
 * 
 * wp_localize_script()
 * http://codex.wordpress.org/Function_Reference/wp_localize_script
 * 
 * wp_enqueue_style
 * http://codex.wordpress.org/Function_Reference/wp_enqueue_style
 * 
 * One set of radio buttons per form
 * And the checkbox must have different name attribute
 * 
 * To do:
 * 
 * Replace strtr for preg_replace() for performance
 * Replace stripos() for preg_replace() for performance
 * 
 * Refactor methods in the model class
 * 
 * Take a loot at remove_empty() & delete() calculations not correct on dynamic form
 * 
 * Need to display error message if number of checkboxes not used in the field array
 */

class Form_View extends OptionController\Form_Controller {

    protected static $form;

    function __construct() {

        /**
         * The code in the Form_View constructor is essential to the functioning of 
         * all the script and it is not recommened that you remove them
         */


        $this->add_action_admin_menu();

        $args = func_get_args();

        foreach ($args as $result) {

            $result = array_values($result);

            if (count($result) !== 4) {
                wp_die("Please make sure you place the right number of values into your array");
            }

            list($option_name, $page_title, $page_url, $dynamic_output) = $result;

            self::$form = $this->config_settings($option_name, $page_title, $page_url, $dynamic_output);

            add_action('admin_enqueue_scripts', array($this, 'scripts_enqueue_cov'));

            parent::__construct();

            //create database table
            register_activation_hook(__FILE__, array(&$this, 'create_table'));

        } // end foreach

    } // end construct


    public function scripts_enqueue_cov() {

        // essential.
        extract(self::$form);

        if (strpos($this->find_url(), $page_url)) {


            // Only display script on plugin admin page. Is there a Wordpress way of doing this?
            $plugin_url = plugin_dir_url(__DIR__ );
            wp_enqueue_script("option_scripts", $plugin_url."javascript/scripts.js");

            if ($dynamic_output == FALSE) {
                wp_localize_script("option_scripts", "option_plugin_params", get_option($option_name));
            }

            //wp_enqueue_style("option_styles", $plugin_url."css/styles.css");

        } // emd of strpos

    }


    /**
     * Form_View::add_action_admin_menu()
     * 
     * Calls the Wordpress add_action() hook funciton
     * 
     * @return calls Wordpress add_action function
     */
    private function add_action_admin_menu() {

        add_action('admin_menu', array($this, 'add_options_page_method_cov'));

    }

    /**
     * Form_View::add_options_page_method_cov()
     * 
     * callback method for add_action().
     * 
     * @return calls wordpress add_options_page function
     */
    public function add_options_page_method_cov() {

        // essential.
        extract(self::$form);

        add_options_page('Affiliate Hoover', 'Affiliate Hoover', 'manage_options', $page_url, array
            ($this, 'create_html_cov'));

    }

    /**
     * Form_View::create_html_cov()
     *
     * callback method for add_options_page()
     *
     * @return echo
     */
    public function create_html_cov() {

        $this->create_table();

        // essential.
        extract(self::$form);

        //delete_option($option_name);

        $form = '<div class="wrap">';
        $form .= '<table class="widefat"><tr><td class="left">';
        $form .= screen_icon();
        $form .= "<h2>{$page_title}</h2>";
        $form .= '<p>This is the admin section for Affiliate Hoover plugin</p>';

        echo $form;

        $this->add_ind_items();

        $this->add_ind_form();


        $form = '<div id="result">';

        echo $form;

        if (isset($_POST['submitLar'])) {

            $error = array();
            $empty = 0;

            // ESSENTIAL! Do not leave this out. Needs to come first
            $form = $this->security_check($_POST);

            // SANITIZE

            $this->trim_post($form, 'siteName', TRUE);

            $this->stripslashes($form, 'siteName', TRUE);

            $this->wp_kses_new($form, 'siteName', TRUE);

            // EMPTY VALUES

            if ($this->empty_value($form) === FALSE) {
                $error[] = "Please don't leave any input values empty";
            }

            // Make sure that none of the form values are duplicates
            if ($this->duplicate_entries($form) === FALSE) {
                $error[] = "Please make sure that all feed names are unique";
            }

            if (empty($error)) {

                echo $this->update_option($form);

            } else {

                echo $this->failure_message($error);

            } // end if error

        } // end if isset submitForm

        echo '</div>';

        $site_name = array(
            "input" => "text", // input type
            "name" => "siteName", // name attribute
            "desc" => "Feed name:", // for use in input label
            "maxlength" => "200", // max attribute
            "value" => "YES", // value attribute
            "select" => FALSE // array only for the select input
                );

        $form = array(
            'method' => 'post',
            'action' => '#result',
            'enctype' => 'application/x-www-form-urlencoded',
            'description' => 'Add your new feeds here',
            'option' => TRUE,
            'submit' => "submitLar",
            'submtiTwo' => null,
            'synchronize' => null);

        if (!isset($_GET['unique_name'])) {
            $this->create_form($form, $site_name);
        }

        echo '</td> <!-- [left] -->'; // right block here for widgets

        echo '<td class="right">';
        echo '<div class="postbox">';
        echo '<div class="inside">';
        echo '<h3 class="hndle">Author details</h3>';
        echo '<p>This plugin has been created by <a href="http://andywalpole.me/">Andy Walpole</a></p>';
        echo '<p>At the moment it is optimised to work with Affiliate Window, Paid on Demand and TradeDoubler but it should be okay to upload a CSV file from any company.</p>';
        echo '<p>Please report any bugs or feature requests to...</p>';
        echo '</div><!-- end inside -->';
        echo '</div><!-- end postbox -->';
        echo '</td> <!-- [right] --></tr>';
        echo '</table> <!-- [outer] -->';
        echo '</div><!-- end of wrap div -->';
    }

    private function add_ind_form_validate(&$error, $form) {
        // validation and sanitization for form below

        // VALIDATION

        if ($this->empty_value($form, 'formTitle') === FALSE) {
            $error[] = "Please don't leave the title empty";
        }

        if ($this->empty_value($form, 'formBody') === FALSE) {
            $error[] = "Please don't leave the body empty";
        }

        if ($this->empty_value($form, 'formTitle') === FALSE) {
            // title_check
            $error[] =
                "Only include one code such as [#3#] and nothing else for the title. You can change the title once the form has been created.";
        }

        $formMinRows = false;
        $formMaxRows = false;
        if ($this->empty_value($form, 'formMinRows') === FALSE) {
            $formMinRows = TRUE;
        }

        if ($this->empty_value($form, 'formMaxRows') === FALSE) {
            $formMaxRows = TRUE;
        }

        if (($formMinRows === TRUE && $formMaxRows === FALSE) || ($formMinRows === FALSE && $formMaxRows
            === TRUE)) {
            $error[] = "Please make sure that set both a min rows number and a max rows number";
        }

        return $error;
    }

    private function add_ind_form() {

        if (isset($_GET['unique_form']) && $_GET['unique_form'] !== "") {

            // essential.
            extract(self::$form);
            echo "<h3>Form for ".urldecode($_GET['unique_form'])." feed</h3>";
            echo "<p>Once you have created the form you are happy with click on process feed at the bottom.</p>";
            echo "<p><strong>Warning! </strong>Clicking \"save changes\" will create new posts AND update all existing posts.</p>";
            echo "<p>Clicking update will only change the content of NEW posts.</p>";
            echo "<p>Clicking on \"synchronize\" will check the current feed file against published items</p>";
            echo "<p>If published items are not in the feed file it will delete them.</p>";
            echo "<p>This ensures that your content is up to date with that being released by the company</p>";
            echo "<p>Below are the codes corresponding with the file.</p>";
            echo "<p>The only mandatory fields are the title and the body.</p>";
            $post_types = get_post_types('', 'names');
            $post_array = array();
            // find all post types that are relevant to this type of content
            foreach ($post_types as $post_type) {

                if ($post_type === "page" || $post_type === "attachment" || $post_type ===
                    "revision" || $post_type === "nav_menu_item") continue;
                $post_array[] = $post_type;
            }

            $form_data = $this->select_all($_GET['unique_form']);
            if ($form_data->form_title != "") {
                $form_title = $form_data->form_title;
            } else {
                $form_title = "YES";
            }

            if ($form_data->form_title_contains != "") {
                $form_title_contains = $form_data->form_title_contains;
            } else {
                $form_title_contains = "YES";
            }

            if ($form_data->form_body != "") {
                $form_body = $form_data->form_body;
            } else {
                $form_body = "YES";
            }

            if ($form_data->form_body_contains != "") {
                $form_body_contains = $form_data->form_body_contains;
            } else {
                $form_body_contains = "YES";
            }

            if ($form_data->form_categories != "") {
                $form_categories = $form_data->form_categories;
            } else {
                $form_categories = "YES";
            }

            if ($form_data->form_tags != "") {
                $form_tags = $form_data->form_tags;
            } else {
                $form_tags = "YES";
            }

            if ($form_data->form_allow_comments != "") {

                if ($form_data->form_allow_comments === "1") {
                    $form_allow_comments = 1;
                } else {
                    $form_allow_comments = 0;
                }

            } else {
                $form_allow_comments = 0;
            }

            if ($form_data->form_allow_trackback != "") {

                if ($form_data->form_allow_trackback === "1") {
                    $form_allow_trackback = 1;
                } else {
                    $form_allow_trackback = 0;
                }

            } else {
                $form_allow_trackback = 0;
            }

            if ($form_data->min_rows != "") {
                $min_rows = $form_data->min_rows;
            } else {
                $min_rows = "YES";
            }

            if ($form_data->max_rows != "") {
                $max_rows = $form_data->max_rows;
            } else {
                $max_rows = "YES";
            }

            $form_status = NULL;
            if ($form_data->post_status != "") {

                if ($form_data->post_status === "publish") {
                    $form_status = "publish";
                } elseif ($form_data->post_status === "draft") {
                    $form_status = "draft";
                }

            } else {
                $form_status = NULL;
            }


            echo '<p>';
            foreach (unserialize($form_data->header_array_amend) as $key => $result) {

                echo '<strong>'.$key.'</strong>'."   =   ".$result.'<br />';
            }

            echo '</p>';
            echo '<div id="form-result">';
            if (isset($_POST['updateInd'])) {

                $error = array(); // ESSENTIAL! Do not leave this out. Needs to come first
                $form = $this->security_check($_POST); // SANITIZATION

                $this->sanitize($form, 'stripslashes');
                $this->add_ind_form_validate($error, $form);
                if (empty($error)) {

                    $startTime = microtime(true);
                    $this->update_ind_form($form, urldecode($_GET['unique_form']));
                    $this->create_post_items(urldecode($_GET['unique_form']), FALSE);
                    $endTime = microtime(true);
                    $elapsed = $endTime - $startTime;
                    var_dump("Execution time : $elapsed seconds");
                } else {

                    echo $this->failure_message($error);
                } // end if error

            }

            if (isset($_POST['submitForm'])) {

                $error = array(); // ESSENTIAL! Do not leave this out. Needs to come first
                $form = $this->security_check($_POST); // SANITIZATION

                $this->sanitize($form, 'stripslashes');
                $this->add_ind_form_validate($error, $form);
                if (empty($error)) {

                    if ($this->update_ind_form($form, urldecode($_GET['unique_form']))) {

                        $startTime = microtime(true);
                        $this->create_post_items(urldecode($_GET['unique_form']));
                        $endTime = microtime(true);
                        $elapsed = $endTime - $startTime;
                        var_dump("Execution time : $elapsed seconds");
                    }

                } else {

                    echo $this->failure_message($error);
                } // end if error

            }

            //synchronize

            if (isset($_POST['synchronize'])) {

                $error = array(); // ESSENTIAL! Do not leave this out. Needs to come first
                $form = $this->security_check($_POST); // SANITIZATION

                $this->sanitize($form, 'stripslashes');
                $this->add_ind_form_validate($error, $form);
                if (empty($error)) {

                    //var_dump($form[$option_name]['formTitle']);

                    $this->synchronize_feeds($form[$option_name]['formTitle']);
                } else {

                    echo $this->failure_message($error);
                }

            }

            echo '</div>';
            $form_title = array(
                "input" => "text", // input type
                "name" => "formTitle", // name attribute
                "desc" =>
                    "<strong>Post title</strong>: <br />Do not add anything other than the above codes for a title", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => $form_title, // value attribute
                "select" => FALSE // array only for the select input
                    );
            $form_title_contains = array(
                "input" => "text", // input type
                "name" => "TitleContains", // name attribute
                "desc" => "Title contains keywords (comma seperated list): ", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => $form_title_contains, // value attribute
                "select" => FALSE // array only for the select input
                    );
            $form_title_not_contains = array(
                "input" => "text", // input type
                "name" => "TitleNotContains", // name attribute
                "desc" => "Title does not contain keywords (comma seperated list): ", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => "YES", // value attribute
                "select" => FALSE // array only for the select input
                    );
            $form_body = array(
                "input" => "textarea", // input type
                "name" => "formBody", // name attribute
                "desc" =>
                    '<strong>Post body.</strong> You can use HTML in here. Examples:<br>To place an image: <br>'.
                    htmlspecialchars("<img src=\"[#7#]\">")."<br>To create a link:<br>".
                    htmlspecialchars("<a href=\"[#5#]\">[#1#]</a>"), // for use in input label
                "maxlength" => "250", // max attribute
                "value" => $form_body, // value attribute
                "select" => FALSE // array only for the select input
                    );
            $form_body_contains = array(
                "input" => "text", // input type
                "name" => "BodyContains", // name attribute
                "desc" => "Body contains keywords (comma seperated list): ", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => $form_body_contains, // value attribute
                "select" => FALSE // array only for the select input
                    );
            $form_body_not_contains = array(
                "input" => "text", // input type
                "name" => "BodyNotContains", // name attribute
                "desc" => "Body does not contain keywords (comma seperated list): ", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => "YES", // value attribute
                "select" => FALSE // array only for the select input
                    );
            $form_categories = array(
                "input" => "text", // input type
                "name" => "formCategories", // name attribute
                "desc" =>
                    "<strong>Post categories.</strong> Can be either text or code. All values must be separated with a comma:", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => $form_categories, // value attribute
                "select" => FALSE // array only for the select inpu
                    );
            $form_categories_contains = array(
                "input" => "text", // input type
                "name" => "CategoryContains", // name attribute
                "desc" => "Categories contains keywords (comma seperated list): ", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => "YES", // value attribute
                "select" => FALSE // array only for the select input
                    );
            $form_categories_not_contains = array(
                "input" => "text", // input type
                "name" => "CategoryNotContains", // name attribute
                "desc" => "Categories does not contain keywords (comma seperated list): ", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => "YES", // value attribute
                "select" => FALSE // array only for the select input
                    );
            $form_tags = array(
                "input" => "text", // input type
                "name" => "formTags", // name attribute
                "desc" =>
                    "<strong>Post tags</strong>. Can be either text or code. All values must be separated with a comma:", // for use in input label
                "maxlength" => "250", // max attribute
                "value" => $form_tags, // value attribute
                "select" => FALSE // array only for the select inpu
                    );
            $form_allow_comments = array(
                "input" => "checkbox", // input type
                "name" => "formAllowComments", // name attribute
                "desc" => "<strong>Allow comments on this post?</strong>", // for use in input label
                "maxlength" => $form_allow_comments, // max attribute
                "value" => 1, // value attribute
                "select" => 1 // array only for the select inpu
                    );
            $form_allow_trackback = array(
                "input" => "checkbox", // input type
                "name" => "formAllowTrackbacks", // name attribute
                "desc" => "<strong>Allow trackbacks and pingbacks on this post?</strong>", // for use in input label
                "maxlength" => $form_allow_trackback, // max attribute
                "value" => 1, // value attribute
                "select" => 1 // array only for the select inpu
                    );
            $post_type = array(
                "input" => "select", // input type
                "name" => "formPostType", // name attribute
                "desc" => "What post type should this feed be allocated to?", // for use in input label
                "maxlength" => null, // max attribute
                "value" => null, // value attribute
                "select" => $post_array // array only for the select inpu
                    );
            $min_rows = array(
                "input" => "text", // input type
                "name" => "formMinRows", // name attribute
                "desc" => "Start processing on which row? (Out of a total of $form_data->num_rows entries)", // for use in input label
                "maxlength" => "11", // max attribute
                "value" => $min_rows, // value attribute
                "select" => FALSE // array only for the select inpu
                    );
            $max_rows = array(
                "input" => "text", // input type
                "name" => "formMaxRows", // name attribute
                "desc" => "End processing on which row? (Out of a total of $form_data->num_rows entries)", // for use in input label
                "maxlength" => "11", // max attribute
                "value" => $max_rows, // value attribute
                "select" => FALSE // array only for the select inpu
                    );
            $post_status = array(
                "input" => "select", // input type
                "name" => "formPostStatus", // name attribute
                "desc" =>
                    "<strong>Should the post be held back as a draft or be immediately published?</strong>", // for use in input label
                "maxlength" => $form_status, // max attribute
                "value" => null, // value attribute
                "select" => array('draft', 'publish') // array only for the select inpu
                    );
            $form = array(
                'method' => 'post',
                'action' => '#message',
                'enctype' => 'multipart/form-data',
                'description' => 'Create your post form here',
                'option' => FALSE,
                'submit' => 'submitForm',
                'submtiTwo' => 'updateInd',
                'synchronize' => 'synchronize');
            $this->create_form($form, $form_title, $form_title_contains, $form_body, $form_body_contains,
                $form_categories, $form_tags, $form_allow_comments, $form_allow_trackback, $min_rows,
                $max_rows, $post_status);
        }

    }


    private function add_ind_items() {

        // essential.
        extract(self::$form);
        if (isset($_GET['unique_name']) && $_GET['unique_name'] !== "") {

            if ($this->check_table(urldecode($_GET['unique_name'])) === NULL) {

                $feed_url_value = "YES";
            } else {

                $item = $this->select_all(urldecode($_GET['unique_name']));
                if ($item->URL == "") {
                    $feed_url_value = "YES";
                } else {
                    $feed_url_value = $item->URL;
                }

            }

            // CHANGE THIS - only need cron data and
            $form_data = $this->select_all($_GET['unique_name']);
            $form_cron = NULL;
            if ($form_data !== NULL) {

                if ($form_data->form_cron != "") {

                    if ($form_data->form_cron === "daily") {
                        $form_cron = "daily";
                    } else
                        if ($form_data->form_cron === "twicedaily") {
                            $form_cron = "twicedaily";
                        }

                } else {
                    $form_cron = NULL;
                }

            } else {

                $form_cron = NULL;
            }

            $feed_file_value = FALSE;
            echo "<h3>Feed details for ".urldecode($_GET['unique_name'])."</h3>";
            echo '<div id="ind-result">';
            if (isset($_POST['submitInd'])) {

                $error = array();
                $empty = 0;
                $feed_file_empty = FALSE;
                $feed_url_empty = FALSE;
                // ESSENTIAL! Do not leave this out. Needs to come first
                $form = $this->security_check($_POST); // SANITISATION

                $this->trim_post($form, 'feedURL', TRUE);
                $this->stripslashes($form, 'feedURL', TRUE); // VALIDATION

                if ($this->check_file_empty($_FILES[$option_name], 'feedFile') === FALSE) {
                    $empty += 1;
                    $feed_file_empty = TRUE;
                }

                /*

                if ($this->empty_value($form, 'formCron') === FALSE && $feed_file_empty === TRUE) {
                $error[] = "Please pick whether you want the cron to run daily or twice daily";
                }

                */

                if ($this->empty_value($form, 'feedURL') === FALSE) {
                    $empty += 1;
                    $feed_url_empty = TRUE;
                }

                /*

                if ($this->empty_value($form, 'formCron') !== FALSE && $feed_url_empty === TRUE) {
                $error[] =
                "If you manually import a feed then it is no possible to update it automatically";
                }

                */

                if ($empty === 0) {
                    $error[] = "Opps! You forgot to add a URL or file";
                }

                if ($empty === 2) {
                    $error[] = "Either add a URL or a file but not both";
                }

                if ($this->validate_url($form, 'feedURL') === FALSE) {
                    $error[] = "Are you sure the URL address you entered is correctly formatted?";
                } elseif ($this->validate_remote_url($form, 'feedURL') === FALSE) {
                    $error[] =
                        "Are you sure you typed the URL address correctly? It doesn't appear to be correct. There is no remote response. Click save changes again to make sure";
                }

                if ($this->check_file_ext($_FILES[$option_name], 'feedFile') === FALSE) {
                    $error[] = "Only upload XML or CSV files";
                }

                if ($this->check_file_error($_FILES[$option_name], 'feedFile') === FALSE) {
                    $error[] = "Sorry the maximum file upload size is 2MB";
                }

                if (empty($error)) {

                    echo $this->update_record($form);
                } else {

                    echo $this->failure_message($error);
                } // end if error

            } // end if isset($_POST['submitInd'])

            echo '</div><!-- end "ind-result" -->';
            if (isset($item)) {

                echo "<p>File can be found here: ".'<strong>'.AH_FEEDS_DIR.$item->fileName.
                    '</strong></p>';
            } else {

                echo '<p>You have not uploaded a flle for '.urldecode($_GET['unique_name']).
                    ' yet</p>';
            }

            $feed_url = array(
                "input" => "text", // input type
                "name" => "feedURL", // name attribute
                "desc" => "The URL of the feed", // for use in input label
                "maxlength" => "1000", // max attribute
                "value" => $feed_url_value, // value attribute
                "select" => $_GET['unique_name'] // array only for the select input
                    );
            $feed_file = array(
                "input" => "file", // input type
                "name" => "feedFile", // name attribute
                "desc" => "Add file here", // for use in input label
                "maxlength" => false, // max attribute
                "value" => $feed_file_value, // value attribute
                "select" => FALSE // array only for the select input
                    );
            $cron_run = array(
                "input" => "select", // input type
                "name" => "formCron", // name attribute
                "desc" =>
                    "If the feed comes from a URL when should this feed be automatically updated?", // for use in input label
                "maxlength" => $form_cron, // max attribute
                "value" => null, // value attribute
                "select" => array("daily", "twicedaily") // array only for the select inpu
                    );
            $form = array(
                'method' => 'post',
                'action' => '#outer',
                'enctype' => 'multipart/form-data',
                'description' => 'Individual feed details',
                'option' => FALSE,
                'submit' => 'submitInd',
                'submtiTwo' => null,
                'synchronize' => null);
            $this->create_form($form, $feed_url, $feed_file);
        } // end if isset($_GET['unique_name'])

    }

    private function instructions() {

        if (ini_get('safe_mode')) {

            echo "Your server has safe mode on. This will restrict your use of this module because it often requires more than 30 seconds to parse a feed";
        }

    }

} // end class

$first_form = array(
    'option_name' => 'affiliate_hoover_plugin_options', // has to be alphanumeric and underscores only
    'page_title' => 'Affiliate Hoover Plugin Admin', // Main page title
    'page_url' => 'affiliate-hoover-plugin-admin', // URL
    'dynamic_output' => TRUE); // Should the form be generated on more input

new \OptionView\Form_View($first_form);
/*
http://datafeed.api.productserve.com/datafeed/download/apikey/1a5de2d28b6bbc860d98b3d69bc69aec/mid/736/columns/aw_deep_link,aw_image_url,aw_product_id,aw_thumb_url,brand_id,brand_name,category_id,category_name,commission_amount,commission_group,condition,currency,delivery_cost,delivery_time,description,display_price,ean,in_stock,is_for_sale,is_hotpick,isbn,language,merchant_category,merchant_deep_link,merchant_id,merchant_image_url,merchant_name,merchant_product_id,merchant_thumb_url,model_number,mpn,parent_product_id,pre_order,product_name,product_type,promotional_text,rrp_price,search_price,specifications,stock_quantity,store_price,upc,valid_from,valid_to,warranty,web_offer/format/csv/compression/zip/
*/
