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
            echo '<span class="displaying-num">' . $total  . ' items</span>';
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