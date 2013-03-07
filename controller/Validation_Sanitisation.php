<?php namespace controller;

/**
 * Validation_Sanitisation
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

class Validation_Sanitisation extends \model\Database {

    function __construct() {

        parent::__construct();


    } // end construct


    /**
     * Validation_Sanitisation::method_args_validation()
     * 
     * @param digit $number
     * @param function: func_num_args() $args
     * @param string $method
     * @return boolean
     */

    protected function method_args_validation($number, $args, $method) {

        if ($args > (int)$number) {
            die("Please make sure that you place the right number of arguments into the $method method");
        }

    }


    /**
     * Validation_Sanitisation::sanitize()
     * 
     * @param string $handle
     * @param array $form_output
     * @return array
     */
    public function sanitize(&$form_output, $handle) {

        $this->method_args_validation(2, func_num_args(), 'sanitize');

        switch ($handle) {

            case 'sanitize_post':
                array_walk_recursive($form_output, array($this, 'sanitize_post'));
                break;
            case 'trim_post':
                array_walk_recursive($form_output, array($this, 'trim_post'));
                break;
            case 'strip_tags_post':
                array_walk_recursive($form_output, array($this, 'strip_tags_post'));
            case 'stripslashes':
                array_walk_recursive($form_output, array($this, 'stripslashes'));
                break;
            case 'sanitize_file_name_new':
                array_walk_recursive($form_output, array($this, 'sanitize_file_name_new'));
                break;
            case 'wp_kses_new':
                array_walk_recursive($form_output, array($this, 'wp_kses_new'));
                break;
            default:
                die("The value you ented into the sanitize() method is not recognised: $handle");
                break;

        } // end switch

    }


    /**
     * Validation_Sanitisation::wp_kses_new()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */


    public function wp_kses_new(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, ' wp_kses_kses');
            } else {
                $form_output = wp_filter_nohtml_kses($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (!preg_match("/$att/i", $thisKey)) continue;

                if (is_string($thisKey)) {
                    $form_output[$option_name][$thisKey] = wp_filter_nohtml_kses($result);
                }

            }

            return $form_output;
        }

    }


    /**
     * Validation_Sanitisation::trim_post()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */

    public function trim_post(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'trim');
            } else {
                $form_output = trim($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (!preg_match("/$att/i", $thisKey)) continue;

                if (is_string($thisKey)) {
                    $form_output[$option_name][$thisKey] = trim($result);
                }

            }

            return $form_output;
        }

    }


    /**
     * Validation_Sanitisation::sanitize_post()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */
    public function sanitize_post(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'sanitize_text_field');
            } else {
                $form_output = sanitize_text_field($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (!preg_match("/$att/i", $thisKey)) continue;

                if (is_string($thisKey)) {
                    $form_output[$option_name][$thisKey] = sanitize_text_field($result);
                }

            } // end foreach

            return $form_output;

        }

    }


    /**
     * Validation_Sanitisation::strip_tags_post()
     * 
     * @param string $att
     * @param boolean $single
     * @param array $form_output
     * @return array $form_output
     */

    public function strip_tags_post(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'wp_strip_all_tags');
            } else {
                $form_output = wp_strip_all_tags($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (!preg_match("/$att/i", $thisKey)) continue;

                if (is_string($thisKey)) {
                    $form_output[$option_name][$thisKey] = wp_strip_all_tags($result);
                }

            } // end foreach

            return $form_output;

        }

    }


    /**
     * Validation_Sanitisation::stripslashes()
     * 
     * @param string $att
     * @param string $single
     * @param array $form_output
     * @return array
     */

    public function stripslashes(&$form_output, $att = NULL, $single = NULL) {

        if ($single == NULL) {

            if (is_array($form_output)) {
                array_walk_recursive($form_output, 'stripslashes_deep');
            } else {
                $form_output = stripslashes_deep($form_output);
            }

        } else {

            extract(static::$form);

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (preg_match("/$att/i", $thisKey)) continue;

                if (is_string($thisKey)) {
                    $form_output[$option_name][$thisKey] = stripslashes_deep($result);
                }


            } // end foreach

            return $form_output;

        }

    }


    /**
     * Validation_Sanitisation::alnum()
     * 
     * @param array $form_output
     * @param string $att
     * @return boolean
     */

    public function alnum($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $key => $value) {

                if (!preg_match("/$att/i", $key)) continue;
                if ($value == '') continue;

                if (!ctype_alnum($value)) {
                    return FALSE;
                }

            } // end foreach

        } else {
            die('Make sure that the inputs for validate_url() is an array and a string');
        }


    }


    /**
     * Validation_Sanitisation::title_check()
     * 
     * Checks to make sure that title only uses code and nothing else
     * 
     * @param string $att
     * @param array $form_output
     * @return boolean
     */

    // need to make sure that only code [#425#] is
    public function title_check($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (!preg_match("/$att/i", $thisKey)) continue;

                if ($result == '') {

                    if (!preg_match('/^\[#([0-9]*)#\]$/', $result)) {
                        return FALSE;
                    }

                } // end if

            } // end foreach

        } else {
            die('Make sure that the inputs for validate_url() is an array and a string');
        }


    }

    /**
     * Validation_Sanitisation::check_file_error()
     * 
     * @param string $att
     * @param array $file
     * @return string
     * 
     */


    public function check_file_error($file, $att) {

        $max_up = ah_max_upload();

        foreach ($file as $key => $value) {

            if ($key == 'size') {

                foreach ($value as $new_key => $new_value) {

                    if ((int)implode($new_value) >= $max_up) {

                        return FALSE;

                    }

                } // end foreach

            } // end if

        } // end foreach

    }


    /**
     * Validation_Sanitisation::security_check()
     * 
     * ESSENTIAL! Must include this
     * Removes non-relevant HTML form fields before database update
     * 
     * @param array $array
     * @return array
     */
    public function security_check($array) {

        if (!wp_verify_nonce($array['_wpnonce_options_cov'], 'options_form_cov')) die('Security check failed');
        if ($_SERVER['REQUEST_URI'] != $array['_wp_http_referer']) die('Security check failed');
        // The values below need to be removed before further validation and database entry

        unset($array['option_page']);
        unset($array['_wpnonce_options_cov']);
        unset($array['_wp_http_referer']);
        unset($array['submit']);

        return $array;
    }


    /**
     * Validation_Sanitisation::check_file_empty()
     * 
     * @param array $file 
     * @param string $att
     * @return boolean
     * 
     */

    public function check_file_empty($file, $att) {

        foreach ($file as $key => $value) {

            if ($key == 'type') {

                foreach ($value as $new_key => $new_value) {

                    if (implode($new_value) == '') {

                        return FALSE;

                    }

                } // end foreach

            } // end if

        } // end foreach

    }


    /**
     * Validation_Sanitisation::check_file_ext()
     * 
     * @param array $file 
     * @param string $att
     * @return boolean
     * 
     */

    public function check_file_ext($file, $att) {

        foreach ($file as $key => $value) {

            if ($key == 'name') {

                foreach ($value as $new_key => $new_value) {

                    if (implode($new_value) == '') continue;
                    $ext = $this->get_file_extension(implode($new_value));

                    if ($ext != 'csv') {

                        return FALSE;

                    } // end if

                } // end foreach

            } // end if

        } // end foreach

    }


    public function check_file_duplicate($file, $att) {

        foreach ($file as $key => $value) {

            if ($key == 'name') {
                
                $file = $this->db_find_file_name();

                foreach ($value as $new_key => $new_value) {

                    if (implode($new_value) == '') continue;

                    foreach ($file as $result) {

                        $basename = $this->get_file_filename(implode($new_value));

                        if (preg_match("/$basename/", $result->fileName)) {

                            return FALSE;

                        } // end if

                    } // end foreach
                    
                } // end foreach

            } // end if

        } // end foreach

    }

    /**
     * Validation_Sanitisation::parse_feeds_loop()
     * 
     * Finds string for parse_feeds()
     * 
     * @param array $file 
     * @param string $att
     * @return boolean
     * 
     */

    public function parse_feeds_loop($form_output, $att) {

        extract(static::$form);

        if (is_array($form_output) && is_string($att)) {

            foreach ($form_output[$option_name] as $thisKey => $result) {

                if (!preg_match("/$att/i", $thisKey)) continue;

                if ($result == '') {

                    return $this->parse_feeds($result);


                } // end if

            } // end foreach

        } else {
            die('Make sure that the inputs for validate_file() is an array and a string');
        }

    }

    public function get_file_extension($file_name) {

        return pathinfo($file_name, PATHINFO_EXTENSION);

    }

    public function get_file_basename($file_name) {

        return pathinfo($file_name, PATHINFO_BASENAME);

    }

    public function get_file_filename($file_name) {

        return pathinfo($file_name, PATHINFO_FILENAME);

    }

    /**
     * Validation_Sanitisation::failure_message()
     * 
     * @param mixed $message
     * @return
     */
    protected function failure_message($message) {

        //essential
        extract(static::$form);
        $html = '<div id="message" class="error">';
        if (is_array($message)) {
            foreach ($message as $line) {
                $html .= '<p><strong>'.$line.'</strong></p>';
            }
        } else {
            $html .= '<p><strong>'.$message.'</strong></p>';
        } // end if

        $html .= '</div>';
        return $html;
    }


    public function failure_message_interface($value) {

        return $this->failure_message($value);

    }


    /**
     * Validation_Sanitisation::empty_value()
     * 
     * Checks if form fields are empty
     * 
     * Will only work form arrays that only include input or textarea
     * Radio, select and checkboxs have to be invididually processed with a string value
     * 
     * @param mixed $form_output
     * @return boolean
     */
    public function empty_value($form_output, $single = NULL) {

        extract(static::$form);

        $database = static::check_options_table();
        $output = (int)$form_output['total_user_fields'];
        $data = get_option($option_name);

        $total_inputs = array();
        $total_arrays = array();
        $total_checkboxes = 0;
        $total_radio_buttons = 0;

        // This is a repeat of code. Refactor it

        // if new form without option database created yet make sure ALL fields are not empty
        foreach ($form_output[$option_name] as $n_key => $n_value) {

            // find the total amount of individual checkboxes per form block
            if (is_array($n_value) && isset($n_value['checkbox_number']) && $n_value['checkbox_number'] !=
                '') {
                $total_checkboxes = (int)$n_value['checkbox_number'];
            }

            // find the total amount of individual radio buttons per form block
            if (is_array($n_value) && isset($n_value['radio_number']) && $n_value['radio_number'] !=
                '') {
                $total_radio_buttons = (int)$n_value['radio_number'];
            }

            static $x = 1;
            static $z = 0;

            if (is_string($n_value)) {

                // remove delete checkbox
                if (preg_match('/xyz/', $n_key)) continue;
                // remove regular checkboxes
                if (preg_match('/zqxjk/', $n_key)) continue;
                // remove radio buttons
                if (preg_match('/zyxwv/', $n_key)) continue;

                // the total inputs are fluid depending if the user has checked the delete box
                // The only TRUE way to determine the number is to access it here
                $total_inputs[] = $x++;
            } // end is_string

            if (is_array($n_value)) {

                // the total inputs are fluid depending if the user has checked the delete box
                // The only TRUE way to determine the number is to access it here
                $total_arrays[] = $z++;
            } // end is_string

        } // end foreach loop

        $total = (array_pop($total_inputs) - ($output - $total_checkboxes - $total_radio_buttons));

        if ($dynamic_output) {

            if ($database && !empty($data[$option_name])) {

                if ($single == NULL) {
                    // if entire form is entered

                    // if new form without option database created yet make sure ALL fields are not empty
                    foreach ($form_output[$option_name] as $key => $value) {

                        // remove delete checkbox
                        if (preg_match('/xyz/', $key)) continue;
                        // remove regular checkboxes
                        if (preg_match('/zqxjk/', $key)) continue;
                        // remove radio buttons
                        if (preg_match('/zyxwv/', $key)) continue;

                        if (!is_string($value)) continue;

                        //This is to prevent checking for empty the bottom form
                        static $c = 0;

                        if ($c++ < $total) {

                            if (!is_string($value)) continue;

                            if (empty($value)) {
                                return FALSE;
                            } // end if

                        } // end if

                    } // end foreach loop

                } else {

                    // if only single form input entered
                    foreach ($form_output[$option_name] as $key => $n_value) {

                        if (preg_match("/$single/i", $key)) {

                            if (empty($n_value)) {
                                return FALSE;
                            } // end if

                        } // end if

                    } // end foreach

                } // end if single

            } else {


                if ($this->empty_non_dynamic($form_output, $single) == FALSE) {
                    return FALSE;
                }

            } // end if $database

            // if $dynamic_output is set to FALSE
        } else {

            if ($this->empty_non_dynamic($form_output, $single) == FALSE) {
                return FALSE;
            }

        } // end if $dynamic_output

    }


    /**
     * Validation_Sanitisation::empty_non_dynamic()
     * 
     * Checks if form fields are empty if no dynamic is set
     * 
     * A private method to work with empty_value() only
     * 
     * @param array $form_output
     * @param single $single
     * @return boolean
     */
    protected function empty_non_dynamic($form_output, $single) {

        extract(static::$form);

        if ($single == NULL) {

            // if new form without option database created yet make sure ALL fields are not empty
            foreach ($form_output[$option_name] as $n_key => $n_value) {

                // remove delete checkbox
                if (preg_match('/xyz/', $n_key)) continue;
                // remove regular checkboxes
                if (preg_match('/zqxjk/', $n_key)) continue;
                // remove radio buttons
                if (preg_match('/zyxwv/', $n_key)) continue;

                if (is_string($n_value)) {

                    if (empty($n_value)) {
                        return FALSE;
                        break;
                    }

                } // end is_string

            } // end foreach loop

            return TRUE;

        } else {

            foreach ($form_output[$option_name] as $key => $n_value) {

                if (preg_match("/$single/i", $key)) {

                    if (empty($n_value)) {
                        return FALSE;
                    } // end if

                } // end if

            } // end foreach

        } // end if not single

    }


    /**
     * Validation_Sanitisation::duplicate_entries()
     * 
     * Checks to make sure all array values are unique
     * 
     * For an explanation of this code read my blog post here: http://www.suburban-glory.com/blog?page=152
     * 
     * @param array $array
     * @return boolean
     */

    public function duplicate_entries($array) {

        extract(static::$form);

        $tmp = array();

        foreach ($array[$option_name] as $key => $value) {

            // root out radio buttons
            if (preg_match('/zyxwv/', $key)) continue;

            // remove checkboxes from the loop
            if (preg_match('/zqxjk/', $key)) continue;

            // remove select options
            if (preg_match('/kvbpy/', $key)) continue;

            if (is_string($key) && !empty($value)) {
                $tmp[] = $value;
            }

        } // end foreach

        if (count($tmp) != count(array_unique($tmp))) {
            return FALSE;
        }

    }


}
