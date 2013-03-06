/*global clearInterval: FALSE, clearTimeout: FALSE, document: FALSE, event: FALSE, frames: FALSE, history: FALSE, Image: FALSE, location: FALSE, name: FALSE, navigator: FALSE, Option: FALSE, parent: FALSE, screen: FALSE, setInterval: FALSE, setTimeout: FALSE, window: FALSE, XMLHttpRequest: FALSE, jQuery: FALSE */
/*

 refactor loops in finalForm() for performance

 This is only needed if the admin declers the dynamic output to FALSE
 Once the user navigates away from the admin page there needs to be a way of filling empty forms
 This takes the javascript variables created in the constructor of the FormView class -> wp_enqueue_script()
 */
// https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array/indexOf

jQuery(document).ready(function () {

    //OptionForm.init();
    jQuery("textarea").markItUp(mySettings);

// remove disabled from attributes on page submit
    jQuery('.settings_page_affiliate-hoover-plugin-admin').find('form').submit(function () {
        jQuery('[disabled]').each(function (i) {
            var d_name = jQuery(this).attr("name");
            var d_val = jQuery(this).val();
            jQuery(this).attr("disabled", false);
        });
        return true;
    });

});