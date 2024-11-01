<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link   https://hypestudio.org/
 * @since  1.0.0
 * @author HYPEStudio <info@hypestudio.org>
 */
class SocialWebSuite_Admin {


	private $ui;

	public function __construct() {
		SocialWebSuite::load_required( 'includes/admin/class-socialwebsuite-admin-ui.php' );
		$this->ui      = new SocialWebSuite_Admin_UI;
		$this->is_rest = strpos( $_SERVER['REQUEST_URI'], 'wp/v2/posts/' );
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// set menus (happens before admin_init!)
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );


		// add api handlers
		$this->register_ajax_apis();

		// init other admin stuff
		add_action( 'init', array( $this, 'public_init' ), 10 );
		add_action( 'admin_init', array( $this, 'admin_init' ), 10 );


	}

	/**
	 * Set ajax calls used for our API
	 *
	 * @since  1.0.0
	 * @access public
	 */
	private function register_ajax_apis() {
		// activation ajax call
		add_action( 'wp_ajax_nopriv_sws_activate', array( $this, 'ajax_activate_plugin' ) );

		// called by web-hook to reload settings
		add_action( 'wp_ajax_nopriv_sws_refresh_settings', array( $this, 'ajax_refresh_settings' ) );

		// get the list of categories
		add_action( 'wp_ajax_nopriv_sws_list_categories', array( $this, 'ajax_list_categories' ) );

		// get the list of posts
		add_action( 'wp_ajax_nopriv_sws_list_posts', array( $this, 'ajax_list_posts' ) );

		// get the list of post types
		add_action( 'wp_ajax_nopriv_sws_list_post_types', array( $this, 'ajax_list_post_types' ) );

		// get next post for auto-publishing
		add_action( 'wp_ajax_nopriv_sws_get_content', array( $this, 'ajax_get_content' ) );

		// get single post content
		add_action( 'wp_ajax_nopriv_sws_get_single_post', array( $this, 'ajax_get_single_post' ) );

		// get single post content
		add_action( 'wp_ajax_nopriv_sws_get_post_image', array( $this, 'ajax_get_post_image' ) );

		// de-activate plugin when site is deleted on the server
		add_action( 'wp_ajax_nopriv_sws_unlink', array( $this, 'ajax_unlink_site' ) );

		// check confirmation for url for WordPress site
		add_action( 'wp_ajax_nopriv_sws_ping', array( $this, 'ajax_ping_site' ) );

		// submit reason for uninstalling plugin
		add_action( 'wp_ajax_sws_submit_uninstall_reason', array( $this, 'ajax_submit_uninstall_reason' ) );

		// admin notice rate action
		add_action( 'wp_ajax_sws_notice_rate', array( $this, 'ajax_notice_rate' ) );

	}


	/*** Action handlers ***/

	public function admin_menu() {
		add_menu_page(
			esc_html__( SocialWebSuite::get_plugin_name(), 'social-web-suite' ),
			esc_html__( 'Social Web Suite', 'social-web-suite' ),
			'manage_options', 'social-web-suite',
			array(
				$this->ui,
				'main_page',
			), 'dashicons-megaphone'
		);

	}

	/**
	 * Called on init hook
	 */
	public function public_init() {
		//$gutenberg = function_exists( 'gutenberg_can_edit_post_type' );

		// SocialWebSuite_Log::info('Request URI' . $_SERVER['REQUEST_URI']);
		if ( false !== $this->is_rest ) { //only for gutenberg workaround
			//
			//classic-editor-replace
			$settings = SocialWebSuite::get_settings();
			SocialWebSuite_Log::info( 'Gutenberg is present' );
			if ( ! empty( $settings->activated ) ) {

				$this->register_share_actions( $settings );
			}
		}

	}

	/**
	 * Called on admin_init hook
	 */
	public function admin_init() {


		SocialWebSuite_Log::log_actions();
		SocialWebSuite::gdpr_actions();


		// plugin links
		add_action( 'plugin_action_links_' . plugin_basename( SocialWebSuite::get_plugin_path() ), array(
			$this,
			'add_plugin_links'
		) );

		// enqueue css & js
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		$settings = SocialWebSuite::get_settings();

		if ( ! empty( $settings->activated ) ) { // if plugin active

			// add options to editor
			add_action( 'post_submitbox_misc_actions', array( $this->ui, 'post_submitbox_misc_actions' ) );
			add_action( 'add_meta_boxes', array( $this, 'post_meta_boxes_setup' ) );

			add_action( 'save_post', array( $this, 'save_post_meta' ), 10, 2 );

			// add auto-post sharing actions
			if ( $this->is_rest === false ) {
				$this->register_share_actions( $settings );
			}
		} else {

			/* disabled after requests from some users */
			//add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			//add_action( 'admin_notices', array( $this, 'admin_plugin_rate_notice' ) );
		}
	}

	private function register_share_actions( $settings ) {
		//if( function_exists( 'is_gutenberg_page' ) )

		//add_action( "rest_insert_post",  array($this, 'action_share_rest_post'), 10, 3); // $post, $request, false );


		if ( ! empty( $settings->share_on_publish ) ) {
			//add_action( 'transition_post_status', array($this, 'action_transitions'), 10, 3 );
			add_action( 'pending_to_publish', array( $this, 'action_share_on_publish', 20, 1 ) );
			add_action( 'new_to_publish', array( $this, 'action_share_on_publish' ), 20, 1 );
			add_action( 'draft_to_publish', array( $this, 'action_share_on_publish' ), 20, 1 );
			add_action( 'auto-draft_to_publish', array( $this, 'action_share_on_publish' ), 20, 1 );
		}

		if ( ! empty( $settings->share_on_update ) ) {
			//add_action( 'edit_post', array( $this, 'action_share_on_update' ), 10 );
			add_action( 'transition_post_status', array( $this, 'pre_action_share_on_update' ), 10, 3 );
		}

		add_action( 'save_post', array( $this, 'action_save_post' ), 20, 2 ); // priority after save_post_meta()


		add_action( 'pending_to_publish', array( $this, 'action_send_post_to_stack' ), 11, 1 );
		add_action( 'new_to_publish', array( $this, 'action_send_post_to_stack' ), 11, 1 );
		add_action( 'draft_to_publish', array( $this, 'action_send_post_to_stack' ), 11, 1 );
		add_action( 'auto-draft_to_publish', array( $this, 'action_send_post_to_stack' ), 11, 1 );

	}

	public function action_share_rest_post( $post, $request, $sit ) {
		SocialWebSuite_Log::info( 'insert $post' . json_encode( $post ) . "\n" . '$request' . json_encode( $request ) );
	}

	public function action_transitions( $new_status, $old_status, $post ) {
		$transition = $old_status . '_to_' . $new_status;

		$sws_transitions = array( 'pending_to_publish', 'new_to_publish', 'draft_to_publish', 'auto-draft_to_publish' );

		if ( in_array( $transition, $sws_transitions ) ) {
			SocialWebSuite_Log::info( 'From ' . $old_status . ' to ' . $new_status . ' for post ' . $post->ID );
			$this->action_share_on_publish( $post );
		}


	}

	/**
	 * Add metaboxes in editor
	 *
	 * @since 1.0.0
	 */
	public function post_meta_boxes_setup() {

		$include_non_public = SocialWebSuite::get_option( 'include_non_public_post_types' );

		if ( $include_non_public == true ) {
			$args = array(
				//	'public'   => false,
				'_builtin' => false,
			);
		} else {
			$args = array(
				'public'   => true,
				'_builtin' => false,
			);
		}
		$custom_posts = get_post_types(
			$args
		);

		$excluded_post_types = SocialWebSuite::get_option( 'excluded_post_types', array(), true );


		if ( count( $excluded_post_types ) > 0 ) {
			$custom_posts = array_diff( $custom_posts, $excluded_post_types );
		}

		$post_types = array_merge( array( 'post', 'page' ), (array) $custom_posts );

		// add meta box for each post type
		add_meta_box(
			'sws-post-meta', // unique ID
			/* translators: %s is replaced with "string" */
			sprintf( esc_html__( 'Sharing Settings - %s', 'social-web-suite' ), esc_html__( SocialWebSuite::get_plugin_name(), 'social-web-suite' ) ), // title
			array( $this->ui, 'post_meta_box' ), // callback function
			$post_types,    // all post types
			'normal',       // where on screen to add it
			'high'          // priority
		);
	}

	/**
	 * Action links on index page for plugins
	 *
	 * @param array $links
	 *
	 * @return  array
	 * @since 1.0.0
	 *
	 */
	public function add_plugin_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=social-web-suite' ) ), esc_html__( 'Settings', 'social-web-suite' ) );
		$links[] = sprintf( '<a href="//socialwebsuite.com/" target="_blank">%s</a>', esc_html__( 'Website', 'social-web-suite' ) );

		return $links;
	}

	/**
	 * Register styles and scripts for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts( $hook ) {

		$url     = SocialWebSuite::get_plugin_url();
		$version = SocialWebSuite::get_version();

		// css
		wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'sws_admin-style', $url . 'css/sws-admin.css', array( 'jquery-ui-style' ), $version, 'all' );
		wp_enqueue_style( 'sws_admin-forms-style', $url . 'css/forms.css', array(), $version, 'all' );
		//wp_enqueue_style( 'sws_admin-bootstrap', $url . 'css/bootstrap.min.css', array(), $version, 'all' );

		// js
		wp_enqueue_script( 'sws-admin', $url . 'js/sws-admin.js', array(
			'jquery',
			'jquery-ui-datepicker'
		), $version, false );
	}

	/**
	 * Activate the plugin on the main server
	 *
	 * @since 1.0.0
	 */
	public function ajax_activate_plugin() {

		try {
			// validate call
			$hash      = filter_input( INPUT_POST, 'hash' );
			$timestamp = filter_input( INPUT_POST, 'timestamp' );

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );

			}

			// get API token from the server
			$url = SocialWebSuite::get_server_url() . 'wp/activate';

			$data = array(
				'tmp_key' => filter_input( INPUT_POST, 'tmp_key' ),
				'user_id' => filter_input( INPUT_POST, 'user_id' ),
			);

			//if ( defined( 'SWS_BASIC_AUTH_USER' ) && defined( 'SWS_BASIC_AUTH_PASS' ) ) {
			//	$basic_auth = base64_encode( SWS_BASIC_AUTH_USER . ':' . SWS_BASIC_AUTH_PASS );// WPCS: XSS ok, sanitization ok, CSRF ok, override ok, loose comparison.
			//	$headers = array(
			//	'Authorization' => 'Basic ' . $basic_auth,
			//	);

			//} else {
			$headers = array();
			//}

			$response = wp_remote_post(
				$url, array(
					'method'      => 'POST',
					'timeout'     => 120,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => $headers,
					'body'        => $data,
					'cookies'     => array(),
					//'sslverify'   => false, //only for debugging
				)
			);

			if ( is_wp_error( $response ) ) {
				SocialWebSuite_Log::error( $response->get_error_message() );
				throw new Exception( $response->get_error_message() );

			} else {

				$data = json_decode( $response['body'] );

				if ( empty( $data->settings ) || empty( $data->token ) ) {
					$msg = 'Invalid server response: ' . $response['body'];
					SocialWebSuite_Log::error( $msg );
					throw new Exception( $msg );
				}

				// add settings
				SocialWebSuite::merge_settings( $data->settings );

				// activate plugin
				SocialWebSuite::set_option( 'api_token', $data->token );
				SocialWebSuite::set_option( 'site_id', (int) filter_input( INPUT_POST, 'site_id' ) );
				SocialWebSuite::set_option( 'activated', true );

				$reply = array(
					'status' => 'OK',
				);
			}
		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}// End try().

		echo wp_json_encode( $reply );
		exit;
	}

	public function ajax_refresh_settings() {
		try {
			// validate call
			$hash      = filter_input( INPUT_POST, 'hash' );
			$timestamp = filter_input( INPUT_POST, 'timestamp' );

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			$settings = json_decode( filter_input( INPUT_POST, 'settings' ) );

			if ( ! $settings ) {
				SocialWebSuite_Log::error( 'Invalid settings data' );
				throw new Exception( 'Invalid settings data' );
			}

			SocialWebSuite::merge_settings( $settings );

			$reply = array(
				'status' => 'OK',
			);

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}

		echo wp_json_encode( $reply );
		exit;
	}

	/**
	 * retrieves posts from database and outputs them in json format
	 *
	 * @return void
	 */
	public function ajax_list_posts() {

		global $wpdb;

		try {
			// validate call
			$hash              = filter_input( INPUT_GET, 'hash' );
			$timestamp         = filter_input( INPUT_GET, 'timestamp' );
			$search            = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_STRING );
			$category          = filter_input( INPUT_GET, 'category', FILTER_SANITIZE_STRING );
			$exclude_posts     = filter_input( INPUT_GET, 'exclude_posts', FILTER_SANITIZE_STRING );
			$ignore_exclusions = filter_input( INPUT_GET, 'ignore_exclusions', FILTER_SANITIZE_STRING );
			$limit             = (int) filter_input( INPUT_GET, 'limit' );
			$offset            = filter_input( INPUT_GET, 'offset' );
			$prefs             = (object) $_GET; // WPCS: CSRF ok.
			if ( $limit < 50 || empty( $limit ) ) {
				$limit = 1000;
			} elseif ( $limit > 500 || empty( $limit ) ) {
				$limit = 1000;
			}
			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			$posts = array();

			// posts already picked
			//$used_posts_ids = SocialWebSuite::get_option( 'used_posts_ids', array(), true );
			$exclude_categories = array();
			$include_categories = array();

			//if( ! empty( $ignore_exclusions ) ){
			//excluded categories
			//$exclude_categories = SocialWebSuite::get_option( 'exclude_categories', array(), true );
			//included categories
			//$include_categories = SocialWebSuite::get_option( 'include_categories', array(), true );
			//}

			if ( ! empty( $category ) ) {

				$include_categories   = array();
				$include_categories[] = $category;
			}

			$excluded_post_types = SocialWebSuite::get_option( 'excluded_post_types', array(), true );

			$share_post_types = SocialWebSuite::get_option( 'share_types', '', true );

			$posts_not_in = '';
			//$used_posts_ids_string = implode( ',' , $used_posts_ids );
			//$posts_not_in = ( !empty($used_posts_ids_string) ) ? 'posts.ID NOT IN (' . $used_posts_ids_string . ') AND ' : '';
			$exclude_posts_list = array();
			if ( ! empty( $exclude_posts ) ) {
				$sql_exclude  = 'SELECT posts.ID, posts.post_title, posts.post_type FROM ' . $wpdb->posts . ' AS posts WHERE posts.ID IN (' . $exclude_posts . ')';
				$posts_not_in = 'posts.ID NOT IN (' . $exclude_posts . ') AND ';

				$results_exclude = $wpdb->get_results( $sql_exclude ); // WPCS: unprepared SQL OK.

				if ( $results_exclude && count( $results_exclude ) > 0 ) {
					$exclude_posts_list = $results_exclude;
				}
			}
			$exclude_categories_string = implode( ',', $exclude_categories );
			$include_categories_string = implode( ',', $include_categories );

			if ( 'pages' === $share_post_types ) {
				$custom_types = array();
			} else {

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
			}

			if ( ! $custom_types ) {
				$custom_types = array();
			} else {
				if ( count( $excluded_post_types ) > 0 ) {
					$custom_types = array_diff( $custom_types, $excluded_post_types );
				}
			}
			if ( 'posts' === $share_post_types ) {
				$all_post_types = array_merge( array( 'post' ), $custom_types );
			} elseif ( 'both' === $share_post_types ) {
				$all_post_types = array_merge( array( 'page', 'post' ), $custom_types );
			} else {
				$all_post_types = array_merge( array( 'page' ), $custom_types );
			}

			$post_types = "'" . implode( "','", $all_post_types ) . "'";

			$terms_sql           = '';
			$terms_sql_condition = '';

			if ( ( ! empty( $exclude_categories_string ) || ! empty( $include_categories_string ) ) && ! empty( $ignore_exclusions ) ) {

				if ( ! empty( $include_categories_string ) ) {
					$terms_sql           = 'INNER JOIN ' . $wpdb->term_relationships . ' AS terms ON posts.ID = terms.object_id';
					$terms_sql_condition = ' AND terms.term_taxonomy_id IN (SELECT term_taxonomy_id FROM ' . $wpdb->term_taxonomy . ' WHERE term_id IN (' . $include_categories_string . ')) ';
				}
				if ( ! empty( $exclude_categories_string ) ) {
					$terms_sql           = 'LEFT JOIN ' . $wpdb->term_relationships . ' AS terms ON posts.ID = terms.object_id';
					$terms_sql_condition = ' AND (terms.term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM ' . $wpdb->term_taxonomy . ' WHERE term_id IN (' . $exclude_categories_string . ')) OR terms.object_id IS NULL) ';
				}
			}

			$q = 'SELECT %s
                  FROM ' . $wpdb->posts . ' AS posts ' . $terms_sql . ' 
                  WHERE ' . $posts_not_in . '
                    posts.`post_type` IN (' . $post_types . ") AND
				    posts.`post_status` = 'publish'";

			//build the date query part if needed
			$date_query = $this->sws_get_date_query( $prefs );

			if ( $date_query !== false ) {
				$q .= ' ' . str_replace( $wpdb->posts, 'posts', $date_query ) . ' ';
			}
			$q             .= $terms_sql_condition;
			$sql_count     = sprintf( $q, 'count(*) as total' ); // select count
			$results_count = $wpdb->get_row( $sql_count ); // WPCS: unprepared SQL OK.

			$posts_with_data = array();
			if ( null !== $results_count ) {
				$sql = sprintf( $q, 'DISTINCT posts.ID, posts.post_title, posts.post_type, posts.post_excerpt, posts.post_content' ); // select posts

				if ( ! empty( $search ) ) {
					$sql .= " AND posts.post_title LIKE '%" . $search . "%'";
				}

				$sql .= ' LIMIT ' . $offset . ',' . $limit;

				$results = $wpdb->get_results( $sql ); // WPCS: unprepared SQL OK.

				if ( $results && count( $results ) > 0 ) {
					$posts = $results;
					foreach ( $posts as $post ) {
						$posts_with_data[] = SocialWebSuite::get_post_data( $post );
					}
				}
			}


			$reply = array(
				'status'        => 'OK',
				'data'          => $posts,
				'posts'         => $posts_with_data,
				'excluded_data' => $exclude_posts_list,
				'total'         => is_null( $results_count ) ? 0 : $results_count->total,
			);

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}// End try().

		echo wp_json_encode( $reply );
		exit;
	}

	public function ajax_list_posts2() {

		global $wpdb;

		try {
			// validate call
			$hash              = filter_input( INPUT_GET, 'hash' );
			$timestamp         = filter_input( INPUT_GET, 'timestamp' );
			$search            = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_STRING );
			$category          = filter_input( INPUT_GET, 'category', FILTER_SANITIZE_STRING );
			$exclude_posts     = filter_input( INPUT_GET, 'exclude_posts', FILTER_SANITIZE_STRING );
			$ignore_exclusions = filter_input( INPUT_GET, 'ignore_exclusions', FILTER_SANITIZE_STRING );
			$limit             = (int) filter_input( INPUT_GET, 'limit' );
			$offset            = filter_input( INPUT_GET, 'offset' );
			$prefs             = (object) $_GET; // WPCS: CSRF ok.

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			$reply = wp_count_posts();

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}// End try().

		echo wp_json_encode( $reply );
		exit;
	}

	/**
	 * retrieves post types from database and outputs them in json format
	 *
	 * @return void
	 */
	public function ajax_list_post_types() {
		try {
			// validate call
			$hash      = sanitize_text_field( $_GET['hash'] );
			$timestamp = sanitize_text_field( $_GET['timestamp'] );

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}
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


			$output = 'objects'; // names or objects

			$all_post_types = array(
				//	array( 'name' => 'page', 'label' => 'Pages' ),
				//	array( 'name' => 'post', 'label' => 'Posts' ),
			);

			$custom_types = get_post_types( $args2, $output );

			if ( $custom_types ) {
				foreach ( $custom_types as $custom_type ) {
					$all_post_types[] = array(
						'name'  => $custom_type->name,
						'label' => $custom_type->label,
					);
				}
			}

			$reply = array(
				'status' => 'OK',
				'data'   => $all_post_types,
			);

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}// End try().

		echo wp_json_encode( $reply );
		exit;

	}

	public function ajax_list_categories() {
		try {
			// validate call
			$hash      = sanitize_text_field( $_GET['hash'] );
			$timestamp = sanitize_text_field( $_GET['timestamp'] );

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			$cats = array();

			// get all taxonomies, including custom and private ones

			$taxonomies = get_taxonomies(
				array(
					'public' => true,
				), 'objects'
			);

			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxonomy ) {

					// skip some builtin taxonomies (but show categories)
					if ( 'post_tag' === $taxonomy->name || 'post_format' === $taxonomy->name || ! $taxonomy->hierarchical ) {
						continue;
					}

					$terms = get_terms(
						$taxonomy->name, array(
							'hide_empty' => false,
						)
					);

					foreach ( $terms as $term ) {
						$cats[] = array(
							'id'          => $term->term_id,
							'term_id'     => $term->term_id,
							'name'        => $term->name,
							'slug'        => $term->slug,
							'group'       => $term->term_group,
							'taxonomy_id' => $term->term_taxonomy_id,
							'description' => $term->description,
							'parent'      => $term->parent,
							'count'       => $term->count,
						);
					}
				}
			}

			$reply = array(
				'status' => 'OK',
				'data'   => $cats,
			);

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}// End try().

		echo wp_json_encode( $reply );
		exit;
	}

	public function ajax_get_content() {

		try {
			// validate call
			$hash      = filter_input( INPUT_GET, 'hash' );
			$timestamp = filter_input( INPUT_GET, 'timestamp' );

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			// get posts
			$prefs   = (object) $_GET; // WPCS: CSRF ok.
			$content = SocialWebSuite::pick_posts( $prefs );

			$used_posts_ids = SocialWebSuite::get_option( 'used_posts_ids', array(), true );

			foreach ( $content as $post ) {
				// update counter
				$times_shared = get_post_meta( $post->id, 'sws_meta_times_shared', true );
				update_post_meta( $post->id, 'sws_meta_times_shared', $times_shared + 1 );

				// remember it was shared
				$used_posts_ids[] = $post->id;
			}

			// save the list
			SocialWebSuite::set_option( 'used_posts_ids', $used_posts_ids );

			// we're done
			$reply = array(
				'status' => 'OK',
				'data'   => $content,
			);

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}// End try().

		echo wp_json_encode( $reply );
		exit;
	}

	public function ajax_get_single_post() {

		try {
			// validate call
			$hash      = filter_input( INPUT_GET, 'hash' );
			$timestamp = filter_input( INPUT_GET, 'timestamp' );

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			// get post
			$post_id = filter_input( INPUT_GET, 'id' );
			$post    = get_post( $post_id );
			$data    = SocialWebSuite::get_post_data( $post );


			// we're done
			$reply = array(
				'status' => 'OK',
				'data'   => $data,
			);

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}// End try().

		echo wp_json_encode( $reply );
		exit;
	}

	public function ajax_get_post_image() {

		try {
			// validate call
			$hash      = filter_input( INPUT_GET, 'hash' );
			$timestamp = filter_input( INPUT_GET, 'timestamp' );

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			// get posts
			$post_id = filter_input( INPUT_GET, 'id' );
			$post    = get_post( $post_id );
			$data    = array();
			//$post_data = SocialWebSuite::get_post_data( $post );
			$thumb_id = get_post_thumbnail_id( $post->ID );

			if ( $thumb_id ) {
				$featured_image = wp_get_attachment_image_src( $thumb_id, 'full' );

				if ( $featured_image ) {
					$data['image_url']    = $featured_image[0];
					$data['image_width']  = $featured_image[1];
					$data['image_height'] = $featured_image[2];
				}
			}

			// we're done
			$reply = array(
				'status' => 'OK',
				'data'   => $data,
			);


		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}// End try().

		echo wp_json_encode( $reply );
		exit;
	}

	public function ajax_unlink_site() {

		try {
			// validate call
			$hash      = filter_input( INPUT_GET, 'hash' );
			$timestamp = filter_input( INPUT_GET, 'timestamp' );

			if ( ! SocialWebSuite::verify( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			SocialWebSuite::set_option( 'activated', false );

			$reply = array(
				'status' => 'OK',
			);

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}

		echo wp_json_encode( $reply );
		exit;
	}

	public function ajax_ping_site() {
		try {
			// validate call
			$hash      = filter_input( INPUT_POST, 'hash' );
			$timestamp = filter_input( INPUT_POST, 'timestamp' );

			if ( ! SocialWebSuite::verify_optin( $hash, $timestamp ) ) {
				SocialWebSuite_Log::error( 'Invalid Call' );
				throw new Exception( 'Invalid Call' );
			}

			//			SocialWebSuite::set_option( 'activated', false );

			$reply = array(
				'status' => 'OK',
			);

		} catch ( Exception $e ) {
			SocialWebSuite_Log::error( $e->getMessage() );
			$reply = array(
				'status' => 'Error',
				'msg'    => $e->getMessage(),
			);
		}

		echo wp_json_encode( $reply );
		exit;
	}

	public function ajax_submit_uninstall_reason() {
		$reason_id   = filter_input( INPUT_POST, 'reason_id' );
		$reason_info = filter_input( INPUT_POST, 'reason_info' );

		$data = array(
			'event_type'                 => 'plugin_deactivated',
			'event_description'          => 'Plugin Has Been Uninstalled',
			'deactivation_reason'        => $reason_id,
			'deactivation_reason_custom' => $reason_info,
		);

		$data['ajax_url'] = admin_url( 'admin-ajax.php' );
		$tmp_token        = md5( wp_generate_password( rand( 32, 64 ), true, true ) );
		SocialWebSuite::set_option( 'optin_secret_key', $tmp_token );
		$data['secret'] = $tmp_token;

		$response = SocialWebSuite::call_optin_api( 'opt-in/event', $data );

		if ( is_object( $response ) && 'OK' === $response->status ) {
			//SocialWebSuite::delete_option( 'optin_token', $response->optin_token );
			SocialWebSuite::set_option( 'optin_plugin_status', 'plugin_deactivated' );
		}
	}

	public function action_share_on_publish( $post ) {
		// we have to save meta here because this hook comes before save_post hook


		if ( $this->is_rest === false ) {
			SocialWebSuite_Log::info( 'sharing post' . json_encode( $post ) );
			$this->save_post_meta( $post->ID, $post, ' called from action_share_on_publish ' );
			SocialWebSuite::auto_share_post( $post );
		} else {
			SocialWebSuite_Log::info( 'sharing post gutenberg' . json_encode( $post ) );
			update_post_meta( $post->ID, 'sws_gutenberg_share_on_publish', true );
		}


	}


	/**
	 * Save meta fields when post is saved
	 *
	 * @param integer $post_id
	 * @param WP_Post $post
	 *
	 * @since 1.0.0
	 *
	 */
	public function save_post_meta( $post_id, $post, $from_action_share = false ) {


		if ( $from_action_share !== false ) {
			SocialWebSuite_Log::info( 'save_post_meta - ' . $from_action_share . ' - ' . json_encode( $_POST ) );
		} else {
			SocialWebSuite_Log::info( 'save_post_meta - from action - ' . json_encode( $_POST ) );
		}

		$sws_post_nonce = filter_input( INPUT_POST, 'sws_post_nonce' );
		if ( empty( $sws_post_nonce ) || ! wp_verify_nonce( $sws_post_nonce, 'save-post-meta' ) ) {
			return;
		}

		// Stop WP from clearing custom fields on autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Prevent quick edit from clearing custom fields
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// check  permissions
		$post_type = get_post_type_object( $post->post_type );

		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, 'sws_meta_post_status', $post->post_status );

		$fields = array(
			'sws_meta_manual',            // [default, skip, custom]
			'sws_meta_send_now',            // [now, schedule]
			'sws_meta_schedule_calendar',    // date as shown in the calendar
			'sws_meta_schedule_date',        // YYYY-mm-dd date
			'sws_meta_schedule_hours',        // 0 - 12
			'sws_meta_schedule_mins',        // 0 - 60
			'sws_meta_schedule_ampm',        // [am, pm]
			'sws_meta_include_image',        // [default, skip, include]
			'sws_meta_use_hashtags',        // [default, none, cats, tags, custom]
			'sws_meta_hashtags',            // string
			'sws_meta_format',                // string
			'sws_meta_custom_message',           //string
			'sws_meta_custom_message_variations_data',           //string
			'sws_meta_startdate_date',          //timestamp
			'sws_meta_enddate_date',          //timestamp
			'sws_meta_use_cutom_messages', //boolean
			'sws_meta_social_accounts_exclude', //array

			// 'sws_show_misc_options',          //boolean
		);

		$meta_manual = sanitize_text_field( $_POST['sws_meta_manual'] );

		if ( 'notevergreen' !== $meta_manual ) {
			$_POST['sws_meta_startdate_date'] = '';
			$_POST['sws_meta_enddate_date']   = '';
		} else {
			$dates = $this->convert_nonevergreen_dates( $_POST );

			$_POST['sws_meta_startdate_date'] = $dates['startdate'];
			$_POST['sws_meta_enddate_date']   = $dates['enddate'];
			if ( empty( $dates['startdate'] ) && empty( $dates['enddate'] ) ) {
				$_POST['sws_meta_manual'] = 'default';
			}
		}

		foreach ( $fields as $field ) {
			if ( 'sws_meta_custom_message_variations_data' !== $field && 'sws_meta_social_accounts_exclude' !== $field ) {
				if ( 'sws_meta_format' === $field ) {
					$new = isset( $_POST[ $field ] ) ? implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST[ $field ] ) ) ) : ''; //preserve line breaks for format
				} else {
					$new = isset( $_POST[ $field ] ) ? sanitize_text_field( $_POST[ $field ] ) : '';
				}

			}

			$old = get_post_meta( $post_id, $field, true );

			if ( 'sws_meta_custom_message_variations_data' === $field ) {

				$custom_message_variations_data = (array) json_decode( get_post_meta( $post->ID, 'sws_meta_custom_message_variations_data', true ) );

				$last_shared_variation                             = isset( $custom_message_variations_data['last_shared_key'] ) ? $custom_message_variations_data['last_shared_key'] : - 1;
				$custom_message_variations_data['last_shared_key'] = - 1;
				$variations_old                                    = ( isset( $custom_message_variations_data['variations'] ) ) ? $custom_message_variations_data['variations'] : array();


				$variations                   = filter_input( INPUT_POST, 'sws_meta_custom_message_variations', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				$variations_share_times       = filter_input( INPUT_POST, 'sws_meta_variations_share_times', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				$variations_share_count_reset = filter_input( INPUT_POST, 'sws_meta_variations_share_count_reset', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );


				$variations_new            = array();
				$last_shared_key_reindexed = false;

				foreach ( $variations as $v_key => $variation ) {
					$variation = trim( $variation );
					//echo 'variation key ' . $v_key . ' variation - ' . $variation;
					//$variation = addslashes($variation);

					$variation = str_replace( array( PHP_EOL, "\r\n", "\n", "\r" ), ' ', $variation );
					$variation = preg_replace( '!\s+!', ' ', $variation );

					$variation_hash = md5( $variation );
					if ( ! empty( $variation ) ) {
						$variation_data = array(
							'message'     => $variation,
							'hash'        => $variation_hash,
							'share_times' => (int) $variations_share_times[ $v_key ],
							'share_count' => 0
						);

						foreach ( $variations_old as $vo_key => $variation_old ) {
							$variation_old = (array) $variation_old;
							if ( $variation_old['hash'] === $variation_data['hash'] ) {
								if ( $last_shared_variation === $vo_key && $last_shared_key_reindexed === false ) {
									$custom_message_variations_data['last_shared_key'] = count( $variations_new ); //if index has changed for last shared variation
									$last_shared_key_reindexed                         = true;
								}
								if ( (int) $variations_share_count_reset[ $v_key ] !== 1 ) {
									$variation_data['share_count'] = $variation_old['share_count'];
								}

								break;
							}
						}
						//unset( $variations[ $key ] );
						$variations_new[] = $variation_data;
					}

				}

				$custom_message_variations_data['variations'] = $variations_new;

				//print_r($custom_message_variations_data);
				//exit;
				$new = json_encode( $custom_message_variations_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );


			}

			if ( 'sws_meta_social_accounts_exclude' === $field ) {
				$new = filter_input( INPUT_POST, 'sws_meta_social_accounts_exclude', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				if ( ! is_array( $new ) ) {
					$new = array();
				}
				$exclude_social_accounts = array();
				$social_accounts         = SocialWebSuite::get_social_accounts();
				$twitter_included        = false;
				foreach ( $social_accounts as $social_account ) {
					if ( ! in_array( $social_account->id, $new ) ) {
						$exclude_social_accounts[] = $social_account->id;
						$exclude_action            = 'add';
					} else {
						if ( $social_account->service !== 'twitter' ) {
							$exclude_action = 'remove';
						} else {
							if ( $twitter_included === false ) {
								$exclude_action   = 'remove';
								$twitter_included = true;
							} else {
								$exclude_social_accounts[] = $social_account->id;
								$exclude_action            = 'add';
							}
						}

					}
					//update option, so we can use it in query, rather than searching through post meta using too complex meta query combination
					SocialWebSuite::update_excluded_social_accounts_posts( $post_id, $social_account->id, $exclude_action );
				}
				$new = implode( ',', $exclude_social_accounts );
			}

			if ( $new && $new !== $old ) {
				update_post_meta( $post_id, $field, $new );
				if ( 'sws_meta_manual' === $field ) {
					SocialWebSuite::update_exclude_list_single( $post_id, $new, $old );
				}
			} elseif ( $old && ( '' === $new || null === $new ) ) {
				delete_post_meta( $post_id, $field, $old );
			}
		}

		SocialWebSuite_Log::info( 'saved_post_meta' . json_encode( $post ) );
		$gutenberg_share_on_publish = get_post_meta( $post_id, 'sws_gutenberg_share_on_publish' );

		if ( $gutenberg_share_on_publish && ! empty( $gutenberg_share_on_publish ) && ! empty( $post ) ) {
			delete_post_meta( $post_id, 'sws_gutenberg_share_on_publish' );
			SocialWebSuite_Log::info( 'calling auto_share_post from saved post meta ' . $post_id );
			SocialWebSuite::auto_share_post( $post );
		}
		$gutenberg_share_manually = get_post_meta( $post_id, 'sws_gutenberg_share_manually' );


		if ( $gutenberg_share_manually && ! empty( $gutenberg_share_manually ) && ! empty( $post ) ) {
			delete_post_meta( $post_id, 'sws_gutenberg_share_manually' );
			SocialWebSuite_Log::info( 'calling manually_share_post from saved post meta manually ' . $post_id );
			SocialWebSuite::manually_share_post( $post );
		}

	}

	/**
	 * takes the submitted values for dates and converts them in timestamp
	 *
	 * @param array $data post data from submitted post form
	 *
	 * @return array $dates dates converted in timestamp
	 * @since 1.0.5
	 *
	 */
	public function convert_nonevergreen_dates( $data ) {

		$prefix         = 'sws_meta_';
		$dates_prefixes = array( 'startdate', 'enddate' );
		$dates          = array();
		foreach ( $dates_prefixes as $date_prefix ) {
			$date_string = '';

			$prefix_key = $prefix . $date_prefix . '_';

			if ( isset( $data[ $prefix_key . 'calendar' ] ) && ! empty( $data[ $prefix_key . 'calendar' ] ) ) {

				$date_string .= $data[ $prefix_key . 'calendar' ];
				if ( isset( $data[ $prefix_key . 'hours' ] ) ) {
					$date_string .= ' ' . $data[ $prefix_key . 'hours' ];
				} else {
					$date_string .= ' 00';
				}
				if ( isset( $data[ $prefix_key . 'mins' ] ) ) {
					$date_string .= ':' . $data[ $prefix_key . 'mins' ];
				} else {
					$date_string .= ':00';
				}
				if ( isset( $data[ $prefix_key . 'ampm' ] ) ) {
					$date_string .= ' ' . $data[ $prefix_key . 'ampm' ];
				} else {
					$date_string .= ' am';
				}
			}

			if ( ! empty( $date_string ) ) {
				$date_string = strtotime( $date_string );

			}

			$dates[ $date_prefix ] = $date_string;

		}

		return $dates;

	}


	public function pre_action_share_on_update( $new_status, $old_status, $post ) {
		//avoid triggering share when restoring from trash
		if ( $old_status == 'publish' && $new_status == 'publish' ) {
			$this->action_share_on_update( $post );
		}
	}

	public function action_share_on_update( $post ) {
		//$post = get_post( $post_id );

		// we have to save meta here because this hook comes before save_post hook
		//$this->save_post_meta( $post_id, $post, ' called from action_share_on_update ' );

		// SocialWebSuite::auto_share_post( $post );

		if ( $this->is_rest === false ) {
			SocialWebSuite_Log::info( 'sharing post' . json_encode( $post ) );
			$this->save_post_meta( $post->ID, $post, ' called from action_share_on_update ' );
			SocialWebSuite::auto_share_post( $post );
		} else {
			//in case of rest API call, we need to wait for post meta to be updated first.
			SocialWebSuite_Log::info( 'sharing post gutenberg' . json_encode( $post ) );
			update_post_meta( $post->ID, 'sws_gutenberg_share_on_update', true );
		}

	}

	public function action_save_post( $post_id, $post ) {
		// meta is already saved, just share it if set so

		if ( $this->is_rest === false ) {
			SocialWebSuite_Log::info( 'sharing post' . json_encode( $post ) );
			//$this->save_post_meta( $post->ID, $post, ' called from action_share_on_publish ' );
			SocialWebSuite::manually_share_post( $post );
		} else {
			SocialWebSuite_Log::info( 'manually sharing post gutenberg' . json_encode( $post ) );
			update_post_meta( $post->ID, 'sws_gutenberg_share_manually', true );
		}

	}

	public function admin_notice() {

		$page = get_current_screen();

		if ( ! strstr( $page->id, 'social-web-suite' ) ) {

			/* disabled after requests from some users */
			//$this->ui->show_admin_notice();
		}

	}

	public function admin_plugin_rate_notice() {
		//check if user has fulfilled conditions to show him notice
		$rate_notice_hide = SocialWebSuite::get_option( 'rate_notice_hide' );
		$rate_notice_time = SocialWebSuite::get_option( 'rate_notice_time' );
		if ( empty( $rate_notice_time ) ) {
			$rate_notice_time = strtotime( '+ 2 weeks' );
			SocialWebSuite::set_option( 'rate_notice_time', $rate_notice_time );

			return;
		}

		$current_time = strtotime( 'now' );

		if ( $current_time > $rate_notice_time && ( false === $rate_notice_hide || empty( $rate_notice_hide ) ) ) {

			//show user notice every two weeks
			/* disabled after requests from some users */
			//$this->ui->show_admin_rate_notice();
		}

	}

	public function ajax_notice_rate() {

		// Continue only if the nonce is correct
		check_admin_referer( 'sws_rate_action_nonce', '_n' );

		$rate_action = filter_input( INPUT_POST, 'rate_action' );

		if ( 'do-rate' === $rate_action || 'done-rating' === $rate_action ) {
			//don't show notice any more
			SocialWebSuite::set_option( 'rate_notice_hide', true );
		} else {
			//postpone showing
			SocialWebSuite::set_option( 'rate_notice_hide', false );
			$rate_notice_time = strtotime( '+ 2 weeks' );
			SocialWebSuite::set_option( 'rate_notice_time', $rate_notice_time );

		}

		echo 1;
		exit;
	}

	public function action_send_post_to_stack( $post ) {
		// we have to save meta here because this hook comes before save_post hook
		//	$this->save_post_meta( $post->ID, $post );


		//SocialWebSuite::auto_share_post( $post );
		SocialWebSuite::send_post_to_stack( $post );
	}

	public function sws_get_date_query( $prefs ) {

		if ( ( isset( $prefs->min_age_post ) && ! empty( $prefs->min_age_post ) ) || ( isset( $prefs->max_age_post ) && ! empty( $prefs->max_age_post ) ) ) {
			$date_query = array();

			if ( isset( $prefs->min_age_post ) && ! empty( $prefs->min_age_post ) ) {
				$date_query[] = array(
					'before' => $prefs->min_age_post . ' days ago',
				);
			}

			if ( isset( $prefs->max_age_post ) && ! empty( $prefs->max_age_post ) ) {
				$date_query[] = array(
					'after' => $prefs->max_age_post . ' days ago',
				);
			}
			$dates = new WP_Date_Query( $date_query );


			return $dates->get_sql();
		}

		return false;

	}

} // end class SocialWebSuite_Admin_UI

//EOF
