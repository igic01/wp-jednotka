<?php
/**
 * Editable navigation header for Elementor Free.
 */

namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Header_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-header'; }
	public function get_title() { return esc_html__( 'Jednotka Header', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-header'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'header', 'navigation', 'menu', 'logo', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-header' ); }
	public function get_script_depends() { return array( 'jednotka-header' ); }

	protected function register_controls() {
		$this->register_header_controls();
	}

	/** Share the same navigation controls with the hero, using separate setting keys. */
	protected function register_header_controls( $prefix = '' ) {
		$map_args = static function ( $args ) use ( $prefix ) {
			if ( isset( $args['condition'] ) ) {
				$conditions = array();
				foreach ( $args['condition'] as $key => $value ) { $conditions[ $prefix . $key ] = $value; }
				$args['condition'] = $conditions;
			}
			return $args;
		};
		$section = function ( $id, $args ) use ( $prefix ) { $this->start_controls_section( $prefix . $id, $args ); };
		$add_control = function ( $id, $args ) use ( $prefix, $map_args ) { $this->add_control( $prefix . $id, $map_args( $args ) ); };
		$responsive = function ( $id, $args ) use ( $prefix, $map_args ) { $this->add_responsive_control( $prefix . $id, $map_args( $args ) ); };
		$group = function ( $type, $args ) use ( $prefix ) { $args['name'] = $prefix . $args['name']; $this->add_group_control( $type, $args ); };
		$section( 'logo_section', array( 'label' => esc_html__( 'Logo / media', 'jednotka-elementor' ) ) );
		$add_control( 'media_type', array(
			'label' => esc_html__( 'Media type', 'jednotka-elementor' ), 'type' => Controls_Manager::SELECT,
			'default' => 'image', 'options' => array( 'image' => 'Image', 'video' => 'Uploaded video' ),
		) );
		$add_control( 'logo_image', array(
			'label' => esc_html__( 'Logo image', 'jednotka-elementor' ), 'type' => Controls_Manager::MEDIA,
			'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/jednotka-logo.svg' ),
			'condition' => array( 'media_type' => 'image' ),
		) );
		$add_control( 'logo_video', array(
			'label' => esc_html__( 'Logo video', 'jednotka-elementor' ), 'type' => Controls_Manager::MEDIA,
			'media_types' => array( 'video' ), 'condition' => array( 'media_type' => 'video' ),
			'description' => esc_html__( 'Use a short MP4 or WebM. It plays muted in a loop; visitors can pause it.', 'jednotka-elementor' ),
		) );
		$add_control( 'video_poster', array(
			'label' => esc_html__( 'Video poster / reduced motion image', 'jednotka-elementor' ),
			'type' => Controls_Manager::MEDIA, 'condition' => array( 'media_type' => 'video' ),
		) );
		$add_control( 'logo_label', array(
			'label' => esc_html__( 'Logo accessible name', 'jednotka-elementor' ), 'type' => Controls_Manager::TEXT, 'default' => 'Jednotka',
		) );
		$add_control( 'logo_link', array(
			'label' => esc_html__( 'Logo link', 'jednotka-elementor' ), 'type' => Controls_Manager::URL,
			'default' => array( 'url' => home_url( '/' ) ),
		) );
		$this->end_controls_section();

		$section( 'navigation_section', array( 'label' => esc_html__( 'Navigation links', 'jednotka-elementor' ) ) );
		$repeater = new Repeater();
		$repeater->add_control( 'label', array( 'label' => 'Text', 'type' => Controls_Manager::TEXT, 'default' => 'Link' ) );
		$repeater->add_control( 'link', array( 'label' => 'Link', 'type' => Controls_Manager::URL ) );
		$repeater->add_control( 'text_color', array(
			'label' => 'Text color', 'type' => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .jn-header__nav-text' => 'color: {{VALUE}};' ),
		) );
		$repeater->add_control( 'hover_color', array(
			'label' => 'Hover / focus color', 'type' => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}:is(:hover, :focus-visible) .jn-header__nav-text' => 'color: {{VALUE}};' ),
		) );
		$repeater->add_group_control( Group_Control_Typography::get_type(), array(
			'name' => 'typography', 'label' => 'Font and size',
			'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .jn-header__nav-text',
		) );
		$defaults = array();
		foreach ( array( 'Domov' => '/', 'O nás' => '/o-nas/', 'Služby' => '/sluzby/', 'Kariéra' => '#kariera', 'Blog' => '/blog/' ) as $label => $url ) {
			$defaults[] = array( 'label' => $label, 'link' => array( 'url' => '#' === substr( $url, 0, 1 ) ? $url : home_url( $url ) ) );
		}
		$add_control( 'nav_items', array(
			'label' => esc_html__( 'Links', 'jednotka-elementor' ), 'type' => Controls_Manager::REPEATER,
			'fields' => $repeater->get_controls(), 'default' => $defaults, 'title_field' => '{{{ label }}}',
			'description' => esc_html__( 'Click text in the preview to edit it. Open a link here to change its URL, color, font, or size. Update the default URLs to match your pages.', 'jednotka-elementor' ),
		) );
		$this->end_controls_section();

		$section( 'button_section', array( 'label' => esc_html__( 'Action button', 'jednotka-elementor' ) ) );
		$add_control( 'show_button', array( 'label' => 'Show button', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$add_control( 'button_text', array( 'label' => 'Text', 'type' => Controls_Manager::TEXT, 'default' => 'Pozrieť služby' ) );
		$add_control( 'button_link', array( 'label' => 'Link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => home_url( '/sluzby/' ) ) ) );
		$this->end_controls_section();

		$section( 'layout_section', array( 'label' => 'Header layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$add_control( 'background', array(
			'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#09031A',
			'selectors' => array( '{{WRAPPER}} .jn-header' => 'background-color: {{VALUE}};' ),
		) );
		$responsive( 'padding', array(
			'label' => 'Padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%', 'em' ),
			'selectors' => array( '{{WRAPPER}} .jn-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );
		$responsive( 'logo_width', array(
			'label' => 'Logo width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 40, 'max' => 400 ) ),
			'selectors' => array( '{{WRAPPER}} .jn-header' => '--jn-logo-width: {{SIZE}}{{UNIT}};' ),
		) );
		$responsive( 'nav_gap', array(
			'label' => 'Space between links', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
			'selectors' => array( '{{WRAPPER}} .jn-header__nav' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );
		$add_control( 'mobile_background', array(
			'label' => 'Mobile menu background', 'type' => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .jn-header__nav' => '--jn-mobile-background: {{VALUE}};' ),
		) );
		$add_control( 'toggle_color', array(
			'label' => 'Mobile menu icon', 'type' => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .jn-header__toggle' => 'color: {{VALUE}};' ),
		) );
		$this->end_controls_section();

		$section( 'button_style', array( 'label' => 'Action button', 'tab' => Controls_Manager::TAB_STYLE ) );
		$group( Group_Control_Typography::get_type(), array( 'name' => 'button_typography', 'selector' => '{{WRAPPER}} .jn-header__cta' ) );
		foreach ( array( 'button_color' => array( 'Text color', 'color' ), 'button_background' => array( 'Background', 'background-color' ) ) as $name => $control ) {
			$add_control( $name, array( 'label' => $control[0], 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-header__cta' => $control[1] . ': {{VALUE}};' ) ) );
		}
		foreach ( array( 'button_hover_color' => array( 'Hover text color', 'color' ), 'button_hover_background' => array( 'Hover background', 'background-color' ) ) as $name => $control ) {
			$add_control( $name, array( 'label' => $control[0], 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-header__cta:is(:hover, :focus-visible)' => $control[1] . ': {{VALUE}};' ) ) );
		}
		$responsive( 'button_padding', array(
			'label' => 'Padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em' ),
			'selectors' => array( '{{WRAPPER}} .jn-header__cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$this->render_header();
	}

	protected function render_header( $prefix = '' ) {
		$all_settings = $this->get_settings_for_display();
		$settings = array();
		foreach ( $all_settings as $key => $value ) {
			if ( '' === $prefix || 0 === strpos( $key, $prefix ) ) {
				$settings[ substr( $key, strlen( $prefix ) ) ] = $value;
			}
		}
		$nav_id = 'jn-nav-' . $this->get_id();
		$this->add_link_attributes( $prefix . 'logo_link', $settings['logo_link'] );
		$this->add_render_attribute( $prefix . 'logo_link', array( 'class' => 'jn-header__logo site-header-logo', 'aria-label' => $settings['logo_label'] ) );
		$this->add_link_attributes( $prefix . 'button_link', $settings['button_link'] );
		$this->add_render_attribute( $prefix . 'button_link', 'class', 'jn-header__cta button button--primary site-header-button' );
		$this->add_render_attribute( $prefix . 'button_text', 'class', 'jn-header__button-text button-label-line' );
		$this->add_inline_editing_attributes( $prefix . 'button_text', 'none' );
		?>
		<header class="jn-header site-header" aria-label="<?php echo esc_attr__( 'Main navigation', 'jednotka-elementor' ); ?>">
			<div class="jn-header__brand">
				<a <?php $this->print_render_attribute_string( $prefix . 'logo_link' ); ?>>
					<?php if ( 'video' === $settings['media_type'] ) : ?>
						<?php if ( ! empty( $settings['logo_video']['url'] ) ) : ?>
							<video class="jn-header__media" src="<?php echo esc_url( $settings['logo_video']['url'] ); ?>" poster="<?php echo esc_url( $settings['video_poster']['url'] ?? '' ); ?>" muted loop playsinline preload="metadata" aria-label="<?php echo esc_attr( $settings['logo_label'] ); ?>"></video>
						<?php endif; ?>
					<?php elseif ( ! empty( $settings['logo_image']['url'] ) ) : ?>
						<img class="jn-header__media" src="<?php echo esc_url( $settings['logo_image']['url'] ); ?>" alt="<?php echo esc_attr( $settings['logo_label'] ); ?>" />
					<?php endif; ?>
				</a>
				<?php if ( 'video' === $settings['media_type'] && ! empty( $settings['logo_video']['url'] ) ) : ?>
					<button class="jn-header__video-toggle" type="button" aria-label="<?php echo esc_attr__( 'Play logo animation', 'jednotka-elementor' ); ?>" data-play-label="<?php echo esc_attr__( 'Play logo animation', 'jednotka-elementor' ); ?>" data-pause-label="<?php echo esc_attr__( 'Pause logo animation', 'jednotka-elementor' ); ?>" aria-pressed="false">▶</button>
				<?php endif; ?>
			</div>
			<nav class="jn-header__nav site-nav" id="<?php echo esc_attr( $nav_id ); ?>" aria-label="<?php echo esc_attr__( 'Primary navigation', 'jednotka-elementor' ); ?>">
				<?php foreach ( $settings['nav_items'] as $index => $item ) :
					$text_key = $this->get_repeater_setting_key( 'label', $prefix . 'nav_items', $index );
					$link_key = $prefix . 'nav_link_' . $index;
					$this->add_link_attributes( $link_key, $item['link'] );
					$this->add_render_attribute( $link_key, 'class', 'jn-header__nav-link elementor-repeater-item-' . $item['_id'] );
					$this->add_render_attribute( $text_key, 'class', 'jn-header__nav-text' );
					$this->add_inline_editing_attributes( $text_key, 'none' );
					?>
					<a <?php $this->print_render_attribute_string( $link_key ); ?>><span <?php $this->print_render_attribute_string( $text_key ); ?>><?php echo esc_html( $item['label'] ); ?></span></a>
				<?php endforeach; ?>
			</nav>
			<?php if ( 'yes' === $settings['show_button'] ) : ?>
				<a <?php $this->print_render_attribute_string( $prefix . 'button_link' ); ?>>
					<span class="jn-header__button-label button-label"><span class="jn-header__button-track button-label-track"><span <?php $this->print_render_attribute_string( $prefix . 'button_text' ); ?>><?php echo esc_html( $settings['button_text'] ); ?></span><span class="jn-header__button-copy button-label-line" aria-hidden="true"><?php echo esc_html( $settings['button_text'] ); ?></span></span></span>
					<span class="button-arrow" aria-hidden="true"><svg class="jn-header__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span>
				</a>
			<?php endif; ?>
			<button class="jn-header__toggle site-nav-toggle" type="button" aria-label="<?php echo esc_attr__( 'Toggle menu', 'jednotka-elementor' ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $nav_id ); ?>"><span class="site-nav-toggle-line"></span><span class="site-nav-toggle-line"></span><span class="site-nav-toggle-line"></span></button>
		</header>
		<?php
	}
}
