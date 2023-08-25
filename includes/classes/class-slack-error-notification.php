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


class Slack_Error_Notification {

    use Singleton;

    /**
     * Variable
     * @var boolean
     */

	/**
	 * Constructor.
	 *
	 * Creates the instances of this class.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {

        if ( ! $this->is_local_server() ) {
            register_shutdown_function([$this, 'send_fatal_error_to_slack']);
        }
 
	}

     /**
	 * Callback function to send any error to slack channel
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
    public function send_fatal_error_to_slack() {

            $error = error_get_last();
            $site_url = site_url();

            if ( $error['type'] === E_ERROR ) {
                $message = $error['message'];
                $stackTracePos = strpos($message, "Stack trace:");
                if ( $stackTracePos !== false ) {
                    $message = substr($message, 0, $stackTracePos);
                    $final_message = $site_url . ' :rotating_light: :rotating_light: ' . $message;
                    
                }

                $slack_webhook_url = 'https://hooks.slack.com/services/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';
                $slack_channel = '#test-channel';

                $data = array(
                    'channel' => $slack_channel,
                    'text' => $final_message,
                );

                $options = array(
                    'http' => array(
                        'header'  => "Content-type: application/json",
                        'method'  => 'POST',
                        'content' => json_encode($data),
                    ),
                );

                $context  = stream_context_create($options);
                $result = file_get_contents($slack_webhook_url, false, $context);
            
            }
    
    }
    
    /**
     * Check if the current server is localhost
     * 
     * @since 1.0.0
     * @return boolean
     */
    public function is_local_server() {
        $is_local = in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) );

        return apply_filters( 'std_is_local', $is_local );
    }

}