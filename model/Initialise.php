<?php namespace model;

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
 * wp_die():
 * http://codex.wordpress.org/Function_Reference/wp_die
 * wp_enqueue_style():
 * http://codex.wordpress.org/Function_Reference/wp_enqueue_style
 * wp_localize_script():
 * http://codex.wordpress.org/Function_Reference/wp_localize_script
 * wp_enqueue_script():
 * http://codex.wordpress.org/Function_Reference/wp_enqueue_script
 * plugin_dir_url():
 * http://codex.wordpress.org/Function_Reference/plugin_dir_url
 * add_action():
 * http://codex.wordpress.org/Function_Reference/add_action
 * 
 */
class Initialise {

    public static $form;
    protected static $tracking;
    protected static $wpdb = null;

    function __construct() {

        /**
         * The code in the Form_View constructor is essential to the functioning of 
         * all the script and it is not recommened that you remove them
         */

        $args = func_get_args();

        global $wpdb;
        self::$wpdb = $wpdb;

        foreach ($args as $result) {

            $result = array_values($result);

            if (count($result) != 4) {
                wp_die('Please make sure you place the right number of values into your array');
            }

            list($option_name, $page_title, $page_url, $dynamic_output) = $result;

            self::$form = $this->config_settings($option_name, $page_title, $page_url, $dynamic_output);

            add_action('admin_enqueue_scripts', array($this, 'scripts_enqueue_cov'), '1');

        }

    } // end __construct


    /**
     * Initialise::scripts_enqueue_cov(
     * 
     * Callback function of add_action above
     * Adds scripts and styles to the headers
     * 
     */
    public function scripts_enqueue_cov() {

        // essential.
        extract(self::$form);

        if (strpos(ah_find_url(), $page_url)) {

            set_time_limit(200);

            // Only display script on plugin admin page. Is there a Wordpress way of doing this?
            $plugin_url = plugin_dir_url(__DIR__ );
            wp_enqueue_script('markup', $plugin_url.'markitup/jquery.markitup.js');
            wp_enqueue_script('markup_two', $plugin_url.'markitup/sets/default/set.js');
            wp_enqueue_script('option_scripts', $plugin_url.'javascript/scripts.js');
            wp_localize_script('option_scripts', 'option_plugin_params', get_option($option_name));
            wp_enqueue_style('option_styles', $plugin_url.'css/styles.css');
            wp_enqueue_style('markup_styles', $plugin_url.'markitup/skins/simple/style.css');
            wp_enqueue_style('markup_two_styles', $plugin_url.'markitup/sets/default/style.css');

        } // emd of strpos

    }

    /**
     * Initialise::config_settings()
     * Main array for important values throughout the class
     * @param string $option_name
     * @param string $page_title
     * @param string $page_url
     * @param boolean $dynamic_output
     * @return array
     */

    protected function config_settings($option_name, $page_title, $page_url, $dynamic_output = FALSE) {

        // put together the output array
        $output['option_name'] = $option_name; // name of option database field
        $output['page_title'] = $page_title; // name of page
        $output['page_url'] = $page_url; // url of page
        $output['dynamic_output'] = $dynamic_output;
        return $output;

    }

    /**
     * Initialise::check_options_table()
     * Checks to see if option database field is used
     * @return boolean
     */
    protected static function check_options_table() {

        extract(static::$form);

        if (get_option($option_name)) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

}

$first_form = array(
    'option_name' => 'affiliate_hoover_plugin_options', // has to be alphanumeric and underscores only
    'page_title' => 'Affiliate Hoover Plugin Admin', // Main page title
    'page_url' => 'affiliate-hoover-plugin-admin', // URL
    'dynamic_output' => TRUE); // Should the form be generated on more input

new \model\Initialise($first_form);
