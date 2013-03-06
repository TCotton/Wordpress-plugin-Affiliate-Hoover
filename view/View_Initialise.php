<?php namespace view;

/**
 * Tracking_Controller
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
 * get_option()
 * http://codex.wordpress.org/Function_Reference/get_option
 * 
 * 
 * 
 */
class View_Initialise extends \model\Database {

    protected static $form_builder;
    protected static $check;
    protected static $success;
    protected static $files;
    protected static $secure = FALSE;
    protected static $option = FALSE;
    protected static $read_write;

    function __construct() {

        parent::__construct();

        add_action('admin_init', array(&$this, 'meta_boxes'));

        $this->add_action_admin_menu();

        if (!(self::$form_builder instanceof \view\Form_Builder)) {
            self::$form_builder = new \view\Form_Builder();
        }

        if (!(self::$check instanceof \controller\Validation_Sanitisation)) {
            self::$check = new \controller\Validation_Sanitisation();
        }

        if (!(self::$success instanceof \controller\Validation_Sanitisation_Success)) {
            self::$success = new \controller\Validation_Sanitisation_Success();
        }

        if (!(self::$files instanceof \model\Handle_Files)) {
            self::$files = new \model\Handle_Files();
        }

        if (!(self::$read_write instanceof \model\Write_Read_Files)) {
            self::$read_write = new \model\Write_Read_Files();
        }

        // create static variable if HTTPS is on
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] ==
            443) {

            static::$secure = TRUE;

        }

        self::$option = get_option('ah_tracking');

        if (self::$option === FALSE || self::$option === '') {

            add_action('init', array(&$this, 'tracking_scripts'), '1');

        }

        add_action('wp_ajax_nopriv_ah_update', array(&$this, 'db_update_tracking'), '1');
        add_action('wp_ajax_ah_update', array(&$this, 'db_update_tracking'), '1');


    } // end __construct


    public function tracking_scripts() {

        $plugin_url = plugin_dir_url(__DIR__ );

        wp_enqueue_script('tracking_scripts', $plugin_url.'javascript/tracking.js', array('jquery'),
            '0.1', TRUE);

        $protocol = static::$secure === TRUE ? 'https://' : 'http://';

        $params = array('ajaxurl' => admin_url('admin-ajax.php', $protocol), 'my_nonce' =>
                wp_create_nonce('myajax-nonce'));

        wp_localize_script('tracking_scripts', 'ah_tracking_scripts', $params);

    }


    /**
     * Form_View::meta_boxes()
     * 
     * Addes meta boxes to appropiate content types
     * 
     * @return calls add_meta_box() hook
     * 
     */
    public function meta_boxes() {

        $post_types = $this->db_find_post_types();

        if ($post_types) {

            $option = get_option('ah_tracking');

            foreach ($post_types as $key => $value) {

                $post_types = implode('', array_values($value));

                if ($post_types == '') continue;

                add_meta_box('feed_details', 'Affiliate Hoover', array(&$this, 'feed_details'), $post_types,
                    'side', 'low');

                if ($option != '1') {

                    add_meta_box('tracking_details', 'Affiliate Hoover Tracking Details', array(&$this,
                            'tracking_details'), $post_types, 'side', 'low');

                }

            } // end foreach

        } // end if($post_types){

    }


    /**
     * Form_View::feed_details()
     * 
     * Adds feed name to post admin page
     * 
     * @return string
     * 
     */

    public function feed_details() {

        $feed_name = $this->db_find_feed_name_from_post_id(get_the_ID());

        if (is_null($feed_name)) {

            echo '<p>This post was not created from an Affiliate Hoover feed</p>';

        } else {

            echo 'This post was created from the '.$feed_name.' feed file';

        }

    }


    /**
     * Form_View::tracking_details()
     * 
     * Adds tracking data to post admin page
     * 
     * @return string
     * 
     */

    public function tracking_details() {

        $track_results = $this->db_find_tracking_details_from_post_id(get_the_ID());

        if (empty($track_results)) {

            echo '<p>No tracking details available for this post.</p>';

        } else {

            echo '<p>The affiliate link on this page was clicked on the following dates</p>';

            foreach ($track_results as $key => $value) {

                $date = date_create($value['date']);
                echo date_format($date, 'l jS \of F Y').AH_BR;

            } // end foreach

        } // if emptu

    }


    /**
     * Form_View::add_action_admin_menu()
     * 
     * Calls the Wordpress add_action() hook funciton
     * 
     * @return calls Wordpress add_action function
     */
    protected function add_action_admin_menu() {

        add_action('admin_menu', array(&$this, 'add_options_page_method_cov'), '1');

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

        //delete_option($option_name);

        add_options_page('Affiliate Hoover', 'Affiliate Hoover', 'manage_options', $page_url, array
            (&$this, 'create_html_cov'));
    }


    /**
     * Form_View::create_html_cov()
     *
     * callback method for add_options_page()
     *
     * @return echo
     */
    public function create_html_cov() {


        //$init::form()_builder->create_table();

        // essential.
        extract(self::$form);

        //delete_option($option_name);

        $form = '<h2 class="nav-tab-wrapper">';
        $form .= '<a href="options-general.php?page='.$page_url.'" class="nav-tab">Main Page</a>';
        $form .= '<a href="options-general.php?page='.$page_url.
            '&feed-list=total" class="nav-tab">Feed lists</a>';
        $form .= '<a href="options-general.php?page='.$page_url.
            '&instructions=here" class="nav-tab">Instructions</a>';
        $form .= '<a href="options-general.php?page='.$page_url.
            '&tracking=here" class="nav-tab">Tracking</a>';
        $form .= '<a href="options-general.php?page='.$page_url.
            '&reset=here" class="nav-tab">Reset</a>';
        $form .= '<a href="options-general.php?page='.$page_url.
            '&log=here" class="nav-tab">Log</a>';
        $form .= '<a href="options-general.php?page='.$page_url.
            '&changelog=here" class="nav-tab">Changelog</a>';

        $form .= '<div class="wrap">';
        $form .= '<table class="widefat"><tr><td class="left">';
        $form .= "<h2>{$page_title}</h2>";
        $form .= '<p>This is the admin section for Affiliate Hoover plugin</p>';
        $form .= '</h2>';

        echo $form;

        switch ($_GET) {

                // This displays the appropiate section on the appropiate page

            case ($_GET['page'] == $page_url && !isset($_GET['feed-list']) && !isset($_GET['instructions']) &&
                    !isset($_GET['reset']) && !isset($_GET['changelog']) && !isset($_GET['log']) &&
                    !isset($_GET['tracking']) ? TRUE : FALSE):

                $main_form = new \View\Main_Form();
                $main_form->main_form_facade();

                break;
            case (isset($_GET['feed-list']) && $_GET['feed-list'] == 'total' ? TRUE : FALSE):

                $upload_form = new \View\Upload_Form();
                $upload_form->upload_form_facade();

                $feed_form = new \view\Feed_Form();
                $feed_form->feed_form_facade();

                if (!isset($_GET['unique_form']) && !isset($_GET['unique_name'])) {

                    echo $this->list_feeds();

                }
                break;
            case (isset($_GET['instructions']) && $_GET['instructions'] == 'here' ? TRUE : FALSE):

                echo ah_instructions();

                break;
            case (isset($_GET['reset']) && $_GET['reset'] == 'here' ? TRUE : FALSE):

                $reset_form = new \View\Reset_Form();
                $reset_form->reset_form_facade();

                break;
            case (isset($_GET['changelog']) && $_GET['changelog'] == 'here' ? TRUE : FALSE):

                echo ah_changelog();

                break;
            case (isset($_GET['log']) && $_GET['log'] == 'here' ? TRUE : FALSE):

                echo \view\Log::log();

                break;
            case (isset($_GET['tracking']) && $_GET['tracking'] == 'here' ? TRUE : FALSE):

                $tracking_form = new \view\Tracking_Form();
                echo $tracking_form->tracking_form_facade();

                break;

        } // end switch


        $form = '</td> <!-- [left] -->'; // right block here for widgets
        $form .= '<td class="right">';
        $form .= ah_admin_sidebar();
        $form .= '</td> <!-- [right] --></tr>';
        $form .= '</table> <!-- [outer] -->';
        $form .= '</div><!-- end of wrap div -->';
        echo $form;

    } // end create_html_cov()


    protected function list_feeds() {

        // essential.
        extract(self::$form);

        $feed_names = $this->db_get_all_feed_names();

        $form = '<h3>List of total feeds</h3>';

        if (empty($feed_names)) {

            $form .= '<p>You have not created any feeds yet</p>';

        } else {

            $form .= '<ul>';

            foreach ($feed_names as $result) {
                $form .= "<li><a href='?page=".$page_url."&feed-list=total&unique_name=".$result->
                    name."'>".$result->name.'</a></li>';
            }

            $form .= '</ul>';

        } // end if statement

        return $form;

    } // end list_feeds()


}

new \view\View_Initialise();
