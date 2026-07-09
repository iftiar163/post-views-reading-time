<?php
/**
 * Registers and renders the plugin settings page.
 *
 * @package Webxperthub_PVRT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Webxperthub_PVRT_Settings {

    /**
     * The option name used to store all settings as one array in wp_options.
     */
    const OPTION_NAME = 'webxperthub_pvrt_settings';

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_settings_page' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function add_settings_page() {
        add_options_page(
            __( 'Post Views & Reading Time', 'webxperthub-post-views-reading-time' ),
            __( 'Post Views & Reading Time', 'webxperthub-post-views-reading-time' ),
            'manage_options',
            'webxperthub-pvrt-settings',
            [ __CLASS__, 'render_settings_page' ]
        );
    }

    public static function get_defaults() {
        return [
            'excluded_roles'    => array( 'administrator', 'editor', 'author', 'contributor' ),
            'min_reading_time'  => 3,
            'tracked_post_types' => array( 'post' ),
        ];
    }
}