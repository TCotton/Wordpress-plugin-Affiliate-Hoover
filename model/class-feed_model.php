<?php

/**
 * @author Andy Walpole
 * @date 21/2/2012
 * 
 */

class Feed_Model {

    function __construct() {


    } // end construct


    public function remote_file_exists($url) {

        // IMPORTANT : Check to see if the file exists on the remote server
        // If the HTTP code is 200 then return true - everything else false

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($retcode === 200) {
            return true;
        } else {
            return false;
        }

        unset($retcode);

    }

    // Modified from: http://stackoverflow.com/questions/3713472/using-curl-with-pclzip-class
    public function grab_file($url, $new_file) {

        //get file
        $ch = curl_init();
        $fp = fopen(AH_DIR_PATH."zip/$new_file", "w");

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_FAILONERROR => 1,
            CURLOPT_AUTOREFERER => 1,
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FILE => $fp);

        curl_setopt_array($ch, $options);
        $file = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        if (!$file) {
            //$error = "cURL error number:" . curl_errno($ch);
            //$error .= "cURL error:" . curl_error($ch);
            return false;

        } else {

            return true;

        }
    }


} // end class


$feedM = new Feed_Model();
