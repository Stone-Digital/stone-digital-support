<?php

namespace Std_Support\Includes\Helpers;

defined( 'ABSPATH' ) || exit; // disable direct access


/**
 * Auto loader function.
 *
 * @param string $resource Source namespace.
 *
 * @return void
 * @package Support @ Stone Digital
 * @since 1.1.0
 */

function autoloader( $resource = '' ) {
	$resource_path  = false;
	$namespace_root = 'Std_Support\\';
	$resource       = trim( $resource, '\\' );

	if ( empty( $resource ) || strpos( $resource, '\\' ) === false || strpos( $resource, $namespace_root ) !== 0 ) {
		// Not our namespace, bail out.
		return;
	}

	// Remove our root namespace.
	$resource = str_replace( $namespace_root, '', $resource );

	$path = explode(
		'\\',
		str_replace( '_', '-', strtolower( $resource ) )
	);

	/**
	 * Time to determine which type of resource path it is,
	 * so that we can deduce the correct file path for it.
	 */
	if ( empty( $path[0] ) || empty( $path[1] ) ) {
		return;
	}
// echo "<pre>";
// 	print_r($path);
// 	echo "</pre>";
	$directory = '';
	$file_name = '';

	if ( 'includes' === $path[0] ) {

		switch ( $path[1] ) {
			case 'traits':
				$directory = 'traits';
				$file_name = sprintf( 'trait-%s', trim( strtolower( $path[2] ) ) );
				break;

			case 'admin':
				$directory = 'admin';
				$file_name = sprintf( 'class-%s', trim( strtolower( $path[2] ) ) );
				// print_r($file_name);
				break;

			case 'widgets':
			case 'blocks': // phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.TerminatingComment
				/**
				 * If there is class name provided for specific directory then load that.
				 * otherwise find in includes/ directory.
				 */
				if ( ! empty( $path[2] ) ) {
					$directory = sprintf( 'classes/%s', $path[1] );
					$file_name = sprintf( 'class-%s', trim( strtolower( $path[2] ) ) );
					break;
				}
			default:
				$directory = 'classes';
				$file_name = sprintf( 'class-%s', trim( strtolower( $path[1] ) ) );
				break;
		}

		$resource_path = sprintf( '%s/includes/%s/%s.php', untrailingslashit( STD_ROOT_DIR_PATH ), $directory, $file_name );

	}

	/**
	 * If $is_valid_file has 0 means valid path or 2 means the file path contains a Windows drive path.
	 */
	$is_valid_file = validate_file( $resource_path );

	if ( ! empty( $resource_path ) && file_exists( $resource_path ) && ( 0 === $is_valid_file || 2 === $is_valid_file ) ) {
		// We already making sure that file is exists and valid.
		require_once( $resource_path ); // phpcs:ignore
		// echo "<pre>";
		// print_r($resource_path);
		// echo "</pre>";
	}



}

spl_autoload_register( '\Std_Support\Includes\Helpers\autoloader' );