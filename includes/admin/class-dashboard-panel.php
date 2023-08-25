<?php 

namespace Std_Support\Includes\Admin;

use Std_Support\Includes\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stone Digital Dashboard 
 *
 * @package     Support @ Stone Digital
 * @author      Stone Digital
 * @link        https://stonedigital.com.au
 * @since       1.1.0
 */

class Dashboard_Panel {

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

        remove_action( 'welcome_panel', 'wp_welcome_panel' );
        // add_action( 'welcome_panel', array( $this,  'stone_digital_dashboard_markup' ) );
        add_action( 'admin_menu', array( $this, 'add_welcome_page' ), 20);
        add_action( 'admin_init', array( $this, 'register_settings'), 10);

		if (check_user_email_domain()) {
        	add_action( 'admin_menu', array( $this, 'add_submenu_settings_page' ), 22 );
		}

        add_action( 'admin_init',  array( $this, 'redirect_dashboard_index' ) );
        add_action( 'admin_enqueue_scripts', array($this, 'load_admin_style') );

		add_action( 'admin_menu', array( $this, 'add_plugins_list_submenu_page' ), 20);

		new User_Log_details();
	}

    /**
     * Stone Digital Settings Submenu Page
     */
    public function add_welcome_page() {
	    add_menu_page(
            esc_html__( 'Stone Digital', 'stone-digital-support' ),
            esc_html__( 'Stone Digital', 'stone-digital-support' ),
			'manage_options', 
			'stone-digital-support-plugin', 
			array( $this, 'dashboard_welcome_markup' ), 
			'dashicons-chart-area', 
			2.3 // Right after dashboard.
		);
	}

    /**
     * Stone Digital Welcome Templates page.
	 * @since 1.0.0
     */
    public function add_submenu_settings_page() {
        add_submenu_page(
            'stone-digital-support-plugin',
            esc_html__( 'Settings', 'stone-digital-support' ),
            esc_html__( 'Settings', 'stone-digital-support' ),
            'manage_options',
           'stone-digital-support-settings',
            [$this, 'settings_page_html']
        );
    }

    /**
	 * Return Ip Address when User login
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
    public function dashboard_welcome_markup() {
        ?>
      <div class="sd-dashboard-main-wrapper">

            <div class="sd-dashboard-header-wrapper">
                <div class="sd-dashboard-header-left">
                    <img src="<?php echo STD_PLUGIN_URL . 'assets/img/stone-digital-logo.png'; ?>">
                </div>
                <div class="sd-dashboard-header-right">
                   <a href="https://stonedigital.com.au/" target="_blank">Learn More About Stone Digital</a>
                </div>
            </div>

            <div class="sd-dashboard-wrapper">
                <h2>Thank you for choosing Stone Digital as your trusted partner!</h2>
                
                <div class="sd-dashboard-description-wrapper">
              
                    <p>Welcome to your personalized WordPress dashboard, providing you with seamless access to all the essential options to edit, configure, and update your website.</p>
                    <p>At Stone Digital, our mission is to create highly customizable client websites that empower you to effortlessly manage your online presence through the WordPress dashboard.</p>
                    <p>Should you require any guidance on utilizing the WordPress dashboard or wish to explore additional features, our dedicated team is here to assist you. We are committed to ensuring your experience is smooth and successful.</p>
                    <p>We appreciate the opportunity to work with you, and we look forward to helping you unlock the full potential of your website.</p>
                    <h5>Best regards, The Stone Digital Team</h5>
                </div>
                
        </div>
   
        <?php
    }
    
	/**
	 * Return Settings page html
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
    public function settings_page_html() {
		// check user capabilities
	    if ( ! current_user_can( 'administrator' ) ) {
	        return;
	    }

	    // add error/update messages
	    // check if the user have submitted the settings
	    // WordPress will add the "settings-updated" $_GET parameter to the url
	    if ( isset( $_GET['settings-updated'] ) ) {
	        // add settings saved message with the class of "updated"
	        add_settings_error('lhr_wc_rentals_messages', 'lhr_wc_rentals_message', 'Plugin Settings Saved', 'updated');
	    }

	    // show error/update messages
	    settings_errors('lhr_wc_rentals_messages');
	    ?>
	    <div class="std-wrap__settings-page">
		
	    <h2>Stone Digital - Support Plugin</h2>
			<form action="options.php" method="post">
				<?php 
				settings_fields( 'stonedigital_plugin_options' );
				do_settings_sections( 'stone-digital-support-plugin' );
				submit_button();
				?>
			</form>
	    </div>
	    <?php
	}

    /**
	 * Return Settings Register page html
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
	public function register_settings() {
	    register_setting( 'stonedigital_plugin_options', 'stonedigital_plugin_disable_notices', 'sanitize_checkbox');
	    register_setting( 'stonedigital_plugin_options', 'stonedigital_plugin_show_notices', 'sanitize_checkbox' );
	    register_setting( 'stonedigital_plugin_options', 'stonedigital_plugin_dev_mode', 'sanitize_checkbox' );
	    register_setting( 'stonedigital_plugin_options', 'stonedigital_plugin_slack_alert_all_user', 'sanitize_checkbox' );
	    register_setting( 'stonedigital_plugin_options', 'stonedigital_plugin_slack_alert_for_admin', 'sanitize_checkbox' );
	    add_settings_section(
	        'stonedigital_plugin_notices_section',
	        'Notices', 
	        '',
	        'stone-digital-support-plugin'
	    );

	    // Register a new field in the "wporg_section_developers" section, inside the "wporg" page.
	    add_settings_field(
	        'stonedigital_plugin_disable_notices',
	        'Disable all notices',
	        array($this, 'settings_field_callback'),
	        'stone-digital-support-plugin',
	        'stonedigital_plugin_notices_section',
	        array(
	        	'id' => 'disable_notices',
	        	'label_for' => 'disable_notices'
	        )
	    );

		// Register a new field in the "wporg_section_developers" section, inside the "wporg" page.
	    add_settings_field(
	        'stonedigital_plugin_dev_mode',
	        'Developer Mode',
	        array($this, 'settings_field_callback'),
	        'stone-digital-support-plugin',
	        'stonedigital_plugin_notices_section',
	        array(
	        	'id' => 'dev_mode',
	        	'label_for' => 'dev_mode'
	        )
	    ); 
		
		add_settings_field(
	        'stonedigital_plugin_slack_alert_all_user',
	        'Slack Alert For all user logins',
	        array($this, 'settings_field_callback'),
	        'stone-digital-support-plugin',
	        'stonedigital_plugin_notices_section',
	        array(
	        	'id' => 'slack_alert_all_user',
	        	'label_for' => 'slack_alert_all_user'
	        )
	    );	
		
		add_settings_field(
	        'stonedigital_plugin_slack_alert_for_admin',
	        'Only alert for Admin logins',
	        array($this, 'settings_field_callback'),
	        'stone-digital-support-plugin',
	        'stonedigital_plugin_notices_section',
	        array(
	        	'id' => 'slack_alert_for_admin',
	        	'label_for' => 'slack_alert_for_admin'
	        )
	    );

	    // add_settings_field(
	    //     'stonedigital_plugin_show_notices',
	    //     'Show all notices',
	    //     array($this, 'settings_field_callback'),
	    //     'stone-digital-support-plugin',
	    //     'stonedigital_plugin_notices_section',
	    //     array(
	    //     	'id' => 'show_notices',
	    //     	'label_for' => 'show_notices'
	    //     )
	    // );
	}

    /**
     * Get the value of a settings field
     *
     * @param string  $option  settings field name
     * @param string  $section the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */
    public function std_get_option( $option, $section, $default = '' ) {

        $options = get_option( $section );

        if ( isset( $options[$option] ) ) {
            return $options[$option];
        }

        return $default;
    }

 	/**
	 * Settings Register fields html
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
	public function settings_field_callback($args) {
		$field_id = $args['label_for'];
		$options = get_option("stonedigital_plugin_{$field_id}");
	 
		$id = "stonedigital_plugin_{$field_id}";
	    $field_value = $id;
		
        $html  = '<fieldset class="stdoptions_settings_checkbox">';
        $html  .= sprintf( '<input type="hidden" name="%1$s" value="0" />', $id );
        $html  .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s" name="%2$s" value="1" %3$s />', $id, $id, checked(1, $options, false ) );
        $html  .= sprintf( '<label for="%1$s"></label>', $id );
        $html  .= '</fieldset>';

        echo $html;
	}

	/**
	 * Redirect dashboard welcome page
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
    public function redirect_dashboard_index() {
        if (is_admin() && basename($_SERVER['PHP_SELF']) == 'index.php' && empty($_GET)) {
            wp_safe_redirect( admin_url( 'admin.php?page=stone-digital-support-plugin' ) );;
            exit;
        }
    }

	/**
	 * Load asstes for support plugin 
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
    public function load_admin_style() {
	    wp_enqueue_style('stone-digital-support-custom', STD_PLUGIN_URL . 'assets/css/custom.css', array(), time(), 'all' );
	    wp_enqueue_script('stone-digital-support-dashboard', STD_PLUGIN_URL . 'assets/js/dashboard.js', array('jquery', 'common'), time(), true);
	}

	/**
	 * Showing list of plugin active / Inactive
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
	public function add_plugins_list_submenu_page() {
		add_submenu_page(
			'plugins.php', 
			'Plugins Table', 
			'Plugins Table', 
			'edit_plugins', 
			'stone-digital-support-plugins-table',
			array( $this, 'display_plugin_admin_dashboard' ),
		);
	}

	/**
	 * Showing list of plugin active / Inactive html
     * @since 1.0.0
	 * @param array
	 * @return string
	 */
	public function display_plugin_admin_dashboard() {
		// Assuming that plugins have properly named folders:
	    $all_plugins = get_plugins();
	    $plugin_updates = get_site_transient( 'update_plugins' );

	    echo "<pre>";
	    echo str_pad("", 81, '+') . "<br>";

	    foreach ( $all_plugins as $plugin_file => $single_plugin ){
	        // $slug = str_pad($single_plugin['TextDomain'], 90, " ")
	        $name = str_pad($single_plugin['TextDomain'], 50, '~');
	        if($single_plugin['TextDomain'] == "") {
	            $plugin_slug = sanitize_title($single_plugin['Name']);
	            $name = str_pad($plugin_slug, 50, '~');
	        }

	        if( is_plugin_active($plugin_file) ) {
	            $status = str_pad("Active", 10, '~');
	        } else {
	            $status = str_pad("Inactive", 10, '~');
	        }
	        
	        if ( isset( $plugin_updates->response[ $plugin_file ] ) ) {
	            $update = str_pad("Available", 10, '~');
	        } else {
	            $update = str_pad("None", 10, '~');
	        }
	        $version = str_pad($single_plugin['Version'], 8, '~');

	        $plugin_html_line = implode("|", array($name, $status, $update, $version)) . "<br>";
	        $plugin_html_line = str_replace("~", " ", $plugin_html_line);
	        echo $plugin_html_line;
	    }
	    echo str_pad("", 81, '+');
	    echo "</pre>";
	}

}