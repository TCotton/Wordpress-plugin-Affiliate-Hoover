<?php namespace controller;

/**
 * Update_Individual_Feeds
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
class Update_Individual_Feeds extends \model\Database {

    function __construct() {

        parent::__construct();

    } // end construct

    /**
     * Update_Individual_Feeds::update_ind_form())
     * 
     * Remove associated data from the feed details table and the feeds folder
     * when the title is deleted from the options tables filed
     * 
     * @param boolean $form
     * @param string $form_name
     * 
     */

    protected function update_individual_feeds($form, $form_name) {

        //essential
        extract(static::$form);

        $form_categories = NULL;
        $form_tags = NULL;
        
        foreach ($form[$option_name] as $key => $value) {

            if ($key == 'formTitle') {
                $form_title = $value;
            }

            if ($key == 'TitleContains') {
                if ($value != '') {
                    $form_title_contains = $value;
                } else {
                    $form_title_contains = NULL;
                }
            }

            if ($key == 'formBody') {
                $form_body = $value;
            }

            if ($key == 'BodyContains') {
                if ($value != '') {
                    $form_body_contains = $value;
                } else {
                    $form_body_contains = NULL;
                }

            }

            if ($key == 'formCategories') {

                if ($value != '') {
                    $form_categories = $value;
                } else {
                    $form_categories = NULL;
                }

            }

            if ($key == 'formCatParents') {

                if ($value != '') {

                    //$cat_ID = $this->find_cat_id($value);
                    $form_cat_parents = $value;
                    if ($form_cat_parents == '0') {
                        $form_cat_parents = NULL;
                    }
                } else {
                    $form_cat_parents = NULL;
                }

            }


            if ($key == 'formMinRows') {
                if ($value != '') {
                    $form_min_rows = (int)$value;
                } else {
                    $form_min_rows = NULL;
                }

            }

            if ($key == 'formMaxRows') {
                if ($value != '') {
                    $form_max_rows = (int)$value;
                } else {
                    $form_max_rows = NULL;
                }

            }

            if ($key == 'formPostStatus') {
                if ($value != '') {
                    $form_post_status = $value;
                } else {
                    $form_post_status = 'published';
                }

            }

            if ($key == 'formTags') {

                if ($value != '') {
                    $form_tags = $value;
                } else {
                    $form_tags = NULL;
                }

            }

            if ($key == 'formTaxonomy') {

                if ($value != '0') {
                    $form_tax = $value;
                } else {
                    $form_tax = 'category';
                }

            }

            if (preg_match('/^formAllowComments/', $key)) {
                $form_allow_comments = (integer)$value;
            }

            if (preg_match('/^formAllowTrackbacks/', $key)) {
                $form_allow_trackback = (integer)$value;
            }

            if (preg_match('/^formNoFollow/', $key)) {
                $form_nofollow = (integer)$value;
            }

            if (preg_match('/^formStagger/', $key)) {
                $form_stagger = (integer)$value;
            }

            if ($key == 'formPostType') {

                if ($value != '') {
                    $form_posttype = $value;
                } else {
                    $form_posttype = 'post';
                }

            }

        }

        if ($this->db_update_feed_details($form_title, $form_title_contains, $form_body, $form_body_contains,
            $form_categories, $form_tags, $form_allow_comments, $form_allow_trackback, $form_name, $form_min_rows,
            $form_max_rows, $form_post_status, $form_posttype, $form_nofollow, $form_cat_parents, $form_tax,
            $form_stagger)) {

            // Once the feed_details table has been updated then process the feed:

            return TRUE;

        }


    }


    public function update_individual_feeds_facade($form, $form_name) {

        $this->update_individual_feeds($form, $form_name);

    }

}
