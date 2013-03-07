<?php namespace controller;

/**
 * Validation_Sanitisation_Success
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

class Validation_Sanitisation_Success extends \model\Database {

    function __construct() {

        parent::__construct();

    } // end construct


    /**
     * Validation_Sanitisation_Success::update_option()
     * 
     * Updates databass. Includes important remove_empty() method
     * 
     * @param array $form
     * @return boolean
     */
    protected function update_option($form) {
        //essential
        extract(static::$form);
        $this->remove_empty($form);
        $this->delete($form);
        $this->check_feed_details_table($form);

        if (update_option($option_name, $form)) {
            return $this->success_message('You have successfully updated the form');
        }


    }


    /**
     * Validation_Sanitisation_Success::check_feed_details_table()
     * 
     * Remove associated data from the feed details table and the feeds folder
     * when the title is deleted from the options tables filed
     * 
     * @param array $form
     * @return boolean
     */

    private function check_feed_details_table($form) {
        //essential
        extract(static::$form);
        $table_name = AH_FEED_DETAILS_TABLE;
        $fields = array();
        foreach ($form[$option_name] as $key2 => $value2) {

            if (is_array($value2)) continue;
            if ($value2 == '') continue;
            $fields[] = $value2;
        }

        $all_feeds = static::$wpdb->get_results("SELECT name, fileName FROM $table_name");
        foreach ($all_feeds as $key => $value) {

            if (in_array($value->name, $fields)) continue;
            $this->db_delete_record($value->name, $value->fileName);

        }

    }

    /**
     * Validation_Sanitisation_Success::remove_empty()
     * 
     * Necessary for not including empty HTML fields in the database update if dynamic options is set to TRUE
     * If this method is not used then unnessecary fields will become part of the option database field
     * 
     * The reason this is complicated code is because the array is an irregular mix of strings and nested arrays
     * There needs a method to delete both from the array before it is submitted to the database
     * 
     * @param array $form_output
     * @return array
     */
    protected function remove_empty(&$form_output) {

        extract(static::$form);
        $database = get_option($option_name);
        $output = (int)$form_output['total_user_fields'];
        $fields = count($form_output[$option_name]);
        $unset = FALSE;
        $new_key = array();
        $total_inputs = array();
        $total_arrays = array();
        $radio = FALSE;
        if (static::check_options_table() && $dynamic_output && !empty($database[$option_name])) {

            // if new form without option database created yet make sure ALL fields are not empty
            foreach ($form_output[$option_name] as $n_key => $n_value) {

                // need to take into caculations whether radio buttons are used
                // the extra non-checked radio buttons need to be added the to the final totals
                // Only one radio button will ever be checked, so the remained are left
                if (preg_match('/zyxwv/', $n_key)) {
                    $form_output['radio_buttons'] = TRUE;
                    $radio[] = TRUE;
                }

                static $x = 0;
                static $z = 0;
                if (is_string($n_value)) {

                    $x++; // the total inputs are fluid depending if the user has checked the delete box
                    // The only TRUE way to determine the number is to access it here
                    $total_inputs[] = $x;
                } // end is_string

                if (is_array($n_value)) {
                    $z++; // the total inputs are fluid depending if the user has checked the delete box
                    // The only TRUE way to determine the number is to access it here
                    $total_arrays[] = $z;
                } // end is_string

            } // end foreach loop

            // previously was total_inputs
            $total = array_pop($total_arrays) - $output;
            $total_minus = array_pop($total_arrays) - $output;
            if (!empty($radio)) {

                foreach ($form_output[$option_name] as $key => $value) {

                    if (is_string($value)) continue;
                    if (!isset($value['radio_number'])) continue;
                    if ($value['radio_number'] == NULL) continue;
                    if (preg_match('/^\d$/', $key)) continue;
                    $number = (int)$value['radio_number'];
                    break;
                } // end foreach

                $form_output[$option_name] = array_reverse($form_output[$option_name]);
                $total_empties = array();
                $t = NULL;
                foreach ($form_output[$option_name] as $key => $value) {

                    if (!is_array($value)) {

                        static $t = 1;
                        if ($value != '') {
                            // if values are not as above then they have content
                            // if they are as above or in the case of radio buttons not set at all
                            // then that means
                            static $n = 1;
                            $total_empties[] = $n++;
                        }

                    }

                    if ($t++ == $output) break;
                } // end foreach

                if (empty($total_empties)) {
                    // if the array is empty then all the input fields including radion buttons are empty
                    $unset = TRUE;
                }

                if ($unset == TRUE) {

                    // total invididual number of arrays to be deleted are
                    // total number of individual radio button fields
                    // plus the complete field arrays minus above * 2 remainder.
                    // This is because every non-radio button has two arrays associated with it
                    $total_ars = $number + (($output - $number) * 2); // remove empty form from entire array
                    array_splice($form_output[$option_name], 0, $total_ars, NULL);
                    // on successful completion rearrange array to previous order but without unwanted fields
                    $form_output[$option_name] = array_reverse($form_output[$option_name]);
                    return $form_output;
                } else {

                    // if not TRUE then put the array back to how it was before;
                    $form_output[$option_name] = array_reverse($form_output[$option_name]);
                    return $form_output;
                }
                // beginning of if not $radio - no radio buttons in the form submit process
            } elseif (empty($radio)) {

                foreach ($form_output[$option_name] as $n_key => $n_value) {

                    static $i = 1;
                    static $y = 0;
                    static $b = 0;
                    if (is_string($n_value)) {

                        // don't allow checkboxes to be submitted
                        //if(preg_match('/zqxjk/', $n_key)) continue;

                        if ($i++ > $total) {

                            if (empty($n_value)) {
                                $y++;
                                $new_key[] = $y;
                            }

                            if (array_pop($new_key) == $output) {
                                $unset = TRUE;
                            } // end if

                        } // end if

                    } // end if

                } // end foreach

                // if unset then make sure the unwanted arrays and strings are removed from the parent array before submission to the database
                if ($unset == TRUE) {

                    foreach ($form_output[$option_name] as $n_key => $n_value) {

                        static $c = 0;
                        static $f = 0;
                        if (is_string($n_value)) {

                            if ($c++ >= $total) {
                                unset($form_output[$option_name][$n_key]);
                            } // end if

                        } // end if

                        if (is_array($n_value)) {

                            if ($f++ >= $total_minus) {
                                unset($form_output[$option_name][$n_key]);
                            } // end if

                        }

                    } // end foreach

                } // end if unset

                return $form_output;
            } // if not $radio

        } else {

            return $form_output;
        } // end if ($dynamic_output)

    }


    /**
     * Validation_Sanitisation_Success::delete()
     * 
     * Deletes data before submission to database if the checkbox is checked
     * Unsets array items and then rebuilds array with fresh index
     * 
     * @param array $form
     * @return array $form
     */

    protected function delete(&$form_output) {

        // essential.
        extract(static::$form);
        $database = get_option($option_name);
        if (static::check_options_table() && $dynamic_output && !empty($database[$option_name])) {

            $delete = NULL;
            $output = (int)$form_output['total_user_fields'];
            $total_arrays = array();
            $delete = array();
            $radio = array();
            // if new form without option database created yet make sure ALL fields are not empty
            foreach ($form_output[$option_name] as $n_key => $n_value) {

                if (preg_match('/zyxwv/', $n_key)) {
                    $radio[] = TRUE;
                }

                static $x = 0;
                $x++;
                $total_arrays[] = $x;
            } // end foreach loop

            $total_elements = array_pop($total_arrays);
            ah_reset_array($form_output[$option_name]);
            // Find any button
            foreach ($form_output[$option_name] as $result => $value) {

                if ($value == '1') {
                    $delete[] = $result;
                }

            } // end foreach

            if ($radio && (isset($form_output['radio_buttons']) && $form_output['radio_buttons'] == TRUE)) {

                if ($delete) {

                    // find the total number of radio buttons in the form
                    foreach ($form_output[$option_name] as $key => $value) {

                        if (is_string($value)) continue;
                        if (!isset($value['radio_number'])) continue;
                        if ($value['radio_number'] == NULL) continue;
                        $number = (int)$value['radio_number'];
                        break;
                    } // end foreach

                    foreach ($delete as $n_delete) {

                        //Work out max top and bottom keys to delete
                        $y_delete = $n_delete + 1;
                        $t_element = (int)$y_delete;
                        $b_element = $t_element - (int)(($output * 2) + 2) + ($number - 1); // include missing radio buttons in calcs

                        // use slice to remove unwanted forms from parent array
                        array_splice($form_output[$option_name], $b_element, $t_element, NULL);
                    } // end foreach

                }

            }


            if (!$radio) {

                if ($delete) {

                    foreach ($delete as $n_delete) {

                        //Work out max top and bottom keys to delete
                        $y_delete = $n_delete + 1;
                        $t_element = (int)$y_delete;
                        $b_element = $t_element - (int)(($output * 2) + 2); // use slice to remove unwanted forms from parent array
                        array_splice($form_output[$option_name], $b_element, $t_element, NULL);
                    } // end foreach

                } // end if delete

            } // end if not radio

            if (!empty($form_output[$option_name])) {
                ah_reset_array($form_output[$option_name]);
            }

            return $form_output;
        } else {

            // if not dynamic
            return $form_output;
        } // end dynamic output


    }


    /**
     * Validation_Sanitisation_Success::success_message()
     * 
     * @param mixed $message
     * @return
     */
    public function success_message($message) {

        //essential
        extract(static::$form); // necessary for javascript form values zero to work
        if ($dynamic_output) {

            setcookie('_multi_cov', $option_name, time() + 60);

        }

        $html = '<div id="message" class="updated">';
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

    public function update_option_interface($form) {

        return $this->update_option($form);

    }


}
