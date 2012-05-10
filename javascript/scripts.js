/*global clearInterval: false, clearTimeout: false, document: false, event: false, frames: false, history: false, Image: false, location: false, name: false, navigator: false, Option: false, parent: false, screen: false, setInterval: false, setTimeout: false, window: false, XMLHttpRequest: false, jQuery: false */
/*

 refactor loops in finalForm() for performance

 This is only needed if the admin declers the dynamic output to false
 Once the user navigates away from the admin page there needs to be a way of filling empty forms
 This takes the javascript variables created in the constructor of the FormView class -> wp_enqueue_script()
 */
// https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array/indexOf
if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function(searchElement /*, fromIndex */ ) {
		"use strict";
		if (this == null) {
			throw new TypeError();
		}
		var t = Object(this);
		var len = t.length >>> 0;
		if (len === 0) {
			return -1;
		}
		var n = 0;
		if (arguments.length > 0) {
			n = Number(arguments[1]);
			if (n != n) { // shortcut for verifying if it's NaN
				n = 0;
			} else if (n != 0 && n != Infinity && n != -Infinity) {
				n = (n > 0 || -1) * Math.floor(Math.abs(n));
			}
		}
		if (n >= len) {
			return -1;
		}
		var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
		for (; k < len; k++) {
			if (k in t && t[k] === searchElement) {
				return k;
			}
		}
		return -1;
	}
}

// https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array/filter
if (!Array.prototype.filter) {
	Array.prototype.filter = function(fun /*, thisp */ ) {
		"use strict";

		if (this == null) throw new TypeError();

		var t = Object(this);
		var len = t.length >>> 0;
		if (typeof fun != "function") throw new TypeError();

		var res = [];
		var thisp = arguments[1];
		for (var i = 0; i < len; i++) {
			if (i in t) {
				var val = t[i]; // in case fun mutates this
				if (fun.call(thisp, val, i, t)) res.push(val);
			}
		}

		return res;
	};
}

var OptionForm = {

	//"use strict":
	elementsNo: null,
	optionName: null,
	originalForm: null,
	theForm: null,
	newForm: null,
	Before: null,
	After: null,
	Submit: null,

	// declare the name of the form and keep it in memory
	formName: function() {
	   
		jQuery.each(option_plugin_params, function(i, object) {

			if (i !== "total_user_fields") {

				OptionForm.optionName = i;
				OptionForm.originalForm = document.getElementsByName(OptionForm.optionName)[0];

				if (typeof OptionForm.originalForm !== "undefined") {

					// Below sets a boolean to stop form JavaScript from working on submission
					// This is unnecessary because the sticky values work
					OptionForm.originalForm.onsubmit = function() {
						OptionForm.Submit = TRUE;
					};
					if (OptionForm.Submit === null) {
						OptionForm.theForm = OptionForm.originalForm.cloneNode(true);
						OptionForm.formValues();
					} // end if
				} // end if typeof
			} // end if i !==
		}); // end jQuery 
	},

	// create the Option.Forma array. This takes all the form elements and filters out unneccessary input elements
	formValues: function() {

		var i, l;

		OptionForm.newForm = [];
		OptionForm.elementsNo = ['option_page', 'total_user_fields', '_wpnonce', '_wp_http_referer', 'submit'];

		for (i = 0, l = OptionForm.theForm.length; i < l; i += 1) {
			// filter out unwanted form values
			if (jQuery.inArray(OptionForm.theForm[i].name, OptionForm.elementsNo) === -1 && OptionForm.theForm[i].name.indexOf('input_gen') === -1 && OptionForm.theForm[i].type !== 'hidden') {

				if (OptionForm.theForm[i].type === "radio") {
					OptionForm.newForm.push([OptionForm.theForm[i].name + ":" + OptionForm.theForm[i].value, "type:" + OptionForm.theForm[i].type, "checked:" + OptionForm.theForm[i].checked]);
				} else if (OptionForm.theForm[i].type === "text") {
					OptionForm.newForm.push([OptionForm.theForm[i].name + ":" + OptionForm.theForm[i].value, "type:" + OptionForm.theForm[i].type, "checked:" + "n/a"]);
				} else if (OptionForm.theForm[i].type === "textarea") {
					OptionForm.newForm.push([OptionForm.theForm[i].name + ":" + OptionForm.theForm[i].value, "type:" + OptionForm.theForm[i].type, "checked:" + "n/a"]);
				} else if (OptionForm.theForm[i].type === "checkbox") {
					OptionForm.newForm.push([OptionForm.theForm[i].name + ":" + OptionForm.theForm[i].value, "type:" + OptionForm.theForm[i].type, "checked:" + OptionForm.theForm[i].checked]);
				} else if (OptionForm.theForm[i].type === "select-one") {
					OptionForm.newForm.push([OptionForm.theForm[i].name + ":" + OptionForm.theForm[i].value, "type:" + OptionForm.theForm[i].type, "checked:" + OptionForm.theForm[i].checked]);

				}

			} // end if
		} // end if
		OptionForm.finalForm();

	},

	// Now put the JavaScript objects form option_plugin_params into the form if the values are empty
	finalForm: function() {

		var _Before, _After, x, l;

		jQuery.each(option_plugin_params[OptionForm.optionName], function(i, object) {
			//i = field name attribute, object.field_type = input type
			jQuery.each(OptionForm.newForm, function(a_key, form) {

				_Before = form[0].substring(0, form[0].indexOf(":"));
				_After = form[0].substr(form[0].indexOf(":") + 1);

				// Radio fields
				if (form[1].indexOf("type:radio") !== -1 && form[2].indexOf("checked:false") !== -1) {

					// form radio button is not checked
					// if not then take value from the Wordpress variables in the option_plugin_params and add them
					if (_Before.indexOf(i) !== -1) {

						// Loop through the radio nodelists
						for (x = 0, l = OptionForm.theForm.elements[_Before].length; x < l; x += 1) {

							// if data between the form radio fields and option_plugin_params the same then put checkbox on there
							if (OptionForm.theForm.elements[_Before][x].value === object) {
								OptionForm.theForm.elements[_Before][x].checked = true;
							} // end if
						} // end for loop
					} // end if
				} // end if
				// Text fields
				// form input is text and is empty
				// therefor check if Wordpress variable has a value
				// If so, fill in the field
				if (form[1].indexOf("type:text") !== -1 && _After === "") {

					if (_Before.indexOf(i) !== -1) {
						jQuery(OptionForm.theForm.elements[_Before]).val(object); // use jQuery val() so as to escape 
					} // end if
				} // end if
				// Textareas
				if (form[1].indexOf("type:textarea") !== -1 && _After === "") {

					if (_Before.indexOf(i) !== -1) {
						jQuery(OptionForm.theForm.elements[_Before]).val(object);
					} // end if
				} // end if
				// Checkboxes
				if (form[1].indexOf("type:checkbox") !== -1 && form[2].indexOf("checked:false") !== -1) {

					if (_Before.indexOf(i) !== -1) {

						if (OptionForm.theForm.elements[_Before][1].value === object) {
							jQuery(OptionForm.theForm.elements[_Before]).attr('checked', true);
						}
					} // end if
				} // end if
				// Select dropdown
				// if set to 0 then the first select drop down has been used - meaning that it isn't set
				if (form[1].indexOf("type:select-one") !== -1 && _After === "0") {

					if (i === "selectName") {
						// number of selected option drop down
						OptionForm.theForm.elements[_Before].value = object;
					}

				} // end if
			}); // end jQuery loop
		}); // end each loop
		OptionForm.recreateNode();

	},

	recreateNode: function() {

		// delete original form from parent node
		// add new admended node that is found in the OptionForm.theForm object
		// This means better peformance because it minimises interaction with the DOM
		var parentDiv;

		parentDiv = OptionForm.originalForm.parentNode;
		parentDiv.removeChild(OptionForm.originalForm);
		parentDiv.appendChild(OptionForm.theForm);

	},

	// below clears the input values for the last form block if successfull submitted
	multiFormName: function() {

		var key, cookieMonster, mySplitResult, lastSplitResult;

		function removeArrayElement(element, index, array) {
			return (element !== "_multi_cov");
		}

		// Cookie is set when multi form is successfully completed
		// Cookie is deleted when mutli form fails or when session ends
		cookieMonster = document.cookie;
		mySplitResult = cookieMonster.split(";");

		for (x = 0, l = mySplitResult.length; x < l; x += 1) {

			if (mySplitResult[x].indexOf("_multi_cov") !== -1) {
				lastSplitResult = mySplitResult[x].split("=");
				key = lastSplitResult.filter(removeArrayElement).toString();
			} // end if
		} // end for
		OptionForm.originalForm = document.getElementsByName(key)[0];

		if (typeof OptionForm.originalForm !== "undefined") {
			OptionForm.theForm = OptionForm.originalForm.cloneNode(true);
			OptionForm.multiFormValues();
		} // end if typeof
	},
	multiFormValues: function() {

		var i, remove;

		remove = ['option_page', '_wpnonce', '_wp_http_referer', 'submit', 'total_user_fields'];

		for (i = OptionForm.theForm.length - 1; i >= 0; i--) {

			// ignore unimportant parts of form HTMLElementInput
			if (remove.indexOf(OptionForm.theForm[i].name) === -1 && OptionForm.theForm[i].name.search(".([0-9]+)\]$") === -1 && OptionForm.theForm[i].name.search("input_gen|xyz|checkbox_number|checkbox_type") === -1 && OptionForm.theForm[i].name.search("input_gen|xyz|checkbox_number|checkbox_type") === -1 && OptionForm.theForm[i].name !== "" && OptionForm.theForm[i].type !== "hidden") {

				// reset all form value back to zero
				if (OptionForm.theForm[i].type === "text") {

					OptionForm.theForm[i].value = "";

				} else if (OptionForm.theForm[i].type === "textarea") {

					OptionForm.theForm[i].value = "";

				} else if (OptionForm.theForm[i].type === "radio") {

					for (y = 0, l = OptionForm.theForm[i].length; y < l; y += 1) {
						jQuery(OptionForm.theForm[i][y]).removeAttr("selected");
					}

					// something here for radio buttons
				} else if (OptionForm.theForm[i].type === "select-one") {

					// Loop through htmlselectelements
					for (x = 0, l = OptionForm.theForm[i].length; x < l; x += 1) {
						jQuery(OptionForm.theForm[i][x]).removeAttr("selected");
					}

					// something here for select forms
				} else if (OptionForm.theForm[i].type === "checkbox") {

					jQuery(OptionForm.theForm[i]).attr('checked', false);

				}

			} // end if giant statement
		} // for (var i = OptionForm.originalForm.length - 1; i >= 0; i--) 
		OptionForm.multiFecreateNode();

	},

	multiFecreateNode: function() {

		// delete original form from parent node
		// add new admended node that is found in the OptionForm.theForm object
		// This means better peformance because it minimises interaction with the DOM
		var parentDiv;

		parentDiv = OptionForm.originalForm.parentNode;
		parentDiv.removeChild(OptionForm.originalForm);
		parentDiv.appendChild(OptionForm.theForm);

		// delete cookie after form rebuilding has been done
		document.cookie = "_multi_cov" + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';

	},

	init: function() {

		if (typeof option_plugin_params !== "undefined") {
			OptionForm.formName();
		}

		if (document.cookie.indexOf("_multi_cov") !== -1) {
			OptionForm.multiFormName();
		}

	}

};

jQuery(document).ready(function() {

		OptionForm.init();

});