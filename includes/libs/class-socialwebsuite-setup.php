<?php

/**
 * Handle Setup (Activate/Deactivate/Uninstall)
 *
 * @link   https://hypestudio.org/
 * @since  1.0.0
 * @author HYPEStudio <info@hypestudio.org>
 */
class SocialWebSuite_Setup {




	/**
	 * Handle the activation.
	 *
	 * @since  1.0.0
	 * @static
	 */
	public static function activate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$plugin = filter_input( INPUT_GET, 'plugin' );
		check_admin_referer( 'activate-plugin_' . $plugin );

		$token = SocialWebSuite::get_option( 'api_token' );

		// reactivate
		$options = (object) array(
		 'activated' => false,
		);

		if ( ! empty( $token ) ) {
			$reactivate = SocialWebSuite::reactivate_site();     //call remote to deactivate site
			if ( isset( $reactivate->status ) && 'OK' === $reactivate->status ) {
				$options = (object) array(
				  'activated' => true,
				);
			}
		}

		SocialWebSuite::merge_settings( $options ); // init settings
		SocialWebSuite::set_option( 'redirect', true );

		$settings = SocialWebSuite::get_settings();

		if ( isset( $settings->optin_token ) && isset( $settings->optin_plugin_status ) && 'plugin_deactivated' === $settings->optin_plugin_status ) {

			 $data        = array(
			  'event_type' => 'plugin_reactivated',
			  'event_description' => 'Plugin has been reactivated',
			 );
			 SocialWebSuite::send_optin_event( $data );
			 SocialWebSuite::set_option( 'optin_plugin_status', 'plugin_reactivated' );
		}

	}

	/**
	 * Handle the deactivation.
	 *
	 * @since  1.0.0
	 * @static
	 */
	public static function deactivate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$plugin = filter_input( INPUT_GET, 'plugin' );
		check_admin_referer( 'deactivate-plugin_' . $plugin );

		// deactivate
		SocialWebSuite::set_option( 'activated', false );
		SocialWebSuite::delete_option( 'skip_optin' );

		//call remote to deactivate site
		$deactivate = SocialWebSuite::deactivate_site();

	}

	/**
	 * Handle the uninstall.
	 *
	 * @since  1.0.0
	 * @static
	 */
	public static function uninstall() {
		if ( ! current_user_can( 'activate_plugins' ) || ( defined( 'WP_UNINSTALL_PLUGIN' ) && WP_UNINSTALL_PLUGIN !== plugin_basename( __FILE__ ) ) ) {
			return;
		}
		
		//check_admin_referer( 'bulk-plugins' );
		
		// uninstall
		SocialWebSuite::delete_settings();
	}

} // end class SocialWebSuite_Admin


//EOF
