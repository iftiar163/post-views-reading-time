<?php
/**
 * Adds a meta box to exclude individual posts from view/reading time tracking.
 *
 * @package Webxperthub_PVRT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Webxperthub_PVRT_Post_Exclusion {

    const NONCE_ACTION = 'webxperthub_pvrt_exclude_nonce_action';
    const NONCE_NAME = 'webxperthub_pvrt_exclude_nonce';

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ) );
        add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 10, 2 );
    }

    /**
     * Register the meta box on post types that are tracked in settings.
     */
    public static function register_meta_box() {

        $settings = Webxperthub_PVRT_Settings::get_settings();

        foreach ( $settings['tracked_post_types'] as $post_type ) {
            add_meta_box(
                'webxperthub_pvrt_exclude_box',
                __( 'Post Views & Reading Time', 'webxperthub-post-views-reading-time' ),
                array( __CLASS__, 'render_meta_box' ),
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render the checkbox inside the meta box.
     *
     * @param WP_Post $post The current post object.
     */
    public static function render_meta_box( $post ) {

        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
        $excluded = (bool) get_post_meta( $post->ID, WEBXPERTHUB_PVRT_META_EXCLUDE, true );
        ?>

            <label for="webxperthub_pvrt_exclude_checkbox">
            <input
                type="checkbox"
                id="webxperthub_pvrt_exclude_checkbox"
                name="webxperthub_pvrt_exclude"
                value="1"
                <?php checked( $excluded ); ?>
            />
            <?php esc_html_e( 'Exclude this post from tracking', 'webxperthub-post-views-reading-time' ); ?>
            </label>
            <p class="description" style="margin-top:8px;">
                <?php esc_html_e( 'Views and reading time will not be recorded for this post.', 'webxperthub-post-views-reading-time' ); ?>
            </p>

        <?php

    }

    /**
     * Save the exclusion checkbox value when the post is saved.
     *
     * @param int     $post_id The ID of the post being saved.
     * @param WP_Post $post    The full post object.
     */
    public static function save_meta_box( $post_id, $post ) {

        // 1. Bail on autosave — same pattern as your tracker's reading time calc
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // 2. Verify the nonce exists and is valid
       if (
            ! isset( $_POST[ self::NONCE_NAME ] ) ||
            ! wp_verify_nonce(
                sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ),
                self::NONCE_ACTION
            )
        ) {
            return;
        }

        // 3. Verify the current user can actually edit this post
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // 4. Only act on post types this meta box is registered for
        $settings = Webxperthub_PVRT_Settings::get_settings();
        if ( ! in_array( $post->post_type, $settings['tracked_post_types'], true ) ) {
            return;
        }

        // 5. Checkbox present in $_POST = checked. Absent = unchecked.
        if( isset($_POST['webxperthub_pvrt_exclude']) ) {
            update_post_meta( $post_id, WEBXPERTHUB_PVRT_META_EXCLUDE, 1 );
        } else {
            delete_post_meta( $post_id, WEBXPERTHUB_PVRT_META_EXCLUDE );
        }

    }

}