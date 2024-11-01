<?php

/**
 * The core plugin class, just includes & inits everything
 *
 * @since      1.0.0
 * @package    SocialWebSuite
 * @subpackage SocialWebSuite/includes
 * @author     HYPEStudio <info@hypestudio.org>
 */
class SocialWebSuite {


	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    string
	 */
	protected static $plugin_name;

	/**
	 * The path to the plugin file
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    string
	 */
	protected static $plugin_file;

	/**
	 * The plugin dir path
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    string
	 */
	protected static $plugin_dir;

	/**
	 * The plugin dir url
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    string
	 */
	protected static $plugin_url;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    string
	 */
	protected static $version;

	/**
	 * The text-domain for the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    string
	 */
	protected static $textdomain;
	/**
	 * Name of the WP option where we keep settings
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    string
	 */
	protected static $plugin_options_key = 'sws_settings';
	/**
	 * Singleton instance
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    SocialWebSuite
	 */
	protected static $instance;
	/**
	 * Server URL is hard-coded here (w/ slash on the end)
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    SocialWebSuite
	 */
	protected static $server_url;
	/**
	 * Instance of class handling admin actions
	 *
	 * @since  1.0.0
	 * @access protected
	 * @static
	 * @var    SocialWebSuite
	 */
	protected $plugin_admin;

	/**
	 * Constructor is private, call create() instead
	 *
	 * @access private
	 * @since  1.0.0
	 */
	private function __construct() {
		self::$plugin_name = 'Social Web Suite Client';
		self::$version     = '2.0.6';
		self::$textdomain  = 'social-web-suite';
		self::$plugin_file = SWS_PLUGIN_PATH;
		self::$plugin_dir  = plugin_dir_path( self::$plugin_file );
		self::$plugin_url  = plugin_dir_url( self::$plugin_file );
		self::$server_url  = SWS_SERVER_URL;

	}

	/**
	 * Handle the activation
	 *
	 * @since  1.0.0
	 * @access public
	 * @static
	 */
	public static function activate_plugin() {
		self::load_required( 'includes/libs/class-socialwebsuite-setup.php' );
		SocialWebSuite_Setup::activate();
	}

	/**
	 * Handle the deactivation
	 *
	 * @since  1.0.0
	 * @access public
	 * @static
	 */
	public static function deactivate_plugin() {
		self::load_required( 'includes/libs/class-socialwebsuite-setup.php' );
		SocialWebSuite_Setup::deactivate();
	}

	/**
	 * Handle uninstall
	 *
	 * @since  1.0.0
	 * @access public
	 * @static
	 */
	public static function uninstall_plugin() {
		self::load_required( 'includes/libs/class-socialwebsuite-setup.php' );
		SocialWebSuite_Setup::uninstall();
	}

	/**
	 * Merge old and new settings
	 *
	 * @param stdClass $new Options to add
	 *
	 * @since  1.0.0
	 * @static
	 *
	 */
	public static function merge_settings( $new ) {
		$settings = self::get_settings();
		$settings = (object) array_merge( (array) $settings, (array) $new );
		self::save_settings( $settings );
	}

	/**
	 * Delete settings
	 *
	 * @since  1.0.0
	 * @static
	 */
	public static function delete_settings() {
		return delete_option( self::$plugin_options_key );
	}

	/**
	 * The full path of the plugin
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function get_plugin_path() {
		self::create();

		return self::$plugin_file;
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function get_plugin_name() {
		self::create();

		return self::$plugin_name;
	}

	/**
	 * The plugin folder
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function get_plugin_dir() {
		self::create();

		return self::$plugin_dir;
	}

	/**
	 * The plugin folder
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function get_plugin_url() {
		self::create();

		return self::$plugin_url;
	}

	/**
	 * Retrieve the version of the plugin.
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function get_version() {
		self::create();

		return self::$version;
	}

	/**
	 * Get the server URL (for all server calls)
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function get_server_url() {
		self::create();

		return self::$server_url;
	}

	/*** Helper Methods ***/

	public static function upload_image( $img_path ) {

		$file_data = wp_remote_get( $img_path );

		if ( false === $file_data ) {
			return false;
		}

		$headers = array(
			'content-type' => 'application/binary', // Set content type to binary
		);

		return self::call_api( 'upload-image', $file_data, $headers );
	}

	public static function call_api( $url, $data, $headers = array() ) {

		$token = self::get_option( 'api_token' );

		$data['api_token'] = $token;

		$response = wp_remote_post(
			self::$server_url . 'api/' . $url, array(
				'method'      => 'POST',
				'timeout'     => 600,
				'redirection' => 50,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $headers,
				'body'        => $data,
				'cookies'     => array(),
			)
		);

		if ( is_wp_error( $response ) ) {
			SocialWebSuite_Log::error( $response->get_error_message() );

			return $response->get_error_message();
		}

		if ( empty( $response['response']['code'] ) || 200 !== $response['response']['code'] ) {
			$msg = 'Error: ' . $response['response']['code'] . ' ' . $response['response']['message'];
			SocialWebSuite_Log::error( $msg );

			return $msg;
		}

		$body = json_decode( trim( $response['body'] ) );

		return $body ? $body : trim( $response['body'] );
	}

	/**
	 * Get one key from plugin settings
	 *
	 * @param string        Option name
	 *
	 * @return mixed        Option value
	 * @since  1.0.0
	 * @static
	 *
	 */
	public static function get_option( $key, $default_val = null, $force_reload = false ) {
		$settings = self::get_settings( $force_reload );

		return isset( $settings->$key ) ? $settings->$key : $default_val;
	}

	public static function manually_share_post( $post ) {

		$allowed_statuses = array( 'publish', 'future' );
		// check if already handled or not published at all
		$sws_already_shared = self::is_already_shared( $post->ID );
		//$sws_already_shared = filter_input( INPUT_POST, 'sws_already_shared' );
		if ( $sws_already_shared === true || ! in_array( $post->post_status, $allowed_statuses, true ) ) {
			SocialWebSuite_Log::info( 'manually share post skipping? ' . $sws_already_shared . ' - status  ' . $post->post_status );

			return;
		}


		$meta_manual = get_post_meta( $post->ID, 'sws_meta_manual', true );

		if ( 'custom' !== $meta_manual ) { // not manually set to share
			if ( $post->post_status !== 'future' ) {
				SocialWebSuite_Log::info( 'not manual  not custom ? ' . $post->post_status . $meta_manual );

				return;
			}
		}
		SocialWebSuite_Log::info( 'manually share post' . json_encode( $post ) );
		self::share_post( $post );
		// $_POST['sws_already_shared'] = true; // flag to avoid multiple sharing
		self::set_already_shared( $post->ID );
	}

	public static function share_post( $post ) {

		//check if sharing is active, if not, skip sharing post
		$sharing_active = self::check_sharing_active_status();


		$share_this_post = apply_filters( 'socialwebsuite_share_post_' . $post->post_type, true );


		if ( true != $sharing_active && true != $share_this_post ) {
			return false;
		}

		$settings    = self::get_settings();
		$meta_manual = get_post_meta( $post->ID, 'sws_meta_manual', true );

		//if ( empty($settings->paused_manual_posting) and ! empty($meta_manual) and $meta_manual === 'custom') {
		if ( ! empty( $meta_manual ) && 'custom' === $meta_manual ) {

			/* available options
            'sws_meta_manual',   			// [default, skip, custom]
            'sws_meta_send_now', 			// [now, schedule]
            'sws_meta_schedule_calendar',	// date as shown in the calendar
            'sws_meta_schedule_date', 		// YYYY-mm-dd date
            'sws_meta_schedule_hours',		// 0 - 12
            'sws_meta_schedule_mins',		// 0 - 60
            'sws_meta_schedule_ampm',		// [am, pm]
            'sws_meta_include_image',		// [default, 0, 1]
            'sws_meta_hashtags',			// string
            'sws_meta_format',				// string
            */

			$meta_schedule = get_post_meta( $post->ID, 'sws_meta_send_now', true );

			if ( 'schedule' === $meta_schedule ) {
				$meta_date      = get_post_meta( $post->ID, 'sws_meta_schedule_date', true );
				$meta_hour      = get_post_meta( $post->ID, 'sws_meta_schedule_hours', true );
				$meta_mins      = get_post_meta( $post->ID, 'sws_meta_schedule_mins', true );
				$meta_ampm      = get_post_meta( $post->ID, 'sws_meta_schedule_ampm', true );
				$next_year_date = strtotime( '+1 year' );
				$scheduled_date = strtotime( $meta_date );
				if ( $next_year_date < $scheduled_date ) {
					return false;
				}

				$meta_date = ( ! empty( $meta_date ) ) ? $meta_date : date( 'd/m/Y' );
				$meta_hour = ( ! empty( $meta_hour ) ) ? $meta_hour : '12';
				$meta_mins = ( ! empty( $meta_mins ) ) ? $meta_mins : '00';
				$meta_ampm = ( ! empty( $meta_ampm ) ) ? $meta_ampm : 'am';

				if ( '' !== $meta_date && '' !== $meta_hour && '' !== $meta_mins && '' !== $meta_ampm ) {
					$meta_datetime = sprintf( '%s %s:%s:00 %s', $meta_date, str_pad( $meta_hour, 2, '0', STR_PAD_LEFT ), str_pad( $meta_mins, 2, '0', STR_PAD_LEFT ), $meta_ampm );
					$gmt_datetime  = get_gmt_from_date( date( 'Y-m-d H:i:s', strtotime( $meta_datetime ) ) );
				}
			}
		}

		if ( 'future' === $post->post_status ) {
			$gmt_datetime = $post->post_date_gmt;
		}

		$data = array(
			'publish_at' => isset( $gmt_datetime ) ? $gmt_datetime : current_time( 'mysql', true ),
			'content'    => wp_json_encode( self::get_post_data( $post ) ),
		);

		$api_url  = 'share/' . $settings->site_id;
		$response = self::call_api( $api_url, $data );
		SocialWebSuite_Log::info( ' response  api_url ' . $api_url . '  data ' . json_encode( $data ) . ' response ' . json_encode( $response ) );
		if ( is_object( $response ) && isset( $response->status ) && $response->status === 'Error' ) {
			if ( isset( $response->subscription_expired ) && isset( $response->msg ) ) {

				$message = $response->msg;
				self::set_option( 'subscription_expired', true );
				self::set_option( 'subscription_expired_message', $message );

			} else {

				$message = isset( $response->msg ) ? $response->msg : '';
				self::delete_option( 'subscription_expired' );
				self::delete_option( 'subscription_expired_message' );
				self::set_option( 'share_error_message', $message );

			}
		} else {
			self::delete_option( 'subscription_expired' );
		}

		return $response;
	}


	public static function get_post_data( $post ) {
		//do the swap with custom message variations
		$meta_use_cutom_messages                = (int) get_post_meta( $post->ID, 'sws_meta_use_cutom_messages', true );
		$custom_message_variations_data_encoded = get_post_meta( $post->ID, 'sws_meta_custom_message_variations_data', true );
		$custom_message_variations_data         = (array) json_decode( $custom_message_variations_data_encoded );

		if ( ! isset( $custom_message_variations_data['variations'] ) ) {
			$custom_message_variations_data['variations'] = array();
		}
		if ( count( $custom_message_variations_data['variations'] ) === 0 ) {
			$custom_message = get_post_meta( $post->ID, 'sws_meta_custom_message', true );
			$custom_message = trim( $custom_message );
			if ( ! empty( $custom_message ) ) {
				$post->post_title = $custom_message;
			}
		} else {
			if ( $meta_use_cutom_messages === 1 ) {

				if ( isset( $custom_message_variations_data['last_shared_key'] ) && isset( $custom_message_variations_data['variations'] ) ) {
					$last_shared_key = $custom_message_variations_data['last_shared_key'];

					$next_key            = $last_shared_key + 1;
					$custom_message_data = self::get_custom_message( $next_key, $custom_message_variations_data );


					if ( $custom_message_data['custom_message'] ) {
						$post->post_title = $custom_message_data['custom_message'];
					} else {
						$custom_message_data['variations_data']['last_shared_key'] = - 1;
					}

					update_post_meta( $post->ID, 'sws_meta_custom_message_variations_data', json_encode( $custom_message_data['variations_data'] ) );
				}
			}

		}


		// author
		$author = get_user_by( 'id', $post->post_author );

		// excerpt
		if ( empty( $post->post_excerpt ) ) {
			$excerpt = wp_trim_words( strip_shortcodes( $post->post_content ) );
		} else {
			$excerpt = $post->post_excerpt;
		}

		if ( ! strlen( $excerpt ) ) {
			$excerpt = $post->post_title;
		}

		$taxonomies     = get_object_taxonomies( get_post_type( $post->ID ), 'objects' );
		$cat_taxonomies = array(); //hieararchical taxonomies like built in post categories
		$tag_taxonomies = array(); //non-hieararchical taxonomies like built in post tags

		if ( ! empty( $taxonomies ) ) {
			if ( ! is_wp_error( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {

					//for WooCommerce product, we skip other taxonomies
					if ( 'product' === $post->post_type && ! in_array( $taxonomy->name, array(
							'category',
							'post_tag',
							'product_cat',
							'product_tag'
						) ) ) {
						continue;
					}
					if ( $taxonomy->hierarchical ) {
						$cat_taxonomies[] = $taxonomy->name;
					} else {
						$tag_taxonomies[] = $taxonomy->name;
					}
				}
			}
		}

		//categories and other hierarchical taxonomy terms
		$cat_names = array();
		$cat_ids   = array();
		$cats      = wp_get_object_terms( $post->ID, $cat_taxonomies );
		if ( ! empty( $cats ) ) {
			foreach ( $cats as $cat ) {
				if ( $cat->name ) {
					$cat_names[] = str_replace( ' ', '', trim( $cat->name ) );
					$cat_ids[]   = $cat->term_id;
				}
			}
		}

		//tags and other non-hiearchical taxonomy terms
		$tag_names = array();
		$tag_ids   = array();
		$tags      = wp_get_object_terms( $post->ID, $tag_taxonomies );
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				if ( $tag->name ) {
					$tag_names[] = str_replace( ' ', '', trim( $tag->name ) );
					$tag_ids[]   = $tag->term_id;
				}
			}
		}

		//apply filters to content
		$post_title = apply_filters( 'socialwebsuite_post_title', $post->post_title, $post );
		$excerpt    = apply_filters( 'socialwebsuite_post_content', $excerpt, $post );

		// compile all data
		$data = array(
			'sitename'                     => get_bloginfo( 'name' ),
			'id'                           => $post->ID,
			'author'                       => $author->display_name,
			'post_status'                  => $post->post_status,
			//'date_gmt' => $post->post_date_gmt,
			'date'                         => get_the_date( '', $post->ID ), // use the default WP format
			'content'                      => $excerpt,
			'title'                        => $post_title,
			'post_type'                    => $post->post_type,
			'categories'                   => $cat_names,
			'category_ids'                 => $cat_ids,
			'url'                          => get_permalink( $post->ID ),
			'tags'                         => $tag_names,
			'tag_ids'                      => $tag_ids,
			'meta_use_hashtags'            => get_post_meta( $post->ID, 'sws_meta_use_hashtags', true ),
			'meta_hashtags'                => get_post_meta( $post->ID, 'sws_meta_hashtags', true ),
			'meta_times_shared'            => get_post_meta( $post->ID, 'sws_meta_times_shared', true ),
			'meta_format'                  => get_post_meta( $post->ID, 'sws_meta_format', true ),
			'meta_include_image'           => get_post_meta( $post->ID, 'sws_meta_include_image', true ),
			'meta_social_accounts_exclude' => get_post_meta( $post->ID, 'sws_meta_social_accounts_exclude', true ),
		);

		if ( 'skip' !== $data['meta_include_image'] ) { // add featured image
			$thumb_id = get_post_thumbnail_id( $post->ID );

			if ( $thumb_id ) {
				$featured_image = wp_get_attachment_image_src( $thumb_id, 'full' );

				if ( $featured_image ) {
					$data['image_url']    = $featured_image[0];
					$data['image_width']  = $featured_image[1];
					$data['image_height'] = $featured_image[2];
				}
			}
		}

		return (object) $data;
	}

	public static function auto_share_post( $post, $share_scheduled = false ) {

		// check if already handled or not published at all
		//$sws_already_shared = filter_input( INPUT_POST, 'sws_already_shared' );
		if ( false === $share_scheduled ) {
			$sws_already_shared = self::is_already_shared( $post->ID );
		} else {
			$sws_already_shared = false;
		}

		// $sws_already_shared_meta = get_post_meta( $post->ID, 'sws_already_shared' );

		if ( $sws_already_shared === true || 'publish' !== $post->post_status ) {
			SocialWebSuite_Log::info( 'post shared or publish not' . json_encode( $post ) );

			return;
		} else {
			SocialWebSuite_Log::info( 'post not shared auto_share_post' . json_encode( $post ) );
		}

		// get settings
		$settings    = self::get_settings();
		$meta_manual = get_post_meta( $post->ID, 'sws_meta_manual', true );
		if ( empty( $meta_manual ) ) {
			$meta_manual = 'default';
		}
		$meta_send_now = get_post_meta( $post->ID, 'sws_meta_send_now', true );

		//if we post is set to be shared now, no need to send same post again. Skip the duplicate
		if ( empty( $meta_manual ) ) {
			SocialWebSuite_Log::info( 'empty($meta_manual) ' . json_encode( $post ) );

			return;
		}
		if ( 'custom' === $meta_manual && ( 'now' === $meta_send_now || 'schedule' === $meta_send_now ) ) {
			SocialWebSuite_Log::info( ' meta manual custom, or meta send now, or meta sens schedule ' . $meta_manual . $meta_send_now . json_encode( $post ) );

			return;
		}

		$excluded_post_types = self::get_option( 'excluded_post_types', array(), true );
		if ( in_array( $post->post_type, $excluded_post_types ) ) {
			SocialWebSuite_Log::info( ' excluded post type ' . json_encode( $post ) );

			return;
		}

		// if manually set to don't share
		//if ( empty($settings->paused_manual_posting) and $meta_manual === 'skip')
		//	return false;
		if ( 'skip' === $meta_manual ) {
			SocialWebSuite_Log::info( ' skip  ' . json_encode( $post ) );

			return false;
		}

		//if set to default, check for terms selected and compare to included and excluded categories from global settings
		if ( 'default' === $meta_manual ) {
			global $wpdb;

			$sql = 'SELECT * FROM ' . $wpdb->term_relationships . ' AS terms WHERE terms.object_id = ' . $post->ID;

			$results = $wpdb->get_results( $sql, ARRAY_A ); // WPCS: unprepared SQL OK.

			//excluded categories
			$exclude_categories = self::get_option( 'exclude_categories', array(), true );
			//included categories
			$include_categories = self::get_option( 'include_categories', array(), true );

			if ( count( $exclude_categories ) > 0 ) {
				$exclude_categories = self::filter_terms( $exclude_categories );
			}

			if ( count( $include_categories ) > 0 ) {
				$include_categories = self::filter_terms( $include_categories );
			}


			if ( count( $include_categories ) > 0 ) {
				$term_included = false;
			} else {
				$term_included = true;
			}

			foreach ( $results as $post_term ) {
				$post_term_id = $post_term['term_taxonomy_id'];

				//if any post term is in excluded categories list, return false
				if ( in_array( $post_term_id, $exclude_categories, true ) ) {
					SocialWebSuite_Log::info( ' in_array( $post_term_id, $exclude_categories, true )' . json_encode( $post ) );

					return false;
				}

				if ( true !== $term_included && in_array( $post_term_id, $include_categories, true ) ) {
					$term_included = true;
				}
			}
			//if none of the post terms is not in included categories list return false
			if ( false === $term_included ) {
				SocialWebSuite_Log::info( ' false === $term_included  ' . json_encode( $post ) );

				return false;
			}
		}// End if().

		$include_non_public = SocialWebSuite::get_option( 'include_non_public_post_types' );

		if ( $include_non_public == true ) {
			$args2 = array(
				//	'public'   => false,
				'_builtin' => false,
			);
		} else {
			$args2 = array(
				'public'   => true,
				'_builtin' => false,
			);
		}

		$custom_types = array_values(
			get_post_types(
				$args2
			)
		);

		if ( ! $custom_types ) {
			$custom_types = array();
		}

		$all_post_types = array_merge( array( 'post' ), $custom_types );

		// check type
		switch ( $settings->share_types ) {

			case 'posts':
				if ( ! in_array( $post->post_type, $all_post_types, true ) ) {
					SocialWebSuite_Log::info( ' not post type post ' . json_encode( $post ) );

					return false;
				}
				break;

			case 'pages':
				if ( 'page' !== $post->post_type ) {
					SocialWebSuite_Log::info( ' not post type page ' . json_encode( $post ) );

					return false;
				}
				break;

			case 'both':
			default:
				if ( 'page' !== $post->post_type && ! in_array( $post->post_type, $all_post_types, true ) ) {
					SocialWebSuite_Log::info( ' not post type both ' . json_encode( $post ) );

					return false;
				}
				break;
		}
		SocialWebSuite_Log::info( 'auto share post' . json_encode( $post ) );
		self::share_post( $post );


		//$_POST['sws_already_shared'] = true; // flag to avoid multiple sharing
		self::set_already_shared( $post->ID );
	}


	public static function format_share( $post, $format, $settings ) {

		$author = get_user_by( 'id', $post->post_author );

		if ( empty( $post->post_excerpt ) ) {
			$excerpt = wp_trim_words( strip_shortcodes( $post->post_content ) );
		} else {
			$excerpt = $post->post_excerpt;
		}

		$cat_names = array();
		$cats      = wp_get_post_categories(
			$post->ID, array(
				'fields' => 'ids',
			)
		);
		if ( ! empty( $cats ) ) {
			foreach ( $cats as $cat_id ) {
				$cat = get_category( $cat_id );
				if ( $cat->name ) {
					$cat_names[] = '#' . strtolower( str_replace( ' ', '', trim( $cat->name ) ) );
				}
			}
		}

		// post tags
		$post_tags = get_the_tags( $post->ID );
		$tag_names = array();

		foreach ( $post_tags as $tag ) {
			$tag_names[] = '#' . strtolower( str_replace( ' ', '-', trim( $tag->name ) ) );
		}

		$formatted = strtr(
			$format, array(
				'{sitename}' => get_bloginfo( 'name' ),
				'{title}'    => $post->post_title,
				'{excerpt}'  => $excerpt,
				'{category}' => implode( ' ', $cat_names ),
				'{date}'     => get_the_date( '', $post ), // use default WP format
				'{author}'   => $author->display_name,
				'{url}'      => get_permalink( $post->ID ),
			)
		);

		$meta_use_hashtags = get_post_meta( $post->ID, 'sws_meta_use_hashtags', true );
		$use_hashtags      = $meta_use_hashtags ? $meta_use_hashtags : $settings->hashtag_type;

		if ( 'cats' === $use_hashtags ) {
			$formatted = str_replace( '{hashtags}', implode( ' ', $cat_names ), $formatted );

		} elseif ( 'tags' === $use_hashtags ) {
			$formatted = str_replace( '{hashtags}', implode( ' ', $cat_names ), $formatted );

		} elseif ( 'custom' === $use_hashtags ) {
			$custom_tags = $meta_use_hashtags ? get_post_meta( $post->ID, 'sws_meta_hashtags', true ) : $settings->global_hashtags;
			$formatted   = str_replace( '{hashtags}', $custom_tags, $formatted );
		}

		return $formatted;
	}

	public static function verify( $hash, $timestamp ) {

		// saved data
		$saved       = self::get_option( 'secret_key' );
		$control_key = hash_hmac( 'sha256', $saved, $timestamp );

		// check that they match
		return ( $hash === $control_key && $timestamp > time() );
	}

	public static function verify_optin( $hash, $timestamp ) {

		// saved data
		$saved       = self::get_option( 'optin_secret_key' );
		$control_key = hash_hmac( 'sha256', $saved, $timestamp );

		// check that they match
		return ( $hash === $control_key && $timestamp > time() ); //
	}

	/**
	 * Get the list of connected soc. media accounts from the server
	 * (NOT USED AT THE MOMENT)
	 */
	public static function get_soc_media_acc() {

		$site_id = self::get_option( 'site_id' );

		$response = self::call_api(
			'list-soc-media', array(
				'site_id' => $site_id,
			)
		);

		return $response;
	}

	public static function pick_posts( $prefs, $repeat = true ) {

		// get posts
		$posts = self::query_pick_posts( $prefs );

		if ( empty( $posts ) ) {
			if ( ! $repeat ) {
				return false;
			}

			self::delete_option( 'used_posts_ids' );

			return self::pick_posts( $prefs, false ); // query again from the start
		}


		$data = array();

		foreach ( (array) $posts as $post ) {
			$data[] = self::get_post_data( $post );
		}

		return $data;
	}

	private static function query_pick_posts( $prefs ) {

		// clear all caches
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			w3tc_pgcache_flush();
			w3tc_dbcache_flush();
			w3tc_minify_flush();
			w3tc_objectcache_flush();
		}

		// posts already picked
		$used_posts_ids = self::get_option( 'used_posts_ids', array(), true );

		//posts that are being excluded
		$excluded_posts_ids = self::get_option( 'exclude_posts', array(), true );

		$excluded_post_types = self::get_option( 'excluded_post_types', array(), true );

		$excluded_social_accounts_posts = self::get_excluded_social_accounts_posts_ids( $prefs );

		if ( ! empty( $prefs->max_repeat ) ) {

			// ignore those shared too many times already
			$args = array(
				'posts_per_page'      => - 1,
				'ignore_sticky_posts' => true,
				'cache_results'       => false,
				'post_status'         => 'publish',
				'orderby'             => 'none',
				'post__not_in'        => $used_posts_ids,
				'meta_key'            => 'sws_meta_times_shared',
				'meta_value'          => (int) $prefs->max_repeat,
				'meta_compare'        => '>=',
				'meta_type'           => 'UNSIGNED',
			);

			$query = new WP_Query( $args );

			$posts = $query->get_posts();

			if ( $posts ) {

				foreach ( $posts as $post ) {
					$used_posts_ids[] = $post->ID;
				}

				SocialWebSuite::set_option( 'used_posts_ids', $used_posts_ids );
			}
		}
		//exclude also non evergreen posts where $current_date is not within their date range
		$current_date = strtotime( 'now' );
		$args         = array(
			'posts_per_page'      => - 1,
			'ignore_sticky_posts' => true,
			'cache_results'       => false,
			'post_status'         => 'publish',
			'orderby'             => 'none',
		);

		$args['meta_query']     = array(
			'relation' => 'OR',
			array(
				'key'     => 'sws_meta_startdate_date',
				'value'   => $current_date,
				'compare' => '>',
			),
			array(
				'key'     => 'sws_meta_enddate_date',
				'value'   => $current_date,
				'compare' => '<',
			)
		);
		$nonevergreen_posts_ids = array();

		$query = new WP_Query( $args );

		$posts = $query->get_posts();
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$nonevergreen_posts_ids[] = $post->ID;
			}
		}


		//print_r($nonevergreen_posts_ids);
		$post_not_in = array_unique( array_merge( (array) $used_posts_ids, (array) $excluded_posts_ids, (array) $excluded_social_accounts_posts, (array) $nonevergreen_posts_ids ) );

		//apply filters for user to programmatically add or remove excluded posts ids
		$post_not_in = apply_filters( 'socialwebsuite_exclude_posts_ids', $post_not_in );

		// query args
		$args = array(
			'posts_per_page'      => $prefs->num_posts_share,
			'ignore_sticky_posts' => true,
			'cache_results'       => false,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'post__not_in'        => $post_not_in,
		);

		// include/exclude ----------------------------------------------------
		$share_by_categories_ok = false;
		if ( ! empty( $prefs->share_by_categories ) ) {
			$share_by_categories = explode( ',', $prefs->share_by_categories );
			if ( count( $share_by_categories ) > 0 ) {
				$share_by_categories = self::filter_terms( $share_by_categories );
				if ( count( $share_by_categories ) > 0 ) {
					$share_by_categories_ok = true;
					$args['tax_query']      = self::make_tax_query( $share_by_categories );
				}
			}
		}
		if ( false === $share_by_categories_ok ) {
			if ( ! empty( $prefs->include_categories ) ) {
				//$args['category__in'] = $prefs->include_categories;

				$args['tax_query'] = self::make_tax_query( $prefs->include_categories );

			}

			if ( ! empty( $prefs->exclude_categories ) ) {

				if ( ! isset( $args['tax_query'] ) || empty( $args['tax_query'] ) ) {
					$args['tax_query'] = self::make_tax_query( $prefs->exclude_categories, true );
				} else {
					$args['tax_query'] = array_merge( $args['tax_query'], self::make_tax_query( $prefs->exclude_categories, true ) );
				}
			}
		}

		if ( ! empty( $prefs->exclude_posts ) ) {
			$args['post__not_in'] = array_merge( $args['post__not_in'], $prefs->exclude_posts );
		}

		// types --------------------------------------------------------------
		$include_non_public = SocialWebSuite::get_option( 'include_non_public_post_types' );

		if ( $include_non_public == true ) {
			$args2 = array(
				//'public'   => false,
				'_builtin' => false,
			);
		} else {
			$args2 = array(
				'public'   => true,
				'_builtin' => false,
			);
		}
		$custom_types = array_values(
			get_post_types(
				$args2
			)
		);

		if ( ! $custom_types ) {
			$custom_types = array();
		} else {
			if ( count( $excluded_post_types ) > 0 ) {
				$custom_types = array_diff( $custom_types, $excluded_post_types );
			}
		}
		if ( ! isset( $prefs->share_types ) ) {
			$prefs->share_types = 'both';
		}
		switch ( $prefs->share_types ) {
			case 'posts':
				$args['post_type'] = array_merge( array( 'post' ), $custom_types );
				break;

			case 'pages':
				$args['post_type'] = 'page';
				break;

			case 'both':
			default:
				$args['post_type'] = array_merge( array( 'post', 'page' ), $custom_types );
				break;
		}

		// date--------------------------------------------------------------

		$date_query = array();

		if ( ! empty( $prefs->min_age_post ) ) {
			$date_query[] = array(
				'before' => $prefs->min_age_post . ' days ago',
			);
		}

		if ( ! empty( $prefs->max_age_post ) ) {
			$date_query[] = array(
				'after' => $prefs->max_age_post . ' days ago',
			);
		}

		if ( $date_query ) {
			$args['date_query'] = array( $date_query );
		}

		// get data
		$query = new WP_Query( $args );

		return $query->have_posts() ? $query->posts : array();
	}

	private static function make_tax_query( $list_term_ids, $not_in = false ) {

		$terms = array();

		foreach ( $list_term_ids as $term_id ) {
			$term = get_term( $term_id );
			if ( ! is_null( $term ) && is_object( $term ) && isset( $term->taxonomy ) ) { //if we return no term i.e. if it was deleted, don't proceed with inner block
				if ( ! isset( $terms[ $term->taxonomy ] ) ) {
					$terms[ $term->taxonomy ] = array();
				}

				$terms[ $term->taxonomy ][] = $term_id;
			}

		}

		if ( ! $terms ) {
			return false;
		}

		$result = array();

		if ( count( $terms ) > 1 ) {
			if ( $not_in ) {
				$result['relation'] = 'AND';
			} else {
				$result['relation'] = 'OR';
			}
		}

		foreach ( $terms as $taxonomy => $term_ids ) {
			$entry = array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_ids,
			);

			if ( $not_in ) { // reverse logic
				$entry['operator'] = 'NOT IN';
			}

			$result[] = $entry;
		}

		return $result;
	}

	/**
	 * Remotelly call server and deactivate site on server
	 *
	 * @return mixed
	 */
	public static function deactivate_site() {

		$settings = self::get_settings();

		if ( ! empty( $settings->site_id ) ) {
			$api_url = 'deactivate/' . $settings->site_id;
		} else {
			$api_url = '';
		}

		$data = array();

		return self::call_api( $api_url, $data );

	}

	public static function reactivate_site() {

		$settings = self::get_settings();

		$api_url = 'reactivate/' . $settings->site_id;

		$data = array();

		return self::call_api( $api_url, $data );
	}

	/**
	 * excludes post from excluded posts list
	 *
	 * @param integer $post_id
	 * @param string  $share_option
	 *
	 * @return mixed void or boolean
	 */
	public static function update_exclude_list_single( $post_id, $share_option, $old_share_option ) {

		$the_post = wp_is_post_revision( $post_id );
		if ( $the_post ) {
			$post_id = $the_post;
		}

		if ( ( 'custom' === $share_option || 'default' === $share_option || 'notevergreen' === $share_option ) && 'skip' !== $old_share_option ) {
			return;
		}

		//posts that are being excluded
		$excluded_posts_ids = self::get_option( 'exclude_posts', array(), true );
		$excluded_posts_ids = (array) $excluded_posts_ids;
		$excluded_posts_ids = array_values( $excluded_posts_ids );

		if ( 'skip' === $share_option ) {
			if ( ! in_array( $post_id, $excluded_posts_ids, true ) ) {

				$excluded_posts_ids[] = $post_id;
				self::set_option( 'exclude_posts', $excluded_posts_ids );
				$exclude_action = 'add';
			}
		} else {
			if ( ( $key = array_search( $post_id, $excluded_posts_ids ) ) !== false ) {
				unset( $excluded_posts_ids[ $key ] );
				self::set_option( 'exclude_posts', $excluded_posts_ids );
				$exclude_action = 'remove';
			}
		}

		if ( isset( $exclude_action ) ) {
			$settings = self::get_settings();
			$data     = array(
				'post_id'        => $post_id,
				'exclude_action' => $exclude_action,
			);
			$api_url  = 'exclude-post/' . $settings->site_id;

			self::call_api( $api_url, $data );
		}

		return true;

	}

	/**
	 * Get one key from plugin settings
	 *
	 * @param string        Option name
	 * @param mixed         Option value
	 *
	 * @since  1.0.0
	 * @static
	 *
	 */
	public static function set_option( $key, $val ) {
		$settings = self::get_settings();
		if ( empty( $settings ) ) {
			$settings = new stdClass();
		}
		$settings->$key = $val;
		self::save_settings( $settings );
	}

	/**
	 * Get plugin settings
	 *
	 * @return stdClass    Options object
	 * @since  1.0.0
	 * @static
	 */
	public static function get_settings( $force_reload = false ) {
		if ( $force_reload ) {
			wp_cache_delete( self::$plugin_options_key );
		}

		return get_option( self::$plugin_options_key );
	}

	/**
	 * Set plugin settings
	 *
	 * @param stdClass
	 *
	 * @return boolean        Status of update_option() operation
	 * @since  1.0.0
	 * @static
	 *
	 */
	public static function save_settings( $options ) {
		return update_option( self::$plugin_options_key, $options, false );
	}

	// called only if manual_posting is enabled

	/**
	 * Delete one key from plugin settings
	 *
	 * @since  1.0.0
	 * @static
	 */
	public static function delete_option( $key ) {
		$settings = get_option( self::$plugin_options_key );
		unset( $settings->$key );
		self::save_settings( $settings );
	}


	/**
	 * Run the plugin
	 *
	 * @since 1.0.0
	 */
	public function run() {
		global $pagenow;
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'activated_plugin', array( $this, 'activated_plugin' ) );
		// we've got a separate class for the admin stuff
		self::load_required( 'includes/admin/class-socialwebsuite-admin.php' );
		$this->plugin_admin = new SocialWebSuite_Admin();
		$this->plugin_admin->init();
	}

	/**
	 * Include the given plugin file
	 *
	 * @param string $file Path relative to the plugin folder
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 */
	public static function load_required( $file ) {
		self::create();
		include_once self::$plugin_dir . $file;
	}

	/**
	 * Create a singleton
	 *
	 * @return SocialWebSuite
	 * @since  1.0.0
	 * @static
	 */
	public static function create() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new SocialWebSuite;
		}

		return self::$instance;
	}

	/**
	 * Initialize & run everything (on init hook)
	 *
	 * @since 1.0.0
	 */
	public function init() {
		global $pagenow;
		$this->general_stuff();
		$this->handle_optin();
		$this->enqueue_front_scripts();
		$this->enqueue_front_styles();
		//do the redirect for opt-in propose to user
		$activate_multi = filter_input( INPUT_GET, 'activate-multi' );
		if ( self::get_option( 'redirect' ) && true === self::get_option( 'redirect' ) && true !== $activate_multi ) {
			self::delete_option( 'redirect' );
			self::redirect( admin_url( 'admin.php?page=social-web-suite' ) );
		}
		if ( 'plugins.php' === $pagenow ) {
			add_filter( 'plugin_action_links', array( $this, 'add_action_links' ), 20, 2 );
			add_action( 'admin_footer', array( $this, 'add_deactivation_feedback_dialog_box' ) );
		}

		$settings = self::get_settings();
		//print_r($settings);
		if ( ! empty( $settings->share_scheduled ) ) {
			add_action( 'future_to_publish', array( $this, 'action_share_scheduled' ) );
		}

		//add_action( 'before_delete_post', array( $this, 'action_delete_wpscheduled' ) );
		add_action( 'trash_post', array( $this, 'action_delete_wpscheduled' ) );
	}

	public function activated_plugin() {

	}

	public function handle_optin() {

		$page       = filter_input( INPUT_GET, 'page' );
		$sws_action = filter_input( INPUT_GET, 'sws_action' );
		$nonce      = filter_input( INPUT_GET, '_wpnonce' );
		if ( 'social-web-suite' === $page ) {
			if ( 'sws-skip-optin' === $sws_action && wp_verify_nonce( $nonce, 'sws-skip-optin' ) ) {
				//TODO disable optin asking again
				$optin_token = self::get_option( 'optin_token' );

				if ( ! empty( $optin_token ) ) {

					$data = array(
						'event_type'        => 'plugin_optedout',
						'event_description' => 'User has been Opted-out',
					);
					self::call_optin_api( 'opt-in/event', $data );
					self::delete_option( 'optin_token' );

				}

				self::set_option( 'skip_optin', true );
				self::redirect( admin_url( 'admin.php?page=social-web-suite' ) );
			}
			$sws_action       = filter_input( INPUT_POST, 'sws_action' );
			$nonce            = filter_input( INPUT_POST, '_wpnonce' );
			$_wp_http_referer = filter_input( INPUT_POST, '_wp_http_referer' );
			if ( 'sws-activate-optin' === $sws_action && wp_verify_nonce( $nonce, 'sws-activate-optin' ) ) {


				$data                     = array();
				$current_user             = wp_get_current_user();
				$user_data                = array(
					'username'          => $current_user->user_login,
					'user_email'        => $current_user->user_email,
					'user_firstname'    => $current_user->user_firstname,
					'user_lastname'     => $current_user->user_lastname,
					'user_display_name' => $current_user->display_name,
					'user_ip'           => self::get_ip(),
				);
				$data['url']              = get_site_url();
				$data['email']            = $current_user->user_email;
				$data['domain']           = get_site_url();
				$data['title']            = get_bloginfo( 'name' );
				$data['platform_version'] = get_bloginfo( 'version' );
				$data['php_version']      = phpversion();
				$data['language']         = get_bloginfo( 'language' );
				$data['charset']          = get_bloginfo( 'charset' );
				$data['user_data']        = $user_data;
				$data['plugin_version']   = SocialWebSuite::get_version();
				$data['plugin_url']       = admin_url( 'admin.php?page=social-web-suite' );
				$data['ajax_url']         = admin_url( 'admin-ajax.php' );
				$data['plugins']          = self::get_all_plugins();
				$data['active_theme']     = self::get_current_theme();

				$tmp_token = md5( wp_generate_password( rand( 32, 64 ), true, true ) );
				SocialWebSuite::set_option( 'optin_secret_key', $tmp_token );
				$data['secret'] = $tmp_token;
				$response       = self::call_optin_api( 'opt-in', $data );

				if ( is_object( $response ) && isset( $response->status ) && 'OK' === $response->status && isset( $response->optin_token ) ) {
					SocialWebSuite::set_option( 'optin_token', $response->optin_token );
				} else {

				}
				SocialWebSuite::set_option( 'skip_optin', true );
			}// End if().
		}// End if().

	}

	/**
	 * Set general settings
	 *
	 * @since  1.0.0
	 * @access public
	 */
	private function general_stuff() {
		// define the locale
		load_plugin_textdomain( self::get_textdomain(), false, self::$plugin_dir . '/languages/' );

		// add custom image size
		add_image_size( 'sws_image', 600, 1200, true );
	}

	public static function get_textdomain() {
		self::create();

		return self::$textdomain;
	}

	public static function redirect( $path = null ) {
		if ( is_null( $path ) ) {
			return;
		}
		wp_redirect( $path );
		exit;

	}

	public static function get_ip() {
		$fields = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $fields as $ip_field ) {
			if ( ! empty( $_SERVER[ $ip_field ] ) ) {
				return $_SERVER[ $ip_field ];
			}
		}

		return null;
	}

	private static function get_all_plugins() {

		self::require_plugin_essentials();

		$all_plugins              = get_plugins();
		$active_plugins_basenames = get_option( 'active_plugins' );

		foreach ( $all_plugins as $basename => & $data ) {
			// By default set to inactive (next foreach update the active plugins).
			$data['is_active'] = false;
			// Enrich with plugin slug.
			$data['slug'] = self::get_plugin_slug( $basename );
		}

		// Flag active plugins.
		foreach ( $active_plugins_basenames as $basename ) {
			if ( isset( $all_plugins[ $basename ] ) ) {
				$all_plugins[ $basename ]['is_active'] = true;
			}
		}

		return $all_plugins;
	}

	private static function get_active_plugins() {
		self::require_plugin_essentials();

		$active_plugin            = array();
		$all_plugins              = get_plugins();
		$active_plugins_basenames = get_option( 'active_plugins' );

		foreach ( $active_plugins_basenames as $plugin_basename ) {
			$active_plugin[ $plugin_basename ] = $all_plugins[ $plugin_basename ];
		}

		return $active_plugin;
	}

	private static function require_plugin_essentials() {
		if ( ! function_exists( 'get_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	private static $_plugins_info;

	private static function get_plugin_slug( $basename ) {
		if ( ! isset( self::$_plugins_info ) ) {
			self::$_plugins_info = get_site_transient( 'update_plugins' );
		}

		$slug = '';

		if ( is_object( self::$_plugins_info ) ) {
			if ( isset( self::$_plugins_info->no_update )
			     && isset( self::$_plugins_info->no_update[ $basename ] )
			     && ! empty( self::$_plugins_info->no_update[ $basename ]->slug )
			) {
				$slug = self::$_plugins_info->no_update[ $basename ]->slug;
			} elseif ( isset( self::$_plugins_info->response )
			           && isset( self::$_plugins_info->response[ $basename ] )
			           && ! empty( self::$_plugins_info->response[ $basename ]->slug )
			) {
				$slug = self::$_plugins_info->response[ $basename ]->slug;
			}
		}

		if ( empty( $slug ) ) {
			// Fallback to plugin's folder name.
			$slug = dirname( $basename );
		}

		return $slug;
	}

	public static function get_current_theme() {
		//collect active theme data
		$theme      = wp_get_theme();
		$theme_data = array();
		if ( $theme ) {
			$theme_data = array(
				'name'      => $theme->get( 'Name' ),
				'version'   => $theme->get( 'Version' ),
				'theme_uri' => $theme->get( 'ThemeURI' ),
			);
		}

		return $theme_data;
	}

	public static function call_optin_api( $url, $data, $method = 'post', $headers = array() ) {
		try {
			$token = self::get_option( 'optin_token' );
			if ( ! empty( $token ) ) {
				$data['token'] = $token;
			}
			$data['url'] = get_site_url();

			if ( 'post' !== $method ) {
				throw new Exception( "Method '$method' not supported yet!" );
			}

			$response = wp_remote_post(
				self::$server_url . 'api/' . $url, array(
					'method'      => 'POST',
					'timeout'     => 600,
					'redirection' => 50,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => $headers,
					'body'        => $data,
					'cookies'     => array(),
				)
			);

			if ( is_wp_error( $response ) ) {
				SocialWebSuite_Log::error( $response->get_error_message() );

				return $response->get_error_message();
			}

			if ( empty( $response['response']['code'] ) || 200 !== $response['response']['code'] ) {
				$msg = 'Error: ' . $response['response']['code'] . ' ' . $response['response']['message'];
				SocialWebSuite_Log::error( $msg );

				return $response;
			}

			$body = json_decode( trim( $response['body'] ) );

			return $body ? $body : trim( $response['body'] );
		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $response->get_error_message() );

			return $e->getMessage();
		}

	}

	public function add_action_links( $links, $file ) {
		if ( plugin_basename( SWS_PLUGIN_PATH ) !== $file ) {
			return $links;
		}

		//		 exit;
		$passed_deactivate = false;
		$deactivate_link   = '';
		$before_deactivate = array();
		$after_deactivate  = array();
		foreach ( $links as $key => $link ) {
			if ( 'deactivate' === $key ) {
				$deactivate_link   = $link;
				$passed_deactivate = true;
				continue;
			}

			if ( ! $passed_deactivate ) {
				$before_deactivate[ $key ] = $link;
			} else {
				$after_deactivate[ $key ] = $link;
			}
		}

		if ( ! empty( $deactivate_link ) ) {
			$deactivate_link .= '<i class="sws-slug" data-slug="social-web-suite"></i>';

			// Append deactivation link.
			$before_deactivate['deactivate'] = $deactivate_link;
		}

		return array_merge( $before_deactivate, $after_deactivate );
	}

	public function add_deactivation_feedback_dialog_box() {
		include dirname( __FILE__ ) . '/admin/templates/deactivation-modal.php';
	}

	public static function send_optin_event( $data ) {
		return self::call_optin_api( 'opt-in/event', $data );
	}

	public function action_share_scheduled( $post ) {
		// we have to save meta here because this hook comes before save_post hook
		//$this->save_post_meta( $post->ID, $post );
		SocialWebSuite_Log::info( 'action_share_scheduled' . json_encode( $post ) );
		SocialWebSuite::auto_share_post( $post, true );
	}

	public function action_delete_wpscheduled( $post_id ) {

		$post_status = get_post_meta( $post_id, 'sws_meta_post_status', true );

		if ( 'future' === $post_status ) {
			$settings = self::get_settings();
			$data     = array(
				'post_id' => $post_id,
			);

			$api_url = 'post-delete/' . $settings->site_id;

			return self::call_api( $api_url, $data );
		}

		return true;
	}

	/**
	 * Register the JavaScript for the public area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_front_scripts() {
		//
	}

	/**
	 * Register the stylesheets for the public area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_front_styles() {
		//
	}

	/**
	 * Prevent clone
	 *
	 * @access private
	 * @since  1.0.0
	 */
	private function __clone() {
	}

	private static function get_custom_message( $next_key, $custom_message_variations_data ) {

		$custom_message = false;
		$variations     = $custom_message_variations_data['variations'];
		for ( $i = $next_key; $i < count( $variations ); $i ++ ) {
			if ( isset( $variations[ $i ] ) ) {
				$share_times = $variations[ $i ]->share_times;
				if ( 0 === $share_times ) {
					$share_count                   = $variations[ $i ]->share_count;
					$custom_message                = $variations[ $i ]->message;
					$last_shared_key               = $i;
					$variations[ $i ]->share_count = $share_count + 1;
					break;
				} else {
					$share_count = $variations[ $i ]->share_count;
					if ( $share_count < $share_times ) {
						$custom_message                = $variations[ $i ]->message;
						$variations[ $i ]->share_count = $share_count + 1;
						$last_shared_key               = $i;
						break;
					}
				}
			}
		}
		if ( $custom_message ) {
			$custom_message_variations_data['variations']      = $variations;
			$custom_message_variations_data['last_shared_key'] = $last_shared_key;

			return array( 'custom_message' => $custom_message, 'variations_data' => $custom_message_variations_data );
		} else {
			//reset from start and find the message that fulfills the criteria
			if ( 0 !== $next_key ) {
				return self::get_custom_message( 0, $custom_message_variations_data );
			}
		}

		return array( 'custom_message' => false, 'variations_data' => $custom_message_variations_data );
	}

	public static function get_social_accounts() {
  
		//$social_accounts = SocialWebSuite::get_option( 'social_accounts', array(), true );
		//$retrieved_social_accounts = SocialWebSuite::get_option( 'retrieved_social_accounts', null, true );
		//if(  0 === count( $social_accounts ) &&  1 !== $retrieved_social_accounts){ //
		$social_accounts = array();
		$settings        = self::get_settings();
		$api_url         = 'soc-media/' . $settings->site_id;

		$response = self::call_api( $api_url, array() );

		if ( isset( $response->status ) && 'OK' === $response->status && isset( $response->social_accounts ) ) {
			$social_accounts = $response->social_accounts;
		}
  
		self::set_option( 'social_accounts', $social_accounts );
		self::set_option( 'retrieved_social_accounts', 1 );
		//}
		return $social_accounts;
	}

	public static function update_excluded_social_accounts_posts( $post_id, $social_accounts_id, $action = 'add' ) {

		//find main post id for post revisions
		$the_post = wp_is_post_revision( $post_id );
		if ( $the_post ) {
			$post_id = $the_post;
		}
		$excluded_social_accounts_posts = self::get_option( 'excluded_social_accounts_posts_ids', array(), true );
		$social_key                     = 'social_account_' . $social_accounts_id;
		$updated                        = false;

		if ( 'add' === $action ) {
			if ( ! isset( $excluded_social_accounts_posts[ $social_key ] ) ) {
				$excluded_social_accounts_posts[ $social_key ] = array( $post_id );
			} else {
				if ( ! in_array( $post_id, $excluded_social_accounts_posts[ $social_key ] ) ) {
					$excluded_social_accounts_posts[ $social_key ][] = $post_id;
				}
			}
			$updated = true;
		} elseif ( 'remove' === $action && isset( $excluded_social_accounts_posts[ $social_key ] ) && ( $key = array_search( $post_id, $excluded_social_accounts_posts[ $social_key ] ) ) !== false ) {
			unset( $excluded_social_accounts_posts[ $social_key ][ $key ] );
			$updated = true;
		}
		if ( true === $updated ) {
			self::set_option( 'excluded_social_accounts_posts_ids', $excluded_social_accounts_posts );
		}
	}

	public static function get_excluded_social_accounts_posts_ids( $prefs ) {

		if ( isset( $prefs->social_account_id ) ) {
			$excluded_social_accounts_posts = self::get_option( 'excluded_social_accounts_posts_ids', array(), true );
			$social_key                     = 'social_account_' . $prefs->social_account_id;
			if ( isset( $excluded_social_accounts_posts[ $social_key ] ) ) {
				return $excluded_social_accounts_posts[ $social_key ];
			}
		}

		return array();
	}

	private static function filter_terms( $list_term_ids ) {

		$terms = array();

		foreach ( $list_term_ids as $term_id ) {
			$term = get_term( $term_id );
			if ( ! is_null( $term ) && is_object( $term ) && isset( $term->taxonomy ) ) { //if we return no term i.e. if it was deleted, don't proceed with inner block
				$terms[] = $term_id;
			}

		}

		return $terms;

	}

	public static function sws_meta_keys() {
		return array(
			'sws_show_misc_options',
			'sws_meta_manual',
			'sws_meta_send_now',
			'sws_meta_repeat',
			'sws_meta_schedule_date',
			'sws_meta_schedule_calendar',
			'sws_meta_schedule_hours',
			'sws_meta_schedule_mins',
			'sws_meta_schedule_ampm',
			'sws_meta_startdate_date',
			'sws_meta_enddate_date',
			'sws_meta_include_image',
			'sws_meta_format',
			'sws_meta_custom_message',
			'sws_meta_use_cutom_messages',
			'sws_meta_custom_message_variations_data',
			'sws_meta_use_hashtags',
			'sws_meta_hashtags',
			'sws_meta_social_accounts_exclude',
			'sws_meta_post_status',
			'sws_meta_times_shared',
			'sws_meta_variations_share_count_reset',
		);
	}

	public static function delete_data() {

		global $wpdb;

		$settings = self::get_settings();
		if ( isset( $settings->site_id ) ) {
			self::deactivate_site();
		}


		self::delete_optin_data();

		self::delete_settings();


		$sws_meta_keys = self::sws_meta_keys();

		$sws_meta_keys_string = implode( ", ", array_fill( 0, count( $sws_meta_keys ), '%s' ) );

		$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->postmeta . " WHERE meta_key IN (" . $sws_meta_keys_string . ")", $sws_meta_keys ) );

	}

	public static function export_data() {

		global $wpdb;

		$sws_meta_keys = self::sws_meta_keys();

		$sws_meta_keys_string = implode( ", ", array_fill( 0, count( $sws_meta_keys ), '%s' ) );
		$results              = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->postmeta . " WHERE meta_key IN (" . $sws_meta_keys_string . ") ORDER BY post_id DESC", $sws_meta_keys ) );

		$files = array();

		$sws_meta_export = self::create_csv( $results, 'social-web-suite-postmeta-export-' . strtotime( 'now' ) . '.csv' );
		$files[]         = $sws_meta_export;

		$sws_settings = self::get_settings();

		$files[] = self::create_json( $sws_settings, 'social-web-suite-settings-export-' . strtotime( 'now' ) . '.json' );

		$optin_data = self::export_optin_data();
		if ( $optin_data ) {
			$files[] = $optin_data;
		}
		//print_r($files);
		//exit;
		$archive_arr = self::archive_files( $files );

		foreach ( $files as $file ) {
			self::remove_file( $file['file_path'] );
		}

		self::force_download( $archive_arr['file_name'], $archive_arr['file_path'], true );

	}

	public static function archive_files( $files ) {

		ini_set( 'output_buffering', 'on' );

		ob_get_clean();

		$uploads_dir   = self::upload_dir();
		$site_url      = str_replace( array( 'http://', 'https://', '.' ), array( '', '', '-' ), get_site_url() );
		$zip_file_name = 'social-web-suite-data-' . $site_url . '-' . strtotime( 'now' ) . '.zip';
		$zip_file_path = $uploads_dir . $zip_file_name;
		// Initialize archive object
		$zip = new ZipArchive();

		$zip->open( $uploads_dir . $zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE );

		foreach ( $files as $file ) {
			// Add current file to archive
			$zip->addFile( $file['file_path'], $file['file_name'] );
		}

		// Zip archive will be created only after closing object
		$zip->close();


		return array( 'file_path' => $zip_file_path, 'file_name' => $zip_file_name );

	}

	public static function create_json( $data, $file_name = '' ) {

		if ( empty( $file_name ) ) {
			$file_name = 'sws-data-export-' . strtotime( 'now' ) . '.json';
		}
		$json_data = json_encode( $data );
		$file_path = self::upload_dir() . $file_name;

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			include_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();

		}

		$wp_filesystem->put_contents( $file_path, $json_data );


		return array( 'file_path' => $file_path, 'file_name' => $file_name );

	}

	public static function create_csv( $list, $file_name = '' ) {

		if ( empty( $file_name ) ) {
			$file_name = 'sws-data-export-' . strtotime( 'now' ) . '.csv';
		}

		$file_path = self::upload_dir() . $file_name;

		$file = fopen( $file_path, "w" );

		foreach ( $list as $line ) {
			fputcsv( $file, (array) $line );
		}

		fclose( $file );

		return array( 'file_path' => $file_path, 'file_name' => $file_name );
	}

	public static function upload_dir() {
		$uploads_dir_array = wp_upload_dir();


		$sws_upload_dir = $uploads_dir_array['basedir'] . '/social-web-suite/export-data/';
		if ( ! file_exists( $sws_upload_dir ) ) {
			wp_mkdir_p( $sws_upload_dir );
		}

		return $sws_upload_dir;
	}

	public static function gdpr_actions() {

		self::handle_data();

	}

	public static function handle_data() {

		$page     = filter_input( INPUT_GET, 'page' );
		$action   = filter_input( INPUT_GET, 'action' );
		$filename = filter_input( INPUT_GET, 'filename' );
		if ( 'social-web-suite' === $page ) {
			if ( ! empty( $action ) ) {
				if ( 'download-export-data' === $action ) {
					if ( ! empty( $filename ) ) {

					} else {
						self::export_data();
					}

				}

				if ( 'delete-data' === $action ) {
					$hash = filter_input( INPUT_GET, 'hash' );

					if ( ! empty( $hash ) ) {
						$saved_hash = self::get_option( 'delete-hash' );
						if ( $hash === $saved_hash ) {
							self::delete_data();
						} else {
							wp_redirect( admin_url( 'admin.php?page=social-web-suite' ), 301 );
						}
					} else {
						$tmp_token = md5( wp_generate_password( rand( 32, 64 ), true, true ) );

						self::set_option( 'delete-hash', $tmp_token );
					}

				} else {
					self::delete_option( 'delete-hash' );
				}


			}
		}


	}

	public static function remove_file( $file ) {
		if ( file_exists( $file ) ) {

			unlink( $file );

		}
	}

	public static function force_download( $filename = '', $filepath, $delete_file = false ) {

		try {
			if ( empty( $filename ) ) {
				return;
			}

			ini_set( 'output_buffering', 'on' );
			if ( ob_get_contents() ) {

				// ob_end_clean();
				ob_clean();
				ob_end_flush(); // more important function - (without - error corrupted zip)


			}

			header( "Expires: 0" );
			header( "Cache-Control: no-cache, no-store, must-revalidate" );
			header( 'Cache-Control: pre-check=0, post-check=0, max-age=0', false );
			header( "Pragma: no-cache" );
			header( 'Content-Type: application/zip;\n' );
			header( "Content-Transfer-Encoding: Binary" );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			//   header("Content-Type: application/force-download");

			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				include_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();

			}


			echo $wp_filesystem->get_contents( $filepath );

			if ( $delete_file === true ) {
				self::remove_file( $filepath );
			}
			exit;
			//wp_die();
		} catch ( Exception $e ) {
			echo $e->getMessage();
			exit;
		}
	}

	public static function export_optin_data() {


		$response = self::call_optin_api( 'opt-in/export', array() );

		if ( isset( $response->status ) && 'OK' === $response->status ) {

			$file_name = 'sws-optin-data-export-' . strtotime( 'now' ) . '.json';


			//$json_data = json_decode( $response->data );
			$json_data = json_encode( $response->data->optin_site->data );
			$file_path = self::upload_dir() . $file_name;

			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				include_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();

			}

			$wp_filesystem->put_contents( $file_path, $json_data );


			return array( 'file_path' => $file_path, 'file_name' => $file_name );
		}

		return false;


	}

	public static function delete_optin_data() {


		$response = self::call_optin_api( 'opt-in/delete', array() );

		if ( isset( $response->status ) && 'OK' === $response->status ) {
			return true;
		}

		return false;


	}

	public static function ping_server() {

		$settings = self::get_settings();
		$action   = filter_input( INPUT_GET, 'action' );
		if ( isset( $settings->site_id ) && $action === 'refresh-connection' ) {

			$api_url = 'check-connection/' . $settings->site_id;

			$data     = array();
			$response = self::call_api( $api_url, $data );

			if ( isset( $response->status ) && $response->status === 'OK' && isset( $response->msg ) ) {
				if ( isset( $response->subscription_expired ) ) {

					$message = $response->msg;
					self::set_option( 'subscription_expired', true );
					self::set_option( 'subscription_expired_message', $message );

				} else {

					$message = isset( $response->msg ) ? $response->msg : '';
					self::delete_option( 'subscription_expired' );
					self::delete_option( 'subscription_expired_message' );
					self::set_option( 'share_error_message', $message );

				}
				?>
                <br><br>
                <div class="notice notice-success is-dismissible" style="margin-top: 50px;">
                    <p><?php _e( 'Your Site has been reconnected!', 'sample-text-domain' ); ?></p>
                </div>
				<?php
			}


		}

	}

	public static function send_post_to_stack( $post ) {

		$settings = self::get_settings();
		$data     = array(
			'content' => wp_json_encode( self::get_post_data( $post ) ),
		);

		$api_url  = 'post-created/' . $settings->site_id;
		$response = self::call_api( $api_url, $data );

		if ( is_object( $response ) && isset( $response->status ) && $response->status === 'Error' ) {
			return false;
		}

		return true;
	}

	public static function is_already_shared( $post_id ) {

		$sws_already_shared           = filter_input( INPUT_POST, 'sws_already_shared' );
		$sws_already_shared_meta      = get_post_meta( $post_id, 'sws_already_shared', true );
		$sws_already_shared_meta_time = get_post_meta( $post_id, 'sws_already_shared_time', true );

		$sws_already_shared_expiration = strtotime( 'now - 1 minute' );


		if ( $sws_already_shared_meta_time < $sws_already_shared_expiration ) {

			self::delete_already_shared( $post_id );
		} else {
			$_POST['sws_already_shared'] = true;
		}


		if ( ! empty( $sws_already_shared ) ) {
			SocialWebSuite_Log::info( 'is_already_shared from _post request ' . $sws_already_shared . ' meta ' . $sws_already_shared_meta );

			return true;
		}

		if ( $sws_already_shared_meta == true ) {
			SocialWebSuite_Log::info( 'is_already_shared from meta ' . $sws_already_shared . ' meta ' . $sws_already_shared_meta );

			return true;
		}
		SocialWebSuite_Log::info( 'is_already_shared not ' . $sws_already_shared . ' meta ' . $sws_already_shared_meta );

		return false;

		//return ( ! empty( $sws_already_shared ) || $sws_already_shared_meta === true );
	}

	public static function set_already_shared( $post_id ) {
		SocialWebSuite_Log::info( 'set_already_shared ' . $post_id );
		$_POST['sws_already_shared'] = true;

		update_post_meta( $post_id, 'sws_already_shared', true );
		update_post_meta( $post_id, 'sws_already_shared_time', strtotime( 'now' ) );

	}

	public static function delete_already_shared( $post_id ) {

		delete_post_meta( $post_id, 'sws_already_shared' );

	}


	public static function check_sharing_active_status() {


		$settings = self::get_settings();
		$api_url  = 'sharing-active-status/' . $settings->site_id;

		$response = self::call_api( $api_url, array() );
  
		if ( isset( $response->status ) && 'OK' === $response->status && isset( $response->sharing_active ) ) {
			if ( isset( $response->subscription_expired ) ) {

				$message = $response->msg;
				self::set_option( 'subscription_expired', true );
				self::set_option( 'subscription_expired_message', $message );

			} else {

				$message = isset( $response->msg ) ? $response->msg : '';
				self::delete_option( 'subscription_expired' );
				self::delete_option( 'subscription_expired_message' );
				self::set_option( 'share_error_message', $message );

			}

			return (bool) $response->sharing_active;
		}

		return true;
	}


	public static function show_admin_sharing_notice( $post, $sidebar = true ) {

		$manual_paused       = false; //SocialWebSuite::get_option('paused_manual_posting');
		$manual_settting     = get_post_meta( $post->ID, 'sws_meta_manual', true );
		$sharing_active      = SocialWebSuite::check_sharing_active_status();
		$post_type           = $post->post_type;
		$post_type_object    = get_post_type_object( $post_type );
		$can_publish         = current_user_can( $post_type_object->cap->publish_posts );
		$hide_publish_notice = false;
		if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ), true ) || ! $post->ID ) {

			if ( ! $can_publish || ! SocialWebSuite::get_option( 'share_on_publish' ) ) {
				$hide_publish_notice = true;
			}

			$action = 'Publish';

		} else {

			if ( 'publish' !== $post->post_status || ! SocialWebSuite::get_option( 'share_on_update' ) ) {
				$hide_publish_notice = true;
			}

			$action = 'Update';
		}

		include dirname( __FILE__ ) . '/admin/templates/admin-sharing-notice.php';

		if ( ! $post_type_object->public ) {
			include dirname( __FILE__ ) . '/admin/templates/admin-sharing-notice-not-public.php';
		}
	}

} // end class SocialWebSuite

// EOF
