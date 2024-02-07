<?php
/**
 * PodcastWidget
 *
 * @link       https://swapnild.com
 * @since      1.0.0
 *
 * @package    PulseShare
 * @subpackage PulseShare/widgets
 */

namespace PulseShare\Widgets;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Utils;
use PulseShare\includes\Helper;

/**
 * PodcastWidget
 *
 * @since      1.0.0
 * @package    PulseShare
 * @subpackage PulseShare/widgets
 */
class PodcastWidget extends Widget_Base {

	/**
	 * Elementor Widget Name.
	 *
	 * @inheritDoc
	 */
	public function get_name() {
		return 'pulsesharewordpress-elementor-podcast-widget';
	}

	/**
	 * Elementor Widget Title.
	 *
	 * @inheritDoc
	 */
	public function get_title() {
		return __( 'Podcast & Episodes', 'pulseshare' );
	}

	/**
	 * Elementor Widget Icon.
	 *
	 * @inheritDoc
	 */
	public function get_icon() {
		return 'eicon-video-playlist';  // TODO: Change this icon for the podcast widget.
	}

	/**
	 * Elementor Widget Categories.
	 *
	 * @inheritDoc
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Elementor Widget Keywords.
	 *
	 * @inheritDoc
	 */
	public function get_keywords() {
		return array( 'spotify', 'podcast', 'show', 'embed' );
	}

	/**
	 * Elementor Widget scripts.
	 *
	 * @inheritDoc
	 */
	public function get_script_depends() {
		return array();
	}

	/**
	 * Elementor Widget styles.
	 *
	 * @inheritDoc
	 */
	public function get_style_depends() {
		return array();
	}

	/**
	 * Elementor Widget controls.
	 *
	 * @inheritDoc
	 */
	protected function register_controls() {
		$this->register_content_controls();
	}

	/**
	 * Elementor Widget content controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'sfwe_podcast_content_section',
			array(
				'label' => __( 'Content', 'pulseshare' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'sfwe_podcast_display_type',
			array(
				'label'       => __( 'Display Type', 'pulseshare' ),
				'description' => __( 'Choose whether to display a full show or a single episode.', 'pulseshare' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'full',
				'options'     => array(
					'full'   => __( 'Full Show', 'pulseshare' ),
					'single' => __( 'Single Episode', 'pulseshare' ),
				),
			)
		);

		$this->add_control(
			'sfwe_podcast_list',
			array(
				'label'       => __( 'Select Podcast', 'pulseshare' ),
				'description' => __( 'Select the podcast you want to display.', 'pulseshare' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => Helper::get_pulseshareall_episodes(),
				'condition'   => array(
					'sfwe_podcast_display_type' => 'single',
				),
			)
		);

		$this->add_control(
			'sfwe_podcast_video',
			array(
				'label'        => __( 'Is this a video episode?', 'pulseshare' ),
				'description'  => __( 'Enable this option if this episode is a video.', 'pulseshare' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Video', 'pulseshare' ),
				'label_off'    => __( 'Audio', 'pulseshare' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => array(
					'sfwe_podcast_display_type' => 'single',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'sfwe_podcast_style_section',
			array(
				'label' => __( 'Styles', 'pulseshare' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'sfwe_podcast_height',
			array(
				'label'      => esc_html__( 'Height', 'pulseshare' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 200,
				),
			)
		);

		$this->add_control(
			'sfwe_podcast_width',
			array(
				'label'      => esc_html__( 'Width', 'pulseshare' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => '%',
					'size' => 100,
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Elementor Widget render.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @return void
	 */
	protected function render() {
		$settings           = $this->get_settings_for_display();
		$node_id            = $this->get_id();
		$is_editor          = Plugin::$instance->editor->is_edit_mode();
		$pulseshare_options = get_option( 'pulseshare_options' );

		$this->add_render_attribute( 'container', 'id', 'sfwe-podcast-' . $node_id );
		$this->add_render_attribute( 'container', 'class', array( 'sfwe-podcast' ) );
		$this->add_render_attribute( 'container', 'data-node-id', $node_id );

		if ( $is_editor ) {
			$this->add_render_attribute( 'container', 'class', 'sfwe-podcast-editor' );
		}

		$video  = 'yes' === $settings['sfwe_podcast_video'] ? 'video' : '';
		$height = isset( $settings['sfwe_podcast_height']['size'], $settings['sfwe_podcast_height']['unit'] ) ? esc_attr( $settings['sfwe_podcast_height']['size'] . $settings['sfwe_podcast_height']['unit'] ) : '200';
		$width  = isset( $settings['sfwe_podcast_width']['size'], $settings['sfwe_podcast_width']['unit'] ) ? esc_attr( $settings['sfwe_podcast_width']['size'] . $settings['sfwe_podcast_width']['unit'] ) : '100%';

		?>

		<div <?php echo esc_attr( $this->get_render_attribute_string( 'container' ) ); ?>>
			<?php if ( 'full' === $settings['sfwe_podcast_display_type'] ) : ?>
				<iframe
					id="sfwe-show-<?php echo esc_attr( $pulseshare_options['pulseshare_show_id'] ?? '' ); ?>"
					frameBorder="0"
					allowFullScreen=""
					allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
					loading="lazy"
					width="<?php echo esc_attr( $width ); ?>"
					height="<?php echo esc_attr( $height ); ?>"
					src="https://open.spotify.com/embed/show/<?php echo esc_attr( $pulseshare_options['pulseshare_show_id'] ?? '' ); ?>">
				</iframe>
			<?php endif; ?>

			<?php if ( 'single' === $settings['sfwe_podcast_display_type'] && $settings['sfwe_podcast_list'] ) : ?>
				<iframe
					id="sfwe-episode-<?php echo esc_attr( $settings['sfwe_podcast_list'] ?? '' ); ?>"
					frameBorder="0"
					allowFullScreen=""
					allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
					loading="lazy"
					width="<?php echo esc_attr( $width ); ?>"
					height="<?php echo esc_attr( $height ); ?>"
					src="https://open.spotify.com/embed/episode/<?php echo esc_attr( $settings['sfwe_podcast_list'] ?? '' ); ?>/<?php echo esc_attr( $video ); ?>">
				</iframe>
			<?php endif; ?>

			<?php if ( $is_editor && 'single' === $settings['sfwe_podcast_display_type'] && empty( $settings['sfwe_podcast_list'] ) ) : ?>
				<div class="sfwe-podcast-editor-placeholder elementor-panel-alert elementor-panel-alert-info">
					<?php esc_html_e( 'Please select an episode from the widget settings', 'pulseshare' ); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
	}
}
