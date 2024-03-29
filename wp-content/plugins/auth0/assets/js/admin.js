/* global jQuery, wpa0, wp */
jQuery(document).ready(function($) {
    //uploading files variable
    var media_frame;
    $(document).on('click', '#wpa0_choose_icon', function(event) {
        event.preventDefault();
        //If the frame already exists, reopen it
        if (typeof(media_frame)!=="undefined") {
            media_frame.close();
        }

        var related_control_id = 'wpa0_icon_url';
        if (typeof($(this).attr('related')) != 'undefined' &&
            $(this).attr('related') != '')
        {
            related_control_id = $(this).attr('related');
        }

        //Create WP media frame.
        media_frame = wp.media.frames.customHeader = wp.media({
            title: wpa0.media_title,
            library: {
                type: 'image'
            },
            button: {
                text: wpa0.media_button
            },
            multiple: false
        });

        // Set the frame callback
        media_frame.on('select', function() {
            var attachment = media_frame.state().get('selection').first().toJSON();
            $('#'+related_control_id).val(attachment.url).change();
        });

        //Open modal
        media_frame.open();
    });

    // Show/hide field for specific switches
    $('[data-expand!=""]').each( function() {
        var $thisSwitch = $( this );
        var $showFieldRow = $( '#' + $thisSwitch.attr( 'data-expand' ) ).closest( 'tr' );

        if ( $showFieldRow.length ) {
            if ( ! $thisSwitch.prop( 'checked' ) ) {
                $showFieldRow.hide();
            }
            $thisSwitch.change(function() {
                if ( $( this ).prop( 'checked' ) ) {
                    $showFieldRow.show();
                } else {
                    $showFieldRow.hide();
                }
            } );
        }
    });

    /*
    Admin settings tab switching
     */
    var currentTab;
    if ( window.location.hash ) {
        currentTab = window.location.hash.replace( '#', '' );
    } else if ( localStorageAvailable() && window.localStorage.getItem( 'Auth0WPSettingsTab' ) ) {
        // Previous tab being used
        currentTab = window.localStorage.getItem( 'Auth0WPSettingsTab' );
    } else {
        // Default tab if no saved tab was found
        currentTab = 'features';
    }

    // Uses the Bootstrap tab plugin
    $('#tab-' + currentTab).tab('show');

    // Controls whether the submit button is showing or not
    var $settingsForm = $( '#js-a0-settings-form' );
    $settingsForm.attr( 'data-tab-showing', currentTab );

    // Set the tab showing on the form and persist the tab
    $( '.nav-tabs [role="tab"]' ).click( function () {
        window.location.hash = '';
        var tabHref = $( this ).attr( 'aria-controls' );
        $settingsForm.attr( 'data-tab-showing', tabHref );
        if ( localStorageAvailable() ) {
            window.localStorage.setItem( 'Auth0WPSettingsTab', tabHref );
        }
    } );

    /*
    Clear cache button on Basic settings page
     */
    var deleteCacheId = 'auth0_delete_cache_transient';
    var $deleteCacheButton = $( '#' + deleteCacheId );
    $deleteCacheButton.click( function(e) {
        e.preventDefault();
        $deleteCacheButton.prop( 'disabled', true ).val( wpa0.clear_cache_working );
        var postData = {
            'action': deleteCacheId,
            '_ajax_nonce': wpa0.clear_cache_nonce
        };

        $.post(wpa0.ajax_url, postData, function() {
            $deleteCacheButton.prop( 'disabled', false ).val( wpa0.clear_cache_done );
        }, 'json');
    } );

    /*
    Initial setup
     */
    $('.js-a0-setup-input').keydown(function(e){
        // Do not submit the form if the enter key is pressed.
        if(13 === e.keyCode) {
            e.preventDefault();
            return false;
        }
    });

    $('.js-a0-select-setup').click(function (e) {
        e.preventDefault();
        $('#profile-type').val($(this).attr('data-profile-type'));
        $('#connectionSelectedModal').modal();
    });

    $('#manuallySetToken').click(function (e) {
        e.preventDefault();
        $('#enterTokenModal').modal();
        $('#connectionSelectedModal').modal('hide');
    });

    $('#automaticSetup').click(function (e) {
        e.preventDefault();
        $('#profile-form').submit();
    });

    /**
     * Can we use localStorage?
     *
     * @returns {boolean}
     */
    function localStorageAvailable() {
        try {
            var x = '__Auth0_localStorage_assertion__';
            window.localStorage.setItem(x, x);
            window.localStorage.removeItem(x);
            return true;
        }
        catch(e) {
            return false;
        }
    }
});
