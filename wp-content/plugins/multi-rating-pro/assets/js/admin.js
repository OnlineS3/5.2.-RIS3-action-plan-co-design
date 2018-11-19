jQuery(document).ready(function() {

	// Metabox toggle
	jQuery(".if-js-closed").removeClass("if-js-closed").addClass("closed");

	/**
	 * Handles taxonomy change in the Rating Results List Widget
	 */
	function widgetChangeTaxonomy(elementId) {
		// retrieve widget instance
		var parts = elementId.split("-");
		var instance = parts[2];
		var name =  parts[1];

		// retrieve selected taxonomy
		var taxonomy = jQuery("#" + elementId).val();

		if (taxonomy == "") {
			var termSelect = jQuery("#widget-" + name + "-" + instance + "-term_id");
			termSelect.empty();
			termSelect.prepend("<option value=\"\"></option>");
			return;
		}

		// ajax call to retrieve new terms
		var data = {
				action : "get_terms_by_taxonomy",
				nonce : mrp_admin_data.ajax_nonce, // tbc
				taxonomy : taxonomy
		};

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
				var jsonResponse = jQuery.parseJSON(response);

				var termSelect = jQuery("#widget-" + name + "-" + instance + "-term_id");
				termSelect.empty();

				var index = jsonResponse.length-1;
				for (index; index>=0; index--) {
					termSelect.prepend("<option value=\"" + jsonResponse[index]["term_id"] + "\">" + jsonResponse[index]["name"] + "</option>");
				}
		});
	}

	jQuery(document).on('widget-updated', function(e, widget) { // save widget unbinds events...
		jQuery(".widget .mrp-rating-results-widget-taxonomy, .widget .mrp-user-rating-results-widget-taxonomy").change(function(e) {
			widgetChangeTaxonomy(this.id);
		});
	});

	jQuery(".widget .mrp-rating-results-widget-taxonomy, .widget .mrp-user-rating-results-widget-taxonomy").change(function(e) {
		widgetChangeTaxonomy(this.id);
	});


	/**
	 * Clear database tools
	 */
	jQuery("#clear-db-btn").live('click',function(e) {

		var result = confirm(mrp_admin_data.strings.confirm_clear_db_message);
		if (result == true) {
			jQuery("#clear-db").val("true");
		} else {
			event.preventDefault();
		}

	});

	/**
	 * Export ratings tools
	 */
	jQuery("#export-btn").click(function(event) {
		jQuery("#export-rating-results").val("true");
	});

	/**
	 * Import ratings tools
	 */
	jQuery("#import-db-btn").click(function(event) {

		var result = confirm(mrp_admin_data.strings.confirm_import_db_message);

		if (result == true) {
			jQuery("#import-db").val("true");
		} else {
			event.preventDefault();
		}

	});

	/**
	 * Refresh database tools
	 */
	jQuery("#refresh-db-btn").click(function(event) {
		jQuery("#refresh-db").val("true");
	});

	/**
	 * Clean database tools
	 */
	jQuery("#clean-db-btn").click(function(event) {
		jQuery("#clean-db").val("true");

	});


	/**
	 * Rating moderation update entry status
	 */
	function updateEntryStatus(e) {

		var anchorId = e.id; // e.g. entry_approved-82
		var parts = anchorId.split("-");
		var column = parts[0];
		var rowId = parts[1];

		var entryStatus = jQuery("#" + anchorId).hasClass("approved") ? "approved" : "pending";

		if (column == 'entry_status') {

			var data =  {
					action : "update_entry_status",
					nonce : mrp_admin_data.ajax_nonce,
					ratingEntryId : rowId,
					entryStatus : entryStatus
				};

			jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
				var jsonResponse = jQuery.parseJSON(response);

				var ratingEntryId = jsonResponse.rating_entry_id;

				// anchor will be opposit to what the current rating entry is, so that it can be changed
				var anchorClass = (jsonResponse.entry_status == "approved") ? "pending" : "approved"; // class is used to identify what new entry status will be changed to
				var anchorText = (jsonResponse.entry_status == "approved") ? mrp_admin_data.strings.unapprove_anchor_text : mrp_admin_data.strings.approve_anchor_text;

				// this needs to be current
				var entryStatusText = (jsonResponse.entry_status == "approved") ? mrp_admin_data.strings.approved_entry_status_text : mrp_admin_data.strings.pending_entry_status_text;

				jQuery("#entry_status-" + ratingEntryId).replaceWith('<a href="#" id="' + anchorId + '" class="' + anchorClass+ '">' + anchorText + '</a>');
				jQuery("#entry_status_text-" + ratingEntryId).html(entryStatusText);

				// bind event to new element
				jQuery("#entry_status-" + ratingEntryId).click(function(e) {
					updateEntryStatus(this);
				});
			});
		}

		// stop event
		event.preventDefault();
	}

	var rowActions = jQuery("#rating-entries-table-form .row-actions > a");
	jQuery.each(rowActions, function(index, element) {
		jQuery(element).click(function(e) {
			updateEntryStatus(this);
		});
	});

	/**
	 * Pickers
	 */
	jQuery(document).ready(function() {

		jQuery('.color-picker').wpColorPicker({
		    defaultColor: false,
		    change: function(event, ui){},
		    clear: function() {},
		    hide: true,
		    palettes: true
		});

	    jQuery('.date-picker').datepicker({
	        dateFormat : 'yy-mm-dd'
	    });

	});


	/**
	 * Displays the media uploader for selecting an image.
	 *
	 * @param starImage star image name for media uploader
	 */
	function renderMediaUploader(starImage) {

	    var file_frame, image_data;

	    /**
	     * If an instance of file_frame already exists, then we can open it
	     * rather than creating a new instance.
	     */
	    if (undefined !== file_frame) {
	        file_frame.open();
	        return;
	    }

	    /**
	     * If we're this far, then an instance does not exist, so we need to
	     * create our own.
	     *
	     * Here, use the wp.media library to define the settings of the Media
	     * Uploader. We're opting to use the 'post' frame which is a template
	     * defined in WordPress core and are initializing the file frame
	     * with the 'insert' state.
	     *
	     * We're also not allowing the user to select more than one image.
	     */
	    file_frame = wp.media.frames.file_frame = wp.media({
	        frame:    "post",
	        state:    "insert",
	        multiple: false
	    });

	    /**
	     * Setup an event handler for what to do when an image has been
	     * selected.
	     *
	     * Since we're using the 'view' state when initializing
	     * the file_frame, we need to make sure that the handler is attached
	     * to the insert event.
	     */
	    file_frame.on("insert", function() {

	    	// Read the JSON data returned from the Media Uploader
	        var json = file_frame.state().get("selection").first().toJSON();

	        // After that, set the properties of the image and display it
	        jQuery("#" + starImage + "-preview").attr("src", json.url).css("display", "block");

	        // Store the image's information into the meta data fields
	        jQuery("#" + starImage).val(json.url);
	    });

	    // Now display the actual file_frame
	    file_frame.open();

	}

	/**
	 * Custom images
	 */
	jQuery("#custom-full-star-img-upload-btn, #custom-half-star-img-upload-btn, #custom-empty-star-img-upload-btn, #custom-hover-star-img-upload-btn").on("click", function(evt) {
        // Stop the anchor's default behavior
        evt.preventDefault();

        var btnId = this.id;
        var index = btnId.indexOf("-upload-btn");
		var starImage = btnId.substring(0, index);

        // Display the media uploader
        renderMediaUploader(starImage);
    });


	jQuery("#use-custom-star-images").change(function() {
		if (this.checked) {
			jQuery("#custom-star-images-details").show("slow", function() {});
		} else {
			jQuery("#custom-star-images-details").hide("slow", function() {});
		}
	});

	jQuery('form.filter select[name="filter-type"]').change(function(e) {
		var parts = jQuery(this).closest(".postbox")[0].id.split("-");
		var index = parts[1]; // filter-X

		changeFilterType(index);
	});


	/**
	 * Change filter type
	 */
	function changeFilterType(index) {

		var filterType = jQuery('#filter-' + index + ' form.filter select[name="filter-type"]').find("option:selected").val();
		var filterTypeRow = jQuery("#filter-" + index + " form.filter tr.filter-type-row");

		var data = {
				action : "change_filter_type",
				nonce : mrp_admin_data.ajax_nonce,
				filterType : filterType
		};

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			if (jsonResponse.success == true) {

				filterTypeRow.nextAll().remove();
				filterTypeRow.parent().append(jsonResponse.html);

				// change filter all
				jQuery('div#filter-' + index + ' form.filter input[type="checkbox"][value=""][name="terms"], ' +
						'div#filter-' + index + ' form.filter input[type="checkbox"][value=""][name="post-types"]').change(function(e) {
					changeFilterAll(this.name, index, this.checked);
				});

				if (filterType == "taxonomy") {

					jQuery('#filter-' + index + ' form select[name="taxonomy"]').change(function(e) {
						changeTaxonomy(index);
					});

					jQuery("div#filter-" + index + " form.filter .more-terms").on("click", function(e) {
						e.preventDefault();
						jQuery(this).next().css("display", "block");
						jQuery("div#filter-" + index + " form.filter .more-terms").remove();
					});
				}

			}
		});
	}

	jQuery('form.filter select[name="taxonomy"]').change(function(e) {
		var parts = jQuery(this).closest(".postbox")[0].id.split("-");
		var index = parts[1]; // filter-X

		changeTaxonomy(index);
	});

	/**
	 * Changes taxonony
	 */
	function changeTaxonomy(index) {

		var taxonomy = jQuery('#filter-' + index + ' form select[name="taxonomy"]').val();

		var data =  {
				action : "get_terms",
				nonce : mrp_admin_data.ajax_nonce,
				taxonomy : taxonomy
		};

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			if (jsonResponse.success == true && jsonResponse.html) {

				var filterTermsCol = jQuery("div#filter-" + index + " form.filter .filter-terms-col");

				filterTermsCol.empty();
				filterTermsCol.append(jsonResponse.html);

				jQuery("div#filter-" + index + " form.filter .more-terms").on("click", function(e) {
					e.preventDefault();
					jQuery(this).next().css("display", "block");
					jQuery("div#filter-" + index + " form.filter .more-terms").remove();
				});

				// change filter all
				jQuery('div#filter-' + index + ' form.filter input[type="checkbox"][value=""][name="terms"]').change(function(e) {
					changeFilterAll(this.name, index, this.checked);
				});
			}
		});
	}



	/**
	 * Change filter all checkbox. If all is checked, check and disable unused checkboxes
	 */
	function changeFilterAll(name, index, checked) {
		if (checked) {
			jQuery('#filter-' + index + ' input[type="checkbox"][value!=""][name="' + name + '"]').prop('checked', true);
			jQuery('#filter-' + index + ' input[type="checkbox"][value!=""][name="' + name + '"]').prop('disabled', true);
		} else {
			jQuery('#filter-' + index + ' input[type="checkbox"][value!=""][name="' + name + '"]').prop('disabled', false);
			jQuery('#filter-' + index + ' input[type="checkbox"][value!=""][name="' + name + '"]').prop('checked', false);
		}
	}

	/**
	 * On change all for the terms, taxonomies and post types filters
	 */
	jQuery('form.filter input[type="checkbox"][value=""][name="terms"], ' +
			'form.filter input[type="checkbox"][value=""][name="post-types"]').change(function(e) {
		var parts = jQuery(this).closest(".postbox")[0].id.split("-");
		var index = parts[1]; // filter-X

		changeFilterAll(this.name, index, this.checked);
	});

	/**
	 * Clicks save filter button
	 */
	jQuery("form.filter").submit(function(e) {

		e.preventDefault();

		var parts = jQuery(this).closest(".postbox")[0].id.split("-");
		var index = parts[1]; // filter-X

		saveFilter(index);
	});

	/**
	 * Saves a filter
	 */
	function saveFilter(index) {

		var filterName = jQuery('#filter-' + index + ' form input[name="filter-name"]').val();
		var filterType = jQuery('#filter-' + index + ' form select[name="filter-type"]').val();

		var taxonomy = '';
		if (jQuery('#filter-' + index + ' form select[name="taxonomy"]').length > 0) {
			taxonomy = jQuery('#filter-' + index + ' form select[name="taxonomy"]').val();
		}

		var terms = [];
		jQuery('#filter-' + index + ' form input:checkbox[name="terms"]:checked').each(function(){
			terms.push(jQuery(this).val());
		});

		var postTypes = [];
		jQuery('#filter-' + index + ' form input:checkbox[name="post-types"]:checked').each(function(){
			postTypes.push(jQuery(this).val());
		});

		var postIds = '';
		if (jQuery('#filter-' + index + ' form textarea[name="post-ids"]').length > 0) {
			postIds = jQuery('#filter-' + index + ' form textarea[name="post-ids"]').val();
		}
		var pageUrls = '';
		if (jQuery('#filter-' + index + ' form textarea[name="page-urls"]').length > 0) {
			pageUrls  = jQuery('#filter-' + index + ' form textarea[name="page-urls"]').val();
		}
		var ratingFormId = jQuery('#filter-' + index + ' form select[name="rating-form-id"]').val();
		var ratingFormPosition = jQuery('#filter-' + index + ' form select[name="rating-form-position"]').val();
		var ratingResultsPosition = jQuery('#filter-' + index + ' form select[name="rating-results-position"]').val();
		var priority = jQuery('#filter-' + index + ' form input[name="priority"]').val();
		var overridePostMeta = jQuery('#filter-' + index + ' form input[name="override-post-meta"]').is(':checked');

		var data =  {
				action : "save_filter",
				nonce : mrp_admin_data.ajax_nonce,
				index : index,
				filterName : filterName,
				filterType : filterType,
				taxonomy : taxonomy,
				terms : terms,
				postTypes : postTypes,
				ratingFormId : ratingFormId,
				ratingFormPosition : ratingFormPosition,
				ratingResultsPosition : ratingResultsPosition,
				priority : priority,
				overridePostMeta : overridePostMeta,
				postIds : postIds,
				pageUrls : pageUrls
		};

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			// remove any previous message
			jQuery("div#filter-" + index + " div.updated, div#filter-" + index
					+ " div.error, div#filter-" + index + " div.update-nag").remove();

			if (jsonResponse.success == true) {

				if (jsonResponse.data.messages_html) {
					jQuery(jsonResponse.data.messages_html).insertBefore("div#filter-" + index + " form");
				}

				jQuery("div#filter-" + index + " h3 span").remove();

				var html = '<span>' + jsonResponse.data.name + '</span>';

				jQuery("div#filter-" + index + " h3").append(html);
			}
		});
	}

	/**
	 * Clicks delete filter button
	 */
	jQuery("form.filter .delete-filter-btn").on("click", function(e) {
		var parts = jQuery(this).closest(".postbox")[0].id.split("-");
		var index = parts[1]; // filter-X

		deleteFilter(index);
	});

	jQuery("form.filter .more-terms").on("click", function(e) {
		e.preventDefault();
		jQuery(this).next().css("display", "block");
		jQuery("div#filter-" + index + " form.filter .more-terms").remove(); // fixme index undeclared...
	});

	/**
	 * Deletes a filter
	 */
	function deleteFilter(index) {

		var data = {
				action : "delete_filter",
				nonce : mrp_admin_data.ajax_nonce,
				index : index
		};

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			if (jsonResponse.success == true) {
				jQuery("div#filter-" + index).remove();
			}
		});
	}

	/**
	 * Add filter
	 */
	jQuery("#add-filter").on("click", function(e) {

		var data = {
				action : "add_filter",
				nonce : mrp_admin_data.ajax_nonce
		};

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			jQuery("#postbox-container #normal-sortables").prepend(jsonResponse.html);

			jQuery(".postbox .hndle, .postbox .handlediv , .postbox a.dismiss, .hide-postbox-tog").unbind("click.postboxes");
			postboxes.add_postbox_toggles('mrp');

			// delete filter
			jQuery("div#filter-" + jsonResponse.data.index + " .delete-filter-btn").on("click", function(e) {
				deleteFilter(jsonResponse.data.index);
			});

			// change filter type
			jQuery('div#filter-' + jsonResponse.data.index + ' select[name="filter-type"]').on("change", function(e) {
				changeFilterType(jsonResponse.data.index);
			});

			// change filter all
			jQuery('div#filter-' + jsonResponse.data.index + ' form.filter input[type="checkbox"][value=""][name="terms"], ' +
					'div#filter-' + jsonResponse.data.index + ' form.filter input[type="checkbox"][value=""][name="post-types"]').change(function(e) {
				changeFilterAll(this.name, jsonResponse.data.index, this.checked);
			});

			// save filter
			jQuery("div#filter-" + jsonResponse.data.index + " form.filter").submit(function(e) {
				e.preventDefault();
				saveFilter(jsonResponse.data.index);
			});
		});
	});


	jQuery("#edit-rating-item #add-option-text").on("click", function(e) {
		var clazz = 'alternate';
		if (jQuery("table#option-text tr").length % 2 == 0) {
			clazz = '';
		}

		var html = "<tr class=\"" + clazz + "\">"
				+ "<td><input name=\"option-value[]\" type=\"number\" class=\"small-text\" value=\"\" /></td>"
				+ "<td><input name=\"option-text[]\" type=\"text\" class=\"regular-text\" value=\"\" /></td>"
				+ "<td><a class=\"delete-option-text\" href=\"#\">Delete</a></td>"
				+ "</tr>";

		jQuery("table#option-text tr:last").after(html);

		// add delete option text event
		jQuery("table#option-text tr:last a.delete-option-text").on("click", function(e) {
			jQuery(this).closest("tr").empty();
		});

	});

	jQuery("#edit-rating-item #type").change(function(e) {
		var type = jQuery("#edit-rating-item #type").val();
		if (type == "thumbs") {
			jQuery('#edit-rating-item input[name="only-show-text-options"]').parent().css("display", "none");
		} else {
			jQuery('#edit-rating-item input[name="only-show-text-options"]').parent().css("display", "block");
		}

		if (type == 'thumbs') {
			jQuery('input[name="max-option-value"]').closest("tr").css("display", "none");
			jQuery('input[name="default-option-value"]').closest("tr").css("display", "none");
			jQuery('table#option-text input[name="option-value"]').each(function(index, data) {
				if (data.value > 1) {
					jQuery(this).closest("tr").empty();
				}
			});
		} else {
			jQuery('input[name="max-option-value"]').closest("tr").css("display", "");
			jQuery('input[name="default-option-value"]').closest("tr").css("display", "");
		}
	});

	jQuery("a.delete-option-text").on("click", function(e) {
		jQuery(this).closest("tr").empty();
	});

	/**
	 * Add rating item
	 */
	jQuery(".mrp-add-rating-item").on("click", function(e) {

		var parts = this.id.split("-");
		var id = parts[3]; // mrp-rating-item-X

		var action = "<div class=\"row-actions\"><span class=\"id\">" + mrp_admin_data.strings.id_text + ": " + id + " | </span>"
				+ "<span class=\"edit\"><a href=\"admin.php?page=mrp_rating_items&rating-item-id="
				+ id + "\">" + mrp_admin_data.strings.edit_label + "</a> | </span><span class=\"delete\">"
				+ "<a class=\"submitdelete\" href=\"#\">" + mrp_admin_data.strings.delete_label + "</a></span></div>";

		jQuery(this).closest("li > input").prop('disabled', true);

		addRatingFormItem(this.value, true, 1, id, id, 'rating-item', action);
	});

	/**
	 * Add custom field
	 */
	jQuery(".mrp-add-custom-field").on("click", function(e) {

		var parts = this.id.split("-");
		var id = parts[3]; // mrp-custom-field-X

		var action = "<div class=\"row-actions\"><span class=\"id\">" + mrp_admin_data.strings.id_text + ": " + id + " | </span>"
				+ "<span class=\"edit\"><a href=\"admin.php?page=mrp_custom_fields&custom-field-id="
				+ id + "\">" + mrp_admin_data.strings.edit_label + "</a> | </span><span class=\"delete\">"
				+ "<a class=\"submitdelete\" href=\"#\">" + mrp_admin_data.strings.delete_label + "</a></span></div>";

		jQuery(this).closest("li > input").prop('disabled', true);

		addRatingFormItem(this.value, true, null, id, id, 'custom-field', action);
	});


	/**
	 * Add review field
	 */
	jQuery(".mrp-add-review-field").on("click", function(e) {

		var parts = this.id.split("-");
		var id = parts[3]; // mrp-review-field-X

		var action = "<div class=\"row-actions\"><span class=\"delete\"><a class=\"submitdelete\" href=\"#\">"
				+ mrp_admin_data.strings.delete_label + "</a></span></div>";

		jQuery(this).closest("li > input").prop('disabled', true);

		addRatingFormItem(this.value, true, null, null, id, 'review-field', action);
	});

	/**
	 * Adds rating form item
	 */
	function addRatingFormItem(text, required, weight, meta, id, type, action) {

		var html = "<tr>";
		html += "<td><b>" + text + "</b>";
		html += action;
		html += "<input type=\"hidden\" name=\"id\" value=\"" + id + "\" />";
		html += "<input type=\"hidden\" name=\"type\" value=\"" + type + "\" />";
		html += "</td>";
		html += "<td>";
		if (type == 'rating-item') {
			html += mrp_admin_data.strings.rating_item_label;
		} else if (type == 'custom-field') {
			html += mrp_admin_data.strings.custom_field_label;
		} else {
			html += mrp_admin_data.strings.review_field_label;
		}
		html += "</td>";
		html += "<td><input name=\"required\" type=\"checkbox\" ";
		if (required) {
			html += "checked ";
		}
		html += "/></td>";

		html += "<td>";
		if (weight) {
			html+= "<input type=\"number\" name=\"weight\" class=\"small-text\" value=\"" + weight + "\" />";
		}
		html += "</td>";

		html += "<td>";
		if (type == 'rating-item') {
			html += "<input name=\"not-applicable\" type=\"checkbox\" />";
		}
		html += "</td>";

		html += "</tr>";

		jQuery(".mrp-none").remove();

		jQuery(html).appendTo("table#edit-rating-form tbody").find("td .row-actions .delete a").on("click", function(e) {
			deleteRatingFormItem.call(this);
		});
	}

	jQuery(".mrp-save-rating-form-btn").on("click", function(e) {

		var items = []

		jQuery("#edit-rating-form tbody tr").each(function (index, value) {

			var item = {};

			var inputs = jQuery(this).find("input").each(function (index, value) {
				if (jQuery(this).is(":checkbox")) {
					item[value.name] = value.checked;
				} else {
					item[value.name] = value.value;
				}
			});

			items.push(item);
		});

		var name = jQuery("#name").val();

		var data = {
				action : "save_rating_form",
				nonce : mrp_admin_data.ajax_nonce,
				items : items,
				name : name,
				ratingFormId : jQuery("#ratingFormId").val()
		};

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			if (jsonResponse.data.rating_form_id) {
				jQuery("#ratingFormId").val(jsonResponse.data.rating_form_id);
			}

			// remove any previous message
			jQuery("div#mrp-rating-forms div.updated, div#mrp-rating-forms div.error, div#mrp-rating-forms div.update-nag").remove();

			if (jsonResponse.success == true && jsonResponse.data.messages_html) {
				jQuery(jsonResponse.data.messages_html).insertBefore("div#mrp-rating-forms #poststuff #post-body");
			}
		});
	});


	/**
	 * Switch rating form
	 */
	jQuery("#switch-rating-form").on("click", function(e) {
		var ratingFormId = jQuery(this).prev().val();
		window.location.href = replaceQueryStringParam(window.location.href, "rating-form-id", ratingFormId);
	});

	/**
	 * Delete rating form item
	 */
	jQuery("table#edit-rating-form tr td .row-actions .delete a").on("click", function(e) {
		deleteRatingFormItem.call(this);
	});

	function deleteRatingFormItem() {
		var rowActions = jQuery(this).parent().parent();
		var id = rowActions.next().val();
		var type = rowActions.next().next().val();

		jQuery("#mrp-rating-items, #mrp-custom-fields, #mrp-review-fields").find("li > input#mrp-" + type + "-" + id).prop('disabled', false);
		jQuery(this).closest("tr").remove();

		// if no rows left, add an empty row with colspan
		var count = jQuery("table#edit-rating-form tbody tr").length;

		if (count == 0) {
			var html = "<tr class=\"mrp-none\"><td colspan=\"4\">" + mrp_admin_data.strings.no_items_message + "</td></tr>";
			jQuery("table#edit-rating-form tbody").append(html);
		}
	}

	/**
	 * Replace query string parameter
	 *
	 * @param url
	 * @param paramName
	 * @param paramValue
	 *
	 * @returns url
	 *
	 */
	function replaceQueryStringParam(url, paramName, paramValue) {
		var regex = new RegExp("([?&])" + paramName + "=.*?(&|$)", "i");
		var separator = url.indexOf('?') !== -1 ? "&" : "?";

		if (url.match(regex)) {
			return url.replace(regex, '$1' + paramName + "=" + paramValue + '$2');
		} else {
			return url + separator + paramName + "=" + paramValue;
		}
	}


	/**
	 * Delete rating form
	 */
	jQuery("#mrp-rating-forms table.ratingforms tr td .row-actions .delete a").on("click", function(e) {
		var rowActions = jQuery(this).parent().parent();
		var ratingFormId = rowActions.next().val();

		var data = {
				action : "delete_rating_form",
				nonce : mrp_admin_data.ajax_nonce,
				ratingFormId : ratingFormId
		};

		var context = this;

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			if (jsonResponse.success == true && jsonResponse.data.messages_html) {
				jQuery(jsonResponse.data.messages_html).insertBefore("div#mrp-rating-forms form#rating-form-table-form");

				jQuery(context).closest("tr").remove();

				// if no rows left, add an empty row with colspan
				var count = jQuery("#mrp-rating-forms table.ratingforms tr").length;

				if (count == 0) {
					var html = "<tr class=\"mrp-none\"><td colspan=\"5\">" + mrp_admin_data.strings.no_items_message + "</td></tr>";
					jQuery("#mrp-rating-forms table.ratingforms tbody").append(html);
				}
			}

		});
	});


	/**
	 * Delete custom field
	 */
	jQuery("#mrp-custom-fields table.customfields tr td .row-actions .delete a").on("click", function(e) {
		var rowActions = jQuery(this).parent().parent();
		var customFieldId = rowActions.next().val();

		var data = {
				action : "delete_custom_field",
				nonce : mrp_admin_data.ajax_nonce,
				customFieldId : customFieldId
		};

		var context = this;

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			if (jsonResponse.success == true && jsonResponse.data.messages_html) {
				jQuery(jsonResponse.data.messages_html).insertBefore("div#mrp-custom-fields form#custom-field-table-form");

				jQuery(context).closest("tr").remove();

				// if no rows left, add an empty row with colspan
				var count = jQuery("#mrp-custom-fields table.customfields tr").length;

				if (count == 0) {
					var html = "<tr class=\"mrp-none\"><td colspan=\"6\">" + mrp_admin_data.strings.no_items_message + "</td></tr>";
					jQuery("#mrp-custom-fields table.customfields tbody").append(html);
				}
			}

		});
	});


	/**
	 * Delete custom field
	 */
	jQuery("#mrp-rating-items table.ratingitems tr td .row-actions .delete a").on("click", function(e) {
		var rowActions = jQuery(this).parent().parent();
		var ratingItemId = rowActions.next().val();

		var data = {
				action : "delete_rating_item",
				nonce : mrp_admin_data.ajax_nonce,
				ratingItemId : ratingItemId
		};

		var context = this;

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
			var jsonResponse = jQuery.parseJSON(response);

			if (jsonResponse.success == true && jsonResponse.data.messages_html) {
				jQuery(jsonResponse.data.messages_html).insertBefore("div#mrp-rating-items form#rating-item-table-form");

				jQuery(context).closest("tr").remove();

				// if no rows left, add an empty row with colspan
				var count = jQuery("#mrp-rating-items table.ratingitems tr").length;

				if (count == 0) {
					var html = "<tr class=\"mrp-none\"><td colspan=\"6\">" + mrp_admin_data.strings.no_items_message + "</td></tr>";
					jQuery("#mrp-rating-items table.ratingitems tbody").append(html);
				}
			}

		});
	});

	function changeRatingForm() {

		var ratingFormId = jQuery("form#rating-entry select#rating-form-id").val();
		var userId = jQuery("form#rating-entry select#user-id").val();
		var ratingEntryId = jQuery("form#rating-entry #rating-entry-id").val();

		var data = {
				action : "get_edit_rating_form",
				nonce : mrp_admin_data.ajax_nonce,
				ratingFormId : ratingFormId,
				userId : userId,
				ratingEntryId : ratingEntryId
		};

		jQuery.post(mrp_admin_data.ajax_url, data, function(response) {

			var jsonResponse = jQuery.parseJSON(response);
			var ratingForm = jsonResponse.data.rating_form;

			/*
			 * Rating items
			 */
			var newRatingItems = ratingForm.rating_items;
			var oldRatingItems = [];

			jQuery("form#rating-entry .rating-item").each(function(key) {
			var parts = this.id.split("-"); // in format rating-item-#
				var ratingItemId = parts[2];
				oldRatingItems.push(ratingItemId);
			});

			for (var ratingItemId in newRatingItems) {
				var index = oldRatingItems.indexOf(ratingItemId);
				if (index >= 0) {
					oldRatingItems.splice(index, 1);
				} else {
					jQuery("form#rating-entry .rating-item").last().closest(".form-field").after(newRatingItems[ratingItemId].html);
				}

			};

			jQuery(oldRatingItems).each(function(key, value) {
				jQuery("form#rating-entry #rating-item-" + value).closest(".form-field").remove();
			});

			/*
			 * Custom fields
			 */
			var newCustomFields = ratingForm.custom_fields;
			var oldCustomFields = [];

			jQuery("form#rating-entry .custom-field").each(function(key) {
				var parts = this.id.split("-"); // in format custom-field-#
				var customFieldId = parts[2];
				oldCustomFields.push(customFieldId);
			});

			for (var customFieldId in newCustomFields) {
				var index = oldCustomFields.indexOf(customFieldId);
				if (index >= 0) {
					oldCustomFields.splice(index, 1);
				} else {
					jQuery("form#rating-entry .custom-field").last().closest(".form-field").after(newCustomFields[customFieldId].html);
				}

			};

			jQuery(oldCustomFields).each(function(key, value) {
				jQuery("#custom-field-" + value).closest(".form-field").remove();
			});

			/*
			 * Review fields
			 */
			var reviewFields = ratingForm.review_fields;

			var titleElement = jQuery("form#rating-entry input#title");
			if (titleElement.length && reviewFields[1] == undefined) {
				titleElement.closest(".form-field").remove();
			} else if (titleElement.length == 0 && reviewFields[1]) {
				jQuery("form#rating-entry .rating-item").first().closest(".form-field").parent().prepend(reviewFields[1].html);
			}

			var nameElement = jQuery("form#rating-entry input#name");
			if (nameElement.length && reviewFields[2] == undefined) {
				nameElement.closest(".form-field").remove();
			} else if (nameElement.length == 0 && reviewFields[2]) {
				jQuery("form#rating-entry .rating-item").first().closest(".form-field").parent().prepend(reviewFields[2].html);
			}

			var emailElement = jQuery("form#rating-entry input#email");
			if (emailElement.length && reviewFields[3] == undefined) {
				emailElement.closest(".form-field").remove();
			} else if (emailElement.length == 0 && reviewFields[3]) {
				jQuery("form#rating-entry .rating-item").first().closest(".form-field").parent().prepend(reviewFields[3].html);
			}

			var commentElement = jQuery("form#rating-entry input#comment");
			if (commentElement.length && reviewFields[4] == undefined) {
				commentElement.closest(".form-field").remove();
			} else if (commentElement.length == 0 && reviewFields[4]) {
				jQuery("form#rating-entry .rating-item").first().closest(".form-field").parent().prepend(reviewFields[4].html);
			}
		});
	}
	jQuery("form#rating-entry select#rating-form-id").change(function(e) {
		changeRatingForm();
	});

	function changeUserId() {
		var userId = jQuery("form#rating-entry select#user-id").val();

		if (userId == 0) {
			jQuery("form#rating-entry input#name").val('').prop('disabled', false);
			jQuery("form#rating-entry input#email").val('').prop('disabled', false);
		} else {
			var data = {
				action : "get_user_info",
				nonce : mrp_admin_data.ajax_nonce,
				userId : userId
			};

			jQuery.post(mrp_admin_data.ajax_url, data, function(response) {
				var jsonResponse = jQuery.parseJSON(response);
				jQuery("form#rating-entry input#name").val(jsonResponse.data.name).prop('disabled', true);
				jQuery("form#rating-entry input#email").val(jsonResponse.data.email).prop('disabled', true);
			});
		}
	}
	jQuery("form#rating-entry select#user-id").change(function(e) {
		changeUserId();
	});









});

jQuery(window).load(function() {

	if (jQuery("#mrp-rating-forms").length > 0 || jQuery("#add-filter").length > 0) {
		jQuery(".postbox .hndle, .postbox .handlediv , .postbox a.dismiss, .hide-postbox-tog").unbind("click.postboxes");
		postboxes.add_postbox_toggles('multi-rating-pro');
	}

});

