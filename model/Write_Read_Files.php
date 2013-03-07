<?php namespace model;

/**
 * Write_Read_Files
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

class Write_Read_Files  {

    /**
     * Write_Read_Files::__construct()
     * 
     * @return
     */
    function __construct() {

    } // end construct


    /**
     * Write_Read_Files::write_file()
     * 
     * @param string $info
     * @return
     */
    public function write_file($info) {

        $fh = fopen(AH_LOG_FILE, 'a+');
        fwrite($fh, '<li>'.$info.'</li>'."\n");
        fclose($fh);

    }

    /**
     * Write_Read_Files::read_file()
     * 
     * @return
     */
    public static function read_file() {

        if (file_exists(AH_LOG_FILE)) {

            $data = file(AH_LOG_FILE);
            return $data;

        } // end if

    }


}
