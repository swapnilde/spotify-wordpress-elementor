<?php
/**
 * Provide a class autoloader.
 *
 * @link       https://swapnild.com
 * @since      1.0.0
 * @package    PulseShare
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

spl_autoload_register( 'pulseshare_namespace_autoload' );

/**
 * Callback for autoloader.
 *
 * @param string $class_name The class name.
 */
function pulseshare_namespace_autoload( $class_name ) {

	// If the specified $class_name does not include our namespace, duck out.
	if ( ! str_contains( $class_name, 'PulseShare' ) ) {
		return;
	}

	// Split the class name into an array to read the namespace and class.
	$file_parts = explode( '\\', $class_name );

	// Do a reverse loop through $file_parts to build the path to the file.
	$namespace = '';
	$file_name = '';
	for ( $i = count( $file_parts ) - 1; $i > 0; $i-- ) {
		// Read the current component of the file part.
		$current = $file_parts[ $i ];
		// If we're at the first entry, then we're at the filename.
		if ( count( $file_parts ) - 1 === $i ) {
			$file_name = "$current.php";
		} else {
			$namespace = '/' . strtolower( $current ) . $namespace;
		}
	}

	// Now build a path to the file using mapping to the file location.
	$filepath  = trailingslashit( dirname( __DIR__, 1 ) . $namespace );
	$filepath .= $file_name;

	// If the file exists in the specified path, then include it.
	if ( file_exists( $filepath ) ) {
		include_once $filepath;
	} else {
		wp_die(
			esc_html( "The file attempting to be loaded at $filepath does not exist." )
		);
	}
}
