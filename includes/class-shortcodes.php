<?php
/**
 * Registers and handles frontend shortcodes for post views and reading time.
 *
 * Shortcodes:
 *  [webxperthub_post_views]    — displays total view count for a post
 *  [webxperthub_reading_time]  — displays estimated reading time for a post
 *
 * @package Webxperthub_PVRT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Webxperthub_PVRT_Shortcodes {

    public static function init() {
        add_shortcode( 'webxperthub_post_views', array( __CLASS__, 'render_views' ) );
        add_shortcode( 'webxperthub_reading_time', array( __CLASS__, 'render_reading_time' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
    }

    private static function get_post_id( $atts ) {

        if( ! empty( $atts['id'] ) ) {
            return absint( $atts['id'] );
        }

        $post_id = get_the_ID();

        return $post_id ? (int) $post_id : 0;

    }

    public static function render_views( $atts ) {

        $atts = shortcode_atts( array(
            'id' => 0,
            'label' => 'no',
        ), $atts, 'webxperthub_post_views' );

        $post_id = self::get_post_id( $atts );

        if( ! $post_id || 'publish' !== get_post_status( $post_id ) ) {
            return '';
        }

        $raw = get_post_meta( $post_id, WEBXPERTHUB_PVRT_META_VIEWS, true );
        $views = ( '' !== $raw ) ? (int) $raw : 0;

        $label_html = ( 'yes' === $atts['label'] )
            ? '<span class="wxh-pvrt-label">' . esc_html__( 'Views:', 'webxperthub-post-views-reading-time' ) . '</span> '
            : '';

        return sprintf(
            '<span class="wxh-pvrt-views">%s<span class="wxh-pvrt-count">%s</span></span>',
            $label_html,
            number_format( (float) $views )
        );

    }

    public static function render_reading_time( $atts ) {
        $atts = shortcode_atts([
            'id' => 0,
            'label' => 'no',
        ], $atts, 'webxperthub_reading_time' );
        
        $post_id = self::get_post_id( $atts );

        if( ! $post_id || 'publish' !== get_post_status( $post_id ) ) {
            return '';
        }

        $raw = get_post_meta( $post_id, WEBXPERTHUB_PVRT_META_TIME, true );
        $seconds = ( '' !== $raw ) ? (int) $raw : 0;

        if ( $seconds <= 0 ) {
            return '';
        }

        $formatted = self::format_reading_time( $seconds );
        $label_html = ( 'yes' === $atts['label'] )
            ? '<span class="wxh-pvrt-label">' . esc_html__( 'Read Time:', 'webxperthub-post-views-reading-time' ) . '</span> '
            : '';
        
        return sprintf(
        '<span class="wxh-pvrt-reading-time">%s<span class="wxh-pvrt-time-value">%s</span></span>',
        $label_html,
        esc_html( $formatted )
        );

    }

    private static function format_reading_time( $seconds ) {

        $hours = intdiv( $seconds, 3600 );
        $minutes = intdiv( $seconds % 3600, 60 );

        if( $hours === 0 && $minutes === 0 ) {
            $minutes = 1; // Minimum reading time is 1 minute
        }

        if( $hours > 0 ) {
            return $hours . 'h ' . $minutes . 'm';
        }
        return $minutes . 'm';

    }

    public static function enqueue_styles() {

        if ( ! is_singular( 'post' ) ) {
            return;
        }

        $css = '
            .wxh-pvrt-views,
            .wxh-pvrt-reading-time {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: 0.9em;
                color: #555;
            }
            .wxh-pvrt-label {
                font-weight: 600;
                color: #333;
            }
            .wxh-pvrt-count,
            .wxh-pvrt-time-value {
                font-weight: 700;
                color: #1f2937;
            }
        ';

        wp_add_inline_style( 'wp-admin', $css );
    }

}