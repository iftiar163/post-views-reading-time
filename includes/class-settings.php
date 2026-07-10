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

    // Static functions

    public static function get_defaults() {
        return [
            'excluded_roles'    => array( 'administrator', 'editor', 'author', 'contributor' ),
            'min_reading_time'  => 3,
            'tracked_post_types' => array( 'post' ),
        ];
    }

    public static function get_settings() {
        $saved = get_option( self::OPTION_NAME, [] );
        return wp_parse_args( $saved, self::get_defaults() );
    }

    // Register Settings

    public static function register_settings() {
        
        register_setting(
            'webxperthub_pvrt_settings_group',
            self::OPTION_NAME,
            [ 
                'type'              => 'array',
                'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
                'default'           => self::get_defaults(),
            ]
        );
    }

    // Sanitize Settings
    public static function sanitize_settings( $input ) {

        $defaults = self::get_defaults();
        $output = [];

        // Excluded roles: only allow real, existing WordPress roles
        $valid_roles = array_keys(  wp_roles()->roles );

        $output['excluded_roles'] = isset( $input['excluded_roles'] ) && is_array( $input['excluded_roles'] )
            ? array_values( array_intersect( $valid_roles, $input['excluded_roles'] ) )
            : $defaults['excluded_roles'];

        // Minimum reading time: ensure it's a positive integer
        $min_time = isset( $input['min_reading_time'] ) ? absint( $input['min_reading_time'] ) : $defaults['min_reading_time'];
        $output['min_reading_time'] = min( max( $min_time, 1 ), 60 );

        // Tracked post types: only allow existing public post types
        $valid_post_types = get_post_types( [ 'public' => true ], 'names' );
        $output['tracked_post_types'] = isset( $input['tracked_post_types'] ) && is_array( $input['tracked_post_types'] )
            ? array_values( array_intersect( $valid_post_types, $input['tracked_post_types'] ) )
            : $defaults['tracked_post_types'];

        return $output;

    }

    public static function render_settings_page() {
        
        // Security: only administrators should ever see this page
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = self::get_settings();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Post Views & Reading Time Settings', 'webxperthub-post-views-reading-time' ); ?></h1>

            <form method="post" action="options.php">
                <?php
                // Outputs hidden nonce + option group fields — required by the Settings API
                settings_fields( 'webxperthub_pvrt_settings_group' );
                ?>

                <table class="form-table" role="presentation">

                    <!-- ─── Tracked Post Types ─────────────────────────────── -->
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Track Post Types', 'webxperthub-post-views-reading-time' ); ?>
                        </th>
                        <td>
                            <?php
                            $post_types = get_post_types( array( 'public' => true ), 'objects' );
                            foreach ( $post_types as $post_type ) :
                                $checked = in_array( $post_type->name, $settings['tracked_post_types'], true );
                                ?>
                                <label style="display:block; margin-bottom:6px;">
                                    <input
                                        type="checkbox"
                                        name="<?php echo esc_attr( self::OPTION_NAME ); ?>[tracked_post_types][]"
                                        value="<?php echo esc_attr( $post_type->name ); ?>"
                                        <?php checked( $checked ); ?>
                                    />
                                    <?php echo esc_html( $post_type->labels->name ); ?>
                                </label>
                            <?php endforeach; ?>
                            <p class="description">
                                <?php esc_html_e( 'Select which content types should have views and reading time tracked.', 'webxperthub-post-views-reading-time' ); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- ─── Excluded Roles ─────────────────────────────────── -->
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Exclude User Roles', 'webxperthub-post-views-reading-time' ); ?>
                        </th>
                        <td>
                            <?php
                            $roles = wp_roles()->roles;
                            foreach ( $roles as $role_key => $role_data ) :
                                $checked = in_array( $role_key, $settings['excluded_roles'], true );
                                ?>
                                <label style="display:block; margin-bottom:6px;">
                                    <input
                                        type="checkbox"
                                        name="<?php echo esc_attr( self::OPTION_NAME ); ?>[excluded_roles][]"
                                        value="<?php echo esc_attr( $role_key ); ?>"
                                        <?php checked( $checked ); ?>
                                    />
                                    <?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
                                </label>
                            <?php endforeach; ?>
                            <p class="description">
                                <?php esc_html_e( 'Logged-in users with these roles will not be counted as visitors.', 'webxperthub-post-views-reading-time' ); ?>
                            </p>
                        </td>
                    </tr>

                    <!-- ─── Minimum Reading Time ───────────────────────────── -->
                    <tr>
                        <th scope="row">
                            <label for="wxh_pvrt_min_reading_time">
                                <?php esc_html_e( 'Minimum Reading Time', 'webxperthub-post-views-reading-time' ); ?>
                            </label>
                        </th>
                        <td>
                            <input
                                type="number"
                                id="wxh_pvrt_min_reading_time"
                                name="<?php echo esc_attr( self::OPTION_NAME ); ?>[min_reading_time]"
                                value="<?php echo esc_attr( $settings['min_reading_time'] ); ?>"
                                min="1"
                                max="60"
                                step="1"
                                class="small-text"
                            />
                            <?php esc_html_e( 'seconds', 'webxperthub-post-views-reading-time' ); ?>
                            <p class="description">
                                <?php esc_html_e( 'Visitors must stay on a post for at least this long before it counts as a read.', 'webxperthub-post-views-reading-time' ); ?>
                            </p>
                        </td>
                    </tr>

                </table>

                <?php submit_button( __( 'Save Settings', 'webxperthub-post-views-reading-time' ) ); ?>
            </form>
        </div>
        <?php
    }

}