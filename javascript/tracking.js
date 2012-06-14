/*global clearInterval: false, clearTimeout: false, document: false, event: false, frames: false, history: false, Image: false, location: false, name: false, navigator: false, Option: false, parent: false, screen: false, setInterval: false, setTimeout: false, window: false, XMLHttpRequest: false, jQuery: false*/
var AFFILIATE = {};
AFFILIATE.tracker = (function() {
	"use strict";
	var win = window,
		_private = {
			find_parent_id: function(elem, idN) {
				while (elem.parentNode) {
					elem = elem.parentNode;
					if (elem.id.indexOf(idN) !== -1) {
						return elem.getAttribute('id');
					}
				}
				return null;
			},
			AJAXCall: function(stuff, link) {
				var str = jQuery.param({
					action: 'ah_update',
					post: stuff,
					nonce: ah_tracking_scripts.my_nonce
					//random: Math.random()
				});
				//start the ajax
				jQuery.ajax({
					//this is the php file that processes the data and send mail
					url: ah_tracking_scripts.ajaxurl,
					//GET method is used
					type: "GET",
					//pass the data
					data: str,
					dataType: "text",
					// data type
					//Do not cache the page
					cache: true,
					//success
					success: function(result, textStatus, jqXHR) {
						if (textStatus === "success") {
							win.location = link;
						}
					}
				}); // End $.ajax
			},
			run: function() {
				jQuery(".ah_link").bind('click', function() {
					var result = _private.find_parent_id(this, "post");
					if (result) {
						_private.AJAXCall(result, this);
					} else {
					   win.location = this;
					}
					return false;
				});
			}
		};
	return {
		facade: function() {
			_private.run();
		}
	};
}());
jQuery(document).ready(function() {
	AFFILIATE.tracker.facade({});
});