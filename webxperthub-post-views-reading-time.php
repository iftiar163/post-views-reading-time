<?php
/**
 * Plugin Name: Webxperthub Post Views & Reading Time
 * Plugin URI:  https://wordpress.org/plugins/webxperthub-post-views-reading-time/
 * Description: Track post views and reading time from visitors. Display engagement metrics in the admin dashboard and post list columns.
 * Version:     1.1.0
 * Author:      Iftiar Hossain
 * Author URI:  https://iftiarhossain.com/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: webxperthub-post-views-reading-time
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Tested up to: 7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Constants ─────────────────────────────────────────────────────────────
define( 'WEBXPERTHUB_PVRT_VERSION',    '1.1.0' );
define( 'WEBXPERTHUB_PVRT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WEBXPERTHUB_PVRT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WEBXPERTHUB_PVRT_META_VIEWS', '_webxperthub_pvrt_view_count' );
define( 'WEBXPERTHUB_PVRT_META_TIME',  '_webxperthub_pvrt_reading_time' );
define( 'WEBXPERTHUB_PVRT_META_EXCLUDE', '_webxperthub_pvrt_exclude' );

// ─── Load class files ───────────────────────────────────────────────────────
require_once WEBXPERTHUB_PVRT_PLUGIN_DIR . 'includes/class-tracker.php';
require_once WEBXPERTHUB_PVRT_PLUGIN_DIR . 'includes/class-admin-columns.php';
require_once WEBXPERTHUB_PVRT_PLUGIN_DIR . 'includes/class-dashboard-widget.php';
require_once WEBXPERTHUB_PVRT_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once WEBXPERTHUB_PVRT_PLUGIN_DIR . 'includes/class-settings.php';
require_once WEBXPERTHUB_PVRT_PLUGIN_DIR . 'includes/class-post-exclusion.php';

// ─── Bootstrap ──────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', 'webxperthub_pvrt_init' );

function webxperthub_pvrt_init() {
    Webxperthub_PVRT_Tracker::init();
    Webxperthub_PVRT_Admin_Columns::init();
    Webxperthub_PVRT_Dashboard_Widget::init();
    Webxperthub_PVRT_Shortcodes::init();
    Webxperthub_PVRT_Settings::init();
    Webxperthub_PVRT_Post_Exclusion::init();
}
