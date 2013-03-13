<?php namespace controller;

/**
 * Reset_Form_Processing
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
class Reset_Form_Processing extends \view\View_Initialise {

    /**
     * Reset_Form_Processing::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();

    } // end construct


    /**
     * Reset_Form_Processing::reset_form_processing_sanitisation()
     * 
     * @param array $post_form
     * @return
     */
    public function reset_form_processing_sanitisation($post_form) {

        // ESSENTIAL! Do not leave this out. Needs to come first
        $form = self::$check->security_check($post_form);

        return $form;

    }


    /**
     * Reset_Form_Processing::reset_form_processing_validation()
     * 
     * @param mixed $form
     * @return
     */
    public function reset_form_processing_validation($form) {
        
    

    }


}
