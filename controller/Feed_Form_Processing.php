<?php namespace controller;

/**
 * Feed_Form_Processing
 * 
 * @package Affiliate Hoover
 * @author Andy Walpole
 * @copyright Andy Walpole
 * @link http://andywalpole.me/
 * @access public
 * @license GPLv2: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 */

class Feed_Form_Processing extends \view\View_Initialise {

    /**
     * Feed_Form_Processing::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();

    } // end construct


    /**
     * Feed_Form_Processing::feed_form_processing_sanitisation()
     * 
     * @param array $post_form
     * @return
     */
    public function feed_form_processing_sanitisation($post_form) {

        // ESSENTIAL! Do not leave this out. Needs to come first
        $form = self::$check->security_check($post_form);

        self::$check->sanitize($form, 'stripslashes');
        self::$check->sanitize($form, 'trim_post');

        return $form;
    }


    /**
     * Feed_Form_Processing::feed_form_processing_validation()
     * 
     * @param array $form
     * @return
     */
    public function feed_form_processing_validation($form) {
        
        $error = array();

        if (!self::$check->empty_value($form, 'formTitle')) {
            $error[] = 'Please don\'t leave the title empty';
        }

        if (!self::$check->empty_value($form, 'formBody')) {
            $error[] = 'Please don\'t leave the body empty';
        }

        if (!self::$check->title_check($form, 'formTitle')) {
            // title_check
            $error[] =
                'Only include one code such as [#3#] and nothing else for the title. You can change the title once the form has been created.';
        }

        if (!self::$check->empty_value($form, 'formPostType')) {
            // title_check
            $error[] = 'Please pick a post type';
        }

        if (!self::$check->empty_value($form, 'formPostStatus')) {
            // title_check
            $error[] = 'Please choose whether you would like to save the item as draft or published';
        }

        $formMinRows = FALSE;
        $formMaxRows = FALSE;
        if (!self::$check->empty_value($form, 'formMinRows')) {
            $formMinRows = TRUE;
        }

        if (!self::$check->empty_value($form, 'formMaxRows')) {
            $formMaxRows = TRUE;
        }

        if (($formMinRows === TRUE && $formMaxRows === FALSE) || ($formMinRows === FALSE && $formMaxRows === TRUE)) {
            $error[] = 'Please make sure that set both a min rows number and a max rows number';
        }

        return $error;

    }

}
