<?php
namespace Std_Support\Includes;

use Std_Support\Includes\Traits\Singleton;

defined( 'ABSPATH' ) || exit; // disable direct access

/**
 * Support Plugin Final Class.
 * 
 * @package Support @ Stone Digital
 * @since 1.1.0
 */

class Base {
	
	use Singleton;

	/**
	 * [$dashboard_manager description]
	 * @var null
	 */
	public $dashboard_manager = null;
	
	/**
	 * [$dashboard_settings description]
	 * @var null
	 */
	public $dashboard_settings = null;
	
	/**
	 * [$module_manager description]
	 * @var null
	 */
	public $dev_manager = null;	

	/**
	 * [$wordfence_manager description]
	 * @var null
	 */
	public $wordfence_manager = null;

	/**
	 * [$slack_manager description]
	 * @var null
	 */
	public $slack_manager = null;	
	
	/**
	 * [$slack_error_manager description]
	 * @var null
	 */
	public $slack_error_manager = null;

	public function __construct() {

		// Load class.
	
		add_action( 'init', [ $this, 'init_managers' ], -998 );
		
		$this->setup_hooks();

	}

	/**
	 * [init_managers
	 * @param  array  $args
	 * @return [type]
	 */
	public function init_managers() {

		$dev_mode_status = get_option("stonedigital_plugin_dev_mode");
		$alert_all_user = get_option("stonedigital_plugin_slack_alert_all_user");
		$alert_for_admin = get_option("stonedigital_plugin_slack_alert_for_admin");

		$this->dashboard_manager  = new Admin\Dashboard_Panel();
		$this->dashboard_settings  = new Dashboard_settings();
		$this->dev_manager  = new Dev();
		$this->wordfence_manager  = new Wordfence();
	
		if ($dev_mode_status !== "1" ) {

			if (check_admin_or_editor() && "1" === $alert_for_admin ) {

				$this->slack_manager  = new Slack_Notification();
				$this->slack_error_manager  = new Slack_Error_Notification();
			} else {
		
				$this->slack_manager  = new Slack_Notification();
				$this->slack_error_manager  = new Slack_Error_Notification();
			}
	
		}
		
		new Debug_File();

	}


	/**
	 * Load all hooks
	 * @since    1.1.0
	 * @access   public
	 */
	protected function setup_hooks() {

		register_activation_hook( __FILE__, [ $this, 'activate' ] );

	}

	/**
	 * Plugin activation action.
	 *
	 * Triggers the plugin activation action on plugin activate.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function activate() {

		$this->slack_manager::create_login_tables();

	}

	/**
	 * Plugin deactivation action.
	 *
	 * Triggers the plugin activation action on plugin deactivate.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public static function deactivate() {

	}

	/**
	 * Create Table when plugin Activate
	 *
	 * Create Table action on plugin Activate.
	 *
	 * @since    1.1.4
	 * @access   public
	 */
	
	public function create_login_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$schema = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}std_user_logins` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`ip_address` varchar(100) NOT NULL DEFAULT '',
			`user_name` varchar(30) DEFAULT NULL,
			`user_email` varchar(30) DEFAULT NULL,
			`country` varchar(30) DEFAULT NULL,
			`login_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) $charset_collate";

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		dbDelta( $schema );
	}


}
