<?php
/**
 * Registers and renders the admin dashboard widget.
 *
 * @package Webxperthub_PVRT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Webxperthub_PVRT_Dashboard_Widget {

    public static function init() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        add_action( 'wp_dashboard_setup',    array( __CLASS__, 'register_widget' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
    }

    /**
     * Register the widget with the WordPress dashboard.
     */
    public static function register_widget() {
        wp_add_dashboard_widget(
            'webxperthub_pvrt_dashboard_widget',
            __( 'Post View Stats', 'webxperthub-post-views-reading-time' ),
            array( __CLASS__, 'render_widget' )
        );
    }

    /**
     * Query all stats needed for the widget.
     *
     * @return array Stats data array.
     */
    private static function get_stats() {
        $total_posts = wp_count_posts( 'post' );
        $post_count  = (int) $total_posts->publish;

        $total_views = wp_cache_get( 'webxperthub_pvrt_total_views', 'webxperthub-post-views-reading-time' );
        if ( false === $total_views ) {
            global $wpdb;
            $total_views = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT SUM(CAST(meta_value AS UNSIGNED))
                     FROM {$wpdb->postmeta}
                     WHERE meta_key = %s
                     AND meta_value != ''",
                    WEBXPERTHUB_PVRT_META_VIEWS
                )
            );
            wp_cache_set( 'webxperthub_pvrt_total_views', $total_views, 'webxperthub-post-views-reading-time', 3600 );
        }

        $total_time = wp_cache_get( 'webxperthub_pvrt_total_time', 'webxperthub-post-views-reading-time' );
        if ( false === $total_time ) {
            global $wpdb;
            $total_time = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT SUM(CAST(meta_value AS UNSIGNED))
                     FROM {$wpdb->postmeta}
                     WHERE meta_key = %s
                     AND meta_value != ''",
                    WEBXPERTHUB_PVRT_META_TIME
                )
            );
            wp_cache_set( 'webxperthub_pvrt_total_time', $total_time, 'webxperthub-post-views-reading-time', 3600 );
        }

        return array(
            'post_count'  => $post_count,
            'total_views' => $total_views,
            'total_time'  => $total_time,
        );
    }

    /**
     * Convert total seconds into a human-readable string.
     *
     * @param  int    $total_seconds Raw seconds.
     * @return string Formatted time string.
     */
    private static function format_reading_time( $total_seconds ) {
        if ( $total_seconds <= 0 ) {
            return '0 mins';
        }

        $hours   = (int) intdiv( $total_seconds, 3600 );
        $minutes = (int) intdiv( $total_seconds % 3600, 60 );

        if ( 0 === $hours ) {
            return $minutes . ' mins';
        }

        if ( 0 === $minutes ) {
            return $hours . ' hrs';
        }

        return $hours . ' hrs ' . $minutes . ' mins';
    }

    /**
     * Render the dashboard widget HTML.
     */
    public static function render_widget() {
        $stats = self::get_stats();
        $time  = self::format_reading_time( $stats['total_time'] );
        ?>
        <div class="wxh-pvrt-widget-container">
            <div class="wxh-pvrt-widget-header">
                <h3 class="wxh-pvrt-widget-title">📊 Content Analytics</h3>
                <p class="wxh-pvrt-widget-subtitle">Real-time engagement metrics</p>
            </div>

            <div class="wxh-pvrt-stats-grid">
                <div class="wxh-pvrt-stat-card wxh-pvrt-stat-posts">
                    <div class="wxh-pvrt-stat-header">
                        <span class="wxh-pvrt-stat-icon">📝</span>
                        <span class="wxh-pvrt-stat-title">Published Posts</span>
                    </div>
                    <div class="wxh-pvrt-stat-content">
                        <div class="wxh-pvrt-stat-number"><?php echo number_format( (float) $stats['post_count'] ); ?></div>
                        <div class="wxh-pvrt-stat-description">Total articles</div>
                    </div>
                </div>

                <div class="wxh-pvrt-stat-card wxh-pvrt-stat-views">
                    <div class="wxh-pvrt-stat-header">
                        <span class="wxh-pvrt-stat-icon">👁️</span>
                        <span class="wxh-pvrt-stat-title">Total Views</span>
                    </div>
                    <div class="wxh-pvrt-stat-content">
                        <div class="wxh-pvrt-stat-number"><?php echo number_format( (float) $stats['total_views'] ); ?></div>
                        <div class="wxh-pvrt-stat-description">Visitor pageviews</div>
                    </div>
                </div>

                <div class="wxh-pvrt-stat-card wxh-pvrt-stat-time">
                    <div class="wxh-pvrt-stat-header">
                        <span class="wxh-pvrt-stat-icon">⏱️</span>
                        <span class="wxh-pvrt-stat-title">Read Time</span>
                    </div>
                    <div class="wxh-pvrt-stat-content">
                        <div class="wxh-pvrt-stat-number"><?php echo esc_html( $time ); ?></div>
                        <div class="wxh-pvrt-stat-description">Total user reading time</div>
                    </div>
                </div>
            </div>

            <div class="wxh-pvrt-widget-footer">
                <a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="wxh-pvrt-footer-link">
                    View all posts →
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue styles for the dashboard widget.
     *
     * @param string $hook The current admin page hook.
     */
    public static function enqueue_styles( $hook ) {
        if ( 'index.php' !== $hook ) {
            return;
        }

        $css = '
            .wxh-pvrt-widget-container {
                background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                border-radius: 12px;
                overflow: hidden;
            }
            .wxh-pvrt-widget-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                margin: 0;
            }
            .wxh-pvrt-widget-title {
                margin: 0 0 4px 0;
                font-size: 18px;
                font-weight: 700;
                letter-spacing: -0.3px;
            }
            .wxh-pvrt-widget-subtitle {
                margin: 0;
                font-size: 12px;
                opacity: 0.9;
                font-weight: 500;
            }
            .wxh-pvrt-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 16px;
                padding: 24px;
            }
            .wxh-pvrt-stat-card {
                background: white;
                border-radius: 10px;
                padding: 20px;
                border: 1px solid #e5e7eb;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                position: relative;
                overflow: hidden;
            }
            .wxh-pvrt-stat-card::before {
                content: "";
                position: absolute;
                top: 0; left: 0; right: 0;
                height: 3px;
                background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            }
            .wxh-pvrt-stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 24px rgba(0,0,0,0.08);
                border-color: #667eea;
            }
            .wxh-pvrt-stat-header {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 16px;
            }
            .wxh-pvrt-stat-icon { font-size: 24px; line-height: 1; }
            .wxh-pvrt-stat-title {
                font-size: 12px;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .wxh-pvrt-stat-content { display: flex; flex-direction: column; gap: 6px; }
            .wxh-pvrt-stat-number {
                font-size: 28px;
                font-weight: 800;
                color: #1f2937;
                letter-spacing: -0.5px;
            }
            .wxh-pvrt-stat-description { font-size: 11px; color: #9ca3af; font-weight: 500; }
            .wxh-pvrt-widget-footer {
                background: #f3f4f6;
                padding: 16px 24px;
                border-top: 1px solid #e5e7eb;
                text-align: center;
            }
            .wxh-pvrt-footer-link {
                color: #667eea;
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
            }
            .wxh-pvrt-footer-link:hover { color: #764ba2; }
            @media (max-width: 600px) {
                .wxh-pvrt-stats-grid { grid-template-columns: 1fr; gap: 12px; padding: 16px; }
                .wxh-pvrt-widget-header { padding: 16px; }
                .wxh-pvrt-stat-number { font-size: 24px; }
            }
        ';

        wp_add_inline_style( 'wp-admin', $css );
    }
}
