<?php namespace view;
use ah_pagination;

/**
 * Tracking_Form 
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

class Tracking_Form extends \view\View_Initialise {

    function __construct() {

        parent::__construct();
        
    } // end construct

    protected function tracking_form() {

        echo '<h3>Tracks clicks to external affiliate links</h3>';
        echo '<p>This is an experimental feature. A class is automatically added to all external links when the posts are created. When a user clicks on such a link then their activity is recorded into a database.</p>';

        if (isset($_POST['submitTracking'])) {

            if (isset($_POST['ah_tracking_chq'])) {

                $var = TRUE;

            } else {

                $var = NULL;

            }

            $this->db_update_options($var);

            echo self::$success->success_message('You have changed the tracking settings');

        } // end if

        echo $this->turn_off_tracking();

        echo $this->create_table();

    }

    /**
     * Tracking_Controller::turn_off_tracking()
     * 
     * Form for turning off tracking
     * 
     * @return string
     */
    protected function turn_off_tracking() {

        $option = get_option('ah_tracking');

        $form = '<form action="#" method="post">';
        $form .= '<fieldset><legend>Click checkbox to turn off tracking</legend>';
        $form .= '<table class="form-table">';
        $form .= '<tbody>';
        $form .= '<tr valign="top">';
        $form .= '<th scope="row">Turn off tracking</th>';
        $form .= '<td><input id="plugin_chk1" name="ah_tracking_chq" value="1" type="checkbox" ';
        $form .= isset($_POST['ah_tracking_chq']) || ($option === "1") ? ' checked="checked" ' : null;
        $form .= '></td>';
        $form .= '</tr></tbody></table>';
        $form .= '<p class="submit"><input name="submitTracking" type="submit" class="button-primary" value="Save Changes"></p>';
        $form .= '</fieldset>';
        $form .= '</form>';

        return $form;

    }

    /**
     * Tracking_Form::create_table()
     * 
     * Used for presenting tracking data
     * 
     * @return string
     */
    protected function create_table() {

        $total = $this->db_total_tracking_entries();

        $form = '';

        if ($total) {

            $pag = new ah_pagination;

            $pag->items($total);
            $pag->limit(15); // Limit entries per page
            $pag->target(admin_url("/options-general.php?page=affiliate-hoover-plugin-admin&tracking=here"));
            $pag->currentPage($pag->page); // Gets and validates the current page
            $pag->calculate(); // Calculates what to show
            $pag->parameterName('paging');
            $pag->adjacents(1); //No. of page away from the current page

            if (!isset($_GET['paging'])) {
                $pag->page = 1;
            } else {
                $pag->page = $_GET['paging'];
            }

            //Query for limit paging
            $limit = "LIMIT ".($pag->page - 1) * $pag->limit.", ".$pag->limit;

        }

        if (isset($limit) && $limit != NULL) {

            $form .= '<div class="tablenav">';
            $form .= '<div class="tablenav-pages">';
            $form .= '<span class="displaying-num">'.$total.' items</span>';
            $form .= $pag->show();
            $form .= '</div>';
            $form .= '</div>';

            $data = $this->db_get_all($limit);

        }

        if (isset($data) && $data != NULL) {

            $form .= '<table class="form-table">';
            $form .= '<thead>';
            $form .= '<tr>';
            $form .= '<th scope="row">Date</th>';
            $form .= '<th scope="row">Post</th>';
            $form .= '<th scope="row">IP</th>';
            $form .= '<th scope="row"># clicks</th>';
            $form .= '</tr>';
            $form .= '</thead>';
            $form .= '<tbody>';

            foreach ($data as $result) {

                $post = (int)$result->post_id;

                $post = get_post($post, OBJECT);

                $form .= '<tr>';
                $form .= '<td>';
                $date = date_create($result->date);
                $form .= date_format($date, 'h:i:s A, l jS \of F Y');
                $form .= '</td>';
                $form .= '<td>';
                if ($post) {
                    $form .= $post->post_title;
                } else {
                    $form .= "Deleted post";
                }
                $form .= '</td>';

                $form .= '<td>';
                $form .= $result->ip;
                $form .= '</td>';
                $form .= '<td>';
                $form .= 1;
                $form .= '</td>';
                $form .= '</tr>';

            }
            $form .= '</tbody></table>';

        } else {

            $form .= '<p>No entries yet</p>';

        } // end if($data) {

        return $form;

    }

    public function tracking_form_facade() {

        return $this->tracking_form();

    }

}
