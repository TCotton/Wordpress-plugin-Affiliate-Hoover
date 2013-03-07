<?php namespace view;

/**
 * Feed_Form
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

class Feed_Form extends \view\View_Initialise {

    protected static $synchronize;
    protected static $update;
    protected static $create;
    protected static $processing;

    /**
     * Feed_Form::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();
        
        if (!(self::$processing instanceof \controller\Feed_Form_Processing)) {
            self::$processing = new \controller\Feed_Form_Processing();
        }
        
        if (!(self::$create instanceof \controller\Create_Individual_Feeds)) {
            self::$create = new \controller\Create_Individual_Feeds();
        }
        
        if (!(self::$update instanceof \controller\Update_Individual_Feeds)) {
            self::$update = new \controller\Update_Individual_Feeds();
        }
        
        if (!(self::$synchronize instanceof \model\Synchronize_Feeds)) {
            self:: $synchronize = new \model\Synchronize_Feeds();
        }

    } // end construct

    /**
     * Feed_Form::feed_form()
     * 
     * Creates the main feed form with relevant calls to sanitisation and validation
     * 
     * @return
     */
    protected function feed_form() {

        if (isset($_GET['unique_form']) && $_GET['unique_form'] != '') {

            // essential.
            extract(self::$form);
            echo '<h3>Form for '.$_GET['unique_form'].' feed</h3>';
            echo '<p>Once you have created the form you are happy with click on process feed at the bottom.</p>';
            echo '<p><strong>Warning! </strong>Clicking \'Create and update\' will create new posts AND update all existing posts.</p>';
            echo '<p>Clicking \'Create only\' will only change the content of NEW posts.</p>';
            echo '<p>Clicking on \'Synchronize\' will check the current feed file against published items</p>';
            echo '<p>If published items are not in the feed file it will delete them.</p>';
            echo '<p>Below are the codes corresponding with the file.</p>';
            echo '<p>The only mandatory fields are the title and the body.</p>';

            $post_types = get_post_types('', 'names');

            $post_array = array();
            // find all post types that are relevant to this type of content
            foreach ($post_types as $post_type) {

                if ($post_type == 'attachment' || $post_type == 'revision' || $post_type ==
                    'nav_menu_item') continue;
                $post_array[] = $post_type;

            }

            $taxonomies = get_taxonomies('', 'names');

            $taxonomy_array = array();

            foreach ($taxonomies as $taxonomy) {

                if ($taxonomy == 'post_tag') continue;
                if ($taxonomy == 'link_category') continue;
                if ($taxonomy == 'post_format') continue;
                if ($taxonomy == 'nav_menu') continue;

                $taxonomy_array[] = $taxonomy;

            }

            $cat_names = array();

            foreach ($taxonomy_array as $tax) {

                $args = array('hide_empty' => 0, 'taxonomy' => $tax);

                $categories = get_categories($args);

                foreach ($categories as $result) {

                    if ($result->name == 'Uncategorized') continue;

                    $cat_names[] = $result->name;

                }

            }

            $form_data = $this->db_select_all($_GET['unique_form']);

            if ($form_data !== NULL) {

                $serialized = unserialize($form_data->header_array_amend);

                echo '<p>';
                foreach ($serialized as $key => $result) {
                    echo '<strong>'.$key.'</strong>'.'   =   '.$result.'<br />';
                }

                echo '</p>';

                echo '<div id="form-result">';
                if (isset($_POST['updateInd'])) {

                    // ESSENTIAL! Do not leave this out. Needs to come first
                    $form = self::$processing->feed_form_processing_sanitisation($_POST);
                    $error = self::$processing->feed_form_processing_validation($form);

                    if (empty($error)) {

                        $startTime = microtime(TRUE);
                        self::$update->update_individual_feeds_facade($form, $_GET['unique_form']);
                        self::$create->create_individual_feeds_facade($_GET['unique_form']);
                        $endTime = microtime(TRUE);
                        $elapsed = $endTime - $startTime;

                        echo "<span style=\"color:red\">Execution time : ".round($elapsed, 2).
                            " seconds</span>";

                    } else {

                        echo self::$check->failure_message_interface($error);

                    } // end if error

                }

                if (isset($_POST['submitForm'])) {

                    // ESSENTIAL! Do not leave this out. Needs to come first

                    $form = self::$processing->feed_form_processing_sanitisation($_POST);
                    $error = self::$processing->feed_form_processing_validation($form);

                    if (empty($error)) {

                        //create_indiviual_feeds_facade

                        $startTime = microtime(TRUE);

                        self::$update->update_individual_feeds_facade($form, $_GET['unique_form']);
                        self::$create->create_individual_feeds_facade($_GET['unique_form'], FALSE);

                        $endTime = microtime(TRUE);
                        $elapsed = $endTime - $startTime;
                        echo "<span style=\"color:red\">Execution time : ".round($elapsed, 2).
                            " seconds</span>";

                    } else {

                        echo self::$check->failure_message_interface($error);

                    } // end if error

                }

                //synchronize

                if (isset($_POST['synchronize'])) {

                    $form = self::$processing->feed_form_processing_sanitisation($_POST);
                    $error = self::$processing->feed_form_processing_validation($form);

                    if (empty($error)) {

                        self::$synchronize->synchronise_feeds_facade($form[$option_name]['formTitle']);

                    } else {

                        echo self::$check->failure_message_interface($error);

                    }

                }

                echo '</div>';

                if ($form_data->form_title != '') {
                    $form_title = $form_data->form_title;
                } else {
                    $form_title = 'YES';
                }

                $form_title = array(
                    'input' => 'text', // input type
                    'name' => 'formTitle', // name attribute
                    'desc' =>
                        '<strong>Post title</strong>: <br />Do not add anything other than the above codes for a title', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => $form_title, // value attribute
                    'select' => FALSE // array only for the select input
                        );

                if ($form_data->form_title_contains != '') {
                    $form_title_contains = $form_data->form_title_contains;
                } else {
                    $form_title_contains = 'YES';
                }

                $form_title_contains = array(
                    'input' => 'text', // input type
                    'name' => 'TitleContains', // name attribute
                    'desc' => 'Title contains keywords (comma seperated list): ', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => $form_title_contains, // value attribute
                    'select' => FALSE // array only for the select input
                        );
                $form_title_not_contains = array(
                    'input' => 'text', // input type
                    'name' => 'TitleNotContains', // name attribute
                    'desc' => 'Title does not contain keywords (comma seperated list): ', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => 'YES', // value attribute
                    'select' => FALSE // array only for the select input
                        );

                if ($form_data->form_body != '') {
                    $form_body = $form_data->form_body;
                } else {
                    $form_body = 'YES';
                }

                $form_body = array(
                    'input' => 'textarea', // input type
                    'name' => 'formBody', // name attribute
                    'desc' =>
                        '<strong>Post body.</strong> You can use HTML in here. Examples:<br>To place an image: <br>'.
                        htmlspecialchars("<img src=\"[#7#]\">")."<br>To create a link:<br>".
                        htmlspecialchars("<a href=\"[#5#]\">[#1#]</a>").
                        '<br>If you are going to include internal links you must write the full URL, ie '.
                        htmlspecialchars("http://www.example.com/category/page-here"), // for use in input label
                    'maxlength' => NULL, // max attribute
                    'value' => $form_body, // value attribute
                    'select' => FALSE // array only for the select input
                        );

                if ($form_data->form_body_nofollow != '') {

                    if ($form_data->form_body_nofollow == '1') {
                        $form_body_nofollow = 1;
                    } else {
                        $form_body_nofollow = 0;
                    }

                } else {
                    $form_body_nofollow = 0;
                }

                $form_nofollow = array(
                    'input' => 'checkbox', // input type
                    'name' => 'formNoFollow', // name attribute
                    'desc' => 'Turn links in the body text into nofollow?', // for use in input label
                    'maxlength' => $form_body_nofollow, // max attribute
                    'value' => 1, // value attribute
                    'select' => 1 // array only for the select inpu
                        );

                if ($form_data->form_body_contains != '') {
                    $form_body_contains = $form_data->form_body_contains;
                } else {
                    $form_body_contains = 'YES';
                }

                $form_body_contains = array(
                    'input' => 'text', // input type
                    'name' => 'BodyContains', // name attribute
                    'desc' => 'Body contains keywords (comma seperated list): ', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => $form_body_contains, // value attribute
                    'select' => FALSE // array only for the select input
                        );

                $form_body_not_contains = array(
                    'input' => 'text', // input type
                    'name' => 'BodyNotContains', // name attribute
                    'desc' => 'Body does not contain keywords (comma seperated list): ', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => 'YES', // value attribute
                    'select' => FALSE // array only for the select input
                        );

                if ($form_data->form_vocabulary != '' && in_array($form_data->form_vocabulary, $taxonomy_array)) {
                    $form_vocab = $form_data->form_vocabulary;
                } else {
                    $form_vocab = NULL;
                }

                $form_taxonomy = array(
                    'input' => 'select', // input type
                    'name' => 'formTaxonomy', // name attribute
                    'desc' =>
                        '<strong>Taxonomies.</strong> Pick a taxonomy to use (Leave blank if unsure. This is only needed for custom taxonomies)', // for use in input label
                    'maxlength' => $form_vocab, // max attribute
                    'value' => NULL, // value attribute
                    'select' => $taxonomy_array // array only for the select inpu
                        );

                if ($form_data->form_categories != '') {
                    $form_categories = $form_data->form_categories;
                } else {
                    $form_categories = 'YES';
                }

                $form_categories = array(
                    'input' => 'text', // input type
                    'name' => 'formCategories', // name attribute
                    'desc' =>
                        '<strong>Post categories.</strong> Can be either text or code. All values must be separated with a comma:', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => $form_categories, // value attribute
                    'select' => FALSE // array only for the select inpu
                        );

                if ($form_data->form_categories_parent != '' && in_array($form_data->
                    form_categories_parent, $cat_names)) {
                    $form_categories_parent = $form_data->form_categories_parent;
                } else {
                    $form_categories_parent = NULL;
                }

                $form_categories_parent = array(
                    'input' => 'select', // input type
                    'name' => 'formCatParents', // name attribute
                    'desc' => 'Pick a parent category (Lists all categories from all taxonomies)', // for use in input label
                    'maxlength' => $form_categories_parent, // max attribute
                    'value' => NULL, // value attribute
                    'select' => $cat_names // array only for the select inpu
                        );

                $form_categories_contains = array(
                    'input' => 'text', // input type
                    'name' => 'CategoryContains', // name attribute
                    'desc' => 'Categories contains keywords (comma seperated list): ', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => 'YES', // value attribute
                    'select' => FALSE // array only for the select input
                        );

                $form_categories_not_contains = array(
                    'input' => 'text', // input type
                    'name' => 'CategoryNotContains', // name attribute
                    'desc' => 'Categories does not contain keywords (comma seperated list): ', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => 'YES', // value attribute
                    'select' => FALSE // array only for the select input
                        );

                if ($form_data->form_tags != '') {
                    $form_tags = $form_data->form_tags;
                } else {
                    $form_tags = 'YES';
                }

                $form_tags = array(
                    'input' => 'text', // input type
                    'name' => 'formTags', // name attribute
                    'desc' =>
                        '<strong>Post tags</strong>. Can be either text or code. All values must be separated with a comma:', // for use in input label
                    'maxlength' => '250', // max attribute
                    'value' => $form_tags, // value attribute
                    'select' => FALSE // array only for the select inpu
                        );

                if ($form_data->form_allow_comments != '') {

                    if ($form_data->form_allow_comments == '1') {
                        $form_allow_comments = 1;
                    } else {
                        $form_allow_comments = 0;
                    }

                } else {
                    $form_allow_comments = 0;
                }

                $form_allow_comments = array(
                    'input' => 'checkbox', // input type
                    'name' => 'formAllowComments', // name attribute
                    'desc' => '<strong>Allow comments on this post?</strong>', // for use in input label
                    'maxlength' => $form_allow_comments, // max attribute
                    'value' => 1, // value attribute
                    'select' => 1 // array only for the select inpu
                        );

                if ($form_data->form_allow_trackback != '') {

                    if ($form_data->form_allow_trackback == '1') {
                        $form_allow_trackback = 1;
                    } else {
                        $form_allow_trackback = 0;
                    }

                } else {
                    $form_allow_trackback = 0;
                }

                $form_allow_trackback = array(
                    'input' => 'checkbox', // input type
                    'name' => 'formAllowTrackbacks', // name attribute
                    'desc' => '<strong>Allow trackbacks and pingbacks on this post?</strong>', // for use in input label
                    'maxlength' => $form_allow_trackback, // max attribute
                    'value' => 1, // value attribute
                    'select' => 1 // array only for the select inpu
                        );

                if ($form_data->post_type != '') {
                    $form_types = $form_data->post_type;
                } else {
                    $form_types = 'YES';
                }

                $post_type = array(
                    'input' => 'select', // input type
                    'name' => 'formPostType', // name attribute
                    'desc' =>
                        '<strong>What post type should this feed be allocated to? (Be careful with what you pick here)</strong>', // for use in input label
                    'maxlength' => $form_types, // max attribute
                    'value' => NULL, // value attribute
                    'select' => $post_array // array only for the select inpu
                        );

                if ($form_data->min_rows != '') {
                    $mi_rows = $form_data->min_rows;
                } else {
                    $mi_rows = 'YES';
                }

                $min_rows = array(
                    'input' => 'text', // input type
                    'name' => 'formMinRows', // name attribute
                    'desc' => "Start processing on which row? (Out of a total of $form_data->num_rows entries)", // for use in input label
                    'maxlength' => '11', // max attribute
                    'value' => $mi_rows, // value attribute
                    'select' => FALSE // array only for the select inpu
                        );

                if ($form_data->max_rows != '') {
                    $ma_rows = $form_data->max_rows;
                } else {
                    $ma_rows = 'YES';
                }

                $max_rows = array(
                    'input' => 'text', // input type
                    'name' => 'formMaxRows', // name attribute
                    'desc' => "End processing on which row? (Out of a total of $form_data->num_rows entries)", // for use in input label
                    'maxlength' => '11', // max attribute
                    'value' => $ma_rows, // value attribute
                    'select' => FALSE // array only for the select inpu
                        );

                $form_status = NULL;

                if ($form_data->post_status != '') {

                    if ($form_data->post_status == 'publish') {
                        $form_status = 'publish';
                    } elseif ($form_data->post_status == 'draft') {
                        $form_status = 'draft';
                    }

                } else {
                    $form_status = NULL;
                }

                $post_status = array(
                    'input' => 'select', // input type
                    'name' => 'formPostStatus', // name attribute
                    'desc' =>
                        '<strong>Should the post be held back as a draft or be immediately published?</strong>', // for use in input label
                    'maxlength' => $form_status, // max attribute
                    'value' => NULL, // value attribute
                    'select' => array('draft', 'publish') // array only for the select inpu
                        );

                if ($form_data->form_staggered != '') {

                    if ($form_data->form_staggered == '1') {
                        $form_staggered = 1;
                    } else {
                        $form_staggered = 0;
                    }

                } else {
                    $form_staggered = 0;
                }

                $form_stagger = array(
                    'input' => 'checkbox', // input type
                    'name' => 'formStagger', // name attribute
                    'desc' =>
                        'Stagger the publication of posts so that the time is set for 5 posts publshed every 10 minutes (Only for newly created posts)', // for use in input label
                    'maxlength' => $form_staggered, // max attribute
                    'value' => 1, // value attribute
                    'select' => 1 // array only for the select inpu
                        );
                $form = array(
                    'method' => 'post',
                    'action' => '#wpwrap',
                    'enctype' => 'multipart/form-data',
                    'description' => '<h3>Create your post form here</h3>',
                    'option' => FALSE,
                    'submit' => 'submitForm',
                    'submtiTwo' => 'updateInd',
                    'synchronize' => 'synchronize',
                    'tracking' => NULL);

                self::$form_builder->create_form($form, $form_title, $form_title_contains, $form_body,
                    $form_nofollow, $form_body_contains, $form_categories, $form_taxonomy, $form_categories_parent,
                    $form_tags, $form_allow_comments, $form_allow_trackback, $min_rows, $max_rows, $post_type,
                    $post_status, $form_stagger);

            } else {

                wp_die('Opps, nothing here');

            }

            // end if($form_data == NULL) {
        }

    }


    /**
     * Feed_Form::feed_form_facade()
     * 
     * @return
     */
    public function feed_form_facade() {

        return $this->feed_form();

    }

}
