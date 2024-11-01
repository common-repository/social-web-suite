<?php

/**
 * Various helpers
 *
 * @link   https://hypestudio.org/
 * @since  2.0.5
 * @author HYPEStudio <info@hypestudio.org>
 */
class SocialWebSuite_Helpers {


	public static function share_post_api( $post, $args ) {

		$post = self::prepare_post( $post );

		if ( $post == false ) {
			return false;
		}

		$args = self::validate_arguments( $args );

		$basic_args = array(

			'sitename'          => get_bloginfo( 'name' ),
			'id'                => $post->ID,
			'author'            => self::prepare_author( $post, $args ),
			'post_status'       => $post->post_status,
			'date'              => get_the_date( '', $post->ID ), // use the default WP format
			'content'           => self::prepare_content( $post, $args ),
			'title'             => self::prepare_title( $post, $args ),
			'post_type'         => $post->post_type,
			'url'               => get_permalink( $post->ID ),
			'meta_times_shared' => 0,
		);

		$basic_args = self::prepare_cats_tags( $post, $basic_args, $args );
		$basic_args = self::prepare_image( $post, $basic_args, $args );

		$args = array_merge( $args, $basic_args );


		return self::share( $args );


	}

	public static function prepare_post( $post ) {

		if ( is_integer( $post ) ) {
			$post = get_post( $post );
			if ( is_null( $post ) ) {
				return false;
			}
		}

		if ( ! isset( $post->ID ) ) {
			return false;
		}

		return $post;
	}

	public static function validate_arguments( $args ) {

		if ( isset( $args['template'] ) ) {
			$args['format'] = $args['template'];
		}
		$non_prefixed_args = array( 'use_hashtags', 'hashtags', 'format', 'include_image', 'social_accounts_exclude' );
		$default_values    = array(
			'use_hashtags'  => 'default',
			'hashtags'      => '',
			'format'        => '{title} {url}',
			'include_image' => 'default'
		);

		//iterate through args and set default values
		foreach ( $default_values as $default_key => $default_value ) {
			if ( ! isset( $args[ $default_key ] ) || empty( $args[ $default_key ] ) ) {
				$args[ $default_key ] = $default_value;
			}
		}

		$new_args = array();


		foreach ( $args as $key => $arg ) {
			if ( in_array( $key, $non_prefixed_args ) ) {
				$new_args[ 'meta_' . $key ] = $arg;
			} else {
				$new_args[ $key ] = $arg;
			}
		}


		return $new_args;

	}


	public static function prepare_title( $post, $args ) {

		if ( isset( $args['title'] ) ) {
			return $args['title'];
		}

		return $post->post_title;
	}

	public static function prepare_content( $post, $args ) {

		if ( isset( $args['content'] ) ) {
			return $args['content'];

		}
		if ( empty( $post->post_excerpt ) ) {

			$excerpt = wp_trim_words( strip_shortcodes( $post->post_content ) );

		} else {

			$excerpt = $post->post_excerpt;

		}

		return $excerpt;

	}


	public static function prepare_author( $post, $args ) {

		$author = get_user_by( 'id', $post->post_author );

		return $author->display_name;

	}

	public static function prepare_cats_tags( $post, $basic_args, $args ) {


		$taxonomies     = get_object_taxonomies( get_post_type( $post->ID ), 'objects' );
		$cat_taxonomies = array(); //hieararchical taxonomies like built in post categories
		$tag_taxonomies = array(); //non-hieararchical taxonomies like built in post tags

		if ( ! empty( $taxonomies ) ) {
			if ( ! is_wp_error( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {
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

		$cats_tags = array(
			'categories'   => $cat_names,
			'category_ids' => $cat_ids,
			'tags'         => $tag_names,
			'tag_ids'      => $tag_ids,
		);

		return array_merge( $basic_args, $cats_tags );
	}


	public static function prepare_image( $post, $basic_args, $args ) {

		if ( isset( $args['meta_include_image'] ) && 'skip' !== $args['meta_include_image'] ) { // add featured image

			$thumb_id = get_post_thumbnail_id( $post->ID );

			if ( $thumb_id ) {
				$featured_image = wp_get_attachment_image_src( $thumb_id, 'full' );

				if ( $featured_image ) {
					$basic_args['image_url']    = $featured_image[0];
					$basic_args['image_width']  = $featured_image[1];
					$basic_args['image_height'] = $featured_image[2];
				}
			}
		}

		return $basic_args;
	}

	public static function prepare_url( $post, $args ) {

	}

	public static function share( $args ) {

		$settings = SocialWebSuite::get_settings();


		$data = array(
			'publish_at' => isset( $args['gmt_date_time'] ) ? $args['gmt_date_time'] : current_time( 'mysql', true ),
			//if not set, it will set current date
			'content'    => wp_json_encode( (object) $args ),
		);

		$api_url = 'share/' . $settings->site_id;

		$response = SocialWebSuite::call_api( $api_url, $data );

		if ( is_object( $response ) && isset( $response->status ) && $response->status === 'Error' ) {
			if ( isset( $response->subscription_expired ) && isset( $response->msg ) ) {

				$message = $response->msg;
				SocialWebSuite::set_option( 'subscription_expired', true );
				SocialWebSuite::set_option( 'subscription_expired_message', $message );

			} else {

				$message = isset( $response->msg ) ? $response->msg : '';
				SocialWebSuite::delete_option( 'subscription_expired' );
				SocialWebSuite::delete_option( 'subscription_expired_message' );
				SocialWebSuite::set_option( 'share_error_message', $message );

			}
		} else {
			SocialWebSuite::delete_option( 'subscription_expired' );
		}


		return $response;
	}

	public static function check_image( $image_url ) {

		//	$image_url        = 'https://i.redd.it/fnxbn804hpd31.jpg';
		$image_type_check = @exif_imagetype( $image_url );
		
		if ( strpos( $http_response_header[0], '200' ) || strpos( $http_response_header[0], '302' ) ) {
			return $image_url;
			//echo "image exists<br>";
		} elseif ( strpos( $http_response_header[0], '403' ) ) {
			//plugin_dir_url()
			return SWS_PLUGIN_PATH . '/images/avatar.png';
		}

	}


	public static function check_image_url( $image_url ) {
		
		if ( @getimagesize( $image_url ) ) {
			return $image_url;
		} else {
			return SWS_PLUGIN__DIR_URL . 'images/avatar.png';
		}

	}

} // end class SocialWebSuiteHelpers

//EOF
