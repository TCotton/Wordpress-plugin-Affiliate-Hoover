<?php

/**
 * @author Andy Walpole
 * @date 21/2/2012
 * 
 */


class Feed_View extends Feed_Controller {

    function __construct() {


    } // end construct


    public function a_feed($url) {

        return $this->parse_feed($url);

    }


} // end class

$feed_v = new Feed_View();

?>