<?php

namespace TrackView;
use TrackController;
/**
 * @author Andy Walpole
 * @date 8/6/2012
 * 
 */

class Tracking_View extends \TrackController\Tracking_Controller {

    static $secure = FALSE;
    public $ip = NULL;

    function __construct() {
        
        global $wpdb;
            
        // create static variable if HTTPS is on
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] ==
            443) {

            static::$secure = TRUE;

        }

        add_action('init', array($this, 'scripts_enqueue_cov'), "1");

        $this->ip = $_SERVER['REMOTE_ADDR'];
        
        

    }

    public function scripts_enqueue_cov() {

        $plugin_url = plugin_dir_url(__DIR__ );

        wp_enqueue_script('jquery');
        wp_enqueue_script("tracking_scripts", $plugin_url."javascript/tracking.js");

        $protocol = static::$secure === TRUE ? 'https://' : 'http://';

        $params = array('ajaxurl' => admin_url('admin-ajax.php', $protocol),'my_nonce' => wp_create_nonce('myajax-nonce'));
        
        wp_localize_script("tracking_scripts", "ah_tracking_scripts", $params);

    }

}

$tracker = new \TrackView\Tracking_View;

?>