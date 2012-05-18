<?php

namespace OptionModelSub;
use File_CSV_DataSource;
use XMLReader;

/**
 * Form_Model a
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
 * 
 * 
 */

class Form_Model_Sub {

    function __construct() {


    } // end construct


    /**
     * Form_Model::method_args_validation()
     * 
     * @param digit $number
     * @param function: func_num_args() $args
     * @param string $method
     * @return boolean
     */

    private function method_args_validation($number, $args, $method) {

        if ($args > (int)$number) {
            die("Please make sure that you place the right number of arguments into the $method method");
        }

    }


    /**
     * Form_Model::sanitize()
     * 
     * @param string $handle
     * @param array $form_output
     * @return array
     */
    protected function sanitize(&$form_output, $handle) {

        $this->method_args_validation(2, func_num_args(), "sanitize");

        switch ($handle) {

            case 'sanitize_post':
                array_walk_recursive($form_output, array($this, 'sanitize_post'));
                break;
            case 'trim_post':
                array_walk_recursive($form_output, array($this, 'trim_post'));
                break;
            case 'strip_tags_post':
                array_walk_recursive($form_output, array($this, 'strip_tags_post'));
                break;
            case 'empty_value':
                array_walk_recursive($form_output, array($this, 'empty_value'));
                break;
            case 'stripslashes':
                array_walk_recursive($form_output, array($this, 'stripslashes'));
                break;
            case 'sanitize_file_name_new':
                array_walk_recursive($form_output, array($this, 'sanitize_file_name_new'));
                break;
            case 'wp_kses_new':
                array_walk_recursive($form_output, array($this, 'wp_kses_new'));
                break;
            case 'esc_url_raw_new':
                array_walk_recursive($form_output, array($this, 'esc_url_raw_new'));
                break;
            default:
                die("The value you ented into the sanitize() method is not recognised: $handle");
                break;

        } // end switch

    }

    //validate_file

    /**
     * Form_Model::esc_url_raw_new()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */


    function esc_url_raw_new(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'esc_url_raw');
            } else {
                $form_output = esc_url_raw($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if (is_string($thisKey)) {
                        $form_output[$option_name][$thisKey] = esc_url_raw($result);
                    }

                }

            }

            return $form_output;
        }

    }

    /**
     * Form_Model::wp_kses_new()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */


    function wp_kses_new(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, ' wp_kses_kses');
            } else {
                $form_output = wp_filter_nohtml_kses($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if (is_string($thisKey)) {
                        $form_output[$option_name][$thisKey] = wp_filter_nohtml_kses($result);
                    }

                }

            }

            return $form_output;
        }

    }


    /**
     * Form_Model::trim_post()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */

    function trim_post(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'trim');
            } else {
                $form_output = trim($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if (is_string($thisKey)) {
                        $form_output[$option_name][$thisKey] = trim($result);
                    }

                }

            }

            return $form_output;
        }

    }


    /**
     * Form_Model::sanitize_post()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */
    protected function sanitize_post(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'sanitize_text_field');
            } else {
                $form_output = sanitize_text_field($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if (is_string($thisKey)) {
                        $form_output[$option_name][$thisKey] = sanitize_text_field($result);
                    }

                }

            } // end foreach

            return $form_output;

        }

    }


    /**
     * Form_Model::strip_tags_post()
     * 
     * @param string $att
     * @param boolean $single
     * @param array $form_output
     * @return array $form_output
     */

    protected function strip_tags_post(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'wp_strip_all_tags');
            } else {
                $form_output = wp_strip_all_tags($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if (is_string($thisKey)) {
                        $form_output[$option_name][$thisKey] = wp_strip_all_tags($result);
                    }

                }

            } // end foreach

            return $form_output;

        }

    }


    /**
     * Form_Model::stripslashes()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */

    protected function stripslashes(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'stripslashes_deep');
            } else {
                $form_output = stripslashes_deep($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if (is_string($thisKey)) {
                        $form_output[$option_name][$thisKey] = stripslashes_deep($result);
                    }

                } // end if

            } // end foreach

            return $form_output;

        }

    }


    /**
     * Form_Model::validate_email()
     * 
     * @param string $att
     * @return boolean
     */

    protected function validate_email($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if ($result !== "") {

                        if (!filter_var($result, FILTER_VALIDATE_EMAIL)) {
                            return FALSE;
                        }

                    } // end if

                } // end if

            } // end foreach

        } else {
            die("Make sure that the inputs for validate_url() is an array and a string");
        }


    }

    /**
     * Form_Model::alnum()
     * 
     * @param array $form_output
     * @param string $att
     * @return boolean
     */

    protected function alnum($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if ($result !== "") {

                        if (!ctype_alnum($result)) {
                            return FALSE;
                        }

                    } // end if

                } // end if

            } // end foreach

        } else {
            die("Make sure that the inputs for validate_url() is an array and a string");
        }


    }


    /**
     * Form_Model::title_check()
     * 
     * Checks to make sure that title only uses code and nothing else
     * 
     * @param string $att
     * @param array $form_output
     * @return boolean
     */

    // need to make sure that only code [#425#] is
    protected function title_check($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if ($result !== "") {

                        if (!preg_match("/^\[#([0-9]*)#\]$/", $result)) {
                            return FALSE;
                        }

                    } // end if

                } // end if

            } // end foreach

        } else {
            die("Make sure that the inputs for validate_url() is an array and a string");
        }


    }


    /**
     * Form_Model::validate_url()
     * 
     * @param array $form_output
     * @param string $att
     * @return boolean
     */
    protected function validate_url($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if ($result !== "") {

                        if (!filter_var($result, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                            return FALSE;
                        }

                    } // end if

                } // end if

            } // end foreach

        } else {
            die("Make sure that the inputs for validate_url() is an array and a string");
        }

    }

    /**
     * Form_Model::validate_remote_url()
     * 
     * @param array $form_output
     * @param string $att
     * @return boolean
     */

    protected function validate_remote_url($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if ($result !== "") {

                        $response = wp_remote_get($result);

                        if (wp_remote_retrieve_response_code($response) !== 200) {
                            /*
                            if (is_wp_error($response) || stripos($response['body'],
                            "http://www.webaddresshelp.bt.com")) {
                            */

                            return FALSE;

                        }

                    } // end if

                } // end if

            } // end foreach

        } else {
            die("Make sure that the inputs for validate_remote_url() is an array and a string");
        }

    }

    protected function validate_check_file($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if ($result !== "") {

                        $response = wp_remote_get($result);

                        if ((isset($response['body']) && preg_match("/^tradedoubler\.com/", $response['body'])) ||
                            (isset($response['headers']['p3p']) && preg_match("/^http:\/\/www\.paidonresults\.com/",
                            $response['headers']['p3p'])) || (isset($response['headers']['content-disposition']) &&
                            preg_match("/datafeed_([0-9a-bA-B]*)\.xml/", $response['headers']['content-disposition']))) {

                            return FALSE;

                        }

                    } // end if

                } // end if

            } // end foreach

        } else {
            die("Make sure that the inputs for validate_remote_url() is an array and a string");
        }

    }


    protected function get_file_extension($file_name) {

        return pathinfo($file_name, PATHINFO_EXTENSION);

    }

    protected function get_file_basename($file_name) {

        return pathinfo($file_name, PATHINFO_BASENAME);

    }

    protected function get_file_filename($file_name) {

        return pathinfo($file_name, PATHINFO_FILENAME);

    }


    // UPLOADING FILES HERE

    /**
     * Form_Model::file_upload_error_message()
     * 
     * @param string $error_code
     * @return string
     * 
     */


    function file_upload_error_message($error_code) {
        switch ($error_code) {
            case 0:
                NULL;
                break;
            case 1:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                break;
            case 2:
                return
                    'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                break;
            case 3:
                return 'The uploaded file was only partially uploaded';
                break;
            case 4:
                NULL;
                break;
            case 6:
                return 'Missing a temporary folder';
                break;
            case 7:
                return 'Failed to write file to disk';
                break;
            case 8:
                return 'File upload stopped by extension';
                break;
            default:
                return 'Unknown upload error';
                break;
        }
    }

    /**
     * Form_Model::check_file_error()
     * 
     * @param string $att
     * @param array $file
     * @return string
     * 
     */


    protected function check_file_error($file, $att) {

        foreach ($file as $key => $value) {

            if ($key === "size") {

                foreach ($value as $new_key => $new_value) {

                    if ((int)implode($new_value) >= 2097152) {

                        return FALSE;

                    }

                } // end foreach

            } // end if

        } // end foreach

    }


    /**
     * Form_Model::move_file()
     * 
     * @param array $file_raw 
     * @param string $att
     * @return string
     * 
     */

    protected function move_file($file_raw, $att) {

        $file = apply_filters('wp_handle_upload_prefilter', $file_raw);

        $tmp_name = "";
        $name = "";

        foreach ($file as $key => $value) {

            if ($key === 'tmp_name') {

                foreach ($value as $new_key => $new_value) {

                    $tmp_name = implode($new_value);

                } // end foreach

            } // end if

            if ($key === 'name') {

                foreach ($value as $new_key => $new_value) {

                    $name = implode($new_value);

                } // end foreach

            } // end if

        } // end foreach

        if ($tmp_name !== "" && $name !== "") {

            $new_name = sanitize_file_name($name);

            if (move_uploaded_file($tmp_name, AH_FEEDS_DIR.$new_name)) {
                return $new_name;
            }

        } // end if

    }


    protected function validation_read_temp_file($file) {

        $tmp_name = "";
        $name = "";
        $xml = FALSE;

        foreach ($file as $key => $value) {

            if ($key === 'name') {

                foreach ($value as $new_key => $new_value) {

                    $name = implode($new_value);

                    if (stripos($name, "xml")) {
                        $xml = TRUE;
                    }

                } // end foreach

            } // end if

            if ($key === 'tmp_name') {

                foreach ($value as $new_key => $new_value) {

                    $tmp_name = implode($new_value);

                    if ($xml) {

                        $content = file_get_contents($tmp_name);

                        if (!preg_match("/tradedoubler.com|paidonresults.net/", $content)) {
                            return FALSE;
                        }

                    }

                } // end foreach

            } // end if

        } // end foreach

    }


    /**
     * Form_Model::check_file_empty()
     * 
     * @param array $file 
     * @param string $att
     * @return boolean
     * 
     */

    protected function check_file_empty($file, $att) {

        foreach ($file as $key => $value) {

            if ($key === "type") {

                foreach ($value as $new_key => $new_value) {

                    if (implode($new_value) === "") {

                        return FALSE;

                    }

                } // end foreach

            } // end if

        } // end foreach

    }


    /**
     * Form_Model::check_file_ext()
     * 
     * @param array $file 
     * @param string $att
     * @return boolean
     * 
     */

    protected function check_file_ext($file, $att) {

        foreach ($file as $key => $value) {

            if ($key === "name") {

                foreach ($value as $new_key => $new_value) {

                    if (implode($new_value) !== "") {

                        $ext = $this->get_file_extension(implode($new_value));

                        if ($ext !== "csv" && $ext !== "xml") {

                            return FALSE;

                        } // end if

                    } // end if

                } // end foreach

            } // end if

        } // end foreach

    }


    protected function check_file_duplicate($file, $att) {

        foreach ($file as $key => $value) {

            if ($key === "name") {

                foreach ($value as $new_key => $new_value) {

                    if (implode($new_value) !== "") {

                        $file = $this->find_file_name();

                        foreach ($file as $result) {
                            
                            $basename = $this->get_file_filename(implode($new_value));

                            if (preg_match("/$basename/", $result->fileName)) {

                                return FALSE;
                                
                            } // end if

                        } // end foreach


                    } // end if

                } // end foreach

            } // end if

        } // end foreach

    }


    /**
     * Form_Model::parse_feeds_loop()
     * 
     * Finds string for parse_feeds()
     * 
     * @param array $file 
     * @param string $att
     * @return boolean
     * 
     */

    protected function parse_feeds_loop($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) {

                    if ($result !== "") {

                        return $this->parse_feeds($result);

                    } // end if

                } // end if

            } // end foreach

        } else {
            die("Make sure that the inputs for validate_file() is an array and a string");
        }

    }


    /**
     * Form_Model::save_unzip()
     * 
     * @param string $file 
     * @param string $att
     * @param string $ext
     * @return boolean
     * 
     */

    private function save_unzip($data, $match, $ext) {

        $zip = $data['body'];

        $filename = "datafeed_".$match;

        $file = AH_DIR_PATH."zip".AH_DS.$filename.".zip";
        $fp = fopen($file, "w");
        fwrite($fp, $zip);
        fclose($fp);

        WP_Filesystem();
        if (unzip_file($file, AH_DIR_PATH."feeds")) {
            unlink($file);
            return TRUE;
        } else {
            return FALSE;
        }

    }

    /**
     * Form_Model::save_ungzip()
     * 
     * @param string $file 
     * @param string $att
     * @param string $ext
     * @param string $match
     * @return boolean
     * 
     */

    private function save_ungzip($data, $url, $ext, $match) {

        $gzip = $data['body'];

        $filename = "datafeed_".$match;

        $file = AH_FEEDS_DIR.$filename.".$ext";

        $remote = gzopen($url, "rb");
        $home = fopen($file, "w");

        while ($string = gzread($remote, 4096)) {
            fwrite($home, $string, strlen($string));
        }
        gzclose($remote);
        fclose($home);

        if ($home != "") {
            return TRUE;
        }

    }

    /**
     * Form_Model::parse_feeds()
     * 
     * Takes the XML or CSV file places it into appropiate folder
     * 
     * @param string $url
     * @return string
     * 
     */

    protected function parse_feeds($url) {

        $anArray = array('timeout' => 10);

        $response = wp_remote_get(esc_url_raw($url), $anArray);

        // use this function for filtering files
        $file = apply_filters('wp_handle_upload_prefilter', $response);

        $url_filtered = apply_filters('wp_handle_upload_prefilter', esc_url_raw($url));

        // TRADEDOUBLER

        if (stripos($url, "tradedoubler")) {

            $temp = uniqid();
            $body = $file['body'];

            // check if document is XML

            if (stristr($file['headers']['content-type'], 'text/xml')) {

                $file = $temp.".xml";
                $new_file = AH_DIR_PATH."feeds".AH_DS.$file;
                $fp = fopen($new_file, "w+");
                fwrite($fp, $body);
                fclose($fp);
                return $file;

            }

            // check if document is CVS

            if (stristr($file['headers']['content-type'], 'text/plain')) {

                $file = $temp.".csv";
                $new_file = AH_DIR_PATH."feeds".AH_DS.$file;
                $fp = fopen($new_file, "w+");
                fwrite($fp, $body);
                fclose($fp);
                return $file;

            }

        }

        // AFFILIATE WINDOW

        if (isset($file['headers']['content-disposition'])) {

            if (stripos($file['headers']['content-disposition'], "datafeed_")) {

                preg_match("/.datafeed_([0-9]*)\../", $file['headers']['content-disposition'], $match);

                switch ($file) {

                    case ($file['headers']['content-type'] === "application/zip" ? TRUE : FALSE): // Do stuff for zip file here

                        switch ($file) {

                            case (stripos($file['headers']['content-disposition'], "xml") ? TRUE : FALSE):
                                if ($this->save_unzip($file, $match[1], "xml") === TRUE) {
                                    return "datafeed_".$match[1].".xml";
                                }
                                break;

                            case (stripos($file['headers']['content-disposition'], "csv") ? TRUE : FALSE):
                                if ($this->save_unzip($file, $match[1], "csv") === TRUE) {
                                    return "datafeed_".$match[1].".csv";
                                }
                                break;

                            default:
                                return "This module only uses feeds in the XML or CSV format";
                                break;

                        } //end switch statement

                        break;

                    case ($file['headers']['content-type'] === "application/gzip" ? TRUE : FALSE): // Do stuff for gzip file here

                        switch ($file) {

                            case (stripos($file['headers']['content-disposition'], "xml") ? TRUE : FALSE):
                                if ($this->save_ungzip($file, $url_filtered, "xml", $match[1]) === TRUE) {
                                    return "datafeed_".$match[1].".xml";
                                }
                                break;

                            case (stripos($file['headers']['content-disposition'], "csv") ? TRUE : FALSE):
                                if ($this->save_ungzip($file, $url_filtered, "csv", $match[1]) === TRUE) {
                                    return "datafeed_".$match[1].".csv";
                                }
                                break;

                            default:
                                return "Only XML or CSV files";
                                break;

                        } // end switch statement

                        break;

                    default:
                        return "Sorry, this file is an unknown compression type";
                        break;

                } # end switch

            } // end if stripos($file['headers']['content-disposition'],"datafeed_")

            // PAID ON RESULTS

            if (stripos($file['headers']['content-disposition'], "paidonresults")) {

                if (stripos($file['headers']['content-disposition'], ".csv")) {

                    preg_match("/paidonresults-([\w]*)\.csv$/", $file['headers']['content-disposition'],
                        $match);

                    if (!empty($match[0])) {

                        $new_file = AH_DIR_PATH."feeds".AH_DS.$match[0];
                        $fp = fopen($new_file, "w");
                        fwrite($fp, $file['body']);
                        fclose($fp);
                        return $match[0];

                    }

                }

                if (stripos($file['headers']['content-disposition'], ".xml")) {

                    preg_match("/paidonresults-([\w]*)\.xml$/", $file['headers']['content-disposition'],
                        $match);

                    if (!empty($match[0])) {

                        $new_file = AH_DIR_PATH."feeds".AH_DS.$match[0];
                        $fp = fopen($new_file, "w");
                        fwrite($fp, $file['body']);
                        fclose($fp);
                        return $match[0];

                    }

                }

            } // end if stripos($file['headers']['content-disposition'],"paidonresults")

        } //$file['headers']['content-disposition']

    }


    // PARSE CSV

    /**
     * Form_Model::parse_csv_head()
     * 
     * @param object $file
     * @return array || boolean
     * 
     */

    protected function parse_csv_head($file) {

        $csv = new File_CSV_DataSource;

        if ($csv->load($file)) {

            return $csv->getHeaders();

        } else {

            return FALSE;

        } // end if ($csv->sadfsad
    }

    /**
     * Form_Model::count_csv_rows()
     * 
     * counts entire number of rows
     * 
     * @param object $file
     * @return array || boolean
     * 
     */

    protected function count_csv_rows($file) {

        $csv = new File_CSV_DataSource;

        if ($csv->load($file)) {

            return $csv->countRows();

        } else {

            return FALSE;

        } // end if ($csv->sadfsad
    }

    /*
    // MOVE THIS TO model A file
    public function parse_xml($file) {

    $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

    return $xml;

    }
    */


}

$modelA = new \OptionModelSub\Form_Model_Sub();

?>