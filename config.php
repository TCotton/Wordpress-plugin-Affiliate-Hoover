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

/* Using __autoload() in Wordpress does odd things */

/* library */

require_once ('Datasource.php');
require_once ('pagination.class.php');

/* model */

require_once ('Initialise.php');
require_once ('Database.php');
require_once ('Write_Read_Files.php');
require_once ('Handle_Files.php');
require_once ('Synchronize_Feeds.php');

/* controller */

require_once ('Validation_Sanitisation_Success.php');
require_once ('Validation_Sanitisation.php');
require_once ('Create_Individual_Feeds.php');
require_once ('Update_Individual_Feeds.php');

/* view */

require_once ('Form_Builder.php');
require_once ('View_Initialise.php');

/* controller */

require_once ('Main_Form_Processing.php');
require_once ('Upload_Form_Processing.php');
require_once ('Reset_Form_Processing.php');
require_once ('Feed_Form_Processing.php');

/* view */

require_once ('Log.php');
require_once ('Main_Form.php');
require_once ('Upload_Form.php');
require_once ('Reset_Form.php');
require_once ('Feed_Form.php');
require_once ('Tracking_Form.php');
