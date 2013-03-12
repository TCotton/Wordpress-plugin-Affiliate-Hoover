<?php namespace view;
use ah_pagination;

/**
 * Form_Builder
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

class Form_Builder extends \model\Database {

    function __construct() {

        parent::__construct();

    } // end construct


    /**
     * Form_Builder::create_form()
     * 
     * @param array $array
     * @return echo
     */
    public function create_form($array = array()) {

        // essential.
        extract(static::$form);

        // validation to make sure that parameters are correct
        $arg_list = func_get_args();
        // total number of arrays entered as a parameter = indicates the number of fields the user wants
        // $total_arrays array is used below
        // Minus the the initial form array
        $total_arrays = count($arg_list) - 1;
        $form_elms = array_values($arg_list['0']);
        $database = get_option($option_name);

        if (count(array_keys($form_elms)) != 9) {
            die('Make sure you enter nine values in the form builder array here');
        }

        foreach ($form_elms as $key => $value) {

            static $i = 1;

            switch ($key) {
                case 0:
                case 1:
                case 2:
                case 3:
                    if (!is_string($value) && $value != NULL) {
                        die('Make sure that all form parameters are a string');
                    }
                    break;

                case 0:
                    if ($value != 'post' || $value != 'get') {
                        die('The only form methods allowed are post or get');
                    }
                    break;

                case 2:
                    if ($value != 'application/x-www-form-urlencoded' || $value !=
                        'multipart/form-data' || $value != 'text/plain') {

                        die('Check the value you used for enctype in the create_form() method');
                    }
                    break;

            } // end switch

        } // end foreach

        unset($arg_list['0']);

        $field_list = array();
        $field_list = array_values($arg_list);

        extract(static::$form);
        // create form here

        $data = get_option($option_name);

        //$form = NULL;

        $form = "<form method=\"$form_elms[0]\" ";

        // add form attributes depending on the values of the parameters
        $form .= " action=\"$form_elms[1]\"";
        $form .= " name=\"$option_name\"";
        if ($form_elms['2'] != NULL) {
            $form .= ' enctype="'.$form_elms['2'].'" ';
        }
        $form .= ">";
        echo $form;

        $database = $this->check_options_table();

        if ($database && $dynamic_output && !empty($data[$option_name]) && $form_elms['4'] == TRUE) {

            $form = '<fieldset>';
            $form .= '<legend>Add new feeds below</legend>';
            $form .= '<table class="form-table">';
            $form .= '<tbody>';
            echo $form;

            $this->create_options_fields($total_arrays);

            $form = '</tbody>';
            $form .= '</table>';
            $form .= '</fieldset>';
            $form .= '<p>&nbsp;</p>';
            echo $form;

        } // and if

        $form = '<fieldset>';
        $form .= "<legend><span>$form_elms[3]</span></legend>";
        $form .= '<table class="form-table">';
        $form .= '<tbody>';

        // Create individual fields from the arrays entered into this method
        $x = count($field_list);

        for ($i = 0; $i <= $x - 1; $i++) {

            if (sizeof($field_list[$i]) == 6) {
                $form .= $this->individual_fields($field_list[$i]);
            } else {
                die('Please make sure that you use 6 arguments in the create_form() method fields arrays');
            } // end if

        } // end forloop

        $form .= '</tbody>';
        $form .= '</table>';

        // permenant settings for every form
        $form .= $this->perm_fields($total_arrays);
        $form .= '<p class="submit"><input type="submit" id="submit" name="'.$form_elms['5'].
            '" class="button-primary" value="';
            
        if(isset($_GET['unique_form'])){
            
            $form .= 'Update and Create';
            
        } else {
            
            $form .= 'Submit';
            
            
        }
        
        $form .= '"></p>';
        if ($form_elms['6'] != NULL) {
            $form .=
                '<p class="submit" style="margin-top:0;padding-top:0"><input type="submit"  name="'.
                $form_elms['6'].'" class="button-primary" value="Create only"></p>';
        }
        if ($form_elms['7'] != NULL) {
            $form .=
                '<p class="submit" style="margin-top:0;padding-top:0"><input type="submit"  name="'.
                $form_elms['7'].'" class="button-primary" value="Synchronize"></p>';
        }
        if (isset($form_elms['8']) && $form_elms['8'] != NULL) {
            $form .=
                '<p class="submit" style="margin-top:0;padding-top:0"><input type="submit"  name="'.
                $form_elms['8'].'" class="button-primary" value="Change"></p>';

        }
        $form .= '</fieldset>';
        $form .= '</form>';

        echo $form;

    }


    /**
     * Form_Builder::create_options_fields()
     * 
     * This method is either genius or mental
     * 
     * Description:
     * User generated field input is placed in a hidden field attached to the fields itself
     * This is saved in the database with the name attribute and its value
     * It is serialized but there was an error on unserialization
     * Presumably, this is becuase there is a clash with Option API serialization
     * So it is converted to hez using bin2hex
     * However, its reverse function hex2bin is only available in PHP 5.4
     * So the function is created in the model to replicated it
     * 
     * MUST be protected and not privte for the formOne object to work
     * 
     * @param array $field
     * @return calls individual_fields() method
     * 
     */

    protected function create_options_fields($total_fields) {

        $fields = $total_fields;

        // essential.
        extract(static::$form);
        $database = get_option($option_name);

        // radio_buttons added to array if radio buttons detected
        // remove to avoid error message
        if (isset($database['radio_buttons'])) {
            unset($database['radio_buttons']);
        }

        // checkboxes added to array if checkboxes detectged
        // remove to avoid error message
        if (isset($database['checkboxes_total'])) {
            unset($database['checkboxes_total']);
        }

        if (self::check_options_table() && $dynamic_output && !empty($database[$option_name])) { // only create extra forms if output is dynamic

            // loop through database nested array
            // This is essential for removing the delete checkboxes
            // They are regenerated below
            foreach ($database[$option_name] as $key => $value) {

                if (is_string($value)) continue;

                if (preg_grep('/checkboxDeletexyz/', $value)) {
                    unset($database[$option_name][$key]);
                } // end if

            } // end foreach

            // reset the array but start at one
            $keys = range(1, count($database[$option_name]));
            $values = array_values($database[$option_name]);
            $database[$option_name] = array_combine($keys, $values);

            // loop through database nested array
            foreach ($database as $key => $value) {

                // only use the inputs array and not any string
                if (is_string($value)) continue;

                foreach ($value as $b_key => $b_value) {

                    //eek! refactor
                    static $x = 1;
                    static $y = 1;
                    static $z = 1;
                    static $t = 1;
                    static $v = 1;

                    if (is_numeric($b_key)) {

                        if (is_string($b_value)) {
                            $fieldValue = $b_value;
                        } // end is_string

                        if ($b_key == $x++) {
                            if (is_string($b_value)) continue;
                            $field = array_values($b_value);
                        }

                        // The two separate arrays value needed to be joined together and then submitted to the individual_fields()
                        $user_data = array_values(unserialize(ah_hex2bin($field['0'])));

                        // Build up the attribute value data below

                        // remove previously generated numbers created by the create_options_fields() method
                        $user_data['1'] = preg_replace('/\d$/', '', $user_data['1']);

                        // This is essential to make sure all name values

                        if ($user_data['0'] === 'select') {
                            $user_data['4'] = $fieldValue;
                            $user_data['1'] = preg_replace('/kvbpy/', '', $user_data['1']);
                            $user_data['1'] = $user_data['1']."kvbpy";
                        }

                        // unique key for checkboxes
                        if ($user_data['0'] === 'checkbox') {
                            $user_data['1'] = preg_replace('/zqxjk/', '', $user_data['1']);
                            $user_data['1'] = $user_data['1']."zqxjk";
                        }

                        // unique key for radio buttons
                        if ($user_data['0'] === 'radio') {
                            $user_data['1'] = preg_replace('/zyxwv/', '', $user_data['1']);
                            $user_data['1'] = $user_data['1'].'zyxwv';
                        }

                        if ($y++ % $fields === 0) {
                            $user_data['1'] = $user_data['1'].$t++;
                        } else {
                            $user_data['1'] = $user_data['1'].$t;
                        }

                        if ($user_data['0'] !== 'radio' && $user_data['0'] !== 'checkbox') {
                            // Add previously generated user data to
                            $user_data['4'] = $fieldValue;
                        } elseif ($user_data['0'] === 'radio') {

                            if ($user_data['4'] === $fieldValue) {
                                $user_data['5'] = 'checked';
                            }

                        }

                        if ($user_data['0'] === 'text') {
                            $user_data['5'] = TRUE;
                        }

                        // This is to declare that the checked has previously been checked
                        if ($user_data['0'] === 'checkbox') {

                            if ($fieldValue !== '') {
                                $user_data['5'] = 'checked';
                            } else {
                                $user_data['5'] = 'blank';
                            }

                        }

                        // create field here
                        echo $this->individual_fields($user_data);

                        if ($z++ % $fields == 0) {
                            // add delete checkbox
                            $delete = array(
                                'input' => 'checkbox',
                                'name' => 'checkboxDeletexyz'.($v++),
                                'desc' => 'Delete above:',
                                'maxlength' => FALSE,
                                'value' => '1',
                                'select' => 'blank');

                            // create delete checkbox here
                            echo $this->individual_fields($delete);

                            //echo '</span>';

                        } // end == 0

                    } // end is_numeric

                } // end foreach

            } // end foreach

        } // end if

    }


    /**
     * Form_Builder::perm_fields()
     * 
     * @param string $total_fields
     * @return string $perm 
     */
    public function perm_fields($total_fields = null) {

        extract(static::$form);

        $perm = '<input type="hidden" name="option_page" value="';
        $perm .= $option_name;
        $perm .= '">';
        $perm .= '<input type="hidden" name="total_user_fields" value="'.$total_fields.'">';
        $perm .= wp_nonce_field("options_form_cov", "_wpnonce_options_cov", TRUE, FALSE);

        return $perm;

    }

    /**
     * Form_Builder::individual_fields()
     * Create the individual form fields here
     * 
     * @param array $array
     * @return return HTML field
     */
    protected function individual_fields($array = array()) {

        // create an array out of the parameter values
        $default = array(
            1 => 'type',
            2 => 'name',
            3 => 'desc',
            4 => 'maxlength',
            5 => 'value',
            6 => 'select',
            );

        $fields_essentials = array_combine($default, $array);

        // above combines the default array with the user input data to create a multidimensial array with
        // form attribute names and values

        extract(static::$form);

        foreach ($fields_essentials as $key => $value) {
            // There needs to be a dynamic number to keep the id unique so that the HTML validates
            static $i = 0;
            $i++;
            $value = trim((string )$value);

            if ($key == 'type') {

                switch ($value) {

                    case 'text':
                        // text area

                        //make sure name values are unique - if not throw error
                        $form = 'input type="text" ';

                        foreach ($fields_essentials as $key => $value) {

                            if ($key == 'name') {

                                if ($value != NULL) {
                                    $form .= " name=\"{$option_name}[" . esc_attr($value) . "]\" ";
                                    $name = esc_attr($value);
                                    $id = $name.'-'.$i;
                                    $form .= " id=\"$id\" ";

                                    if ($name == 'formMinRows' || $name == 'formMaxRows') {
                                        $form .= " class=\"small-text \" ";
                                    } else {
                                        $form .= " class=\"regular-text \" ";
                                    }

                                } else {
                                    die('You must provide a value for the name attribute');
                                }

                            }

                            if ($key == 'desc') {
                                $desc = (string )$value;
                            }

                            if ($key == 'maxlength') {
                                ($value != NULL) ? $form .= " maxlength=\"{$value}\" " : NULL;
                            }

                            if ($key == 'value') {

                                if ($value != NULL && $value != 'YES') {
                                    $form .= ' value="';
                                    $form .= isset($_POST[$option_name][$name]) ? esc_attr(stripslashes
                                        ($_POST[$option_name][$name])) : esc_attr($value);
                                    $form .= '"';
                                    $name_value = esc_attr($value);
                                }

                                if ($value == 'YES') {

                                    $form .= ' value="';
                                    $form .= isset($_POST[$option_name][$name]) ? esc_attr(stripslashes
                                        ($_POST[$option_name][$name])) : NULL;
                                    $form .= '"';
                                }

                            } // end if $key

                            if ($key == 'select') {

                                if ($value === TRUE) {
                                    $add_link = TRUE;
                                    $form .= ' disabled="disabled" ';
                                } else {
                                    $add_link = $value;
                                }

                            }

                        } // end foreach

                        $text = '<tr>';
                        $text .= "<th><label for=\"$id\">$desc</label></th>";
                        $text .= '<td><'.$form.' ></td>';
                        $text .= '<td>';
                        $text .= "<input type=\"hidden\" name=\"{$option_name}[$i][input_gen]\" value=\"".
                            bin2hex(serialize($fields_essentials))."\" >";
                        $text .= "<input type=\"hidden\" name=\"{$option_name}[$i][input_type]\" value=\"text\">";
                        $text .= "<input type=\"hidden\" name=\"{$option_name}[$i][file_name]\" value=\"\">";
                        if ($add_link === TRUE) {
                            $text .= '<div class="description">';

                            //check_table

                            if ($this->db_check_table($name_value) != '') {

                                $text .= '<p><a href="';
                                $text .= '?page='.$page_url.'&feed-list=total&unique_form='.$name_value;
                                $text .= '">Feed form</a></p>';

                            }

                            $text .= '<p><a href="';
                            $text .= '?page='.$page_url.'&feed-list=total&unique_name='.$name_value;
                            $text .= '">Edit feed details</a></p>';

                            $text .= '</div>';
                        }
                        if ($add_link == '') {
                            $text .= "<input type=\"hidden\" name=\"indName\" value=\"$add_link\">";
                        }
                        $text .= '</td>';
                        $text .= '</tr>';

                        return $text;

                        break;

                    case 'textarea':
                        // textarea technically not input

                        $form = 'textarea ';
                        foreach ($fields_essentials as $key => $value) {

                            if ($key == 'name') {

                                if ($value != NULL) {
                                    $form .= " name=\"{$option_name}[{$value}]\" ";
                                    $name = $value;

                                    $id = $name.'-'.$i;
                                    $form .= " id=\"$id\" ";

                                    $form .= " class=\"large-text \" ";

                                } else {
                                    die('You must provide a value for the name attribute');
                                }

                            }

                            if ($key == 'desc') {
                                $desc = (string )$value;
                            }

                            if ($key == 'maxlength') {
                                ($value != NULL) ? $form .= " maxlength=\"{$value}\" " : NULL;
                            }

                            if ($key == 'value') {

                                if ($value != NULL && $value != 'YES') {
                                    $textareaValue = isset($_POST[$option_name][$name]) ? esc_attr(stripslashes
                                        ($_POST[$option_name][$name])) : esc_attr($value);
                                }

                                if ($value == 'YES') {
                                    $textareaValue = isset($_POST[$option_name][$name]) ? esc_attr(stripslashes
                                        ($_POST[$option_name][$name])) : NULL;
                                }

                            } // end if $key

                        } // end foreach

                        $textarea = '<tr>';
                        $textarea .= "<th><label for=\"$id\">$desc</label></th>";
                        $textarea .= '<td><'.$form.' cols="50" rows="10">'.$textareaValue.
                            '</textarea></td>';
                        $textarea .= '<td>';

                        $textarea .= "<input type=\"hidden\" name=\"{$option_name}[$i][input_gen]\" value=\"".
                            bin2hex(serialize($fields_essentials))."\" >";

                        $textarea .= "<input type=\"hidden\" name=\"{$option_name}[$i][input_type]\" value=\"textarea\"></td>";
                        $textarea .= '</tr>';
                        return $textarea;

                        break;

                    case 'checkbox': // checkbox input

                        $form = 'input type="checkbox" ';
                        $max_value = NULL;
                        foreach ($fields_essentials as $key => $value) {

                            if ($key == 'name') {

                                if ($value != NULL) {

                                    if (!preg_match('/zqxjk/', $value)) {
                                        $value = $value.'zqxjk'; // checkbox attribute names need unique identifier
                                    }

                                    $form .= " name=\"{$option_name}[{$value}]\" ";
                                    $form_name = "{$option_name}[{$value}]";
                                    $name = $value;

                                    $id = $name.'-'.$i;

                                    $form .= " id=\"$id\" ";

                                } else {
                                    die('You must provide a value for the name attribute');
                                }

                            }

                            if ($key == 'desc') {
                                $desc = (string )$value;
                            }

                            if ($key == 'maxlength') {

                                if ($value == 1) {
                                    $max_value = 'checked';
                                }

                            }

                            if ($key == 'value') {

                                if ($value != NULL && $value != 'YES') {
                                    $form .= " value=\"$value\" ";
                                }
                                $form_value = $value;

                            } // end if

                            // if form has been previusly checked then select will be set to TRUE
                            if ($key == 'select') {

                                if ($value == NULL) {
                                    die('You must specify the number of checkboxes in the select array key');
                                }

                                // set total number of checkboxes as specified by the user in the form field array in views
                                if (preg_match('/^([0-9]+)$/', $value)) {
                                    $total = $value;
                                }

                                if (!isset($_POST[$option_name][$name])) {

                                    if ($value == 'checked' || $max_value == 'checked') {
                                        $form .= ' checked="checked" ';
                                    }

                                }

                                if (isset($_POST[$option_name][$name]) && $_POST[$option_name][$name] ==
                                    $form_value) {

                                    $form .= ' checked="checked" ';

                                }

                            }

                        } // endforeach

                        $checkbox = '<tr>';
                        $checkbox .= "<th scope=\"row\">$desc</th>";
                        $checkbox .= "<td><fieldset><input type=\"hidden\" name=\"$form_name\" value=\"\"><label for=\"$id\">";
                        $checkbox .= "<".$form." >";
                        $checkbox .= "<span class=\"screen-reader-text\">$desc</span></label>";

                        $checkbox .= "<input type=\"hidden\" name=\"{$option_name}[$i][input_gen]\" value=\"".
                            bin2hex(serialize($fields_essentials))."\" >";

                        $checkbox .= "<input type=\"hidden\" name=\"{$option_name}[$i][input_type]\" value=\"checked\">";
                        $checkbox .= "<input type=\"hidden\" name=\"{$option_name}[$i][checkbox_type]\" value=\"$name\">";
                        $checkbox .= "<input type=\"hidden\" name=\"{$option_name}[{$i}][checkbox_number]\" value=\"";
                        $checkbox .= isset($total) ? $total : NULL;
                        $checkbox .= '">';
                        $checkbox .= '</fieldset></td>';
                        $checkbox .= '</tr>';
                        return $checkbox;
                        break;

                    case 'radio': // radio input

                        $form = 'input type="radio" ';
                        foreach ($fields_essentials as $key => $value) {

                            if ($key == 'name') {

                                if ($value != NULL) {

                                    if (!preg_match('/zyxwv/', $value)) {
                                        $value = $value.'zyxwv'; // radio button attribute names need unique identifier
                                    }

                                    $form .= " name=\"{$option_name}[{$value}]\" ";
                                    $form_name = "{$option_name}[{$value}]";
                                    $name = $value;

                                    $id = $name.'-'.$i;
                                    $form .= " id=\"$id\" ";

                                    $form .= " class=\"tog \"";

                                } else {
                                    die('You must provide a value for the name attribute');
                                }

                            }

                            if ($key == 'desc') {
                                $desc = (string )$value;
                            }

                            if ($key == 'maxlength') {
                                ($value != NULL) ? NULL : NULL;
                            }

                            if ($key == 'value') {

                                if ($value != NULL && $value != "YES") {
                                    $form .= " value=\"$value\" ";
                                }

                                if (isset($_POST[$option_name][$name]) && $_POST[$option_name][$name] ==
                                    $value) {
                                    $form .= ' checked="checked" ';
                                } else {
                                    $form .= '';
                                }

                            } // end if

                            // if form has been previusly checked then select will be set to TRUE
                            if ($key == 'select') {

                                if ($value != '') {

                                    if (!preg_match('/kvbpy/', $value)) {
                                        $value = $value.'kvbpy'; // radio button attribute names need unique identifier
                                    }

                                    if ($value == 'checked') {
                                        $total = (int)$value;
                                    } elseif ($value == 'checked') {
                                        $form .= ' checked="checked" ';
                                    }

                                } else {
                                    die('You must specify the number of radio buttons in the form');
                                }

                            }

                        } // endforeach

                        $radio = '<tr>';
                        $radio .= "<th scope=\"row\">$desc</th>";
                        $radio .= "<td><label for=\"$id\"><".$form." >";
                        $radio .= "<span class=\"screen-reader-text\">$desc</span></label>";

                        $radio .= "<input type=\"hidden\" name=\"{$option_name}[{$i}][input_gen]\" value=\"".
                            bin2hex(serialize($fields_essentials))."\" >";

                        $radio .= '</td>';
                        $radio .= "<td><input type=\"hidden\" name=\"{$option_name}[{$i}][field_type]\" value=\"radio\">";
                        $radio .= "<input type=\"hidden\" name=\"{$option_name}[{$i}][radio_number]\" value=\"";
                        $radio .= isset($total) ? $total : NULL;
                        $radio .= '">';
                        $radio .= '</td>';
                        $radio .= '</tr>';
                        return $radio;
                        break;


                    case 'file': // file input

                        $form = 'input type="file" ';
                        foreach ($fields_essentials as $key => $value) {

                            if ($key == "name") {

                                if ($value != NULL) {
                                    $form .= " name=\"{$option_name}[{$i}][{$value}]\" ";
                                    $form_name = " name=\"{$option_name}[{$i}][{$value}]\" ";
                                    $name = $value;

                                    $id = $name.'-'.$i;
                                    $form .= " id=\"$id\" ";

                                } else {
                                    die('You must provide a value for the name attribute');
                                }

                            }

                            if ($key == 'desc') {
                                $desc = (string )$value;
                            }

                            if ($key == 'maxlength') {
                                ($value != NULL) ? NULL : NULL;
                            }

                            if ($key == 'value') {

                                if ($value != NULL && $value != 'YES') {
                                    NULL;
                                }

                            } // end if

                            if ($key == 'select') {

                                if ($value === TRUE) {
                                    $add_link = TRUE;
                                    $form .= ' disabled="disabled" ';
                                } else {
                                    $add_link = $value;
                                }

                            }

                        } // endforeach

                        $file = '<tr>';
                        $file .= "<th><label for=\"$id\">$desc</label></th>";
                        $file .= '<td><'.$form.' ></td>';
                        $file .= "<td><input type=\"hidden\" name=\"{$option_name}[{$i}][input_gen]\" value=\"".
                            bin2hex(serialize($fields_essentials))."\" >";
                        $file .= "<input type=\"hidden\" name=\"{$option_name}[$i][input_type]\" value=\"file\">";
                        $file .= "<input type=\"hidden\" name=\"{$option_name}[$i][file_name]\" value=\"\">";
                        if ($add_link != '') {
                            $file .= "<input type=\"hidden\" name=\"indName\" value=\"$add_link\">";
                        }
                        $file .= '<input type="hidden" name="MAX_FILE_SIZE" value="2097152" /></td>';
                        $file .= '</tr>';
                        return $file;
                        break;


                    case 'select': // select stuff here

                        $form = 'select';
                        foreach ($fields_essentials as $key => $value) {

                            if ($key == "name") {

                                if ($value != NULL) {
                                    $form .= " name=\"{$option_name}[{$value}]\" ";
                                    $name = $value;

                                    $id = $name.'-'.$i;
                                    $form .= " id=\"$id\" ";

                                } else {
                                    die('You must provide a value for the name attribute');
                                }

                            }

                            if ($key == 'desc') {
                                $desc = (string )$value;
                            }

                            if ($key == 'maxlength') {
                                $max_value = $value;
                            }

                            if ($key == 'value') {

                                if (preg_match('/^(select)([0-9]+)$/', $value)) {
                                    preg_match('/([0-9]+)$/', $value, $match);
                                    $selected = (int)array_pop($match);
                                }

                            } // end if

                            if ($key == 'select') {

                                $output = array();
                                $output[] = '<option value="0"></option>';
                                foreach ($value as $key => $value) {

                                    $key = $key + 1;

                                    if (isset($_POST[$option_name][$name])) {

                                        preg_match('/([0-9]+)$/', $_POST[$option_name][$name], $match);
                                        $new_match = (int)array_pop($match);

                                        if ($_POST[$option_name][$name] == $value) {
                                            $output[] = "<option selected=\"selected\" value=\"$value\">$value</option>";
                                        } else {
                                            $output[] = "<option value=\"$value\">$value</option>";
                                        }

                                    }

                                    if (!isset($_POST[$option_name][$name])) {

                                        if ($dynamic_output && $max_value != NULL) {

                                            if ($value == $max_value) {
                                                $output[] = "<option selected=\"selected\" value=\"$value\">$value</option>";
                                            } else {
                                                $output[] = "<option value=\"$value\">$value</option>";
                                            }

                                        } else {
                                            $output[] = "<option value=\"$value\">$value</option>";
                                        }

                                    }

                                } // end foreach

                            } // end if select
                        } // endforeach

                        $select = '<tr>';
                        $select .= "<th scope=\"row\">";
                        $select .= "<label for=\"{$id}\">{$desc}</label>";
                        $select .= "</th>";
                        $select .= "<td>";
                        $select .= "<".$form.">";
                        foreach ($output as $result) {

                            $select .= $result;

                        }
                        $select .= "</select>";

                        $select .= "<input type=\"hidden\" name=\"{$option_name}[{$i}][input_gen]\" value=\"".
                            bin2hex(serialize($fields_essentials))."\">";

                        $select .= "<input type=\"hidden\" name=\"{$option_name}[{$i}][field_type]\" value=\"select\">";
                        $select .= '</td>';
                        $select .= '</tr>';
                        return $select;

                        break;

                    case 'button':
                    case 'image':
                    case 'password':
                    case 'reset':
                    case 'submit':
                    case 'file': // error here - there form inputs are not cattered for
                        die("You cannot use the individual_fields() method to create inputs for $key");
                        break;
                    default: // error message here
                        die("Make sure the input type in the individual_fields() method is correct");
                        break;
                } // end switch statement

            } // end fi

        } // end foreach

    }

    public function create_form_interface() {

        $this->create_form();

    }


}
