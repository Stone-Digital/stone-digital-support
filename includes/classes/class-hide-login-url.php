<?php 
namespace Std_Support\Includes;

use Std_Support\Includes\Traits\Singleton;

defined( 'ABSPATH' ) || exit; // disable direct access


class Hide_Login_Url {

    use Singleton;
    
    private $wp_login_php;

	/**
	 * Constructor.
	 *
	 * Creates the instances of this class.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function __construct() {

		global $wp_version;
     
        if ( version_compare( $wp_version, '5.0', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices_incompatible' ) );
			add_action( 'network_admin_notices', array( $this, 'admin_notices_incompatible' ) );

			return;
		}

        
		if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		}

		if ( is_plugin_active_for_network( 'rename-wp-login/rename-wp-login.php' ) ) {
			deactivate_plugins( STD_BASENAME );
			add_action( 'network_admin_notices', array( $this, 'admin_notices_plugin_conflict' ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			return;
		}

		if ( is_plugin_active( 'rename-wp-login/rename-wp-login.php' ) ) {
			deactivate_plugins( STD_BASENAME );
			add_action( 'admin_notices', array( $this, 'admin_notices_plugin_conflict' ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			return;
		}

        if ( is_multisite() && is_plugin_active_for_network( STD_BASENAME ) ) {
		// 	add_action( 'wpmu_options', array( $this, 'wpmu_options' ) );
			add_action( 'std_update_wpmu_options', array( $this, 'std_update_wpmu_options' ) );

		// 	add_filter( 'network_admin_plugin_action_links_' . STD_BASENAME, array(
		// 		$this,
		// 		'plugin_action_links'
		// 	) );
		}

		if ( is_multisite() ) {
			add_action( 'wp_before_admin_bar_render', array( $this, 'modify_mysites_menu' ), 999 );
		}
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 9999 );

		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );

        add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );

        add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );

		remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );

		add_action( 'template_redirect', array( $this, 'redirect_export_data' ) );

        add_filter( 'login_url', array( $this, 'login_url' ), 10, 3 );

        add_filter( 'user_request_action_email_content', array( $this, 'user_request_action_email_content' ), 999, 2 );

		add_filter( 'site_status_tests', array( $this, 'site_status_tests' ) );
	}

    public function std_update_wpmu_options() {
		if ( ! empty( $_POST ) && check_admin_referer( 'siteoptions' ) ) {
			if ( ( $stonedigital_plugin_hide_login_url_name = sanitize_title_with_dashes( $_POST['stonedigital_plugin_hide_login_url_name'] ) )
			     && strpos( $stonedigital_plugin_hide_login_url_name, 'wp-login' ) === false
			     && ! in_array( $stonedigital_plugin_hide_login_url_name, $this->forbidden_slugs() ) ) {

				flush_rewrite_rules( true );
				update_site_option( 'stonedigital_plugin_hide_login_url_name', $stonedigital_plugin_hide_login_url_name );


			}
			if ( ( $stonedigital_plugin_hide_redirection_url_name = sanitize_title_with_dashes( $_POST['stonedigital_plugin_hide_redirection_url_name'] ) )
			     && strpos( $stonedigital_plugin_hide_redirection_url_name, '404' ) === false ) {

				flush_rewrite_rules( true );
				update_site_option( 'stonedigital_plugin_hide_redirection_url_name', $stonedigital_plugin_hide_redirection_url_name );

			}
		}
	}

	public function test_echo() {
		return "stundent return working";
	}

    public function site_status_tests( $tests ) {
		unset( $tests['async']['loopback_requests'] );

		return $tests;
	}

	public function user_request_action_email_content( $email_text, $email_data ) {
		$email_text = str_replace( '###CONFIRM_URL###', esc_url_raw( str_replace( $this->new_login_slug() . '/', 'wp-login.php', $email_data['confirm_url'] ) ), $email_text );

		return $email_text;
	}
    
	public function admin_notices_incompatible() {

		echo '<div class="error notice is-dismissible"><p>' . __( 'Please upgrade to the latest version of WordPress to activate', 'stone-digital-support' ) . ' <strong>' . __( 'WPS Hide Login', 'stone-digital-support' ) . '</strong>.</p></div>';

	}

	public function admin_notices_plugin_conflict() {

		echo '<div class="error notice is-dismissible"><p>' . __( 'WPS Hide Login could not be activated because you already have Rename wp-login.php active. Please uninstall rename wp-login.php to use WPS Hide Login', 'stone-digital-support' ) . '</p></div>';

	}

    
	private function use_trailing_slashes() {

		return ( '/' === substr( get_option( 'permalink_structure' ), - 1, 1 ) );

	}

	private function user_trailingslashit( $string ) {

		return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );

	}
    
	private function wp_template_loader() {

		global $pagenow;

		$pagenow = 'index.php';

		if ( ! defined( 'WP_USE_THEMES' ) ) {

			define( 'WP_USE_THEMES', true );

		}

		wp();

		require_once( ABSPATH . WPINC . '/template-loader.php' );

		die;

	}

	public function modify_mysites_menu() {
		global $wp_admin_bar;

		$all_toolbar_nodes = $wp_admin_bar->get_nodes();

		foreach ( $all_toolbar_nodes as $node ) {
			if ( preg_match( '/^blog-(\d+)(.*)/', $node->id, $matches ) ) {
				$blog_id = $matches[1];
				if ( $login_slug = $this->new_login_slug( $blog_id ) ) {
					if ( ! $matches[2] || '-d' === $matches[2] ) {
						$args       = $node;
						$old_href   = $args->href;
						$args->href = preg_replace( '/wp-admin\/$/', "$login_slug/", $old_href );
						if ( $old_href !== $args->href ) {
							$wp_admin_bar->add_node( $args );
						}
					} elseif ( strpos( $node->href, '/wp-admin/' ) !== false ) {
						$wp_admin_bar->remove_node( $node->id );
					}
				}
			}
		}
	}

	private function new_login_slug( $blog_id = '' ) {
		if ( $blog_id ) {
			if ( $slug = get_blog_option( $blog_id, 'stonedigital_plugin_hide_login_url_name' ) ) {
				return $slug;
			}
		} else {
			if ( $slug = get_option( 'stonedigital_plugin_hide_login_url_name' ) ) {
				return $slug;
			} else if ( ( is_multisite() && is_plugin_active_for_network( STD_BASENAME ) && ( $slug = get_site_option( 'stonedigital_plugin_hide_login_url_name', 'login' ) ) ) ) {
				return $slug;
			} else if ( $slug = 'login' ) {
				return $slug;
			}
		}
	}

	private function new_redirect_slug() {
		if ( $slug = get_option( 'stonedigital_plugin_hide_redirection_url_name' ) ) {
			return $slug;
		} else if ( ( is_multisite() && is_plugin_active_for_network( STD_BASENAME ) && ( $slug = get_site_option( 'stonedigital_plugin_hide_redirection_url_name', '404' ) ) ) ) {
			return $slug;
		} else if ( $slug = '404' ) {
			return $slug;
		}
	}

	public function new_login_url( $scheme = null ) {

		$url = apply_filters( 'std_hide_login_home_url', home_url( '/', $scheme ) );

		if ( get_option( 'permalink_structure' ) ) {

			return $this->user_trailingslashit( $url . $this->new_login_slug() );

		} else {

			return $url . '?' . $this->new_login_slug();

		}

	}

	public function new_redirect_url( $scheme = null ) {

		if ( get_option( 'permalink_structure' ) ) {

			return $this->user_trailingslashit( home_url( '/', $scheme ) . $this->new_redirect_slug() );

		} else {

			return home_url( '/', $scheme ) . '?' . $this->new_redirect_slug();

		}

	}

    public function plugins_loaded() {

		global $pagenow;

		if ( ! is_multisite()
		     && ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-signup' ) !== false
		          || strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-activate' ) !== false ) && apply_filters( 'std_hide_login_signup_enable', false ) === false ) {

			wp_die( __( 'This feature is not enabled.', 'stone-digital-support' ) );

		}

		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

		if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
		       || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) ) )
		     && ! is_admin() ) {

			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';

		} elseif ( ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' ) )
		           || ( ! get_option( 'permalink_structure' )
		                && isset( $_GET[ $this->new_login_slug() ] )
		                && empty( $_GET[ $this->new_login_slug() ] ) ) ) {

			$_SERVER['SCRIPT_NAME'] = $this->new_login_slug();

			$pagenow = 'wp-login.php';

		} elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
		             || ( isset( $request['path'] ) && untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) ) )
		           && ! is_admin() ) {

			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';
		}

	}

	public function setup_theme() {
		global $pagenow;

		if ( ! is_user_logged_in() && 'customize.php' === $pagenow ) {
			wp_die( __( 'This has been disabled', 'stone-digital-support' ), 403 );
		}
	}

	public function redirect_export_data() {
		if ( ! empty( $_GET ) && isset( $_GET['action'] ) && 'confirmaction' === $_GET['action'] && isset( $_GET['request_id'] ) && isset( $_GET['confirm_key'] ) ) {
			$request_id = (int) $_GET['request_id'];
			$key        = sanitize_text_field( wp_unslash( $_GET['confirm_key'] ) );
			$result     = wp_validate_user_request_key( $request_id, $key );
			if ( ! is_wp_error( $result ) ) {
				wp_redirect( add_query_arg( array(
					'action'      => 'confirmaction',
					'request_id'  => $_GET['request_id'],
					'confirm_key' => $_GET['confirm_key']
				), $this->new_login_url()
				) );
				exit();
			}
		}
	}

	public function wp_loaded() {

		global $pagenow;

		$request = parse_url( rawurldecode( $_SERVER['REQUEST_URI'] ) );

		do_action( 'std_hide_login_before_redirect', $request );

		if ( ! ( isset( $_GET['action'] ) && $_GET['action'] === 'postpass' && isset( $_POST['post_password'] ) ) ) {

			if ( is_admin() && ! is_user_logged_in() && ! defined( 'WP_CLI' ) && ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' ) && $pagenow !== 'admin-post.php' && $request['path'] !== '/wp-admin/options.php' ) {
				wp_safe_redirect( $this->new_redirect_url() );
				die();
			}

			if ( ! is_user_logged_in() && isset( $_GET['wc-ajax'] ) && $pagenow === 'profile.php' ) {
				wp_safe_redirect( $this->new_redirect_url() );
				die();
			}

			if ( ! is_user_logged_in() && isset( $request['path'] ) && $request['path'] === '/wp-admin/options.php' ) {
				header('Location: ' . $this->new_redirect_url() );
				die;
			}

			if ( $pagenow === 'wp-login.php' && isset( $request['path'] ) && $request['path'] !== $this->user_trailingslashit( $request['path'] ) && get_option( 'permalink_structure' ) ) {
				wp_safe_redirect( $this->user_trailingslashit( $this->new_login_url() )
				                  . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

				die;

			} elseif ( $this->wp_login_php ) {

				if ( ( $referer = wp_get_referer() )
				     && strpos( $referer, 'wp-activate.php' ) !== false
				     && ( $referer = parse_url( $referer ) )
				     && ! empty( $referer['query'] ) ) {

					parse_str( $referer['query'], $referer );

					@require_once WPINC . '/ms-functions.php';

					if ( ! empty( $referer['key'] )
					     && ( $result = wpmu_activate_signup( $referer['key'] ) )
					     && is_wp_error( $result )
					     && ( $result->get_error_code() === 'already_active'
					          || $result->get_error_code() === 'blog_taken' ) ) {

						wp_safe_redirect( $this->new_login_url()
						                  . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

						die;

					}

				}

				$this->wp_template_loader();

			} elseif ( $pagenow === 'wp-login.php' ) {
				global $error, $interim_login, $action, $user_login;

				$redirect_to = admin_url();

				$requested_redirect_to = '';
				if ( isset( $_REQUEST['redirect_to'] ) ) {
					$requested_redirect_to = $_REQUEST['redirect_to'];
				}

				if ( is_user_logged_in() ) {
					$user = wp_get_current_user();
					if ( ! isset( $_REQUEST['action'] ) ) {
						$logged_in_redirect = apply_filters( 'std_logged_in_redirect', $redirect_to, $requested_redirect_to, $user );
						wp_safe_redirect( $logged_in_redirect );
						die();
					}
				}

				@require_once ABSPATH . 'wp-login.php';

				die;

			}

		}

	}

	
	/**
	 * Load scripts
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'options-general.php' != $hook ) {
			return false;
		}

		wp_enqueue_style( 'plugin-install' );

		wp_enqueue_script( 'plugin-install' );
		wp_enqueue_script( 'updates' );
		add_thickbox();
	}

	public function site_url( $url, $path, $scheme, $blog_id ) {

		return $this->filter_wp_login_php( $url, $scheme );

	}

	public function network_site_url( $url, $path, $scheme ) {

		return $this->filter_wp_login_php( $url, $scheme );

	}

	public function wp_redirect( $location, $status ) {

		if ( strpos( $location, 'https://wordpress.com/wp-login.php' ) !== false ) {
			return $location;
		}

		return $this->filter_wp_login_php( $location );

	}

	public function filter_wp_login_php( $url, $scheme = null ) {

		if ( strpos( $url, 'wp-login.php?action=postpass' ) !== false ) {
			return $url;
		}

		if ( strpos( $url, 'wp-login.php' ) !== false && strpos( wp_get_referer(), 'wp-login.php' ) === false ) {

			if ( is_ssl() ) {

				$scheme = 'https';

			}

			$args = explode( '?', $url );

			if ( isset( $args[1] ) ) {

				parse_str( $args[1], $args );

				if ( isset( $args['login'] ) ) {
					$args['login'] = rawurlencode( $args['login'] );
				}

				$url = add_query_arg( $args, $this->new_login_url( $scheme ) );

			} else {

				$url = $this->new_login_url( $scheme );

			}

		}

		return $url;

	}
    
    public function forbidden_slugs() {

		$wp = new \WP;

		return array_merge( $wp->public_query_vars, $wp->private_query_vars );

	}

    /**
	 *
	 * Update url redirect : wp-admin/options.php
	 *
	 * @param $login_url
	 * @param $redirect
	 * @param $force_reauth
	 *
	 * @return string
	 */
	public function login_url( $login_url, $redirect, $force_reauth ) {
		if ( is_404() ) {
			return '#';
		}

		if ( $force_reauth === false ) {
			return $login_url;
		}

		if ( empty( $redirect ) ) {
			return $login_url;
		}

		$redirect = explode( '?', $redirect );

		if ( $redirect[0] === admin_url( 'options.php' ) ) {
			$login_url = admin_url();
		}

		return $login_url;
	}

}