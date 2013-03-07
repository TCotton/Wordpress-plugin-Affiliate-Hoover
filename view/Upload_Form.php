<?php namespace view;

/**
 * Upload_Form
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

class Upload_Form extends \view\View_Initialise {

    protected static $processing;

    /**
     * Upload_Form::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();

        if (!(self::$processing instanceof \controller\Upload_Form_Processing)) {
            self::$processing = new \controller\Upload_Form_Processing();
        }

    } // end construct

    /**
     * Upload_Form::upload_form()
     * 
     * @return
     */
    protected function upload_form() {

        if (isset($_GET['unique_name'])) {

            // essential.
            extract(self::$form);

            if (isset($_POST['submitInd'])) {

                $error = array();
                $form = self::$processing->upload_form_processing_sanitisation($_POST);
                $error = self::$processing->upload_form_processing_validation($form);

                if (empty($error)) {

                    $feed_file_value = TRUE;
                    echo self::$files->update_record($form);

                } else {

                    echo self::$check->failure_message_interface($error);

                } // end if error

            } // end if isset($_POST['submitInd'])

            $max_up = ah_max_upload();
            $feed_file_value = FALSE;

            // CHANGE THIS - only need cron data and
            $form_data = $this->db_select_all($_GET['unique_name']);
            echo '<h3>Feed details for '.$_GET['unique_name'].'</h3>';
            echo '<div id="ind-result">';

            echo "</div><!-- end 'ind-result' -->";

            // populate form with previously saved values

            if ($this->db_check_table($_GET['unique_name']) == NULL) {

                $feed_url_value = 'YES';

            } else {

                $item = $this->db_select_all($_GET['unique_name']);
                if ($item->URL == '') {
                    $feed_url_value = 'YES';
                } else {
                    $feed_url_value = $item->URL;
                }

            }

            if (isset($item)) {

                echo '<p><strong>Max upload size for this server is <em>'.($max_up / 1048576).
                    'MBs</em></strong></p>';


                echo '<p>File can be found here: '.'<strong>'.AH_FEEDS_DIR.$item->fileName.
                    '</strong></p>';

                echo '<p><a href="?page='.$page_url.'&feed-list=total&unique_form='.$item->name.
                    '">Edit this form feed here</a></p>';

            } else {

                echo '<p>You have not uploaded a flle for <strong><em>'.$_GET['unique_name'].
                    ' </em></strong>yet!</p>';
            }

            $feed_file = array(
                'input' => 'file', // input type
                'name' => 'feedFile', // name attribute
                'desc' => 'Add file here', // for use in input label
                'maxlength' => FALSE, // max attribute
                'value' => $feed_file_value, // value attribute
                'select' => $_GET['unique_name'] // array only for the select input
                    );

            $form = array(
                'method' => 'post',
                'action' => '#wpbody',
                'enctype' => 'multipart/form-data',
                'description' => '<h3>Individual feed details</h3>',
                'option' => FALSE,
                'submit' => 'submitInd',
                'submtiTwo' => NULL,
                'synchronize' => NULL,
                'tracking' => NULL);

            self::$form_builder->create_form($form, $feed_file);

        } // end if isset($_GET['unique_name'])


    }


    /**
     * Upload_Form::upload_form_facade()
     * 
     * @return
     */
    public function upload_form_facade() {

        $this->upload_form();

    }


}
