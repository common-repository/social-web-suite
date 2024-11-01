<?php
/**
 * @package Social Web Suite template functions
 */



if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! function_exists( 'sws_list_social_profiles_api' ) ) {

	/**
	 *
	 * Retrieve a list of connected social profiles with this site on Social Web Suite Server
	 *
	 * @return mixed|object
	 */
	function sws_list_social_profiles_api() {

		return SocialWebSuite::get_social_accounts();

	}

}
if ( ! function_exists( 'sws_share_post_api' ) ) {

	/**
	 *
	 * Share
	 *
	 * @param $post
	 * @param $args
	 *
	 *    sample keys/values for $args:
	 *
	 *    $args = array(
	 *    'gmt_date_time'                 =>  '2019-07-04 22:45:00'/ //Y-m-d H:i:s, date time in GMT-0 timezone
	 *    'content'                       => 'Some Content', //optional, if not set, it will use post excerpt
	 *    'title'                         => 'Some Title', //optional, if not set, it will use post title
	 *    'use_hashtags'                  => 'default', //optional, if not set, it will use 'default' value, values can be: default, none, cats, tags, custom,
	 *                                                  // for tags and cats it will dynamically pull terms and for custom, must provide values for 'hashtags'k ey
	 *    'hashtags'                      => '#some #social #skills', //optional, must use if use_hashtags has value 'custom'
	 *    'template'                      => '{title} {url} {hashtags}', //optional, if not set, it will use '{title} {url}' can be one of all of these {title}, {excerpt}, {sitename}, {date}, {author}, {category}, {url}, {hashtags}
	 *    'include_image'                 => 'skip', 'include', 'default' //optional, if not set, it will use 'default'
	 *    'social_accounts_exclude'    => [], //optional, array of IDs of social profiles added on SWS account, leave empty if you want to share on all social media profiles
	 *                                        //connected in SWS with current WordPress site.
	 *                                        // use SocialWebSuite::get_social_accounts(); to retrieve all connected social profiles
	 *     );
	 *
	 * @return array|mixed|object|string
	 */
	function sws_share_post_api( $post, $args ) {

		return SocialWebSuite_Helpers::share_post_api( $post, $args );

	}
}

if ( ! function_exists( 'sws_debug_data_output' ) ) {


	function sws_debug_data_output( $data,  $format = 'pre' ) {

		echo  '<pre>' . print_r($data, true) . '</pre>';
		exit;

	}
}

//add_filter('socialwebsuite_share_post_post', '__return_false' );