<?php

namespace TrackModel;
/**
 * @author Andy Walpole
 * @date 8/6/2012
 * 
 */


class Tracking_Model {

    function __construct() {


    }

    public function update_tracking() {

        if (isset($_REQUEST['post'])) {

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

            return $wpdb->query($wpdb->prepare("INSERT INTO ".AH_TRACKING_TABLE.
                " (date, post_id, ip) VALUES (now(), %d, %s)", $digit, (string )$this->ip));

            //die();
        }

    }

    protected function get_all($limit) {

        global $wpdb;
        return $wpdb->get_results("SELECT ip,post_id,date FROM ".AH_TRACKING_TABLE.
            " ORDER BY date DESC ".$limit);
    }

    protected function total_entries() {

        global $wpdb;
        $total = $wpdb->get_results("SELECT id, post_id FROM ".AH_TRACKING_TABLE);
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

}

?>