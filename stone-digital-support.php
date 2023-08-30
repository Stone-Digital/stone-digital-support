<?php
/**
 * Plugin Name: Support @ Stone Digital
 * Plugin URI: https://stonedigital.com.au
 * Description: Custom WordPress plugin provoding number of support features for Stone Digital customers.
 * Version: 1.1.2
 * Author: Stone Digital
 * Author URI: https://stonedigital.com.au
 * Text Domain: stone-digital-support
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'STD_PLUGIN_URL' ) ) {
	define( 'STD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'STD_ROOT_DIR_PATH' ) ) {
	define( 'STD_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'STD_ABSPATH' ) ) {
    define( 'STD_ABSPATH', dirname( __FILE__ ) );
}
define( 'STD_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/views/' );

define( 'STD_BASENAME', plugin_basename( __FILE__ ) );

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// // plugin update checker
require_once STD_ROOT_DIR_PATH . '/vendor/plugin-update-checker/plugin-update-checker.php';

$std_plugin_updater = PucFactory::buildUpdateChecker(
	'https://github.com/Stone-Digital/stone-digital-support/',
	__FILE__,
	'stone-digital-support'
);

// $std_plugin_updater->setAuthentication();
$std_plugin_updater->getVcsApi()->enableReleaseAssets();
$std_plugin_updater->setBranch('master');


// plugin Autoload
require_once STD_ROOT_DIR_PATH . '/includes/helpers/autoloader.php';
require_once STD_ROOT_DIR_PATH . '/includes/helpers/helper-functions.php';

// Autoload
require STD_ROOT_DIR_PATH . '/vendor/slack/autoload.php';


function stone_digital_support_plugin() {
	\Std_Support\Includes\Base::get_instance();
}

// Initialize the theme.
stone_digital_support_plugin();

// change wp login url
$enable_hide_login_url = get_option("stonedigital_plugin_enable_std_hide_login_url");

// print_r($enable_hide_login_url);

if ($enable_hide_login_url === "1" ) {
	new \Std_Support\Includes\Hide_Login_Url();
}
