<?php namespace view;

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

class Log {

    /**
     * Log::__construct()
     * 
     * @return
     */
    function __construct() {


    } // end construct


    /**
     * Log::read_file()
     * 
     * @return
     */
    protected static function read_file() {

        if (file_exists(AH_LOG_FILE)) {

            $data = file(AH_LOG_FILE);
            return $data;

        } // end if

    }

    /**
     * Log::read_top_lines()
     * 
     * @return
     */
    protected static function read_top_lines() {

        $file = static::read_file();

        $form = '<h3>Log file</h3>';

        if ($file != NULL) {

            $file = array_reverse($file);

            static $i = 1;
            $form .= '<ul>';
            foreach ($file as $result) {

                $form .= $result;
                if ($i++ == 15) break;

            } // end foreach
            $form .= '</ul>';

        } // end if($file != NULL) {

        return $form;

    }

    /**
     * Form_View::log()
     *
     * Displays page of database activity associated with Affiliate Hoover
     *
     * @return array of data / html
     */

    public static function log() {

        return static::read_top_lines();

    }

}
