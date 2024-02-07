<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://swapnild.com
 * @since      1.0.0
 * @package    PulseShare
 */

namespace PulseShare\Admin;

use PulseShare\Classes\PulseShareLoader;
use PulseShare\includes\Helper;
use PulseShare\Widgets\AlbumWidget;
use PulseShare\Widgets\PodcastWidget;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The admin-specific functionality of the plugin.
 */
class PulseShareAdmin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 * @var string Minimum Elementor version required to run the addon.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.7.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 * @var string Minimum PHP version required to run the addon.
	 */
	const MINIMUM_PHP_VERSION = '7.3';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      PulseShareLoader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->loader      = PulseShareLoader::get_instance();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, PULSESHARE_URLPATH . 'assets/admin/css/pulseshare-wordpress-elementor-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			$this->plugin_name . '-manifest',
			PULSESHARE_URLPATH . 'assets/manifest.js',
			array(),
			$this->version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_enqueue_script(
			$this->plugin_name . '-vendor',
			PULSESHARE_URLPATH . 'assets/vendor.js',
			array(),
			$this->version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_enqueue_script(
			$this->plugin_name,
			PULSESHARE_URLPATH . 'assets/admin/js/pulseshare-wordpress-elementor-admin.js',
			array( 'jquery' ),
			$this->version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_localize_script(
			$this->plugin_name,
			'PulseShareAdminVars',
			array(
				'home_url'           => get_home_url(),
				'site_url'           => esc_url_raw( get_site_url() ),
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'rest_url'           => esc_url_raw( get_rest_url() ),
				'user'               => wp_get_current_user(),
				'user_avatar'        => get_avatar_url( wp_get_current_user()->ID ),
				'pulseshare_options' => array(
					'client_id'     => Helper::check_pulseshareapi_keys_empty() ? '' : get_option( 'pulseshare_options' )['pulseshare_client_id'],
					'client_secret' => Helper::check_pulseshareapi_keys_empty() ? '' : get_option( 'pulseshare_options' )['pulseshare_client_secret'],
					'show_id'       => get_option( 'pulseshare_options' )['pulseshare_show_id'],
					'album_id'      => get_option( 'pulseshare_options' )['pulseshare_album_id'],
				),
			)
		);
	}

	/**
	 * Display notice if the pulseshare client id and secret are empty.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return void
	 */
	public function pulseshareapi_keys_empty_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php
				printf(
					/* translators: 1: Plugin name 2: Settings page link */
					esc_html__( '%1$sPlease set the Spotify Client ID and Client Secret in the %2$s.', 'pulseshare' ),
					sprintf(
						'<strong>%1$s</strong>',
						esc_html__( 'PulseShare: ', 'pulseshare' )
					),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( admin_url( 'admin.php?page=pulseshare-options-panel' ) ),
						esc_html__( 'settings page', 'pulseshare' )
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add block categories.
	 *
	 * @param array $block_categories Array of categories.
	 * @since    1.0.0
	 * @return array Array of categories.
	 */
	public function add_block_categories( $block_categories ) {
		$block_categories[] = array(
			'slug'  => 'pulseshare',
			'title' => __( 'Spotify For Wordpress', 'pulseshare' ),
		);

		return $block_categories;
	}

	/**
	 * Register block script.
	 *
	 * @since    1.0.0
	 */
	public function register_block_script() {
		if ( ! Helper::check_pulseshareapi_keys_empty() ) {
			register_block_type( PULSESHARE_DIRPATH . 'assets/admin/blocks/list-embed' );
			register_block_type( PULSESHARE_DIRPATH . 'assets/admin/blocks/album-embed' );
		}
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_missing_elementor_plugin() {

		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
					printf(
					/* translators: 1: Plugin name 2: Elementor */
						esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'pulseshare' ),
						sprintf(
							'<strong>%1$s</strong>',
							esc_html__( 'PulseShare', 'pulseshare' )
						),
						sprintf(
							'<strong>%1$s</strong>',
							esc_html__( 'Elementor', 'pulseshare' )
						)
					);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'pulseshare' ),
			'<strong>' . esc_html__( 'PulseShare', 'pulseshare' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'pulseshare' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', esc_html( $message ) );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'pulseshare' ),
			'<strong>' . esc_html__( 'PulseShare', 'pulseshare' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'pulseshare' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', esc_html( $message ) );
	}

	/**
	 * Initialize the widgets.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void Register widgets.
	 */
	public function init_widgets() {
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register Widgets
	 *
	 * Load widgets files and register new Elementor widgets.
	 *
	 * Fired by `elementor/widgets/register` action hook.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {

		if ( ! Helper::check_pulseshareapi_keys_empty() || did_action( 'elementor/loaded' ) ) {
			$widgets_manager->register( new PodcastWidget() );
			$widgets_manager->register( new AlbumWidget() );
		}
	}
}
