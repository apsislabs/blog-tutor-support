<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

	/**
	 * NerdPress_Support_Snapshot
	 *
	 * @package  NerdPress
	 * @category Core
	 * @author Apsis Labs
	 */

class NerdPress_Support_Snapshot {
	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'ping_relay' ) );
		add_action( 'wp_loaded', array( $this, 'schedule_snapshot_cron' ) );

		add_action( 'np_scheduled_snapshot', array( $this, 'take_snapshot'));
	}

	/**
	 * Ping the relay server if the PING is set in the GET request.
	 */
	public static function ping_relay() {
		// If the request is a one-time call from the dashboard.
		if ( isset( $_GET['np_snapshot'] ) && NerdPress_Helpers::is_relay_server_configured() ) {
			self::take_snapshot();
			wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
			die;
		}
	}

	public function schedule_snapshot_cron() {

		if ( ! wp_next_scheduled( 'np_scheduled_snapshot' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'np_scheduled_snapshot' );
		}

		// if ( isset( get_option( 'blog_tutor_support_settings' )['schedule_snapshot'] ) ) {
		// } else {
		// 	if ( wp_next_scheduled( 'np_scheduled_snapshot' ) ) {
		// 		wp_clear_scheduled_hook( 'np_scheduled_snapshot' );
		// 	}
		// }
	}

	public function take_snapshot()
	{
		$dump         = self::assemble_snapshot();
		$api_response = self::send_request_to_relay( $dump );

		return $api_response;
	}

	public static function send_request_to_relay( $dump ) {

		if ( defined( 'SSLVERIFY_DEV' ) && SSLVERIFY_DEV === false ) {
			$status = false;
		} else {
			$status = true;
		}

		$relay_url = NerdPress_Helpers::relay_server_url() . '/wp-json/nerdpress/v1/snapshot';
		$api_token = NerdPress_Helpers:: relay_server_api_token();

		$args = array(
			'headers'   => array(
				'Authorization' => "Bearer $api_token",
				"Content-Type" => 'application/json',
			),
			'body'      => wp_json_encode( $dump ),
			// Bypass SSL verification when using self signed cert. Like when in a local dev environment.
			'sslverify' => $status,
		);


		// Make request to the relay server.
		$api_response = wp_remote_post( $relay_url, $args );

		return $api_response;
	}


	public static function assemble_snapshot()
	{
		// The HTML must be escaped to prevent JSON errors on the relay server.
		function filter_htmlspecialchars( &$value ) {
			$value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
		}

		// Check if get_plugins() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$current_plugins = get_plugins();
		$current_theme   = wp_get_theme();
		array_walk_recursive( $current_plugins, 'filter_htmlspecialchars' );
		require ABSPATH . WPINC . '/version.php';

		$user                             = wp_parse_url( get_bloginfo( 'wpurl' ) )['host'];
		$options                          = get_option( 'blog_tutor_support_settings', array() );

		$dump                             = array();
		$dump['free_disk_space']          = NerdPress_Helpers::format_size( NerdPress_Helpers::get_disk_info()['disk_free'] );
		$dump['firewall_setting']         = $options['firewall_choice'];
		$dump['domain']                   = $user;
		$dump['all_plugins']              = $current_plugins;
		$dump['active_plugins']           = get_option( 'active_plugins' );
		$dump['active_theme']             = $current_theme['Name'];
		$dump['active_theme_version']     = $current_theme['Version'];
		$dump['plugin_update_data']       = get_option( '_site_transient_update_plugins' )->response;
		$dump['wordpress_version']        = $wp_version;
		$dump['inactive_themes_data']     = wp_get_themes();

		// Removing the active theme from the theme data.
		$i = -1;
		foreach ( $dump['inactive_themes_data'] as $key => $value ) {
			$i++;
			if ( $value['Name'] === $current_theme['Name'] ) {
				unset( $dump['inactive_themes_data'][ $key ] );
			}
		}

		// The notes field is NULL on first install, so we check if it's present.
		if ( isset( get_option( 'blog_tutor_support_settings' )['admin_notice'] ) ) {
			$dump['notes'] = get_option( 'blog_tutor_support_settings' )['admin_notice'];
		}

		return $dump;
	}
}

add_action( 'init', array( 'NerdPress_Support_Snapshot', 'init' ) );
