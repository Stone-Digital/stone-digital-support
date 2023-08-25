<?php 
namespace Std_Support\Includes;

use Std_Support\Includes\Traits\Singleton;

defined( 'ABSPATH' ) || exit; // disable direct access

/**
 * Stone Digital Slack Notification 
 *
 * @package     Support @ Stone Digital
 * @author      Stone Digital
 * @link        https://stonedigital.com.au
 * @since       1.0.0
 */


class Slack_Notification {

    use Singleton;

    /**
     * Variable
     * @var boolean
     */
    protected $check_ip_status = false;

    /**
	 * $slack_webhook url
	 * @var null
	 */
	public $slack_webhhok_url = null;	    
    
    /**
	 * $slack channel
	 * @var null
	 */
	public $slack_channel_name = null;	

	/**
	 * Constructor.
	 *
	 * Creates the instances of this class.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {

        add_action('wp_login',  [ $this, 'insert_wpdb_user_login_details' ], 10, 2);

        add_action( 'wp_login', [ $this, 'get_login_alert' ], 10, 2 );

        $this->slack_webhhok_url =  get_option("stonedigital_plugin_slack_webhook_url");
        $this->slack_channel_name =  get_option("stonedigital_plugin_slack_channel_name");

	}


     /**
	 * Return Ip Address when User login
     * @since 0.0.0
	 * @param array
	 * @return string
	 */
    public function get_ip_address( ) {
        $ipHeaders = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CLIENT_IP',
        );

        foreach ($ipHeaders as $header) {
            if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
                return $_SERVER[$header];
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
	 * Return Country Name When User Login 
     * @since 0.0.0
	 * @param array
	 * @return string
	 */
    public function get_country() {
        $country = [];
        $ip = $this->get_ip_address();
        $apiurl = 'http://ip-api.com/json/' . $ip;
    
        $jsondata = file_get_contents($apiurl);
        $jsondata = json_decode($jsondata);
        $country['ip'] = $ip;
        $country['country'] = $jsondata->country;
        $country['countryCode'] = $jsondata->countryCode;

        return $country;

    }

    /**
	 * Create the UserLogins table during plugin activation
     * @since 0.0.0
	* @param array
	* @return array
	*/
    public static function create_login_tables() {
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
    
    /**
	 * Insert User Log Data When User Login
     * @since 0.0.0
	 * @param array
	 * @return array
	 */
    public function insert_wpdb_user_login_details($user_login, \WP_User $user) {

        $get_ip_data = $this->get_country();
        $user_ip = $get_ip_data['ip'];
        $country = $get_ip_data['country'];

        global $wpdb;
        $table_name = $wpdb->prefix . 'std_user_logins';

        $ip_address = $user_ip;
        $username = $user->display_name;
        $user_email = $user->user_email;
        $country_name = $country;

        // Check if IP address already exists in the table
        $existing_entry = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE ip_address = %s",
                $ip_address
            )
        );
        if (!$existing_entry) {
            
            $this->check_ip_status = true;

            $wpdb->insert(
                $table_name,
                array(
                    'ip_address' => $ip_address,
                    'user_name' => $username,
                    'user_email' => $user_email,
                    'country' => $country_name
                )
            );
        }
    }

    /**
	 * Forward User Login Details to Slack Channel
     * @since 0.0.0
	 * @param array
	 * @return array
	 */
    public function get_login_alert( $user_login, \WP_User $user ) {

        if ( $this->check_ip_status === false  ) {
            return;
        }

        // get user data
        $get_ip_data = $this->get_country();
        $user_ip = $get_ip_data['ip'];
        $country = $get_ip_data['country'];
        $country_code = $get_ip_data['countryCode'];
        $site_url = parse_url( get_site_url(), PHP_URL_HOST );
        $user_name = $user->display_name;
        $user_email = $user->user_email;
        $user_roles  = $user->roles[0];
        $login_time = current_time('mysql');
        $slack_url = '';

        $client = new \Maknz\Slack\Client($this->slack_webhhok_url);
            
        $client->to($this->slack_channel_name)->attach([
            'fallback' => 'This is a Fallback Messages',
            'country' => $country,
            'color' => 'danger',
            'fields' => [
                [
                    'title' => 'New alert from',
                    'value' => "IP Address: $user_ip, Country: $country :flag-{$country_code}: , User Email: $user_email, User Name: $user_name, Login Time: $login_time",
                    'long' => true
                
                ]
            ]
        ])->send("New Admin Login: {$site_url}");
           
        return;
    }

}