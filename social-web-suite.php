<?php
/**
 * Plugin Name: Social Web Suite - Social Media Auto Post, Auto Publish and Schedule
 * Plugin URI: https://socialwebsuite.com/
 * Description: Manage all your social media accounts from one place. Automate, schedule & publish your posts/custom
 * post types/pages to major social networks. Social Web Suite is social media scheduler.
 * Version: 4.1.12
 * Author: hypestudio,nytogroup,dejanmarkovic,tinat
 * Author URI: https://hypestudio.org/
 * Text domain: social-web-suite Domain
 * Path: /languages License: GPL2 License URI:
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from the direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit( "You're not allowed to access this file directly." );// Exit if accessed directly
}

// path to the plugin
define( 'SWS_PLUGIN_PATH', __FILE__ );
if ( ! defined( 'SWS_PLUGIN__DIR_URL' ) ) {
	define( 'SWS_PLUGIN__DIR_URL', plugin_dir_url( __FILE__ ) );
}

// server url
define( 'SWS_SERVER_URL', 'https://app.socialwebsuite.com/' );

//default sharing option
define( 'SWS_DEFAULT_META_MANUAL', 'default' );

// set if site is using BASIC_AUTH, otherwise set them to false or comment it out
//define( 'SWS_BASIC_AUTH_USER', '' );//login
//define( 'SWS_BASIC_AUTH_PASS', '' );//password

// include the logs class
require_once plugin_dir_path( __FILE__ ) . 'includes/libs/class-socialwebsuite-log.php';
// include the main class
require_once plugin_dir_path( __FILE__ ) . 'includes/class-socialwebsuite.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-socialwebsuite-helpers.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/social-web-suite-functions.php';

// Handle the activation
register_activation_hook( __FILE__, array( 'SocialWebSuite', 'activate_plugin' ) );
register_deactivation_hook( __FILE__, array( 'SocialWebSuite', 'deactivate_plugin' ) );
register_uninstall_hook( __FILE__, array( 'SocialWebSuite', 'uninstall_plugin' ) );

// Run the plugin
function sws_run_plugin() {
	$plugin = SocialWebSuite::create();
	$plugin->run();
}

add_action( 'plugins_loaded', 'sws_run_plugin' );

// EOF
