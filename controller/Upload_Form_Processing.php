<?php namespace controller;

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
 */
class Upload_Form_Processing extends \view\View_Initialise {

    /**
     * Upload_Form_Processing::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();

    } // end construct


    /**
     * Upload_Form_Processing::upload_form_processing_sanitisation()
     * 
     * @param array $post_form
     * @return
     */
    public function upload_form_processing_sanitisation($post_form) {

        // ESSENTIAL! Do not leave this out. Needs to come first
        $form = self::$check->security_check($post_form);

        return $form;

    }


    /**
     * Upload_Form_Processing::upload_form_processing_validation()
     * 
     * @param array $form
     * @return
     */
    public function upload_form_processing_validation($form) {
        
        // essential.
        extract(static::$form);

        $error = array();

        if ($_FILES[$option_name]['name']['1']['feedFile'] == '') {
            $error[] = 'Opps! You forgot to add a file';
        }

        if (!self::$check->check_file_ext($_FILES[$option_name], 'feedFile')) {
            $error[] = 'Only upload CSV files';
        }

        if (!self::$check->check_file_duplicate($_FILES[$option_name], 'feedFile')) {
            $error[] = 'Opps, you appear to be already using this file';
        }

        if (!self::$check->check_file_error($_FILES[$option_name], 'feedFile')) {
            $error[] = 'Sorry the maximum file upload size is '.($max_up / 1048576).'MBs';
        }

        return $error;

    }


}
