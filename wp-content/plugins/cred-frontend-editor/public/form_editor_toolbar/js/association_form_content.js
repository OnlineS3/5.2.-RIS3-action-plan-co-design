/**
 * Manage the CRED association form editor toolbar.
 *
 * @since m2m
 * @package CRED
 */

var Toolset = Toolset || {};

if ( typeof Toolset.CRED === "undefined" ) {
    Toolset.CRED = {};
}

Toolset.CRED.AssociationFormsContentEditorToolbar = {};

Toolset.CRED.AssociationFormsContentEditorToolbar.Class = function( $ ) {
	
	var self = this;
	
	self.i18n = cred_association_form_content_editor_toolbar_i18n;
	
	self.relationshipCache = {};
	
	/**
	 * Init Toolset hooks.
	 *
	 * @uses Toolset.hooks
	 * @since m2m
	 */
	self.initHooks = function() {
		Toolset.hooks.addAction( 'toolset_text_editor_CodeMirror_init', self.initScaffoldButton );
        Toolset.hooks.addAction( 'toolset-action-init-cancel-link-shortcode', self.manageCancelButtonSettings );
		return self;
	};
	
	/**
	 * Init underscore templates.
	 *
	 * @uses wp.template
	 * @since m2m
	 */
	self.templates = {};
	self.templates.scaffold = {};
	self.templates.fields = {};
	self.initTemplates = function() {
		self.templates.scaffold.dialog = wp.template( 'cred-editor-scaffold-dialog' );
		self.templates.scaffold.item = wp.template( 'cred-editor-scaffold-item' );
		self.templates.scaffold.itemOptions = wp.template( 'cred-editor-scaffold-itemOptions' );
		self.templates.fields.dialog = wp.template( 'cred-editor-fields-dialog' );
		self.templates.fields.item = wp.template( 'cred-editor-fields-item' );
		self.templates.shortcodeGui = Toolset.hooks.applyFilters( 'toolset-filter-get-shortcode-gui-templates', {} );
		
		return self;
	};
	
	/**
	 * Init GUI dialogs:
	 * - Scaffold dialog.
	 * - Fields dialog.
	 * - Field shortcode dialog.
	 *
	 * @uses jQuery.dialog
	 * @since m2m
	 */
	self.dialogs = {};
	
	self.shortcodeDialogSpinnerContent = $(
		'<div style="min-height: 150px;">' +
		'<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; ">' +
		'<div class="ajax-loader"></div>' +
		'<p>' + self.i18n.action.loading + '</p>' +
		'</div>' +
		'</div>'
	);
	
	self.initDialogs = function() {
		
		if ( ! $( '#js-cred-editor-scaffold-dialog-container' ).length ) {
			$( 'body' ).append( '<div id="js-cred-editor-scaffold-dialog-container" class="toolset-shortcode-gui-dialog-container js-toolset-shortcode-gui-dialog-container js-cred-editor-scaffold-dialog-container"></div>' );
		}
		self.dialogs.scaffold = $( '#js-cred-editor-scaffold-dialog-container' ).dialog({
			dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
			autoOpen:	false,
			modal:		true,
			width:		'90%',
			title:		self.i18n.dialog.scaffold.header,
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			open: function( event, ui ) {
				$( 'body' ).addClass('modal-open');
				self.repositionDialog();
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-primary js-cred-editor-scaffold-craft',
					text: self.i18n.action.insert,
					click: function() {
						self.insertScaffold();
					}
				},
				{
					class: 'button-secondary js-cred-editor-scaffold-close',
					text: self.i18n.action.cancel,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});
		
		if ( ! $( '#js-cred-editor-fields-dialog-container' ).length ) {
			$( 'body' ).append( '<div id="js-cred-editor-fields-dialog-container" class="toolset-shortcode-gui-dialog-container js-toolset-shortcode-gui-dialog-container js-cred-editor-fields-dialog-container"></div>' );
		}
		self.dialogs.fields = $( '#js-cred-editor-fields-dialog-container' ).dialog({
			dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
			autoOpen:	false,
			modal:		true,
			width:		'90%',
			title:		self.i18n.dialog.fields.header,
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			open: function( event, ui ) {
				$( 'body' ).addClass('modal-open');
				self.repositionDialog();
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
			}
		});
		
		if ( ! $( '#js-cred-editor-shortcode-dialog-container' ).length ) {
			$( 'body' ).append( '<div id="js-cred-editor-shortcode-dialog-container" class="toolset-shortcode-gui-dialog-container js-toolset-shortcode-gui-dialog-container js-cred-editor-shortcode-dialog-container"></div>' );
		}
		self.dialogs.shortcode = $( '#js-cred-editor-shortcode-dialog-container' ).dialog({
			dialogClass: 'toolset-ui-dialog toolset-ui-dialog-responsive',
			autoOpen:	false,
			modal:		true,
			width:		'90%',
			title:		self.i18n.dialog.shortcode.header,
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			open: function( event, ui ) {
				$( 'body' ).addClass('modal-open');
				self.repositionDialog();
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
			},
			buttons:[
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-primary js-cred-editor-field-shortcode-craft',
					text: self.i18n.action.insert,
					click: function() {
						var shortcodeToInsert = Toolset.hooks.applyFilters( 'toolset-filter-get-crafted-shortcode', false );
						// shortcodeToInsert will fail on validation failure
						if ( shortcodeToInsert ) {
							$( this ).dialog( "close" );
							Toolset.hooks.doAction( 'toolset-action-do-shortcode-gui-action', shortcodeToInsert );
						}
					}
				},
				{
					class: 'toolset-shortcode-gui-dialog-button-align-right button-secondary toolset-shortcode-gui-dialog-button-back js-cred-editor-field-shortcode-back',
					text: self.i18n.action.back,
					click: function() {
						$( this ).dialog( "close" );
						self.openDialog( 'fields' );
					}
				},
				{
					class: 'button-secondary toolset-shortcode-gui-dialog-button-close js-cred-editor-field-shortcode-close',
					text: self.i18n.action.cancel,
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});
		
		$( window ).resize( self.resizeWindowEvent );
		
		return self;
	};
	
	/**
	 * Open a registered dialog on demand.
	 *
	 * @since m2m
	 */
	self.openDialog = function( dialogId ) {
		if ( _.has( self.dialogs, dialogId ) ) {
			self.dialogs[ dialogId ].dialog( 'open' );
		}
	};
	
	/**
	 * Callback for the window.resize event.
	 *
	 * @since m2m
	 */
	self.resizeWindowEvent = _.debounce( function() {
		self.repositionDialog();
	}, 200);

	/**
	 * Reposition the Types dialogs based on the current window size.
	 *
	 * @since m2m
	 */
	self.repositionDialog = function() {
		var winH = $( window ).height() - 100;
		
		_.each( self.dialogs, function( dialog, key, list ) {
			dialog.dialog( "option", "maxHeight", winH );
			dialog.dialog( "option", "position", {
				my:        "center top+50",
				at:        "center top",
				of:        window,
				collision: "none"
			});
		});
		
	};

    /**
	 * Control Cancel button visibility
	 *
	 * @since m2m
     */
    self.manageCancelButtonSettings = function( shortcode, context ){
        if( 'cred-form-cancel' === shortcode ){
            var $pageSelector = $('.js-toolset-shortcode-gui-attribute-wrapper-for-select_page select', context );
            var $ctSelector = $('.js-toolset-shortcode-gui-attribute-wrapper-for-select_ct select', context );

            // Hide fields by default
            $pageSelector.parent().hide();
            $ctSelector.parent().hide();
            self.initOnChangeForCancelButtonSelector( $pageSelector, $ctSelector );
		}
    };

    /**
	 * Control page and CT selector based on selected value
	 *
	 * @since m2m
     */
	self.initOnChangeForCancelButtonSelector = function ( page, ct ) {

        var $pageSelector = page;
        var $ctSelector = ct;

		$('.js-toolset-shortcode-gui-attribute-wrapper-for-action select').change(function () {

			var currentSelectorValue = $( this ).val();

			if( 'different_page_ct' === currentSelectorValue ){
                $pageSelector.parent().show();
                $ctSelector.parent().show();
                self.initSelect2ForSelector( $pageSelector, '', '');
                self.initSelect2ForSelector( $ctSelector, 'view-template', 'post_name' );
			} else if( 'same_page_ct' === currentSelectorValue ){
                $pageSelector.parent().hide();
                $ctSelector.parent().show();
                self.initSelect2ForSelector( $ctSelector, 'view-template', 'post_name' );
			} else {
                $pageSelector.parent().hide();
                $ctSelector.parent().hide();
			}
        });
    };

    /**
	 * Init Select2 for page or CT selectors
	 *
     * @param selector
     * @param postType
     * @param valueType
	 *
	 * @since m2m
     */
	self.initSelect2ForSelector = function (selector, postType, valueType ) {
		var $selector = selector;
        var $selectorParent = $selector.closest( '.js-toolset-shortcode-gui-dialog-container' );

        $selector.toolset_select2({
            width: '300px',
            dropdownParent:	$selectorParent,
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return {
                        s: params.term, // search term
                        action: 'toolset_select2_suggest_posts_by_title',
                        postType:  postType,
                        valueType: valueType,
                        wpnonce: self.i18n.data.scaffold.fields.formElements.cancel.select2nonce
                    };
                },
                type: 'POST',
                processResults: function ( results ) {
                    return {
                        results: ( results.data ) ? results.data : []
                    };
                },
                cache: true
            },
            placeholder: self.i18n.data.scaffold.fields.formElements.cancel.searchPlaceholder,
            minimumInputLength: 3,
            templateResult: function (results) {
                if (results.loading) {
                    return results.text;
                }
                return results.text;
            },
            templateSelection: function (results) {
                return results.text;
            }
        });
    };
	
	/**
	 * Highlight the scaffold button when the editor is empty.
	 *
	 * @param editorId string
	 *
	 * @since m2m
	 */
	self.initScaffoldButton = function( editorId ) {
		var $button = $( '.js-cred-form-content-scaffold' ),
			buttonTargetId = $button.data( 'target' );
		
		if ( editorId != buttonTargetId ) {
			return;
		}
		
		if ( '' == icl_editor.codemirrorGet( editorId ).getValue() ) {
			$button.addClass( 'button-primary-toolset' );
		}
	};
	
	/**
	 * Request the relationship fields for a given relationship.
	 *
	 * @param relationship string.
	 * @param callback callable
	 *
	 * @since m2m
	 */
	self.requestRelationshipFields = function( relationship, callback ) {
		var data = {
			action:       'cred_get_relationship_fields',
			wpnonce:      self.i18n.data.nonce,
			relationship: relationship,
		};
		
		$.ajax({
			url:      self.i18n.data.ajaxurl,
			data:     data,
			dataType: 'json',
			type:     "GET",
			success:  function( originalResponse ) {
				var response = WPV_Toolset.Utils.Ajax.parseResponse( originalResponse );
				if ( response.success ) {
					self.relationshipCache[ relationship ] = response.data;
					callback.call( null, relationship );
				} else {
					
				}
			},
			error: function ( ajaxContext ) {
				
			}
		});
	};
	
	/**
	 * Generate the scaffold dialog content from a relationship fields cache.
	 *
	 * @param relationship string
	 *
	 * @since m2m
	 */
	self.generateScaffoldDialogContentFromCache = function( relationship ) {
		var templateData = $.extend( true, {}, 
			self.relationshipCache[ relationship ], 
			{ templates:    self.templates },
			{ dialog:       self.i18n.dialog.scaffold },
			{ formElements: self.i18n.data.scaffold.fields.formElements }
		);
		
		self.dialogs.scaffold.html( templateData.templates.scaffold.dialog( templateData ) );
		$( '.js-cred-editor-scaffold-item-list' )
			.addClass( 'js-cred-editor-scaffold-item-list-loaded' )
			.sortable({
				handle: ".js-cred-editor-scaffold-item-move",
				axis: 'y',
				containment: ".js-cred-editor-scaffold-item-list-container",
				items: "> li.js-cred-editor-scaffold-item-container",
				helper: 'clone',
				tolerance: "pointer"
			});

        Toolset.hooks.doAction( 'toolset-action-init-cancel-link-shortcode', 'cred-form-cancel', jQuery('.js-cred-editor-scaffold-dialog-container') );
	};
	
	/**
	 * Open the scaffold generator dialog on button click.
	 *
	 * @since m2m
	 */
	$( document ).on( 'click','.js-cred-form-content-scaffold', function( e ) {
		e.preventDefault();
		
		var relationship = $( '#relationship' ).val();
		
		if ( ! relationship ) {
			alert( self.i18n.messages.relationship_missing );
			return;
		}
		
		$( this ).removeClass( 'button-primary-toolset' );
		
		window.wpcfActiveEditor = $( this ).data( 'target' );
		Toolset.hooks.doAction( 'toolset-action-set-shortcode-gui-action', 'insert' );
		
		self.dialogs.scaffold.dialog( 'open' );
		
		if ( _.has( self.relationshipCache, relationship ) ) {
			self.generateScaffoldDialogContentFromCache( relationship );
		} else {
			self.dialogs.scaffold.html( self.shortcodeDialogSpinnerContent );
			self.requestRelationshipFields( relationship, self.generateScaffoldDialogContentFromCache );
		}
	});
	
	/**
	 * Toggle the visibility of the options for the scaffold elements that include them, on switch click.
	 *
	 * @since m2m
	 */
	$( document ).on( 'click', '.js-cred-editor-scaffold-item-options-toggle', function( e ) {
		e.preventDefault();
		var $toggleControl = $( this ),
			$toggleContainer = $toggleControl
				.closest( '.js-cred-editor-scaffold-item-container' )
					.find( '.js-cred-editor-scaffold-item-options' );
		
		$toggleControl.toggleClass( 'fa-caret-down fa-caret-up' );
		$toggleContainer.slideToggle( 'fast' );
	});
	
	/**
	 * Toggle the visibility of optional scaffold elements, on switch click.
	 *
	 * @since m2m
	 */
	$( document ).on( 'click', '.js-cred-editor-scaffold-item-include-toggle', function( e ) {
		e.preventDefault();
		var $toggleControl = $( this ),
			$toggleContainer = $toggleControl.closest( '.js-cred-editor-scaffold-item-container' ),
			toggleStatus = $toggleContainer.data( 'include' );
		
		$toggleControl.toggleClass( 'fa-eye fa-eye-slash' );
		$toggleContainer
			.data( 'include', ! toggleStatus )
			.toggleClass( 'cred-editor-scaffold-item-container-disabled' );
	});
	
	/**
	 * Get the attributes for a scaffold item.
	 *
	 * @param $scaffoldItem jQuery object
	 *
	 * @since m2m
	 */
	self.getScaffoldShortcodeAttributes = function( $scaffoldItem ) {
		var attributes = $scaffoldItem.data( 'attributes' );
		
		attributes = ( _.size( attributes ) == 0 ) ? {} : attributes;
		
		if ( $scaffoldItem.find( '.js-cred-editor-scaffold-item-options' ).length > 0 ) {
			$( '.js-toolset-shortcode-gui-attribute-wrapper', $scaffoldItem ).each( function() {
				var attributeWrapper = $( this ),
					shortcodeAttributeKey = attributeWrapper.data( 'attribute' ),
					shortcodeAttributeValue = '',
					shortcodeAttributeDefaultValue = attributeWrapper.data( 'default' );
				switch ( attributeWrapper.data('type') ) {
					case 'select':
						shortcodeAttributeValue = $('option:checked', attributeWrapper ).val();
						break;
					case 'radio':
						shortcodeAttributeValue = $('input:checked', attributeWrapper ).val();
						break;
					case 'checkbox':
						shortcodeAttributeValue = $('input:checked', attributeWrapper ).val();
						break;
					default:
						shortcodeAttributeValue = $('input', attributeWrapper ).val();
				};
				
				if (
					shortcodeAttributeValue
					&& shortcodeAttributeValue != shortcodeAttributeDefaultValue
				) {
					attributes[ shortcodeAttributeKey ] = shortcodeAttributeValue;
				}
			});
		}
		
		return attributes;
	};
	
	/**
	 * Compose a shortcode given its handle and its attributs in an object.
	 *
	 * @param shortcode  string
	 * @param attributes object
	 *
	 * @return string
	 *
	 * @since @m2m
	 */
	self.craftShortcodeString = function( shortcode, attributes ) {
		var output = '[' + shortcode;
		_.each( attributes, function( value, key, list ) {
			output += ' ' + key + '="' + value + '"';
		});
		output += ']';
		
		return output;
	};
	
	/**
	 * Craft the scaffold output.
	 *
	 * @since m2m
	 */
	self.craftScaffoldOutput = function() {
		var output = '',
			$scaffoldList = $( '.js-cred-editor-scaffold-item-list' ),
			wpmlLocalization = $( '.js-cred-editor-scaffold-options-wpml' ).prop( 'checked' ),
			formSlug = $( '#slug' ).val();
		
		output = '[' + self.i18n.data.shortcodes.form_container + ']';
		
		$( '.js-cred-editor-scaffold-item-container', $scaffoldList ).each( function() {
			if ( ! $( this ).data( 'include' ) ) {
				return;
			}
			
			var shortcode = $( this ).data( 'shortcode' ),
				fieldType = $( this ).data( 'fieldtype' ),
				label = $( this ).data( 'label' ),
				attributes = self.getScaffoldShortcodeAttributes( $( this ) );
			
			if ( 'formElement' == fieldType ) {
				output += "\n\t" + self.craftShortcodeString( shortcode, attributes );
			} else {
				output += "\n\t" + '<div class="form-group">';
				
				output += "\n\t\t" + '<label>';
				if ( wpmlLocalization ) {
					output += "[wpml-string context='cred-form-" + _.escape( formSlug ) + "' name='" + _.escape( label ) + "']";
					output += label;
					output += '[/wpml-string]';
				} else {
					output += label;
				}
				output += '</label>';
				
				output += "\n\t\t" + self.craftShortcodeString( shortcode, attributes );
				
				output += "\n\t" + '</div>';
			}
		});
		
		output += "\n" + '[/' + self.i18n.data.shortcodes.form_container + ']';
		
		return output;
	};
	
	/**
	 * Craft and insert the scaffold into the editor.
	 *
	 * @since m2m
	 */
	self.insertScaffold = function() {
		var scaffold = self.craftScaffoldOutput();
		icl_editor.insert( scaffold );
		self.dialogs.scaffold.dialog( 'close' );
		Toolset.hooks.doAction( 'cred_editor_focus_content_editor' );
	};
	
	/**
	 * Generate the fields dialog content from a relationship fields cache.
	 *
	 * @param relationship string
	 *
	 * @since m2m
	 */
	self.generateFieldsDialogContentFromCache = function( relationship ) {
		var templateData = $.extend( true, {}, 
			self.relationshipCache[ relationship ], 
			{ templates:    self.templates },
			{ dialog:       self.i18n.dialog.fields },
			{ formElements: $.extend( true, {}, self.i18n.data.fields.fields.formElements, self.i18n.data.scaffold.fields.formElements ) }
		);
		
		self.dialogs.fields.html( templateData.templates.fields.dialog( templateData ) );
	};
	
	/**
	 * Open the fields generator dialog on button click.
	 *
	 * @since m2m
	 */
	$( document ).on( 'click', '.js-cred-form-content-fields', function( e ) {
		e.preventDefault();
		
		var relationship = $( '#relationship' ).val();
		
		if ( ! relationship ) {
			alert( self.i18n.messages.relationship_missing );
			return;
		}
		
		window.wpcfActiveEditor = $( this ).data( 'target' );
		Toolset.hooks.doAction( 'toolset-action-set-shortcode-gui-action', 'insert' );
		
		self.dialogs.fields.dialog( 'open' );
		
		if ( _.has( self.relationshipCache, relationship ) ) {
			self.generateFieldsDialogContentFromCache( relationship );
		} else {
			self.dialogs.fields.html( self.shortcodeDialogSpinnerContent );
			self.requestRelationshipFields( relationship, self.generateFieldsDialogContentFromCache );
		}
	});
	
	/**
	 * Insert a field shortcode without extra options.
	 *
	 * @param $fieldButton jQuery object
	 *
	 * @since m2m
	 */
	self.insertOptionlessField = function( $fieldButton ) {
		var shortcode = $fieldButton.data( 'shortcode' ),
			attributes = $fieldButton.data( 'attributes' ),
			output = '';
		
		attributes = ( _.size( attributes ) == 0 ) ? {} : attributes;
		
		output = '[' + shortcode;
		_.each( attributes, function( value, key, list ) {
			output += ' ' + key + '="' + value + '"';
		});
		output += ']';
		
		if ( self.i18n.data.shortcodes.form_container == shortcode ) {
			output += "\n\n";
			output += '[/' + shortcode + ']';
		}
		
		icl_editor.insert( output );
		self.dialogs.fields.dialog( 'close' );
		Toolset.hooks.doAction( 'cred_editor_focus_content_editor' );
	};
	
	/**
	 * Open the shortcode dialog to set options for a field.
	 *
	 * @param $fieldButton jQuery object
	 *
	 * @since m2m
	 */
	self.openFieldDialog = function( $fieldButton ) {
		var shortcode = $fieldButton.data( 'shortcode' ),
			label = $fieldButton.data( 'label' ),
			attributes = $fieldButton.data( 'attributes' ),
			options = $fieldButton.data( 'options' ),
			output = '';
		
		var templateData = $.extend( true, {}, 
			{
				shortcode: shortcode,
				templates:  self.templates.shortcodeGui,
				parameters: attributes,
				attributes: {
					singleGroup: {
						header: self.i18n.dialog.shortcode.group.header,
						fields: options
					}
				}
			}
		);
		
		self.dialogs.fields.dialog( 'close' );
		self.dialogs.shortcode.dialog( 'open' ).dialog({
			title: label
		});
		self.dialogs.shortcode.html( self.shortcodeDialogSpinnerContent );
		
		self.dialogs.shortcode.html( templateData.templates.dialog( templateData ) );
        Toolset.hooks.doAction( 'toolset-action-init-cancel-link-shortcode', shortcode, jQuery('.js-cred-editor-shortcode-dialog-container') );

	};
	
	/**
	 * Manage the click event on each element in the fields dialog.
	 *
	 * @since m2m
	 */
	$( document ).on( 'click', '.js-cred-editor-fields-item', function() {
		var $fieldButton = $( this ),
			options = $fieldButton.data( 'options' );
		if ( 
			options 
			&& ( _.size( options ) > 0 )
		) {
			self.openFieldDialog( $fieldButton );
		} else {
			self.insertOptionlessField( $fieldButton );
		}
	});
	
	self.init = function() {
		self.initHooks()
			.initTemplates()
			.initDialogs();
	};
	
	self.init();
	
};

jQuery( document ).ready( function( $ ) {
    Toolset.CRED.AssociationFormsContentEditorToolbar.main = new Toolset.CRED.AssociationFormsContentEditorToolbar.Class( $ );
});