<?php

/**
* Handle Logs for SocialWebsuite
*
* @since      1.0.0
* @link       https://hypestudio.org/
* @package    SocialWebSuite
* @subpackage SocialWebSuite/includes
* @author     HYPEStudio <info@hypestudio.org>
*/
class SocialWebSuite_Log {





	/**
	* Logs the error message
	*
	* @param string $msg
	*
	* @return void
	*/
	public static function error( $msg ) {
		self::write_log( $msg, 'error' );
	}
	/**
	* Logs the info message
	*
	* @param string $msg
	*
	* @return void
	*/
	public static function info( $msg ) {
		self::write_log( $msg, 'info' );
	}

	/**
	* Writes the log in log file sorted by date
	*
	* @param string $msg
	* @param string $type
	*
	* @return void
	*/
	public static function write_log( $msg = '', $type = 'error' ) {
		$date          = date( 'd.m.Y h:i:s' );
		$log           = 'Type: ' . $type . "\t| Date: " . $date . "\t| Message: " . $msg . "\n";
		//$sws_enable_log = SocialWebSuite::get_option( 'enable_log' );
		$sws_enable_log = true;
		if ( $sws_enable_log ) {
			self::write_log_to_file( $log );
		}

	}

	public static function write_log_to_file( $log = '' ) {
		if ( empty( $log ) ) {
			return;
		}
		$log_file_date = date( 'Y-m-d' );
		$log_file      = self::log_dir() . 'sws-' . $log_file_date . '.log';
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			 include_once ABSPATH . '/wp-admin/includes/file.php';
			 WP_Filesystem();
		}
		$content = '';
		if ( file_exists( $log_file ) ) {
			$content = $wp_filesystem->get_contents( $log_file );
		}
		$content .= $log . "\n";

		$wp_filesystem->put_contents( $log_file, $content );
	}
	
		/**
		 * Downloads a log file if it exists and is a valid log file.
		 *
		 * @param string $log_file The name of the log file to download.
		 * @return void
		 */
	public static function download_log( $log_file = '' ) {
		if ( empty( $log_file ) || strpos( $log_file, '.log' ) === false ) {
			return;
		}
		
		$log_dir = self::log_dir();
		$sanitized_log_file = sanitize_file_name($log_file);
		if ( ! file_exists( $log_dir . $sanitized_log_file ) ) {
			return;
		}
		
		self::force_download( $sanitized_log_file, $log_dir . $sanitized_log_file );
	}

	public static function force_download( $filename = '', $filepath ) {

		if ( empty( $filename ) ) {
			return;
		}

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			 include_once ABSPATH . '/wp-admin/includes/file.php';
			 WP_Filesystem();
		}
		esc_html_e( $wp_filesystem->get_contents( $filepath ) );

		exit;
	}

	public static function get_last_log( $logs = '' ) {
		$log_dir = self::log_dir();
		if ( empty( $logs ) ) {
			$logs = self::list_logs();
		}
		$last_log = $logs[0];

		if ( file_exists( $log_dir . $last_log ) ) {

			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				include_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
			return $wp_filesystem->get_contents( $log_dir . $last_log );
		}
	}

	public static function download_logs() {

		$logs        = self::list_logs();
		$log_dir     = self::log_dir();
		$uploads_dir = self::uploads_dir();
		$site_url = str_replace( array( 'http://', 'https://', '.' ), array( '', '', '-' ), get_site_url() );
		$zip_filename = 'social-web-suite-logs-' . $site_url . '.zip';
		// Initialize archive object
		$zip         = new ZipArchive();

		$zip->open( $log_dir . $zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE );

		foreach ( $logs as $log ) {
			 // Add current file to archive
			 $zip->addFile( $log_dir . $log, $log );
		}

		// Zip archive will be created only after closing object
		$zip->close();

		self::force_download( $zip_filename, $log_dir . $zip_filename );

	}
	
	
	public static function delete_log( $log_file = '' ) {
		if ( empty( $log_file ) || strpos( $log_file, '.log' ) === false ) {
			return;
		}
		
		$log_dir = self::log_dir();
		$sanitized_log_file = sanitize_file_name($log_file);
		if ( ! file_exists( $log_dir . $sanitized_log_file ) ) {
			return;
		}
		
		unlink( $log_dir . $sanitized_log_file );
	}

	public static function delete_logs() {

	}

	public static function list_logs() {
		$log_dir = self::log_dir();
		$logs    = array();
		$logs_raw = glob( $log_dir . '*.log' );
		/*foreach(new DirectoryIterator($log_dir) as $item){
         if(! $item->isDot() and $item->isFile() and $item->getExtension() === 'log'){
          $logs[] = $item->getFilename();
         }
        }*/
		foreach ( $logs_raw as $item ) {
			 $logs[] = basename( $item );
		}

		rsort( $logs );
		return $logs;
	}
	
	public static function log_actions() {
		$page     = filter_input( INPUT_GET, 'page' );
		$action   = filter_input( INPUT_GET, 'action' );
		$filename = filter_input( INPUT_GET, 'filename' );
		if ( 'social-web-suite' === $page ) {
			if ( ! empty( $action ) ) {
				if ( 'download' === $action && ! empty( $filename ) ) {
					self::download_log( sanitize_file_name($filename) );
				}
				if ( 'download-all' === $action ) {
					self::download_logs();
				}
				if ( 'delete' === $action && ! empty( $filename ) ) {
					self::delete_log( sanitize_file_name($filename) );
					$url = admin_url( 'admin.php?page=social-web-suite&status=logdeleted' );
					wp_redirect( $url );
					exit;
				}
			}
		}
	}

	private static function log_dir() {

		$uploads_dir = self::uploads_dir();

		$log_dir     = $uploads_dir . '/social-web-suite/';
		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}

		return $log_dir;
	}

	private static function uploads_dir() {
		$uploads_dir_array = wp_upload_dir();

		return $uploads_dir_array['basedir'];
	}





}
