<?php /**
 * If not UTF-8 if then encodes the string
 * 
 * @param string $str
 * @return string
 */


function ah_check_utf($str) {

    if (mb_detect_encoding($str, 'UTF-8', TRUE) === FALSE) {
        return utf8_encode($str);
    } else {
        return $str;
    }

}

/**
 * 
 * Need to find the full URI for the admin area pages.
 * Is there a suitable Wordpress function for this purpose? I couldn't find one
 * 
 * @return string
 */
function ah_find_url() {

    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        return $pageURL;
    }

}


/**
 * 
 * Alternative function because hex2bin is not native in PHP until version 5.4!
 * 
 * @param array $form
 * @return string
 */
function ah_hex2bin($data) {
    $bin = "";
    $i = 0;
    do {
        $bin .= chr(hexdec($data{$i}.$data{($i + 1)}));
        $i += 2;
    } while ($i < strlen($data));
    return $bin;
}


/**
 * 
 * Changes the first key number in an array from 0 to 1
 * 
 * @param array $form
 * @return string
 */
function ah_reset_array(&$form) {

    $keys = range(1, count($form));
    $values = array_values($form);
    $form = array_combine($keys, $values);
    return $form;
}

function ah_instructions() {

    if (ini_get('safe_mode')) {
        echo '<p><strong>Your server has safe mode on. This will restrict your use of this module because it often requires more than 30 seconds to parse a feed</strong></p>';
    }

    include_once (AH_DIR_PATH.'misc'.AH_DS.'instructions.php');
}

function ah_changelog() {

    $form = '<h3>Changelog</h3>';
    $form .= '<ul>';
    $form .= '<li>1.5: Major backend code rewrite</li>';
    $form .= '<li>1.33: Category bug fix</li>';
    $form .= '<li>1.3: Added stagger feature so that posts can be published at a rate of 5 per ten minutes</li>';
    $form .= '<li>1.2: Greater control of taxonomies</li>';
    $form .= '<li>1.1: Adds Affiliate Hoover details to content type admin pages. Reset for tracking data.</li>';
    $form .= '<li>0.94: Added support for text editor on textareas</li>';
    $form .= '<li>0.9: Added tracking / bug fixes</li>';
    $form .= '<li>0.8: Added ability to create new categories as children of parent categories / bug fixes</li>';
    $form .= '<li>0.7: Added log file</li>';
    $form .= '<li>0.6: Added ability to automatically add nofollow to outbound links. Bug Fixes</li>';
    $form .= '<li>0.5: Added ability to create posts for different post types. Bug Fixes</li>';
    $form .= '<li>0.4: Added delete all posts on a per-feed basis. Reset section now complete</li>';
    $form .= '<li>0.3: Added ability to remove post remnants in the ah_total_feeds table</li>';
    $form .= '<li>0.2: Added first feature on reset page</li>';
    $form .= '<li>0.1: Initial release</li>';
    $form .= '</ul>';

    return $form;

}

function ah_admin_sidebar() {

    $form = '<div class="postbox clearfix">';
    $form .= '<div class="inside">';
    $form .= '<h3 class="hndle">Important notice on version 1.5</h3>';
    $form .= '<p>This is a major re-write of the PHP code</p>';
    $form .= '<p>It has only been released after extensive testing but nevertheless bugs may still be present</p>';
    $form .= '<p>If you come across any, don\'t panic and follow the procedure for filing bugs below</p>';
    $form .= '<h3 class="hndle">Author details</h3>';
    $form .= '<p>This plugin has been created by <a href="http://about.me/andywalpole">Andy Walpole</a></p>';
    $form .= '<p><a href="http://wordpress.org/support/plugin/affiliate-hoover">Please report any bugs or feature requests here</a></p>';
    $form .= '<p>Once you have done that please email me <a href="http://about.me/andywalpole">here</a> about the new thread</p>';
    $form .= '<p>It\'s best that all discussion about this plugin is kept in the public arena so that others can learn from any issues</p>';
    $form .= '<h3 class="hndle">Hire me</h3>';
    $form .= '<p>Although I work on a full-time contract basis in the web development industry I sometimes carry out private work. Please contact me for a quote.</p>';
    $form .= '<h3 class="hndle">Drupal port</h3>';
    $form .= '<p>There is now a Drupal port of this module <a href="http://drupal.org/sandbox/divisivecottonwood/1667928">here</a>. It is currently in sandbox stage but should hopefully be elevated to full project status eventually.</p>';
    $form .= '</div><!-- end inside -->';
    $form .= '</div><!-- end postbox -->';

    return $form;

}

function ah_max_upload() {

    $max_upload = @(int)(ini_get('upload_max_filesize'));
    $max_post = @(int)(ini_get('post_max_size'));
    $memory_limit = @(int)(ini_get('memory_limit'));
    if (isset($max_upload) && isset($max_post) && isset($memory_limit)) {

        return (int)(min($max_upload, $max_post, $memory_limit) * 1048576);

    } else {

        return 2097152;

    }


}
