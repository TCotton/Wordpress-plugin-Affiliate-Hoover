<?php namespace view;

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

class Main_Form extends \view\View_Initialise {

    /**
     * @var \controller\Main_Form_Processing
     */
    protected static $processing;

    /**
     * Main_Form::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();

    } // end construct


    /**
     * Main_Form::main_form()
     * 
     * @return
     */
    protected function main_form() {

        if (!(self::$processing instanceof \controller\Main_Form_Processing)) {
            self::$processing = new \controller\Main_Form_Processing();
        }

        if (isset($_POST['submitLar'])) {

            $form = self::$processing->main_form_processing_sanitisation($_POST);
            $error = self::$processing->main_form_processing_validation($form);

            if (empty($error)) {

                echo self::$success->update_option_interface($form);

            } else {

                echo self::$check->failure_message_facade($error);

            } // end if error

        }

        echo '</div>';

        $site_name = array(
            'input' => 'text', // input type
            'name' => 'siteName', // name attribute
            'desc' => 'Feed name:', // for use in input label
            'maxlength' => '200', // max attribute
            'value' => 'YES', // value attribute
            'select' => FALSE // array only for the select input
                );

        $form = array(
            'method' => 'post',
            'action' => '#result',
            'enctype' => 'application/x-www-form-urlencoded',
            'description' => '<h3>Add your new feeds here</h3>',
            'option' => TRUE,
            'submit' => 'submitLar',
            'submtiTwo' => NULL,
            'synchronize' => NULL,
            'tracking' => NULL,
            );

        self::$form_builder->create_form($form, $site_name);

    }


    /**
     * Main_Form::main_form_facade()
     * 
     * @return
     */
    public function main_form_facade() {

        $this->main_form();

    }

}
