<?php 
namespace Std_Support\Includes\Admin;

use Std_Support\Includes\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stone Digital User Log 
 *
 * @package     Support @ Stone Digital
 * @author      Stone Digital
 * @link        https://stonedigital.com.au
 * @since       1.1.0
 */

class User_Log_details {

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

        if (check_user_email_domain()) {
            add_action( 'admin_menu', array( $this, 'user_log_details_page' ), 22 );
        }

 
	}

 	/**
     * Stone Digital Welcome Templates page.
     */
    public function user_log_details_page() {
        add_submenu_page(
            'stone-digital-support-plugin',
            esc_html__( 'User Log', 'stone-digital-support' ),
            esc_html__( 'User Log', 'stone-digital-support' ),
            'manage_options',
           'stone-digital-support-user-log',
            [$this, 'log_details_markup']
        );
    }

     /**
	 * Callback function to display the user logins page content
     * @since 0.0.0
	 * @param array
	 * @return string
	 */
    public function log_details_markup() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'std_user_logins';

        $user_logins = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY login_time DESC"
        );
        ?>
        <div class="wrap">
            <h1>User Logins</h1>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>IP Address</th>
                        <th>Username</th>
                        <th>User Email</th>
                        <th>Country Name</th>
                        <th>Login Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_logins as $login) : ?>
                        <tr>
                            <td><?php echo $login->id; ?></td>
                            <td><?php echo $login->ip_address; ?></td>
                            <td><?php echo $login->user_name; ?></td>
                            <td><?php echo $login->user_email; ?></td>
                            <td><?php echo $login->country; ?></td>
                            <td><?php echo $login->login_time; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

}