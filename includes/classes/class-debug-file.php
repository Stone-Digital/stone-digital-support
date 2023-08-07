<?php 
namespace Std_Support\Includes;

use Std_Support\Includes\Traits\Singleton;

defined( 'ABSPATH' ) || exit; // disable direct access

/**
 * Support Plugin Debug Folder & Files Class.
 * 
 * @package Support @ Stone Digital
 * @since 1.1.0
 */

class Debug_File {

	use Singleton;

	/**
	 * Constructor.
	 *
	 * Creates the instances of this class.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Plugin public hooks.
	 *
	 * Triggers the public hooks.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function setup_hooks() {
		add_action('init', [ $this,  'create_debug_folder' ]);
		add_action('init', [ $this, 'cleanup_debug_files_event' ] );
	}

	/**
	 * Create folder for debug files

	 * @since    1.0.0
	 * @access   public
	 */
	public function create_debug_folder() {
		$debug_directory = ABSPATH . 'wp-content/debug/';
	
		if (!file_exists($debug_directory)) {
			mkdir($debug_directory, 0755, true);
		}
	}

	/**
	 * Automatic clean all debug files - after 2 months

	 * @since    1.0.0
	 * @access   public
	 */
	public function cleanup_debug_files_event() {
		$debug_directory = ABSPATH . 'wp-content/debug/';
		$debug_files = glob($debug_directory . '*.log');
		
		foreach ($debug_files as $file) {
			$file_name = basename($file, '.log');
			preg_match('/\d{4}-\d{2}-\d{2}/', $file_name, $matches);
			$file_date = isset($matches[0]) ? $matches[0] : null;
		
			$file_modified_time = date( 'Y-m-d', filemtime($file) );
			$one_week_ago = date( 'Y-m-d', strtotime('-2 month') );
			
			
			if ($file_date < $one_week_ago) {
		
				unlink($file);
			}
		}
	}
	
	
}