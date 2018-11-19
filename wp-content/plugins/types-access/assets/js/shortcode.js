/**
 * shortcode.js
 *
 * Contains helper functions for the popup GUI used to set Access shortcode attributes
 *
 * @since 2.3.0
 * @package Toolset Access
 */

var OTGAccess = OTGAccess || {};

OTGAccess.ShortcodesGUI = function( $ ) {
	
	var self = this;
	
	/**
	 * Store the action to perform when generating a shortcode.
	 * - 'insert' will insert the shortcode into the active editor.
	 * - 'create' will create the shortcode in an auxiliar dialog.
	 *
	 * @since 2.3.0
	 */
	self.shortcode_gui_action					= 'insert';
	
	/**
	 * Store the shortcode to insert on the auxiliar dialog.
	 *
	 * @since 2.3.0
	 */
	self.shortcode_to_insert_on_target_dialog	= '';
	
	/**
	 * ----------------------
	 * Dialogs defaults
	 * ----------------------
	 */
	self.shortcode_dialog						= null;
	self.textarea_target_dialog					= null;
	self.dialog_minWidth						= 870;
	
	/**
	 * @since 2.3.0
	 */
	self.calculate_dialog_maxWidth = function() {
		return ( $( window ).width() - 200 );
	};
	
	/**
	 * @since 2.3.0
	 */
	self.calculate_dialog_maxHeight = function() {
		return ( $( window ).height() - 100 );
	};
	
	/**
	 * Init the admin bar button, if any.
	 *
	 * @since 2.3.0
	 */
	self.init_admin_bar_button = function() {
		if ( $( '.js-otg-access-shortcode-generator-node a' ).length > 0 ) {
			$( '.js-otg-access-shortcode-generator-node a' ).addClass( 'js-otg-access-shortcode-gui-in-adminbar' );
		}
	};
	
	/**
	 * Initi dialogs
	 *
	 * @since 2.3.0
	 */
	self.init_dialogs = function() {
		
		self.shortcode_dialog = $( "#wpcf-access-shortcodes-dialog-tpl" ).dialog({
            autoOpen:	false,
            title:		otg_access_shortcodes_gui_texts.dialog_title,
            modal:		true,
            minWidth:	550,
            show:		{
                effect:		"blind",
                duration:	800
            },
            open:		function( event, ui ) {
                $( 'body' ).addClass( 'modal-open' );
                $( ".js-wpcf-access-list-roles" ).prop( "checked", false );
                $( ".js-wpcf-access-shortcode-operator" ).prop( 'checked', false );
                $( ".js-wpcf-access-shortcode-format" ).prop( 'checked', false );
                $( ".js-wpcf-access-conditional-message" ).val( '' );
                $( '.js-wpcf-access-craft-shortcode' )
                    .prop( 'disabled', true )
                    .addClass( 'button-secondary' )
                    .removeClass( 'button-primary' );
				self.manage_shortcode_dialog_button_labels();
            },
            close:		function (event, ui) {
                $( 'body' ).removeClass( 'modal-open' );
            },
            buttons: [
                {
                    class:	'button button-secondary toolset-shortcode-gui-dialog-button-close js-wpcf-access-discard-shortcode',
                    text:	otg_access_shortcodes_gui_texts.cancel,
                    click:	function () {
						self.shortcode_gui_action = 'insert';
                        $( this ).dialog( "close" );
                    }
                },
                {
                    class:		'button button-secondary js-wpcf-access-craft-shortcode',
                    text:		otg_access_shortcodes_gui_texts.insert_shortcode,
                    click:		function () {
                        self.craft_shortcode();
                    },
                }
            ]
        });
		
		self.textarea_target_dialog = $( '#otg-access-shortcode-generator-target-dialog' ).dialog({
			autoOpen:	false,
			modal:		true,
			width:		self.dialog_minWidth,
			title:		otg_access_shortcodes_gui_texts.dialog_title_generated,
			resizable:	false,
			draggable:	false,
			show: {
				effect:		"blind",
				duration:	800
			},
			create: function( event, ui ) {
				$( event.target ).parent().css( 'position', 'fixed' );
			},
			buttons: [
				{
					class: 'button-primary',
					text: otg_access_shortcodes_gui_texts.close,
					click: function() {
						$( this ).dialog( "close" );
					}
				},
			],
			open: function( event, ui ) {
				$('body').addClass( 'modal-open' );
				$( '#otg-access-shortcode-generator-target' )
					.html( self.shortcode_to_insert_on_target_dialog )
					.focus();
			},
			close: function( event, ui ) {
				$( 'body' ).removeClass( 'modal-open' );
				self.shortcode_gui_action = 'insert';
				self.shortcode_to_insert_on_target_dialog = '';
				$( this ).dialog( "close" );
			}
		});
		
	};
	
	/**
	 * Adjusts the dialog button labels depending on self.shortcode_gui_action.
	 *
	 * @since 2.3.0
	 */

	self.manage_shortcode_dialog_button_labels = function() {
		switch ( self.shortcode_gui_action ) {
			case 'create':
				$( '.js-wpcf-access-discard-shortcode .ui-button-text' ).html( otg_access_shortcodes_gui_texts.cancel );
				$( '.js-wpcf-access-craft-shortcode .ui-button-text' ).html( otg_access_shortcodes_gui_texts.create_shortcode );
				break;
			case 'insert':
			default:
				$( '.js-wpcf-access-discard-shortcode .ui-button-text' ).html( otg_access_shortcodes_gui_texts.close );
				$( '.js-wpcf-access-craft-shortcode .ui-button-text' ).html( otg_access_shortcodes_gui_texts.insert_shortcode );
				break;
		}
	};
	
	/**
	 * Init Toolset.hooks callbacks.
	 *
	 * @since 2.3.0
	 */
	self.init_hooks = function() {
		
		/**
		 * otg-access-action-perform-shortcode-gui-action
		 *
		 * Set the action to perform after crafting a shortcode, based on self.shortcode_gui_action.
		 *
		 * @since 2.3.0
		 */
		Toolset.hooks.addAction( 'otg-access-action-perform-shortcode-gui-action', self.perform_shortcode_gui_action );
		
	};
	
	/**
	 * Calback for the otg-access-action-perform-shortcode-gui-action action.
	 *
	 * @since 2.3.0
	 */
	self.perform_shortcode_gui_action = function( shortcode ) {
		self.shortcode_dialog.dialog( 'close' );
		switch ( self.shortcode_gui_action ) {
			case 'insert':
				window.icl_editor.insert( shortcode );
				break;
			case 'create':
				self.shortcode_to_insert_on_target_dialog = shortcode;
				self.textarea_target_dialog.dialog( "open" ).dialog({
					maxHeight:	self.calculate_dialog_maxHeight(),
					maxWidth:	self.calculate_dialog_maxWidth(),
					position:	{
						my:			"center top+50",
						at:			"center top",
						of:			window,
						collision:	"none"
					}
				});
				break;
		}
		self.shortcode_gui_action = 'insert';
	}
	
	/**
	 * Craft the shortcode based on the dialog selected options.
	 *
	 * @since 2.3.0
	 */
	self.craft_shortcode = function() {
		
		var shortcode = '[toolset_access role="';
		shortcode += $( '.js-wpcf-access-list-roles:checked' ).map( function () {
			return $(this).val();
		}).get().join(",") + '"';
		shortcode += ( $( 'input[name="wpcf-access-shortcode-operator"]:checked' ).length > 0 ) ? ' operator="' + $( 'input[name="wpcf-access-shortcode-operator"]:checked' ).val() + '"' : '';
		shortcode += ( $( '.js-wpcf-access-shortcode-format' ).prop('checked') === true ) ? ' raw="true"' : '';
		shortcode += ']' + $( '.js-wpcf-access-conditional-message' ).val() + '[/toolset_access]';
		
		Toolset.hooks.doAction( 'otg-access-action-perform-shortcode-gui-action', shortcode );
		
	};
	
	/**
	 * ----------------------
	 * Events
	 * ----------------------
	 */
	
	// Click on the Admin Bar entry for Access
	$( document ).on( 'click','.js-otg-access-shortcode-gui-in-adminbar', function( e ) {
		e.preventDefault();
		self.shortcode_gui_action = 'create';
		self.shortcode_dialog.dialog( 'open' ).dialog({
			height:		self.calculate_dialog_maxHeight(),
			width:		self.calculate_dialog_maxWidth(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position: 	{
				my:			"center top+50",
				at:			"center top",
				of:			window,
				collision:	"none"
			}
		});
		//self.manage_shortcode_dialog_button_labels();
		return false;
	});
	
	// Click on the Access editor toolbar button
	$( document ).on( 'click', '.js-wpcf-access-editor-button', function( e ) {
		e.preventDefault();
		window.wpcfActiveEditor = $( this ).data( 'editor' );
		self.shortcode_gui_action = 'insert';
		self.shortcode_dialog.dialog( 'open' ).dialog({
			height:		self.calculate_dialog_maxHeight(),
			width:		self.calculate_dialog_maxWidth(),
			maxWidth:	self.calculate_dialog_maxWidth(),
			position: 	{
				my:			"center top+50",
				at:			"center top",
				of:			window,
				collision:	"none"
			}
		});
		//self.manage_shortcode_dialog_button_labels();
		return false;
	});
	
	// Dialog management: enable craft shortocde button only when one or more roles selected
	$( document ).on( 'change', '.js-wpcf-access-list-roles', function () {
		$( '.js-wpcf-access-craft-shortcode' )
			.prop( 'disabled', true )
			.addClass( 'button-secondary' )
			.removeClass( 'button-primary' );
		if ( $( '.js-wpcf-access-list-roles:checked' ).length > 0 ) {
			$( '.js-wpcf-access-craft-shortcode' )
				.prop( 'disabled', false )
				.addClass( 'button-primary' )
				.removeClass( 'button-secondary' );
		}
	});
	
	self.init = function() {
		self.init_dialogs();
		self.init_hooks();
		self.init_admin_bar_button();
	};
	
	self.init();

};

jQuery( document ).ready( function( $ ) {
	OTGAccess.shortcodes_gui = new OTGAccess.ShortcodesGUI( $ );
});