<?php namespace view;

/**
 * Reset_Form
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

class Reset_Form extends \view\View_Initialise {

    protected static $processing;

    /**
     * Reset_Form::__construct()
     * 
     * @return
     */
    function __construct() {

        parent::__construct();


    } // end construct

    /**
     * Reset_Form::reset()
     *
     * reset page
     *
     * @return array of data / html
     */
    protected function reset() {

        if (!(self::$processing instanceof \controller\Reset_Form_Processing)) {
            self::$processing = new \controller\Reset_Form_Processing();
        }

        // essential.
        extract(self::$form);

        $return = '<h3>Reset feed data</h3>';
        $return .= '<h4>All these features require mass deletions of posts. For the love of god <em>please</em> make a database backup before you use them.</h4>';
        $return .= '<p>You may be having trouble uploading new data from a file you have been using. The following fixes may help</p>';
        $return .= '<h3>Remove all trace of posts</h3>';
        $return .= '<p>This addon keeps a record of every single post generated by the feeds. This prevents the same unwanted item being created again if you manually choose to delete posts.</p>';
        $return .= '<p>Below you can erase this data on a per feed basis.</p>';
        $return .= '<p>Please create a database backup before you delete this data.</p>';
        $return .= '<div id="result">';

        if (isset($_POST['submitDeleteFeed'])) {

            // ESSENTIAL! Do not leave this out. Needs to come first
            $form = self::$processing->reset_form_processing_sanitisation($_POST);

            $startTime = microtime(TRUE);
            $this->db_delete_feed_leftovers($form);
            $endTime = microtime(TRUE);
            $elapsed = $endTime - $startTime;
            $return .= "<p><span style=\"color:red\">Execution time : ".round($elapsed, 2).
                " seconds</span></p>";

        } // end isset($_POST['submitDeleteFeed'])

        if (isset($_POST['deleteFeedPosts'])) {

            // ESSENTIAL! Do not leave this out. Needs to come first
            $form = self::$processing->reset_form_processing_sanitisation($_POST);

            $startTime = microtime(TRUE);
            $this->db_delete_posts($form);
            $endTime = microtime(TRUE);
            $elapsed = $endTime - $startTime;
            $return .= "<span style=\"color:red\">Execution here: ".round($elapsed, 2).
                " seconds</span>";

            //delete_posts

        } // end isset($_POST['deleteFeedPostsForm']))


        if (isset($_POST['deleteAllTracking'])) {

            $form = self::$processing->reset_form_processing_sanitisation($_POST);

            $startTime = microtime(TRUE);
            $this->db_delete_all_tracking();
            $endTime = microtime(TRUE);
            $elapsed = $endTime - $startTime;
            $return .= "<span style=\"color:red\">Execution time : ".round($elapsed, 2).
                " seconds</span>";

        }

        if (isset($_POST['wipeRevisions'])) {

            $form = self::$processing->reset_form_processing_sanitisation($_POST);

            $startTime = microtime(TRUE);
            $this->db_delete_revisions();
            $endTime = microtime(TRUE);
            $elapsed = $endTime - $startTime;
            $return .= "<span style=\"color:red\">Execution time : ".round($elapsed, 2).
                " seconds</span>";


        }


        $return .= '</div>';
        $return .= $this->delete_feed_posts();
        $return .= $this->delete_feed_details_cats();
        $return .= '<h4>Wipe tracking data</h4>';
        $return .= '<p>It may be the case that the tracking data has grown to large. Reset it by clicking below.</p>';
        $return .= $this->delete_tracking_data();
        $return .= '<h3>Wipe post revisions &#8650;</h3>';
        $return .= '<h4>Remove all post revisions</h4>';
        $return .= '<p>This is to ensure that all meta data is wiped. When I was creating this plugin I was deleting posts but I found the revisions were still lingering in the database. This was causing problems.</p>';
        $return .= '<p>It may have just because of my hacking that caused the issue but neverless you can wipe all revisions created by posts from feeds with a single swipe of this button.</p>';
        $return .= '<p>Please create a database backup before you mass delete these posts.</p>';
        echo $return;


        $form = array(
            'method' => 'post',
            'action' => '#wpwrap',
            'enctype' => 'application/x-www-form-urlencoded',
            'description' => '',
            'option' => FALSE,
            'submit' => 'wipeRevisions',
            'submtiTwo' => NULL,
            'synchronize' => NULL,
            'tracking' => NULL,
            );

        self::$form_builder->create_form($form);

    }

    /**
     * Reset_Form::delete_feed_details_cats()
     * 
     * @return
     */
    protected function delete_feed_details_cats() {

        $cats = $this->db_find_all_post_cats();

        $form = '<form method="post" action="#result" name="deleteFeedTraceForm">';
        $form .= '<fieldset>';
        $form .= '<legend>Delete all trace of posts</legend>';
        $form .= '<table class="form-table"><tbody>';

        foreach ($cats as $result) {

            $form .= '<tr>';
            $form .= "<th scope=\"row\">$result->name</th>";
            $form .= '<td><label for="';
            $form .= $result->name.'_delete_feed';
            $form .= '">';
            $form .= "<span class=\"screen-reader-text\">$result->name</span></label>";
            $form .= "<input type=\"hidden\" name=\"$result->name\" value=\"\">";
            $form .= '<input type="checkbox" name="';
            $form .= $result->name.'_delete_feed';
            $form .= '" id="';
            $form .= $result->name.'_delete_feed';
            $form .= '" value="';
            $form .= $result->id;
            $form .= '"';
            if (isset($_POST[$result->name.'_delete_feed']) && $_POST[$result->name.'_delete_feed'] ==
                "") {
                $form .= ' checked="checked" ';
            }
            $form .= '>';
            $form .= "</td>";
            $form .= '</tr>';

        }

        $form .= '</tbody></table>';
        $form .= self::$form_builder->perm_fields();
        $form .= '<p class="submit"><input type="submit" name="submitDeleteFeed" class="button-primary" value="Save Changes"></p>';
        $form .= '</fieldset>';
        $form .= '</form>';
        return $form;

    }

    /**
     * Reset_Form::delete_tracking_data()
     * 
     * @return
     */
    protected function delete_tracking_data() {

        $form = '<form method="post" action="#result" name="deleteTracking">';
        $form .= '<fieldset>';
        $form .= '<legend>Delete tracking data</legend>';
        $form .= self::$form_builder->perm_fields();
        $form .= '<p class="submit"><input type="submit" name="deleteAllTracking" class="button-primary" value="Delete tracking"></p>';
        $form .= '</fieldset>';
        $form .= '</form>';
        return $form;

    }

    /**
     * Reset_Form::delete_feed_posts()
     * 
     * @return
     */
    protected function delete_feed_posts() {

        $cats = $this->db_find_all_post_cats();

        $form = '<form method="post" action="#result" name="deleteFeedPostsForm">';
        $form .= '<fieldset>';
        $form .= '<legend>Delete all posts on a per feed basis</legend>';
        $form .= '<table class="form-table"><tbody>';

        foreach ($cats as $result) {

            $form .= '<tr>';
            $form .= "<th scope=\"row\">$result->name</th>";
            $form .= '<td><label for="';
            $form .= $result->name.'_delete_posts';
            $form .= '">';
            $form .= "<span class=\"screen-reader-text\">$result->name</span></label>";
            $form .= "<input type=\"hidden\" name=\"$result->name\" value=\"\">";
            $form .= '<input type="checkbox" name="';
            $form .= $result->name.'_delete_posts';
            $form .= '" id="';
            $form .= $result->name.'_delete_posts';
            $form .= '" value="';
            $form .= $result->id;
            $form .= '"';
            if (isset($_POST[$result->name.'_delete_posts']) && $_POST[$result->name.
                '_delete_posts'] == "") {
                $form .= ' checked="checked" ';
            }
            $form .= '>';
            $form .= "</td>";
            $form .= '</tr>';

        }

        $form .= '</tbody></table>';
        $form .= self::$form_builder->perm_fields();
        $form .= '<p class="submit"><input type="submit" name="deleteFeedPosts" class="button-primary" value="Save Changes"></p>';
        $form .= '</fieldset>';
        $form .= '</form>';

        return $form;

    }


    /**
     * Reset_Form::reset_form_facade()
     * 
     * @return
     */
    public function reset_form_facade() {

        $this->reset();

    }

}
