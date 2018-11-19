var mrp_data_callbacks = [];

// supporting different versions of Font Awesome icons
var icon_classes = jQuery.parseJSON(mrp_frontend_data.icon_classes);

/**
 * Saves a rating
 */
function saveRating(e) {
	
	var ratingItems = [];
	var customFields = [];
	
	var btnId = e.currentTarget.id; // btnType-ratingFormid-postId-sequence
	var parts = btnId.split("-"); 
	var ratingFormId = parts[1];
	var postId = parts[2];
	var sequence = parts[3];
	
	var id = ratingFormId + '-' + postId +'-' + sequence;
	
	var hiddenRatingEntryId = "#ratingEntryId-" + id;
	var ratingEntryId = jQuery(hiddenRatingEntryId);
	
	// rating items - hidden inputs are used to find all rating items in the rating form
	jQuery('.rating-form input[type="hidden"].rating-item-' + id).each(function(index) {			
		
		var ratingItemId = jQuery(this).val();
		
		// get values for rating items
		var ratingItemElement = jQuery('[name="rating-item-' + ratingItemId + '-' + sequence + '"]');
		var value = null;
		if (jQuery(ratingItemElement).is(':radio')) {
			value = jQuery('input[type="radio"][name="rating-item-' + ratingItemId + '-' + sequence + '"]:checked').val(); 
		} else if (jQuery(ratingItemElement).is('select')) {
			value = jQuery('select[name="rating-item-' +ratingItemId + '-' + sequence + '"] :selected').val(); 
		} else {
			value = jQuery('input[type="hidden"][name="rating-item-' + ratingItemId + '-' + sequence + '"]').val();
		}
		
		var isNotApplicable = false;
		// if a rating item is not applicable
		if ( jQuery('input[type="checkbox"][name="rating-item-' + ratingItemId + '-' + sequence + '-not-applicable"]') ) {
			isNotApplicable = jQuery('input[type="checkbox"][name="rating-item-' + ratingItemId + '-' + sequence + '-not-applicable"]').is(':checked');
		}
		
		var ratingItem = { 'id' : ratingItemId, 'value' : value, 'isNotApplicable' : isNotApplicable };
		ratingItems[index] = ratingItem;
		
	});
	
	var title = jQuery('#mrp-title-' + sequence);
	var name = jQuery('#mrp-name-' + sequence);
	var email = jQuery('#mrp-email-' + sequence);
	var comment = jQuery('#mrp-comment-' + sequence);
	
	// custom fields - hidden inputs are used to find all custom fields in the rating form
	jQuery('.rating-form input[type="hidden"].custom-field-' + id).each(function(index) {			
		
		var customFieldId = jQuery(this).val();
		
		// get values for rating items
		var customFieldElement = jQuery('[name="custom-field-' + customFieldId + '-' + sequence + '"]');
		var value = null;
		var type = null;
		if (jQuery(customFieldElement).is('textarea')) {
			value = jQuery('textarea[name="custom-field-' + customFieldId + '-' + sequence + '"]').val(); 
			type = 'textarea';
		} else {
			value = jQuery('input[name="custom-field-' + customFieldId + '-' + sequence + '"]').val(); 
			type = 'input';
		}
		
		var customField = { 'id' : customFieldId, 'value' : value, 'type' :  type };
		customFields[index] = customField;
		
	});
	
	var data = {
			action : "save_rating",
			nonce : mrp_frontend_data.ajax_nonce,
			ratingItems : ratingItems,
			title : (title != undefined) ? title.val() : '',
			name : (name != undefined) ? name.val() : '',
			email : (email != undefined) ? email.val() : '',
			comment : (comment != undefined) ? comment.val() : '',
			customFields : customFields,
			postId : postId,
			ratingFormId : ratingFormId,
			ratingEntryId : (ratingEntryId != undefined) ? ratingEntryId.val() : '',
			sequence : sequence
	};
	
	// Note: reCAPTCHA add-on uses this
	jQuery(mrp_data_callbacks).each(function(index) {
		if (typeof mrp_data_callbacks[index] == 'function') {
			data = mrp_data_callbacks[index].call(this, id, data);
		}
	});
	
	var spinnerId = 'mrp-spinner-' + id;
	
	jQuery('<i style="margin-left: 10px;" id="' + spinnerId + '" class="' + icon_classes.spinner + '"></i>').insertAfter('#' + btnId);

	jQuery.post(mrp_frontend_data.ajax_url, data, function(response) {
			handle_rating_form_submit_response(response);
	});
}

/**
 * Deletes a rating
 */
function deleteRating(e) {
	
	var btnId = e.currentTarget.id; // btnType-ratingFormid-postId-sequence
	var parts = btnId.split("-"); 
	var ratingFormId = parts[1];
	var postId = parts[2];
	var sequence = parts[3];
	
	var id = ratingFormId + '-' + postId +'-' + sequence;
	
	var hiddenRatingEntryId = "#ratingEntryId-" + id;
	var ratingEntryId = jQuery(hiddenRatingEntryId);
	
	var data = {
			action : "delete_rating",
			nonce : mrp_frontend_data.ajax_nonce,
			postId : postId,
			ratingFormId : ratingFormId,
			ratingEntryId : (ratingEntryId != undefined) ? ratingEntryId.val() : '',
			sequence : sequence
	};
	
	// Note: reCAPTCHA add-on uses this
	jQuery(mrp_data_callbacks).each(function(index) {
		if (typeof mrp_data_callbacks[index] == 'function') {
			data = mrp_data_callbacks[index].call(this, id, data);
		}
	});
	
	var spinnerId = 'mrp-spinner-' + id;
	
	jQuery('<i style="margin-left: 10px;" id="' + spinnerId + '"class="' + icon_classes.spinner + '"></i>').insertAfter('#saveBtn-' + id);	

	jQuery.post(mrp_frontend_data.ajax_url, data, function(response) {
		handle_rating_form_submit_response(response);
	});
}

/**
 * Handles rating form submit response
 */
function handle_rating_form_submit_response(response) {
	
	var jsonResponse = jQuery.parseJSON(response);
	
	var id = jsonResponse.data.rating_form_id + "-" + jsonResponse.data.post_id + "-" + jsonResponse.data.sequence;
	var ratingForm = jQuery("form#rating-form-" + id);
	
	if (jQuery(".user-ratings-dashboard-list table tr.rating-info-" + id).length) {
		
		//jQuery(".user-ratings-dashboard-list table tr.rating-edit-" + id).hide("slow", function() {
			jQuery(".user-ratings-dashboard-list table tr.rating-edit-" + id).remove();
		//});
		
		jQuery("#edit-" + id).css("display", "inline-block");
		jQuery("#cancel-" + id).css("display", "none");
		
		unbindRatingFormEvents(id);
		
		// if successful delete, remove row and unbind edit event
		if (jsonResponse.data.action == "delete" && jsonResponse.status == "success") {
			jQuery(".user-ratings-dashboard-list table tr.rating-info-" + id).remove();
			
		}
		
		// update rating in user dashboard row?
		
	} else {
		
		// update rating results if success
		if (jsonResponse.status == 'success') {
			var ratingResult = jQuery(".rating-result-" + jsonResponse.data.rating_form_id + "-" 
					+ jsonResponse.data.post_id).filter(".mrp-filter");
			
			if (ratingResult) {
				ratingResult.replaceWith(jsonResponse.data.html);
			}
		}
		
		// remove existing errors for rating items, optional fields and custom fields
		jQuery("#rating-form-" + id + " .rating-item .mrp-error, #rating-form-" + id + " .custom-field .mrp-error, " +
				"#rating-form-" + id + " .review-field .mrp-error").html("");
		
		// check validation results
		if ((jsonResponse.validation_results && jsonResponse.validation_results.length > 0) || jsonResponse.message) {
			var messages = '';
			
			if (jsonResponse.validation_results) {
				var index = 0;
				for (index; index<jsonResponse.validation_results.length; index++) {
					
					if (jsonResponse.validation_results[index].field) {
						jQuery("#" + jsonResponse.validation_results[index].field + "-" + jsonResponse.data.sequence + "-error")
								.html(jsonResponse.validation_results[index].message);
					} else {
						messages += '<p class="mrp message mrp-' + jsonResponse.validation_results[index].severity + '">' 
								+ jsonResponse.validation_results[index].message + '</p>';
					}
				}
			}
			
			if (jsonResponse.message) {
				messages += '<p class="message ' + jsonResponse.status + '">' 
						+ jsonResponse.message + '</p>';
			}
			
			if (ratingForm && ratingForm.parent().find('.message')) {
				ratingForm.parent().find('.message').remove();
			}
			
			if (ratingForm && ratingForm.parent()) {
				ratingForm.before(messages);
			}
		}
		
		// remove rating form if success
		if (jsonResponse.status == 'success' && jsonResponse.data.hide_rating_form == true && ratingForm) {
			ratingForm.remove();
		}
		
		var spinnerId = 'mrp-spinner-' + id;
		jQuery("#" + spinnerId).remove();
		
		if (jsonResponse.status == 'success' && jsonResponse.data.user_id) {
			
			// update buttons and hidden fields
			if (jsonResponse.data.rating_entry_id) {
				
				if (! jQuery("#ratingEntryId-" + id).length) { // save
					
					var html = '<input type="hidden" value="' + jsonResponse.data.rating_entry_id + '" id="ratingEntryId-' + id + '" />';
					html += '<input type="button" class="btn btn-default delete-rating" id="deleteBtn-' + id + '" value="' + mrp_frontend_data.strings.delete_btn_text + '" />';
					
					jQuery(html).insertBefore('#saveBtn-' + id);
					
					jQuery("#deleteBtn-" + id).on("click", function(e) {
						deleteRating(e);
					});
					
					// update text
					jQuery("#saveBtn-" + id).attr('value', jsonResponse.data.submit_btn_text);
					
					jQuery("#ratingEntryId-" + id).attr('value', jsonResponse.data.rating_entry_id);
				}
				
				// update - do nothing
			} else {
				// delete
				jQuery("#ratingEntryId-" + id).remove();
				jQuery("#saveBtn-" + id).attr('value', mrp_frontend_data.strings.submit_btn_text);
				jQuery("#deleteBtn-" + id).off();
				jQuery("#deleteBtn-" + id).remove();
				jQuery("#entryId-" + id).remove();
			}
		}
	}
	
}


/**
 * Selected rating item value on hover and click
 */
var ratingItemStatus = {};

var useCustomStarImages = jQuery.parseJSON(mrp_frontend_data.use_custom_star_images);

/**
 * Star rating on click
 */
function starRatingClick(e) {
	var elementId = e.currentTarget.id;
	updateRatingItemStatus(elementId, 'clicked');
	
	if (useCustomStarImages == true) {
		jQuery("#" + elementId).not('.mrp-minus').removeClass('mrp-custom-empty-star mrp-custom-hover-star mrp-star-hover').addClass('mrp-custom-full-star mrp-star-full');
		jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass('mrp-custom-empty-star mrp-custom-hover-star mrp-star-hover').addClass('mrp-custom-full-star mrp-star-full');
		jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass('mrp-custom-full-star mrp-custom-hover-star mrp-star-hover').addClass('mrp-custom-empty-star mrp-star-empty');
	} else {
		jQuery("#" + elementId).not('.mrp-minus').removeClass(icon_classes.star_empty + " " + icon_classes.star_hover).addClass(icon_classes.star_full);
		jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass(icon_classes.star_empty + " " + icon_classes.star_hover).addClass(icon_classes.star_full);
		jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass(icon_classes.star_full + " " + icon_classes.star_hover).addClass(icon_classes.star_empty);
	}
	
	updateSelectedHiddenValue(elementId);
}
	
/**
 * Star rating minus click
 * 
 * @param e
 * @returns
 */
function starRatingMinusClick(e) {
	var elementId = e.currentTarget.id;
	updateRatingItemStatus(elementId, '');
	
	if (useCustomStarImages == true) {
		jQuery("#" + elementId).not('.mrp-minus').removeClass('mrp-custom-empty-star mrp-custom-hover-star mrp-star-hover').addClass('mrp-custom-full-star mrp-star-full');
		jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass('mrp-custom-empty-star mrp-custom-hover-star mrp-star-hover').addClass('mrp-custom-full-star mrp-star-full');
		jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass('mrp-custom-full-star mrp-custom-hover-star mrp-star-hover').addClass('mrp-custom-empty-star mrp-star-empty');
	} else {
		jQuery("#" + elementId).not('.mrp-minus').removeClass(icon_classes.star_empty + " " + icon_classes.star_hover).addClass(icon_classes.star_full);
		jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass(icon_classes.star_empty + " " + icon_classes.star_hover).addClass(icon_classes.star_full);
		jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass(icon_classes.star_full + " " + icon_classes.star_hover).addClass(icon_classes.star_empty);
	}
	
	updateSelectedHiddenValue(elementId);
}

/**
 * Star rating hover 
 * @param e
 * @returns
 */
function starRatingOnHover(e) {
	var elementId = e.currentTarget.id;
	var ratingItemIdSequence = getRatingItemIdSequence(elementId);

	if (jQuery("#" + ratingItemIdSequence).val() == 0 || (ratingItemStatus[ratingItemIdSequence] != 'clicked' 
			&& ratingItemStatus[ratingItemIdSequence] != undefined)) {
		
		updateRatingItemStatus(elementId, 'hovered');
		
		if (useCustomStarImages == true) {
			jQuery("#" + elementId).not('.mrp-minus').removeClass('mrp-custom-empty-star').addClass('mrp-custom-hover-star mrp-star-hover');
			jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass('mrp-custom-empty-star').addClass('mrp-custom-hover-star mrp-star-hover');
			jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass('mrp-custom-full-star mrp-star-full mrp-custom-hover-star mrp-star-hover').addClass('mrp-custom-empty-star mrp-star-empty');	

		} else {
			jQuery("#" + elementId).not('.mrp-minus').removeClass(icon_classes.star_empty).addClass(icon_classes.star_hover);
			jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass(icon_classes.star_empty).addClass(icon_classes.star_hover);
			jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass(icon_classes.star_full + " " + icon_classes.star_hover).addClass(icon_classes.star_empty);	
		}
	}
}

/**
 * Star rating hover out
 * @param e
 * @returns
 */
function starRatingOffHover(e) {	
	
	var elementId = e.currentTarget.id;
	var ratingItemIdSequence = getRatingItemIdSequence(elementId);

	if (jQuery("#" + ratingItemIdSequence).val() == 0 || (ratingItemStatus[ratingItemIdSequence] != 'clicked' 
			&& ratingItemStatus[ratingItemIdSequence] != undefined)) {
		
		updateRatingItemStatus(elementId, '');
		
		if (useCustomStarImages == true) {
			jQuery("#" + elementId).not('.mrp-minus').removeClass('mrp-custom-hover-star').addClass('mrp-custom-empty-star mrp-star-empty');
			jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass('mrp-custom-hover-star').addClass('mrp-custom-empty-star mrp-star-empty');
			jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass('mrp-custom-hover-star').addClass('mrp-custom-empty-star mrp-star-empty');	

		} else {
			jQuery("#" + elementId).not('.mrp-minus').removeClass(icon_classes.star_hover).addClass(icon_classes.star_empty);
			jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass(icon_classes.star_hover).addClass(icon_classes.star_empty);
			jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass(icon_classes.star_hover).addClass(icon_classes.star_empty);	
		}
		
	}
	
}

/**
 * Thumbs up click
 * 
 * @param e
 * @returns
 */
function thumbsUpClick(e) {
	var elementId = e.currentTarget.id;
	
	jQuery("#" + elementId).removeClass(icon_classes.thumbs_up_off).addClass(icon_classes.thumbs_up_on);
	jQuery("#" + elementId).prev().removeClass(icon_classes.thumbs_down_on).addClass(icon_classes.thumbs_down_off);
	
	updateSelectedHiddenValue(elementId);
}

/**
 * Thumbs down click
 * 
 * @param e
 * @returns
 */
function thumbsDownClick(e) {
	var elementId = e.currentTarget.id;
	
	jQuery("#" + elementId).removeClass(icon_classes.thumbs_down_off).addClass(icon_classes.thumbs_down_on);
	jQuery("#" + elementId).next().removeClass(icon_classes.thumbs_up_on).addClass(icon_classes.thumbs_up_off);
	
	updateSelectedHiddenValue(elementId);
}

// now cater for touch screen devices
var touchData = {
	started : null, // detect if a touch event is sarted
	currrentX : 0,
	yCoord : 0,
	previousXCoord : 0,
	previousYCoord : 0,
	touch : null
};

/**
 * Touch start
 */
function touchStart(e) {
	touchData.started = new Date().getTime();
	var touch = e.originalEvent.touches[0];
	touchData.previousXCoord = touch.pageX;
	touchData.previousYCoord = touch.pageY;
	touchData.touch = touch;
}

/**
 * Star rating touch
 */
function starRatingTouch(e) {
	var elementId = e.currentTarget.id;

	var now = new Date().getTime();
	// Detecting if after 200ms if in the same position.
	if ((touchData.started !== null)
			&& ((now - touchData.started) < 200)
			&& (touchData.touch !== null)) {
		var touch = touchData.touch;
		var xCoord = touch.pageX;
		var yCoord = touch.pageY;
		if ((touchData.previousXCoord === xCoord)
				&& (touchData.previousYCoord === yCoord)) {
			
			if (useCustomStarImages == true) {
				jQuery("#" + elementId).not('.mrp-minus').removeClass('mrp-custom-empty-star mrp-star-empty').addClass('mrp-custom-full-star mrp-star-full');
				jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass('mrp-custom-empty-star mrp-star-empty').addClass('mrp-custom-full-star mrp-star-full');
				jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass('mrp-custom-full-star mrp-star-full').addClass('mrp-custom-empty-star mrp-star-empty');
			} else {
				jQuery("#" + elementId).not('.mrp-minus').removeClass(icon_classes.star_empty).addClass(icon_classes.star_full);
				jQuery("#" + elementId).prevAll().not('.mrp-minus').removeClass(icon_classes.star_empty).addClass(icon_classes.star_full);
				jQuery("#" + elementId).nextAll().not('.mrp-minus').removeClass(icon_classes.star_full).addClass(icon_classes.star_empty);
			}
			
			updateSelectedHiddenValue(elementId);
		}
	}
	touchData.started = null;
	touchData.touch = null;
}

/**
 * Thumbs down touch
 */
function thumbsDownTouch(e) {
	var elementId = e.currentTarget.id;
	
	var now = new Date().getTime();
	// Detecting if after 200ms if in the same position.
	if ((touchData.started !== null)
			&& ((now - touchData.started) < 200)
			&& (touchData.touch !== null)) {
		var touch = touchData.touch;
		var xCoord = touch.pageX;
		var yCoord = touch.pageY;
		if ((touchData.previousXCoord === xCoord)
				&& (touchData.previousYCoord === yCoord)) {
			
			jQuery("#" + elementId).removeClass(icon_classes.thumbs_down_off).addClass(icon_classes.thumbs_down_on);
			jQuery("#" + elementId).next().removeClass(icon_classes.thumbs_up_on).addClass(icon_classes.thumbs_up_off);
			
			updateSelectedHiddenValue(elementId);
		}
	}
	touchData.started = null;
	touchData.touch = null;
}

/**
 * Thumbs up touch
 */
function thumbsUpTouch(e) {
	var elementId = e.currentTarget.id;
	
	var now = new Date().getTime();
	// Detecting if after 200ms if in the same position.
	if ((touchData.started !== null)
			&& ((now - touchData.started) < 200)
			&& (touchData.touch !== null)) {
		var touch = touchData.touch;
		var xCoord = touch.pageX;
		var yCoord = touch.pageY;
		if ((touchData.previousXCoord === xCoord)
				&& (touchData.previousYCoord === yCoord)) {
			
			jQuery("#" + elementId).removeClass(icon_classes.thumbs_up_off).addClass(icon_classes.thumbs_up_on);
			jQuery("#" + elementId).prev().removeClass(icon_classes.thumbs_down_on).addClass(icon_classes.thumbs_down_off);
			
			updateSelectedHiddenValue(elementId);
		}
	}
	touchData.started = null;
	touchData.touch = null;
}

/**
 * Updates the rating item status to either hovered or clicked
 */
function updateRatingItemStatus(elementId, status) {
	var ratingItemIdSequence = getRatingItemIdSequence(elementId);
	if (ratingItemIdSequence != null) {
		ratingItemStatus[ratingItemIdSequence] = status;
	}
}

/**
 * Retrieves the rating item id sequence used to store the status of a rating item option
 */
function getRatingItemIdSequence(elementId) {
	var parts = elementId.split("-"); 
	
	var ratingItemId = parts[4]; /// skip 2: rating-item-
	var sequence = parts[5];
	
	var ratingItemIdSequence = 'rating-item-' + ratingItemId + '-' + sequence;
	return ratingItemIdSequence;
}

/**
 * Updates the selected hidden value for a rating item
 */
function updateSelectedHiddenValue(elementId) {
	
	// id is in format "index-3-rating-item-2-1"
	
	var parts = elementId.split("-"); 
	var value = parts[1]; // this is the star index
	var ratingItemId = parts[4]; /// skipt 4: e.g. index-4-rating-item-58-1 so we get 58
	var sequence = parts[5];
	    		
	// update hidden value for storing selected option
	var hiddenValue = '#rating-item-'+ ratingItemId + '-' + sequence;
	    		
	jQuery(hiddenValue).val(value);
}

/**
 * Binds rating form events
 */
function bindRatingFormEvents(id) {
	
	jQuery("#saveBtn-" + id).on("click", function(e) {
		saveRating(e);
	});
	jQuery("#deleteBtn-" + id).on("click", function(e) {
		deleteRating(e);
	});
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-star-empty, #rating-form-" + id + " .mrp-star-rating-select .mrp-star-full").on("click", function(e) {
		starRatingClick(e);
	});
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-star-empty, " +
			"#rating-form-" + id + " .mrp-star-rating-select .mrp-star-full").on("mouseenter", function(e) {
		starRatingOnHover(e);
	});
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-star-empty, " +
			"#rating-form-" + id + " .mrp-star-rating-select .mrp-star-full").on(" mouseleave", function(e) {
		starRatingOffHover(e);
	});
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-minus").on("click", function(e) {
		starRatingMinusClick(e);
	});
	jQuery("#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-on, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-off").on("click", function(e) {
		thumbsUpClick(e);
	});
	jQuery("#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-on, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-off").on("click", function(e) {
		thumbsDownClick(e);
	});
	jQuery("#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-off, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-on").on("touchend touchcancel", function(e) {
		thumbsDownTouch(e);
	});
	jQuery("#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-off, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-on").on("touchend touchcancel", function(e) {
		thumbsUpTouch(e);
	});
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-star-empty, #rating-form-" + id + " .mrp-star-rating-select .mrp-star-full, " +
			"#rating-form-" + id + " .mrp-star-rating-select .mrp-minus").on("touchend touchcancel", function(e) {
		starRatingTouch(e);
	});
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-star-empty, #rating-form-" + id + " .mrp-star-rating-select .mrp-star-full, " +
			"#rating-form-" + id + " .mrp-star-rating-select .mrp-minus, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-on, " +
			"#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-off, .mrp-thumbs-select .mrp-thumbs-down-on, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-on").on("touchstart", function(e) {
		touchStart(e);
	});
}

/**
 * Unbinds rating form events
 */
function unbindRatingFormEvents(id) {
	
	// off() will remove boths touch and click event handlers
	
	jQuery("#saveBtn-" + id).off();
	jQuery("#deleteBtn-" + id).off();
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-star-empty, #rating-form-" + id + " .mrp-star-rating-select .mrp-star-full").off();
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-minus, #rating-form-" + id + " .mrp-star-rating-select .mrp-star-empty, #rating-form-" + id + " .mrp-star-rating-select .mrp-star-full").off();
	jQuery("#rating-form-" + id + " .mrp-star-rating-select .mrp-minus").off();
	
	jQuery("#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-on, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-off").off();
	jQuery("#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-on, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-off").off();
	jQuery("#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-off, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-up-on").off();
	jQuery("#rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-off, #rating-form-" + id + " .mrp-thumbs-select .mrp-thumbs-down-on").off();
}

/**
 * Binds the WP Comment form events
 */
function bindCommentFormEvents() {
	
	jQuery(".mrp-comment-form-field .mrp-star-rating-select .mrp-star-empty, .mrp-comment-form-field .mrp-star-rating-select .mrp-star-full").on("click", function(e) {
		starRatingClick(e);
	});
	jQuery(".mrp-comment-form-field .mrp-star-rating-select .mrp-minus, .mrp-comment-form-field .mrp-star-rating-select .mrp-star-empty, " +
			".mrp-comment-form-field .mrp-star-rating-select .mrp-star-full").on("mouseenter", function(e) {
		starRatingOnHover(e);
	});
	jQuery(".mrp-comment-form-field .mrp-star-rating-select .mrp-minus, .mrp-comment-form-field .mrp-star-rating-select .mrp-star-empty, " +
	".mrp-comment-form-field .mrp-star-rating-select .mrp-star-full").on("mouseleave", function(e) {
		starRatingOffHover(e);
	});
	jQuery(".mrp-comment-form-field .mrp-star-rating-select .mrp-minus").on("click", function(e) {
		starRatingMinusClick(e);
	});
	jQuery(".mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-down-on, .mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-down-off").on("click", function(e) {
		thumbsUpClick(e);
	});
	jQuery(".mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-up-on, .mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-up-off").on("click", function(e) {
		thumbsDownClick(e);
	});
	jQuery(".mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-down-off, .mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-down-on").on("touchend touchcancel", function(e) {
		thumbsDownTouch(e);
	});
	jQuery(".mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-up-off, .mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-up-on").on("touchend touchcancel", function(e) {
		thumbsUpTouch(e);
	});
	jQuery(".mrp-comment-form-field .mrp-star-rating-select .mrp-star-empty, .mrp-comment-form-field .mrp-star-rating-select .mrp-star-full, " +
			".mrp-comment-form-field .mrp-star-rating-select .mrp-minus").on("touchend touchcancel", function(e) {
		starRatingTouch(e);
	});
	jQuery(".mrp-comment-form-field .mrp-star-rating-select .mrp-star-empty, .mrp-comment-form-field .mrp-star-rating-select .mrp-star-full, " +
			".mrp-comment-form-field .mrp-star-rating-select .mrp-minus, .mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-up-on, " +
			".mrp-comment-form-field .mrp-thumbs-select .mrp-thumbs-up-off, .mrp-comment-form-field.mrp-thumbs-select .mrp-thumbs-down-on, " +
			".mrp-thumbs-select .mrp-thumbs-down-on").on("touchstart", function(e) {
		touchStart(e);
	});
}

jQuery(document).ready(function() {	
	
	jQuery("#include-rating").change(function() {
		if (this.checked) {
			jQuery("p.mrp-comment-form-field").show("slow", function() {});
		} else {
			jQuery("p.mrp-comment-form-field").hide("slow", function() {});
		}
	});
	
	var rowActions = jQuery(".user-ratings-dashboard-list td.rating-actions a");
	jQuery.each(jQuery(".user-ratings-dashboard-list td.rating-actions a"), function(index, element) {
		
		jQuery(element).click(function(event) { 
			
			var anchorId = this.id;
			var parts = anchorId.split("-"); 
			var action = parts[0];
			var ratingFormId = parts[1];
			var postId = parts[2];
			var sequence = parts[3];
			
			var id = ratingFormId + '-' + postId +'-' + sequence;
			
			if (action == 'edit' && ! jQuery("#rating-form-" + id).length) {
				
				var data = {
						action : "get_rating_form",
						nonce : mrp_frontend_data.ajax_nonce,
						postId : postId,
						ratingFormId : ratingFormId,
						sequence : sequence
				};
				
				var spinnerId = 'mrp-spinner-' + id;
				
				// only one user ratings dashboard supported on a page...
				var colspan = jQuery(".user-ratings-dashboard-list table:first").find("tr:first th").length;
				
				jQuery("<i style=\"margin-left: 10px;\" id=\"" + spinnerId + "\"class=\"" 
						+ icon_classes.spinner + "\">").insertAfter(".user-ratings-dashboard-list table tr.rating-info-" + id + " td.rating-actions a:last");
				
				jQuery.post(mrp_frontend_data.ajax_url, data, function(response) {
					var responseJSON = jQuery.parseJSON(response);
					
					jQuery("<tr class=\"rating-edit-" + id + "\"><td colspan=\"" + colspan + "\">" + responseJSON.data.html + "</td></tr>").insertAfter("tr.rating-info-" + id);
					
					jQuery("#edit-" + id).css("display", "none");
					jQuery("#cancel-" + id).css("display", "inline-block");
					
					jQuery("#rating-form-" + id).show("slow", function() {});
					jQuery("#" + spinnerId).remove();
					
					bindRatingFormEvents(id);
					
					jQuery("#cancel-" + id).on("click", function(e) {
						
						//jQuery(".user-ratings-dashboard-list table tr.rating-edit-" + id).hide("slow", function() {
							jQuery(".user-ratings-dashboard-list table tr.rating-edit-" + id).remove();
						//});
						
						jQuery("#edit-" + id).css("display", "inline-block");
						jQuery("#cancel-" + id).css("display", "none");
						
						unbindRatingFormEvents(id);
					});
				});
			}
			
			return false;
		});
	});
	
	var ratingForms = jQuery(".rating-form form");
	jQuery.each(ratingForms, function(key, value) {
		var parts = value.id.split("-"); // e.g. rating-form-2-1-0
		var id = parts[2] + "-" + parts[3] + "-" + parts[4];
		bindRatingFormEvents(id);
	});
	
	bindCommentFormEvents();

	jQuery( ".rating-entry-details-list .load-more").on("click", function(e) {
		
		e.preventDefault();

		var anchorId = this.id;
		var parts = anchorId.split("-"); 
		var sequence = parts[2];
		var params = jQuery("#params-" + sequence).val();

		var data = {
				action : "rating_entries_details_list",
				nonce : mrp_frontend_data.ajax_nonce,
				params : params,
				sequence : sequence
		};
		
		jQuery('<i style="margin-left: 10px;" id="mrp-spinner-' + sequence + '" class="' + icon_classes.spinner + '"></i>').insertAfter('#load-more-' + sequence);	

		jQuery.post(mrp_frontend_data.ajax_url, data, function(response) {
			
			var jsonResponse = jQuery.parseJSON(response);

			if (jsonResponse.status == 'success') {
				
				// remove spinner & replace params
				jQuery("#mrp-spinner-" + sequence).remove();
				jQuery("#params-" + sequence).val(JSON.stringify(jsonResponse.data.params)); 
				
				// append the more html with a slide down animation effect
				if (jsonResponse.data.params.layout == "table") {
					jQuery(jsonResponse.html).hide().insertBefore(".rating-entry-details-list .rating-entry-details-list-inner .load-more-row").slideDown("slow");
				} else {
					jQuery(jsonResponse.html).hide().insertBefore(".rating-entry-details-list-inner .load-more").slideDown("slow");
				}

				// remove the load more if no longer required
				if (jsonResponse.data.has_more == false) {
					jQuery("#load-more-" + jsonResponse.data.sequence).remove();
				}
			}
		});
		
	});
});