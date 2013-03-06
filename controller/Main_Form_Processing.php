<?php namespace controller;

/**
 * Main_Form
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

class Main_Form_Processing extends \view\View_Initialise {

    function __construct() {

        parent::__construct();

    } // end construct
    
    
    
    public function main_form_processing_sanitisation($post_form) {
        
         // ESSENTIAL! Do not leave this out. Needs to come first
        $form = self::$check->security_check($post_form);
        
        // SANITIZE

        self::$check->trim_post($form, 'siteName', TRUE);

        self::$check->stripslashes($form, 'siteName', TRUE);
        
        return $form;
        
        
    }
    

    public function main_form_processing_validation($post_form) {

        $error = array();
        $empty = 0;

        // EMPTY VALUES
        
        if (self::$check->empty_value($post_form) === FALSE) {
            $error[] = 'Please don\'t leave any input values empty';
        }

        // Make sure that none of the form values are duplicates
        if (self::$check->duplicate_entries($post_form) === FALSE) {
            $error[] = 'Please make sure that all feed names are unique';
        }

        if (self::$check->alnum($post_form, 'siteName') === FALSE) {
            $error[] = 'Please make sure that you don\'t use any special characters or white spaces for a name';
        }
        
     
        return $error;

    }


}
