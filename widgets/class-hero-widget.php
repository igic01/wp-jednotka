<?php
/** Editable hero, with optional reference lettering and replaceable badge media. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

class Hero_Widget extends Header_Widget {
	public function get_name() { return 'jednotka-hero'; }
	public function get_title() { return esc_html__( 'Jednotka Hero', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-banner'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'hero', 'banner', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-hero' ); }
	public function get_script_depends() { return array( 'jednotka-header', 'jednotka-hero' ); }

	private function media_controls( $stack, $prefix, $image, $text_option = false ) {
		$options = array( 'image' => 'Image', 'video' => 'Uploaded video' );
		if ( $text_option ) { $options = array( 'text' => 'Editable text' ) + $options; }
		$stack->add_control( $prefix . '_mode', array( 'label' => 'Display as', 'type' => Controls_Manager::SELECT, 'options' => $options, 'default' => 'image' ) );
		$stack->add_control( $prefix . '_image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/hero/' . $image ), 'condition' => array( $prefix . '_mode' => 'image' ) ) );
		$stack->add_control( $prefix . '_video', array( 'label' => 'Video', 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ), 'condition' => array( $prefix . '_mode' => 'video' ) ) );
		$stack->add_control( $prefix . '_poster', array( 'label' => 'Video poster / reduced motion image', 'type' => Controls_Manager::MEDIA, 'condition' => array( $prefix . '_mode' => 'video' ) ) );
	}

	private function text_style( $stack, $name, $selector ) {
		$stack->add_control( $name . '_color', array( 'label' => 'Text color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$stack->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $name . '_typography', 'selector' => $selector ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'navigation_visibility', array( 'label' => 'Hero navigation' ) );
		$this->add_control( 'show_navigation', array( 'label' => 'Show navigation', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->register_header_controls( 'header_' );
		foreach ( array( 'top' => array( 'First heading line', 'Marketing na', 'marketing-na.svg' ), 'bottom' => array( 'Second heading line', 'Jednotku', 'jednotku.png' ) ) as $line => $values ) {
			$this->start_controls_section( $line . '_section', array( 'label' => $values[0] ) );
			$this->add_control( $line . '_text', array( 'label' => 'Text / media alternative text', 'type' => Controls_Manager::TEXT, 'default' => $values[1], 'description' => 'Click this line in the preview to edit it in text mode. Image/video lettering is changed through the media picker.' ) );
			$this->media_controls( $this, $line, $values[2], true );
			$this->end_controls_section();
		}
		$this->start_controls_section( 'copy_section', array( 'label' => 'Description and button' ) );
		$this->add_control( 'description', array( 'label' => 'Description', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus at dapibus nisl, et volutpat ex. Donec blandit euismod felis pulvinar facilisis.' ) );
		$this->add_control( 'show_button', array( 'label' => 'Show button', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'button_text', array( 'label' => 'Button text', 'type' => Controls_Manager::TEXT, 'default' => 'Pozrieť služby' ) );
		$this->add_control( 'button_link', array( 'label' => 'Button link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => home_url( '/sluzby/' ) ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'badges_section', array( 'label' => 'Service badges' ) );
		$badge = new Repeater();
		$badge->add_control( 'text', array( 'label' => 'Text', 'type' => Controls_Manager::TEXT, 'default' => 'Service' ) );
		$this->media_controls( $badge, 'icon', 'web.png' );
		$this->text_style( $badge, 'text', '{{WRAPPER}} {{CURRENT_ITEM}} .jn-hero__badge-text' );
		$badge->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}' => 'background: {{VALUE}};' ) ) );
		foreach ( array( 'x' => 'Horizontal position (%)', 'y' => 'Vertical position (%)' ) as $axis => $label ) {
			$badge->add_control( $axis, array( 'label' => $label, 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}' => ( 'x' === $axis ? 'left' : 'top' ) . ': {{SIZE}}%;' ), 'description' => 'Desktop only. Mobile badges flow above the heading.' ) );
		}
		$defaults = array();
		foreach ( array( array( 'Web', 'web.png', 15.3, 30.8 ), array( 'E-shopy', 'services-eshop.svg', 49.8, 27.7 ), array( 'Emaily', 'email.png', 72.6, 31.1 ), array( 'Grafika', 'grafika.png', 21.2, 51.7 ), array( 'Reklamy', 'ppc.png', 66.4, 60 ) ) as $item ) {
			$defaults[] = array( 'text' => $item[0], 'icon_image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/hero/' . $item[1] ), 'x' => array( 'size' => $item[2] ), 'y' => array( 'size' => $item[3] ) );
		}
		$this->add_control( 'badges', array( 'label' => 'Badges', 'type' => Controls_Manager::REPEATER, 'fields' => $badge->get_controls(), 'default' => $defaults, 'title_field' => '{{{ text }}}' ) );
		$this->add_control( 'animate_badges', array( 'label' => 'Floating badges', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		foreach ( array( 'top' => 'First heading line', 'bottom' => 'Second heading line', 'description' => 'Description', 'button' => 'Button' ) as $name => $label ) {
			$this->start_controls_section( $name . '_style', array( 'label' => $label, 'tab' => Controls_Manager::TAB_STYLE ) );
			$this->text_style( $this, $name, '{{WRAPPER}} .jn-hero__' . $name );
			if ( in_array( $name, array( 'top', 'bottom' ), true ) ) {
				$this->add_responsive_control( $name . '_width', array( 'label' => 'Media width', 'type' => Controls_Manager::SLIDER, 'size_units' => array( '%', 'px' ), 'range' => array( '%' => array( 'min' => 10, 'max' => 100 ), 'px' => array( 'min' => 50, 'max' => 1200 ) ), 'selectors' => array( '{{WRAPPER}} .jn-hero__' . $name . ' .jn-hero__media' => 'width: {{SIZE}}{{UNIT}};' ) ) );
			}
			if ( 'button' === $name ) {
				$this->add_control( 'button_background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-hero__button' => 'background: {{VALUE}};' ) ) );
				$this->add_control( 'button_hover_color', array( 'label' => 'Hover text color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-hero__button:is(:hover, :focus-visible)' => 'color: {{VALUE}};' ) ) );
				$this->add_control( 'button_hover_background', array( 'label' => 'Hover background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-hero__button:is(:hover, :focus-visible)' => 'background: {{VALUE}};' ) ) );
			}
			$this->end_controls_section();
		}
		$this->start_controls_section( 'frame_style', array( 'label' => 'Hero layout and gradient', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array( 'gradient_top' => array( 'Top color', '#09031A' ), 'gradient_middle' => array( 'Middle color', '#522FC7' ), 'gradient_bottom' => array( 'Bottom color', '#F4EFFF' ) ) as $key => $value ) {
			$this->add_control( $key, array( 'label' => $value[0], 'type' => Controls_Manager::COLOR, 'default' => $value[1], 'selectors' => array( '{{WRAPPER}} .jn-hero' => '--jn-' . str_replace( '_', '-', $key ) . ': {{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'min_height', array( 'label' => 'Minimum height', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 300, 'max' => 1400 ), 'vh' => array( 'min' => 30, 'max' => 150 ) ), 'selectors' => array( '{{WRAPPER}} .jn-hero' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'content_padding', array( 'label' => 'Content padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-hero__frame' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'frame_margin', array( 'label' => 'Outer frame spacing', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .jn-hero' => 'padding: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'radius', array( 'label' => 'Corner radius', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .jn-hero__frame' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function render_media( $settings, $prefix, $alt = '' ) {
		if ( 'video' === $settings[ $prefix . '_mode' ] ) {
			if ( ! empty( $settings[ $prefix . '_video' ]['url'] ) ) {
				printf( '<video class="jn-hero__media%s" src="%s" poster="%s" aria-label="%s" muted loop playsinline preload="metadata"></video>', 'icon' === $prefix ? ' hero-badge-icon' : '', esc_url( $settings[ $prefix . '_video' ]['url'] ), esc_url( $settings[ $prefix . '_poster' ]['url'] ?? '' ), esc_attr( $alt ) );
			}
		} elseif ( ! empty( $settings[ $prefix . '_image' ]['url'] ) ) {
			printf( '<img class="jn-hero__media%s" src="%s" alt="%s" />', 'icon' === $prefix ? ' hero-badge-icon' : '', esc_url( $settings[ $prefix . '_image' ]['url'] ), esc_attr( $alt ) );
		}
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		foreach ( array( 'top_text', 'bottom_text', 'description', 'button_text' ) as $key ) {
			$this->add_inline_editing_attributes( $key, 'none' );
		}
		$this->add_render_attribute( 'description', 'class', 'jn-hero__description' );
		$this->add_render_attribute( 'button_text', 'class', 'button-label-line' );
		$this->add_link_attributes( 'button_link', $s['button_link'] );
		$this->add_render_attribute( 'button_link', 'class', 'jn-hero__button button button--primary' );
		?>
		<section class="jn-hero hero" data-float="<?php echo esc_attr( $s['animate_badges'] ); ?>">
			<?php if ( 'yes' === $s['show_navigation'] ) { $this->render_header( 'header_' ); } ?>
			<div class="jn-hero__frame hero-frame">
				<div class="jn-hero__content">
				<div class="jn-hero__badges hero-badge-row">
					<?php foreach ( $s['badges'] as $index => $badge ) :
						$key = $this->get_repeater_setting_key( 'text', 'badges', $index );
						$this->add_inline_editing_attributes( $key, 'none' );
						$this->add_render_attribute( $key, 'class', 'jn-hero__badge-text' );
						?>
						<div class="jn-hero__badge hero-badge hero-badge--<?php echo esc_attr( array( 'web', 'eshopy', 'emaily', 'grafika', 'reklamy' )[ $index % 5 ] ); ?> elementor-repeater-item-<?php echo esc_attr( $badge['_id'] ); ?>">
								<span class="jn-hero__icon" aria-hidden="true"><?php $this->render_media( $badge, 'icon' ); ?></span>
								<span <?php $this->print_render_attribute_string( $key ); ?>><?php echo esc_html( $badge['text'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
				
					<h1 class="hero-title jn-hero__title<?php echo ( 'image' === $s['top_mode'] && 'image' === $s['bottom_mode'] ) ? ' jn-hero__title--artwork' : ''; ?>">
						<?php foreach ( array( 'top', 'bottom' ) as $line ) : ?>
							<span class="hero-title-line hero-title-line--<?php echo esc_attr( $line ); ?> jn-hero__line jn-hero__<?php echo esc_attr( $line ); ?>">
								<?php if ( 'text' === $s[ $line . '_mode' ] ) : ?>
									<span <?php $this->print_render_attribute_string( $line . '_text' ); ?>><?php echo esc_html( $s[ $line . '_text' ] ); ?></span>
								<?php else : $this->render_media( $s, $line, $s[ $line . '_text' ] ); endif; ?>
							</span>
						<?php endforeach; ?>
					</h1>
					<div class="hero-copy">
					<p <?php $this->print_render_attribute_string( 'description' ); ?>><?php echo esc_html( $s['description'] ); ?></p>
					<?php if ( 'yes' === $s['show_button'] ) : ?>
						<a <?php $this->print_render_attribute_string( 'button_link' ); ?>><span class="button-label"><span class="button-label-track"><span <?php $this->print_render_attribute_string( 'button_text' ); ?>><?php echo esc_html( $s['button_text'] ); ?></span><span class="button-label-line" aria-hidden="true"><?php echo esc_html( $s['button_text'] ); ?></span></span></span><span class="button-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span></a>
					<?php endif; ?>
				</div>
			</div>
			</div>
		</section>
		<?php
	}
}
