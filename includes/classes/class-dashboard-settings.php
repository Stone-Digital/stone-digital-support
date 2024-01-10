<?php 
namespace Std_Support\Includes;

use Std_Support\Includes\Traits\Singleton;

defined( 'ABSPATH' ) || exit; // disable direct access


class Dashboard_settings {

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

        // load class.
        $this->setup_hooks();

	}

	/**
	 * load all hooks.

	 * @since    1.1.0
	 * @access   public
	 */
    protected function setup_hooks() {
        add_action( 'login_footer', array($this, 'stonedigital_plugin_add_text_after_login_form'), 10, 2 );
	
		add_filter( 'admin_footer_text', array($this, 'load_stone_digital_message') );
		add_action( 'in_admin_footer', array($this, 'add_stone_digital_footer_message') );
		
		// add_action( 'login_enqueue_scripts', array($this, 'client_login_logo') );
		add_filter( 'login_headerurl', array($this, 'custom_login_headerurl'), 20);
		add_filter( 'login_headertext', array($this, 'custom_login_headertext'), 20);
		add_action('admin_init', array( $this, 'remove_user_roles'), 10);
		add_action('admin_init', array( $this, 'add_custom_user_role'), 20);

		add_action('admin_init',array( $this, 'std_gravity_forms_client_account_access' ), 30);
		$enable_comments_form = get_option("stonedigital_plugin_enable_comments_form");
		if ($enable_comments_form !== "1" ) {

			// Close comments on the front-end
			add_filter('comments_open', '__return_false', 20, 2);
			add_filter('pings_open', '__return_false', 20, 2);
			add_filter('comments_array', '__return_empty_array', 10, 2);
			add_action('admin_init', array($this, 'remove_comments_links_from_dashboard'));
			add_action('admin_menu', array($this, 'remove_comments_page_in_menu'));
		}
		
		// add_action('in_admin_footer', array($this, 'get_theme_screenshot'), 30);
		add_action('admin_init', array($this, 'remove_menu_items_for_client_accounts'), 200);

		add_action('login_head', array($this, 'get_custom_logo_from_yoast_url' ) );

		$disable_notices = get_option("stonedigital_plugin_disable_notices");
	
		if ( $disable_notices === "1") {
			add_action('admin_init', array($this, 'stonedigital_plugin_disable_all_admin_notices' ) );
		}
		
    }

    public function remove_menu_items_for_client_accounts() {
		if( !current_user_can('administrator') ) {
			remove_menu_page( 'edit.php?post_type=acf-field-group' );
			remove_menu_page( 'ai1wm_export' );
			remove_menu_page( 'index.php' );
		}
		// if( current_user_can('client-account') ) {
		// 	// add_menu_page( 'plugins.php' );
		// }
	}

	/**
	 * Showing Custom Logo in wp login page

	 * @since    1.1.0
	 * @access   public
	 */
	public function custom_login_headertext() {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
	    $image_html = wp_get_attachment_image( $custom_logo_id , 'full' );
	    return $image_html;
	}

	/**
	 * Custom Logo url in wp login page

	 * @since    1.1.0
	 * @access   public
	 */
	public function custom_login_headerurl() {
		return home_url();
	}

	public function get_theme_screenshot() {
		$theme_object = wp_get_theme();
		$theme_root = $theme_object->__get('theme_root');
		$theme_folder = $theme_object->__get('template');
		$screenshot_img_location = $theme_root . "/" . $theme_folder . "/screenshot.png";
		$home_url_encoded = urlencode(home_url());
		$new_screenshot_url = "https://s.wordpress.com/mshots/v1/" . $home_url_encoded;
		if(!file_exists($screenshot_img_location)){
			
		}
		file_put_contents($screenshot_img_location, file_get_contents($home_url_encoded) );
	}

	public function remove_comments_page_in_menu() {
		remove_menu_page('edit-comments.php');
	}

	public function remove_comments_links_from_dashboard() {
		if (is_admin_bar_showing()) {
	        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
	    }

	    global $pagenow;
	    if ($pagenow === 'edit-comments.php') {
	        wp_safe_redirect(admin_url());
	        exit;
	    }
	 
	    // Remove comments metabox from dashboard
	    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	 
	    // Disable support for comments and trackbacks in post types
	    foreach (get_post_types() as $post_type) {
	        if (post_type_supports($post_type, 'comments')) {
	            remove_post_type_support($post_type, 'comments');
	            remove_post_type_support($post_type, 'trackbacks');
	        }
	    }

	}

	public function add_custom_user_role() {

		$caps = get_role( 'administrator' )->capabilities;
		$restricted_admin_roles = array(
			'switch_themes',
			'edit_themes',
			'edit_plugins',
			'activate_plugins',
			'edit_users',
			'remove_users',
			'delete_users',
			'create_users',
			'edit_files',
			'update_plugins',
			'delete_plugins',
			'update_core',
			'export'
		);
		$capabilities_arg = array();
		foreach ($caps as $single_capability => $is_active) {
			if( !in_array($single_capability, $restricted_admin_roles) ) {
				$capabilities_arg[$single_capability] = true;
			}
		}
		// Add extra permissions
		// No access to ACF Fields
		remove_role('client-account');
        add_role('client-account', "Client Account", $capabilities_arg);

	}

	public function std_gravity_forms_client_account_access() {
		$role = get_role('client-account');
		$role->add_cap( 'gravityforms_view_entries' );
		// $role->add_cap( 'gravityforms_edit_entries' );
		// $role->add_cap( 'gravityforms_delete_entries' );
		// $role->add_cap( 'gravityforms_edit_forms');
		// $role->add_cap( 'gravityforms_export_entries' );
	}


	public function remove_user_roles() {
		//check if role exist before removing it
		$roles_to_remove = array(
			'subscriber',
			'contributor',
			'author',
			'wpseo_manager',
			'wpseo_editor'
		);
		foreach ($roles_to_remove as $role) {
			if( get_role($role) ){
				remove_role($role);
			}
		}

	}

	/**
	 * Custom Logo url in wp login page

	 * @since    1.1.0
	 * @access   public
	 */
	public function get_custom_logo_url() {
	    $custom_logo_id = get_theme_mod( 'custom_logo' );
	    $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
	    return $image[0];
	}

	public function get_custom_logo_from_yoast_url() {
		if ( has_custom_logo() ) :
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
		else :
	    	$image_url = get_option( 'wpseo_social' )['og_default_image_id'];
			$image = wp_get_attachment_image_src( $image_url , 'full' );
		endif;
	    // $image[0];
		// print_r($image[0]);
		if ( ! empty( $image ) ) {
			echo '<style type="text/css">
				.login h1 a {
					background-image: url(' . esc_url( $image[0] ) . ');
					background-size: contain;
					height: 120px;
					width: 300px;
				}
			</style>';
		}
	}

	/**
	 * Custom Logo style css - wp login page

	 * @since    1.1.0
	 * @access   public
	 */
	public function client_login_logo() { ?>
		<style type="text/css">
			#login h1 a,
			.login h1 a {
				background-image: none;
				display: block;
				height: initial;
				box-sizing: border-box;
				width: 200px;
			}
			#login h1 a img, 
			.login h1 a img {
				display: block;
				width: 100%;
				height: initial;
			}
		</style>
	<?php }

	/**
	 * HTML After login form - wp login page

	 * @since    1.1.0
	 * @access   public
	 */
	public function load_stone_digital_message() {

		echo "Thank you for choosing <a href='https://stonedigital.com.au'>Stone Digital</a> for your web development project.";
		// foreach( wp_roles()->role_objects as $role ) {
		// 	echo print_r($role, true);
		// }
		
	}

	/**
	 * HTML After login form - wp login page

	 * @since    1.1.0
	 * @access   public
	 */
	public function add_stone_digital_footer_message() {
		// echo "<div class='stone-digital-support__plugin-wrap'></div>";
		// echo "<div class='stone-digital-support__footer'>Thank you for choosing <a href='https://stonedigital.com.au'>Stone Digital</a> for your web development project.</div>";
	}

	/**
	 * HTML After login form - wp login page

	 * @since    1.1.0
	 * @access   public
	 */
	public function stonedigital_plugin_add_text_after_login_form() {
		echo '<div style="text-align: center;font-size:16px; line-height:1.5;"><p>Built by <b>Stone Digital</b>, If you have any issues <br/>please contact us on <a style="color: #333;" href="mailto:support@stonedigital.com.au"><b>support@stonedigital.com.au</b></a></p></div>';
	}

	/**
	 * Disable all notices

	 * @since    1.1.0
	 * @access   public
	 */
	public function stonedigital_plugin_disable_all_admin_notices() {
		remove_all_actions('admin_notices');
	}
}