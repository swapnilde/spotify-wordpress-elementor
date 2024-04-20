<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://swapnild.com
 * @since             1.0.0
 * @package           PulseShare
 *
 * @wordpress-plugin
 * Plugin Name:       PulseShare
 * Plugin URI:        https://swapnild.com
 * Description:       PulseShare help you share interactive content from Spotify on your website. Embed podcast, an album, or other audio and video content to your website and promote your music, share your new podcast episodes with fans, or highlight your favourite album or playlist.
 * Version:           1.0.1
 * Author:            Swapnil Deshpande
 * Author URI:        https://swapnild.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pulseshare
 * Domain Path:       /languages
 * Elementor tested up to: 3.21.1
 * Elementor Pro tested up to: 3.21.0
 */

use PulseShare\Classes\PulseShare;
use PulseShare\Classes\PulseShareActivator;
use PulseShare\Classes\PulseShareDeactivator;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'PULSESHARE_VERSION', '1.0.1' );
define( 'PULSESHARE_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'PULSESHARE_URLPATH', plugin_dir_url( __FILE__ ) );

// PulseShare autoloader.
require_once PULSESHARE_DIRPATH . 'includes/autoloader.php';

/**
 * The code that runs during plugin activation.
 */
function activate_pulseshare() {
	PulseShareActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_pulseshare() {
	PulseShareDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pulseshare' );
register_deactivation_hook( __FILE__, 'deactivate_pulseshare' );

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_pulseshare() {

	$plugin = PulseShare::get_instance();
	$plugin->run();
}
run_pulseshare();
