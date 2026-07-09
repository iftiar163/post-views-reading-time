<?php
/**
 * Handles view tracking via AJAX and reading time calculation.
 *
 * @package Webxperthub_PVRT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Webxperthub_PVRT_Tracker {

    /**
     * Register all hooks for this class.
     * Called once from webxperthub_pvrt_init() in the main file.
     */
    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_webxperthub_pvrt_track_view',              array( __CLASS__, 'track_view' ) );
        add_action( 'wp_ajax_nopriv_webxperthub_pvrt_track_view',       array( __CLASS__, 'track_view' ) );
        add_action( 'wp_ajax_webxperthub_pvrt_track_reading_time',        array( __CLASS__, 'track_reading_time' ) );
        add_action( 'wp_ajax_nopriv_webxperthub_pvrt_track_reading_time', array( __CLASS__, 'track_reading_time' ) );
    }

    /**
     * Load our tracker JS on single post pages only.
     */
    public static function enqueue_scripts() {
        if ( ! is_singular( 'post' ) ) {
            return;
        }

        wp_enqueue_script(
            'webxperthub-pvrt-tracker',
            WEBXPERTHUB_PVRT_PLUGIN_URL . 'assets/js/tracker.js',
            array(),
            WEBXPERTHUB_PVRT_VERSION,
            true
        );

        wp_localize_script(
            'webxperthub-pvrt-tracker',
            'webxperthubPvrtData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'webxperthub_pvrt_track_view' ),
                'postId'  => get_the_ID(),
            )
        );
    }

    /**
     * Track and accumulate reading time when a visitor leaves a post.
     */
    public static function track_reading_time() {
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'webxperthub_pvrt_track_view' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
        }

        if ( self::should_skip_tracking() ) {
            wp_send_json_success( array( 'message' => 'Reading time tracking skipped for this user role' ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;

        if ( 0 === $post_id ) {
            wp_send_json_error( array( 'message' => 'Invalid post ID' ), 400 );
        }

        if ( 'publish' !== get_post_status( $post_id ) ) {
            wp_send_json_error( array( 'message' => 'Post is not published' ), 404 );
        }

        $time_spent = isset( $_POST['time_spent'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['time_spent'] ) ) : 0;

        if ( $time_spent < 3 || $time_spent > 1800 ) {
            wp_send_json_error( array( 'message' => 'Invalid time spent' ), 400 );
        }

        $current_total = (int) get_post_meta( $post_id, WEBXPERTHUB_PVRT_META_TIME, true );
        $new_total     = $current_total + $time_spent;

        update_post_meta( $post_id, WEBXPERTHUB_PVRT_META_TIME, $new_total );

        wp_cache_delete( 'webxperthub_pvrt_total_time',  'webxperthub-post-views-reading-time' );
        wp_cache_delete( 'webxperthub_pvrt_total_views', 'webxperthub-post-views-reading-time' );

        wp_send_json_success( array( 'reading_time' => $new_total ) );
    }

    /**
     * Check if the current user should be excluded from tracking.
     * Administrators, editors, authors, and contributors are excluded.
     *
     * @return bool True if tracking should be skipped.
     */
    private static function should_skip_tracking() {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $excluded_roles = array( 'administrator', 'editor', 'author', 'contributor' );
        $user           = wp_get_current_user();
        $matched        = array_intersect( $excluded_roles, (array) $user->roles );

        return ! empty( $matched );
    }

    /**
     * AJAX handler: increment the view count for a post.
     */
    public static function track_view() {
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'webxperthub_pvrt_track_view' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
        }

        if ( self::should_skip_tracking() ) {
            wp_send_json_success( array( 'message' => 'Tracking skipped for this user role' ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;

        if ( 0 === $post_id ) {
            wp_send_json_error( array( 'message' => 'Invalid post ID' ), 400 );
        }

        if ( 'publish' !== get_post_status( $post_id ) ) {
            wp_send_json_error( array( 'message' => 'Post is not published' ), 404 );
        }

        $current_views = (int) get_post_meta( $post_id, WEBXPERTHUB_PVRT_META_VIEWS, true );
        $new_views     = $current_views + 1;

        update_post_meta( $post_id, WEBXPERTHUB_PVRT_META_VIEWS, $new_views );

        wp_cache_delete( 'webxperthub_pvrt_total_views', 'webxperthub-post-views-reading-time' );
        wp_cache_delete( 'webxperthub_pvrt_total_time',  'webxperthub-post-views-reading-time' );

        wp_send_json_success( array( 'views' => $new_views ) );
    }
}
