<?php 
namespace Std_Support\Includes;

use Std_Support\Includes\Traits\Singleton;

defined( 'ABSPATH' ) || exit; // disable direct access


class Wordfence {

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

	public function test_echo() {

		return "stundent return working";

	}

    protected function setup_hooks() {

       	add_action( 'wordfence_security_event', array( $this, 'wordfence_alert' ), 10, 3 );

	}

    public function wordfence_alert($event, $data = array(), $alertCallback = null) {
		error_log("std_custom_wordfence_alert");
		error_log(print_r($data, true));
		error_log(print_r($alertCallback[0], true));
		// wfUtils::getIPGeo($IP);
	}
}