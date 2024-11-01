<?php

class SocialWebSuite_Admin_UI {





	public static function post_submitbox_misc_actions( $post ) {

		//if ( SocialWebSuite::get_option('paused_auto_posting') )
		//	return;

	    SocialWebSuite::show_admin_sharing_notice( $post );
	}

	public function show_admin_notice() {
		include dirname( __FILE__ ) . '/templates/admin-notice.php';
	}

	public function show_admin_rate_notice(){
		include dirname( __FILE__ ) . '/templates/admin-rate-notice.php';
    }

	public function main_page() {
		$list_logs = SocialWebSuite_Log::list_logs();
		$settings = SocialWebSuite::get_settings();
		if ( isset( $settings->skip_optin ) && true === $settings->skip_optin ) {

		    SocialWebSuite::ping_server();

			include dirname( __FILE__ ) . '/templates/settings.php';
		} else {
			include dirname( __FILE__ ) . '/templates/admin-optin.php';
		}

	}

	public function post_meta_box( $post ) {
		include dirname( __FILE__ ) . '/templates/post-meta-box.php';

	}

	private function generate_secrets() {

		$tmp_token = SocialWebSuite::get_option( 'secret_key' );
		$tmp_token = trim( $tmp_token );

		if ( empty( $tmp_token ) ) {
			$tmp_token = md5( wp_generate_password( rand( 32, 64 ), true, true ) );
			SocialWebSuite::set_option( 'secret_key', $tmp_token );
		}

		printf( '<input type="hidden" name="secret" value="%s" />', esc_attr( $tmp_token ) );

		printf( '<input type="hidden" name="title" value="%s" />', esc_attr( get_option( 'blogname' ) ) );
		printf( '<input type="hidden" name="tzone" value="%s" />', esc_attr( $this->get_timezone() ) );

		printf( '<input type="hidden" name="url" value="%s" />', esc_attr( get_site_url() ) );
		printf( '<input type="hidden" name="plugin_url" value="%s" />', esc_attr( admin_url( 'admin.php?page=social-web-suite' ) ) );
		printf( '<input type="hidden" name="ajax_url" value="%s" />', esc_attr( admin_url( 'admin-ajax.php' ) ) );

	}

	private function get_timezone() {

		// if site timezone string exists, return it
		$timezone = get_option( 'timezone_string' );
		if ( $timezone ) {
			return $timezone;
		}

		// get UTC offset, if it isn't set then return UTC
		$utc_offset = get_option( 'gmt_offset', 0 );
		if ( 0 === ( $utc_offset ) ) {
			return 'UTC';
		}

		// adjust UTC offset from hours to seconds
		$utc_offset *= 3600;

		// attempt to guess the timezone string from the UTC offset
		$timezone = timezone_name_from_abbr( '', $utc_offset, 0 );
		if ( $timezone ) {
			return $timezone;
		}

		// last try, guess timezone string manually
		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] === $is_dst && $city['offset'] === $utc_offset ) {
					return $city['timezone_id'];
				}
			}
		}

		// fallback to UTC
		return 'UTC';
	}

	private function show_soc_media_panel() {

		$accounts = SocialWebSuite::get_soc_media_acc();

		?>
		<h3>
			<?php esc_html__( 'Connected Social Media Accounts', 'social-web-suite' ); ?>
		</h3>

		<?php
		if ( count( $accounts ) ) : ?>
		<ul>
		<?php
		foreach ( $accounts as $acc ) : ?>
		  <li>

		  </li>
		<?php endforeach; ?>
		</ul>
		<?php
		else : ?>
		<p>
			<?php echo esc_html__( "You didn't connect any accounts yet.", 'social-web-suite' ) ?>
		</p>

		<?php endif; ?>


		<?php
	}
} // end-class SocialWebSuite_Admin_UI
