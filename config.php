<?php include_once ("constants.php");
include_once ('functions.php');

$paths = array(
    AH_PLUGINNAME_PATH.AH_First_LIBS.AH_DS,
    AH_PLUGINNAME_PATH.AH_First_CONTROLLER.AH_DS,
    AH_PLUGINNAME_PATH.AH_First_VIEW.AH_DS,
    AH_PLUGINNAME_PATH.AH_First_MODEL.AH_DS,
    get_include_path(),
    );

set_include_path(implode(PATH_SEPARATOR, $paths));

function __autoload($classname) {

    $file = $classname.'.php';
    
    if (stristr(dirname(stream_resolve_include_path($file)), plugin_basename(__DIR__ ))) {
    
        if (file_exists(stream_resolve_include_path($file)) && is_file(stream_resolve_include_path($file))) {
            
            @require_once($file);

        }

    }

}

require_once ('Initialise.php');
require_once ('View_Initialise.php');