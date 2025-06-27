<?php
/**
 * @package ddevapp
 */

if ( getenv( 'IS_DDEV_PROJECT' ) == 'true' ) {
    // Check if the request is coming from a wp-browser acceptance test.
    $is_test_request = getenv('WPBROWSER_LOAD_ONLY') || getenv( "CODECEPTION_TESTING" ) || isset( $_SERVER["HTTP_X_WPBROWSER_REQUEST"] ) || ( isset( $_SERVER["HTTP_USER_AGENT"] ) && strpos( $_SERVER["HTTP_USER_AGENT"], "wp-browser" ) !== false ) || getenv( "WPBROWSER_HOST_REQUEST" );

	/** The name of the database for WordPress */
	defined( 'DB_NAME' ) || define( 'DB_NAME', $is_test_request ? 'db_test' : 'db' );

	/** MySQL database username */
	defined( 'DB_USER' ) || define( 'DB_USER', 'db' );

	/** MySQL database password */
	defined( 'DB_PASSWORD' ) || define( 'DB_PASSWORD', 'db' );

	/** MySQL hostname */
	defined( 'DB_HOST' ) || define( 'DB_HOST', 'ddev-altinator-wp-db' );

	/** WP_HOME URL */
	defined( 'WP_HOME' ) || define( 'WP_HOME', $is_test_request ? 'http://test.altinator-wp.test' : 'https://altinator-wp.test' );

	/** WP_SITEURL location */
	defined( 'WP_SITEURL' ) || define( 'WP_SITEURL', WP_HOME . '/' );

	/**
	 * Set WordPress Database Table prefix if not already set.
	 *
	 * @global string $table_prefix
	 */
	if ( ! isset( $table_prefix ) || empty( $table_prefix ) ) {
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$table_prefix = 'wp_';
		// phpcs:enable
	}

    define( 'ALTINATOR_AI_SERVICE_API_BASE', 'https://altinator.test/api/v1' );

    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_DISPLAY', false );
    define( 'WP_DEBUG_LOG', getenv('DDEV_APPROOT').'/logs/debug.log' );
    define( 'SCRIPT_DEBUG', true );
}

// Include additional local settings.
$local_settings = __DIR__ . '/wp-config-local.php';
if ( is_readable( $local_settings ) ) {
	require_once( $local_settings );
}
