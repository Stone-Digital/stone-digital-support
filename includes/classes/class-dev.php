<?php 
namespace Std_Support\Includes;

use Std_Support\Includes\Traits\Singleton;

defined( 'ABSPATH' ) || exit; // disable direct access


class Dev {

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
		// echo "Student Calss Inc";
	}

	public function test_echo() {
		return "stundent return working";
	}
}