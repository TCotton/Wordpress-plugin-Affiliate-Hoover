<?php namespace model;
use File_CSV_DataSource;

/**
 * Upload_Form_Processing
 * 
 * @package Affiliate Hoover
 * @author Andy Walpole
 * @copyright Andy Walpole
 * @link http://andywalpole.me/
 * @access public
 * @license GPLv2: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Wordpress functions used:
 * 
 * wp_redirect():
 * http://codex.wordpress.org/Function_Reference/wp_redirect
 * admin_url():
 * http://codex.wordpress.org/Function_Reference/admin_url
 * 
 */
class Handle_Files extends \model\Database {

    function __construct() {

        parent::__construct();

    } // end construct


    /**
     * Upload_Form_Processing::update_record()
     * 
     * This downloads files from URL or from uploading
     * After this the filename is then used to create a row in the feed_details table
     * 
     * @param array $form
     * 
     */


    public function update_record($form) {

        //essential
        extract(static::$form);

        $fileName = $this->move_file($_FILES[$option_name]);
        $header_array = $this->parse_csv_head(AH_FEEDS_DIR.$fileName);

        $header_array_amend = $header_array;
        //count total number of entries
        $num_rows = $this->count_csv_rows(AH_FEEDS_DIR.$fileName);
        foreach ($header_array_amend as $key => $value) {
            $header_array_amend['[#'.$key.'#]'] = $value;
            unset($header_array_amend[$key]);
        } // end foreach


        if ($fileName != NULL) {

            if ($this->db_insert_table($form['indName'], null, $fileName, serialize($header_array),
                serialize($header_array_amend), $num_rows)) {
                ob_start();
                wp_redirect(admin_url('/options-general.php?page='.$page_url));
                ob_end_flush();
                exit;
            }


        }

    }


    /**
     * Upload_Form_Processing::move_file()
     * 
     * @param array $file_raw 
     * @return string
     * 
     */

    protected function move_file($file_raw) {

        $file = apply_filters('wp_handle_upload_prefilter', $file_raw);

        $tmp_name = '';
        $name = '';

        foreach ($file as $key => $value) {

            if ($key == 'tmp_name') {

                foreach ($value as $new_key => $new_value) {

                    $tmp_name = implode($new_value);

                } // end foreach

            } // end if

            if ($key == 'name') {

                foreach ($value as $new_key => $new_value) {

                    $name = implode($new_value);

                } // end foreach

            } // end if

        } // end foreach

        if ($tmp_name != '' && $name != '') {
        
            $new_name = sanitize_file_name($name);

            if (rename($tmp_name, AH_FEEDS_DIR.$new_name)) {
                return $new_name;
            }

        } // end if

    }


    /**
     * Upload_Form_Processing::parse_csv_head()
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
     * Upload_Form_Processing::count_csv_rows()
     * 
     * counts entire number of rows
     * 
     * @param object $file
     * @return array || boolean
     * 
     */

    public function count_csv_rows($file) {

        $csv = new File_CSV_DataSource;

        if ($csv->load($file)) {

            return $csv->countRows();

        } else {

            return FALSE;

        } // end if ($csv->sadfsad
    }


}
