<?php

namespace TrackController;
use TrackModel;
use pagination;

/**
 * @author Andy Walpole
 * @date 10/6/2012
 * 
 */

class Tracking_Controller extends \TrackModel\Tracking_Model {

    function __construct() {


    }

    public function turn_off_tracking() {
        
        $option = get_option('ah_tracking');
        
        echo '<form action="#" method="post">';
        echo '<fieldset><legend>Click checkbox to turn off tracking</legend>';
        echo '<table class="form-table">';
        echo '<tbody>';
        echo '<tr valign="top">';
        echo '<th scope="row">Turn off tracking</th>';
        echo '<td><input id="plugin_chk1" name="ah_tracking_chq" value="1" type="checkbox" ';

        isset($_POST['ah_tracking_chq']) || ($option === "1")? print
            ' checked="checked" ' : null;
            
        echo '></td>';
        echo '</tr></tbody></table>';
        echo '<p class="submit"><input name="submitTracking" type="submit" class="button-primary" value="Save Changes"></p>';
        echo '</fieldset>';
        echo '</form>';

    }


    public function create_table() {

        $total = $this->total_entries();

        if ($total) {

            $pag = new pagination;

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

            echo '<div class="tablenav">';
            echo '<div class="tablenav-pages">';
            echo '<span class="displaying-num">'.$total.' items</span>';
            echo $pag->show();
            echo '</div>';
            echo '</div>';

            $data = $this->get_all($limit);

        }

        if (isset($data) && $data != NULL) {

            $table = '<table class="form-table">';
            $table .= '<thead>';
            $table .= '<tr>';
            $table .= '<th scope="row">Date</th>';
            $table .= '<th scope="row">Post</th>';
            $table .= '<th scope="row">IP</th>';
            $table .= '<th scope="row"># clicks</th>';
            $table .= '</tr>';
            $table .= '</thead>';
            $table .= '<tbody>';
            echo $table;

            foreach ($data as $result) {

                $post = (int)$result->post_id;

                $post = get_post($post, OBJECT);

                if ($post === NULL) continue;

                echo '<tr>';
                echo '<td>';
                $date = date_create($result->date);
                echo date_format($date, 'h:i:s A, l jS \of F Y');
                echo '</td>';
                echo '<td>';
                echo $post->post_title;
                echo '</td>';

                echo '<td>';
                echo $result->ip;
                echo '</td>';
                echo '<td>';
                echo 1;
                echo '</td>';
                echo '</tr>';

            }
            echo '</tbody></table>';

        } else {

            echo '<p>No entries yet</p>';

        } // end if($data) {

    }

}

?>