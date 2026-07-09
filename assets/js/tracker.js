( function() {
    'use strict';

    // Safety check: make sure webxperthubPvrtData was injected by PHP
    if ( typeof webxperthubPvrtData === 'undefined' ) {
        return;
    }

    // Prevent counting the same visit twice in one session
    var sessionKey        = 'wxh_pvrt_tracked_' + webxperthubPvrtData.postId;
    var readingSessionKey = 'wxh_pvrt_reading_tracked_' + webxperthubPvrtData.postId;

    if ( sessionStorage.getItem( sessionKey ) ) {
        return;
    }

    var pageLoadTime      = Date.now();
    var hasTrackedReading = false;

    // Wait for DOM to be ready
    document.addEventListener( 'DOMContentLoaded', function() {

        // 2-second bounce filter before tracking view
        setTimeout( function() {

            var formData = new FormData();
            formData.append( 'action',  'webxperthub_pvrt_track_view' );
            formData.append( 'nonce',   webxperthubPvrtData.nonce );
            formData.append( 'post_id', webxperthubPvrtData.postId );

            fetch( webxperthubPvrtData.ajaxUrl, {
                method: 'POST',
                body:   formData,
            } )
            .then( function( response ) {
                return response.json();
            } )
            .then( function( data ) {
                if ( data.success ) {
                    sessionStorage.setItem( sessionKey, '1' );
                }
            } )
            .catch( function() {
                // Silently fail — never show errors to visitors
            } );

        }, 2000 );

    } );

    // Track reading time when user leaves the page
    function trackReadingTime() {
        if ( hasTrackedReading || ! sessionStorage.getItem( sessionKey ) ) {
            return;
        }

        hasTrackedReading = true;

        var timeSpent = Math.round( ( Date.now() - pageLoadTime ) / 1000 );

        if ( timeSpent < 3 ) {
            return;
        }

        if ( timeSpent > 1800 ) {
            timeSpent = 1800;
        }

        if ( sessionStorage.getItem( readingSessionKey ) ) {
            return;
        }

        var formData = new FormData();
        formData.append( 'action',     'webxperthub_pvrt_track_reading_time' );
        formData.append( 'nonce',      webxperthubPvrtData.nonce );
        formData.append( 'post_id',    webxperthubPvrtData.postId );
        formData.append( 'time_spent', timeSpent );

        fetch( webxperthubPvrtData.ajaxUrl, {
            method:    'POST',
            body:      formData,
            keepalive: true,
        } )
        .then( function( response ) {
            return response.json();
        } )
        .then( function( data ) {
            if ( data.success ) {
                sessionStorage.setItem( readingSessionKey, '1' );
            }
        } )
        .catch( function() {
            // Silently fail
        } );
    }

    window.addEventListener( 'beforeunload', trackReadingTime );

    document.addEventListener( 'visibilitychange', function() {
        if ( document.hidden ) {
            trackReadingTime();
        }
    } );

} )();
