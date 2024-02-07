<?php
/**
 * Class OptionsPanel
 *
 * @link https://swapnild.com
 * @since 1.0.0
 * @package PulseShare
 */

namespace PulseShare\Includes\Options;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Bootstrap class for the PulseShare Options pages.
 *
 * @since 1.0.0
 */
class OptionsPanel {

	/**
	 * Options panel arguments.
	 *
	 * @var array $args
	 * @since 1.0.0
	 */
	protected $args = array();

	/**
	 * Options panel title.
	 *
	 * @var string $title
	 * @since 1.0.0
	 */
	protected $title = '';

	/**
	 * Options panel slug.
	 *
	 * @var string $slug
	 * @since 1.0.0
	 */
	protected $slug = '';

	/**
	 * Option name to use for saving our options in the database.
	 *
	 * @var string $option_name
	 * @since 1.0.0
	 */
	protected $option_name = '';

	/**
	 * Option group name.
	 *
	 * @var string $option_group_name
	 * @since 1.0.0
	 */
	protected $option_group_name = '';

	/**
	 * User capability allowed to access the options page.
	 *
	 * @var string $user_capability
	 * @since 1.0.0
	 */
	protected $user_capability = '';

	/**
	 * Icon URL.
	 *
	 * @var string $icon_url
	 * @since 1.0.0
	 */
	protected $icon_url = '';

	/**
	 * Position.
	 *
	 * @var string $position
	 * @since 1.0.0
	 */
	protected $position = '';

	/**
	 * Our array of settings.
	 *
	 * @var array $settings
	 * @since 1.0.0
	 */
	protected $settings = array();

	/**
	 * Our class constructor.
	 *
	 * @param array $args     Array of arguments.
	 * @param array $settings Array of settings.
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( array $args, array $settings ) {
		$this->args              = $args;
		$this->settings          = $settings;
		$this->title             = $this->args['title'] ?? esc_html__( 'Options', 'pulseshare' );
		$this->slug              = $this->args['slug'] ?? sanitize_key( $this->title );
		$this->option_name       = $this->args['option_name'] ?? sanitize_key( $this->title );
		$this->option_group_name = $this->option_name . '_group';
		$this->user_capability   = $this->args['user_capability'] ?? 'manage_options';
		$this->icon_url          = $this->args['icon_url'] ?? '';
		$this->position          = $this->args['position'] ? $this->args['position'] : null;

		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the new menu page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_menu_page() {
		add_menu_page(
			$this->title,
			$this->title,
			$this->user_capability,
			$this->slug,
			array( $this, 'render_options_page' ),
			$this->icon_url,
			$this->position
		);
	}

	/**
	 * Register the settings.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_settings() {
		register_setting(
			$this->option_group_name,
			$this->option_name,
			array(
				'sanitize_callback' => array( $this, 'sanitize_fields' ),
				'default'           => $this->get_defaults(),
			)
		);

		add_settings_section(
			$this->option_name . '_sections',
			false,
			false,
			$this->option_name
		);

		foreach ( $this->settings as $key => $args ) {
			$type     = $args['type'] ?? 'text';
			$callback = "render_{$type}_field";
			if ( method_exists( $this, $callback ) ) {
				$tr_class = '';
				if ( array_key_exists( 'tab', $args ) ) {
					$tr_class .= 'wpex-tab-item wpex-tab-item--' . sanitize_html_class( $args['tab'] );
				}
				add_settings_field(
					$key,
					$args['label'],
					array( $this, $callback ),
					$this->option_name,
					$this->option_name . '_sections',
					array(
						'label_for' => $key,
						'class'     => $tr_class,
					)
				);
			}
		}
	}

	/**
	 * Returns default values.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Default values.
	 */
	protected function get_defaults() {
		$defaults = array();
		foreach ( $this->settings as $key => $args ) {
			$defaults[ $key ] = $args['default'] ?? '';
		}

		return $defaults;
	}

	/**
	 * Saves our fields.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $value Array of values.
	 */
	public function sanitize_fields( $value ) {
		$value     = (array) $value;
		$new_value = array();
		foreach ( $this->settings as $key => $args ) {
			$field_type       = $args['type'];
			$new_option_value = $value[ $key ] ?? '';
			if ( $new_option_value ) {
				$sanitize_callback = $args['sanitize_callback'] ?? $this->get_sanitize_callback_by_type( $field_type );
				$new_value[ $key ] = call_user_func( $sanitize_callback, $new_option_value, $args );
			} elseif ( 'checkbox' === $field_type ) {
				$new_value[ $key ] = 0;
			}
		}

		return $new_value;
	}

	/**
	 * Returns sanitize callback based on field type.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $field_type Field type.
	 *
	 * @return callable Sanitize callback.
	 */
	protected function get_sanitize_callback_by_type( $field_type ) {
		switch ( $field_type ) {
			case 'select':
				return array( $this, 'sanitize_select_field' );
			case 'textarea':
				return 'wp_kses_post';
			case 'checkbox':
				return array( $this, 'sanitize_checkbox_field' );
			default:
			case 'text':
				return 'sanitize_text_field';
		}
	}

	/**
	 * Renders the options page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_options_page() {
		if ( ! current_user_can( $this->user_capability ) ) {
			return;
		}

		if ( isset( $_GET['settings-updated'] ) ) {    // phpcs:ignore WordPress.Security.NonceVerification
			add_settings_error(
				$this->option_name . '_mesages',
				$this->option_name . '_message',
				esc_html__( 'Settings Saved', 'pulseshare' ),
				'updated'
			);
		}

		settings_errors( $this->option_name . '_mesages' );

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php $this->render_tabs(); ?>
			<form action="options.php" method="post" class="wpex-options-form">
				<?php
				settings_fields( $this->option_group_name );
				do_settings_sections( $this->option_name );
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders options page tabs.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function render_tabs() {
		if ( empty( $this->args['tabs'] ) ) {
			return;
		}

		$tabs = $this->args['tabs'];
		?>

		<style>.wpex-tab-item {
				display: none;
				? ></style>

		<h2 class="nav-tab-wrapper wpex-tabs">
		<?php
			$first_tab = true;
		foreach ( $tabs as $id => $label ) {
			?>
				<a href="#" data-tab="<?php echo esc_attr( $id ); ?>" class="nav-tab <?php echo ( $first_tab ) ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php echo esc_html( ucfirst( $label ) ); ?></a>
				<?php
				$first_tab = false;
		}
		?>
			</h2>

		<script>
			(
				function () {
					document.addEventListener( 'click', ( event ) => {
						const target = event.target;
						if ( !target.closest( '.wpex-tabs a' ) ) {
							return;
						}
						event.preventDefault();
						document.querySelectorAll( '.wpex-tabs a' ).forEach( ( tablink ) => {
							tablink.classList.remove( 'nav-tab-active' );
						} );
						target.classList.add( 'nav-tab-active' );
						targetTab = target.getAttribute( 'data-tab' );
						document.querySelectorAll( '.wpex-options-form .wpex-tab-item' ).forEach( ( item ) => {
							if ( item.classList.contains( `wpex-tab-item--${targetTab}` ) ) {
								item.style.display = 'block';
							} else {
								item.style.display = 'none';
							}
						} );
					} );
					document.addEventListener( 'DOMContentLoaded', function () {
						document.querySelector( '.wpex-tabs .nav-tab' ).click();
					}, false );
				}
			)();
		</script>

		<?php
	}

	/**
	 * Renders a text field.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args Array of arguments.
	 *
	 * @return void
	 */
	public function render_text_field( $args ) {
		$option_name = $args['label_for'];
		$value       = $this->get_option_value( $option_name );
		$description = $this->settings[ $option_name ]['description'] ?? '';
		?>
		<input
			type="text"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
			value="<?php echo esc_attr( $value ); ?>">
		<?php if ( $description ) { ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php } ?>
		<?php
	}

	/**
	 * Returns an option value.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $option_name Option name.
	 *
	 * @return mixed Option value.
	 */
	protected function get_option_value( $option_name ) {
		$option = get_option( $this->option_name );
		if ( ! array_key_exists( $option_name, $option ) ) {
			return array_key_exists(
				'default',
				$this->settings[ $option_name ]
			) ? $this->settings[ $option_name ]['default'] : '';
		}

		return $option[ $option_name ];
	}

	/**
	 * Renders a textarea field.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args Array of arguments.
	 *
	 * @return void
	 */
	public function render_textarea_field( $args ) {
		$option_name = $args['label_for'];
		$value       = $this->get_option_value( $option_name );
		$description = $this->settings[ $option_name ]['description'] ?? '';
		$rows        = $this->settings[ $option_name ]['rows'] ?? '4';
		$cols        = $this->settings[ $option_name ]['cols'] ?? '50';
		?>
		<textarea
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			rows="<?php echo esc_attr( absint( $rows ) ); ?>"
			cols="<?php echo esc_attr( absint( $cols ) ); ?>"
			name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo esc_attr( $value ); ?></textarea>
		<?php if ( $description ) { ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php } ?>
		<?php
	}

	/**
	 * Renders a checkbox field.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args Array of arguments.
	 *
	 * @return void
	 */
	public function render_checkbox_field( $args ) {
		$option_name = $args['label_for'];
		$value       = $this->get_option_value( $option_name );
		$description = $this->settings[ $option_name ]['description'] ?? '';
		?>
		<input
			type="checkbox"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
			<?php checked( $value, 1, true ); ?>
		>
		<?php if ( $description ) { ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php } ?>
		<?php
	}

	/**
	 * Renders a select field.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args Array of arguments.
	 *
	 * @return void
	 */
	public function render_select_field( $args ) {
		$option_name = $args['label_for'];
		$value       = $this->get_option_value( $option_name );
		$description = $this->settings[ $option_name ]['description'] ?? '';
		$choices     = $this->settings[ $option_name ]['choices'] ?? array();
		?>
		<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
		>
			<?php foreach ( $choices as $choice_v => $label ) { ?>
				<option value="<?php echo esc_attr( $choice_v ); ?>" 
										<?php
										selected(
											$choice_v,
											$value,
											true
										);
										?>
					><?php echo esc_html( $label ); ?></option>
			<?php } ?>
		</select>
		<?php if ( $description ) { ?>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		<?php } ?>
		<?php
	}

	/**
	 * Sanitizes the checkbox field.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $value      Value to sanitize.
	 * @param array  $field_args Array of field arguments.
	 *
	 * @return int Sanitized value.
	 */
	protected function sanitize_checkbox_field( $value = '', $field_args = array() ) {
		return ( 'on' === $value ) ? 1 : 0;
	}

	/**
	 * Sanitizes the select field.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $value      Value to sanitize.
	 * @param array  $field_args Array of field arguments.
	 *
	 * @return string Sanitized value.
	 */
	protected function sanitize_select_field( $value = '', $field_args = array() ) {
		$choices = $field_args['choices'] ?? array();
		if ( array_key_exists( $value, $choices ) ) {
			return $value;
		}
	}
}
