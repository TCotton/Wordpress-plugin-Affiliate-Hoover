<?php

namespace Config;
include_once ("constants.php");
/**
 * Configuration
 * 
 * @package Affiliate Hoover
 * @author Andy Walpole
 * @copyright Andy Walpole
 * @version 
 * @access public
 */

class Configuration {

    private static $model = AH_First_MODEL;
    private static $controller = AH_First_CONTROLLER;
    private static $view = AH_First_VIEW;
    private static $libs = AH_First_LIBS;

    private static $link_array = array();

    function __construct() {

        self::connect_lib();
        self::connect_model();
        self::connect_controller();
        self::connect_view();

    } // end construct

    /**
     * Configuration::connect_lib()
     * 
     * Loops through content of libs directory and adds files to array
     * 
     * @return adds to array
     */
    private static function connect_lib() {

        foreach (scandir(AH_PLUGINNAME_PATH.self::$libs) as $result) {

            if (preg_match('/\.php$/', $result)) {

                array_push(self::$link_array, self::$libs.AH_DS.$result);

            }

        } // end foreach loop

    } // end connect_lib

    /**
     * Configuration::connect_model()
     * 
     * Loops through content of model directory and adds files to array
     * 
     * @return adds to array
     */
    private static function connect_model() {

        foreach (scandir(AH_PLUGINNAME_PATH.self::$model) as $result) {

            if (preg_match('/class-.*\.php$/', $result)) {

                array_push(self::$link_array, self::$model.AH_DS.$result);

            } // end preg_match

        } // end foreach loop

    } // end connect_model

    /**
     * Configuration::connect_controller()
     * 
     * Loops through content of controller directory and adds files to array
     * 
     * @return adds to array
     */
    private static function connect_controller() {

        foreach (scandir(AH_PLUGINNAME_PATH.self::$controller) as $result) {

            if (preg_match('/class-.*\.php$/', $result)) {

                array_push(self::$link_array, self::$controller.AH_DS.$result);

            } // end preg_match

        } // end foreach loop

    } // end connect_controller

    /**
     * Configuration::connect_view()
     * 
     * Loops through content of view directory and adds files to array
     * 
     * @return adds to array
     */
    private static function connect_view() {

        foreach (scandir(AH_PLUGINNAME_PATH.self::$view) as $result) {

            if (preg_match('/class-.*\.php$/', $result)) {

                array_push(self::$link_array, self::$view.AH_DS.$result);

            } // end preg_match

        } // end foreach loop

    } // end connect_view

    /**
     * Configuration::total_files()
     * 
     * @return array
     */
    public static function total_files() {

        return self::$link_array;

    }


} // end class

new \Config\Configuration;
