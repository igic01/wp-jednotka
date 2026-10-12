<?php
/** Reference logo strip with editable media and animation settings. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Logo_Strip_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-logo-strip'; }
	public function get_title() { return esc_html__( 'Jednotka Logo Strip', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-carousel'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'logos', 'clients', 'marquee', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-logo-strip' ); }
	public function get_script_depends() { return array( 'jednotka-logo-strip' ); }

	protected function register_controls() {
		$this->start_controls_section( 'media_section', array( 'label' => 'Logos / media' ) );
		$this->add_control( 'accessible_label', array( 'label' => 'Section accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Naši klienti' ) );
		$item = new Repeater();
		$item->add_control( 'label', array( 'label' => 'Alternative text / name', 'type' => Controls_Manager::TEXT, 'default' => '', 'description' => 'Describe the client or logos in this media. This is for accessibility, not visible text.' ) );
		$item->add_control( 'media_type', array( 'label' => 'Media type', 'type' => Controls_Manager::SELECT, 'default' => 'image', 'options' => array( 'image' => 'Image', 'video' => 'Uploaded video' ) ) );
		$item->add_control( 'image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'media_type' => 'image' ) ) );
		$item->add_control( 'video', array( 'label' => 'Video', 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ), 'condition' => array( 'media_type' => 'video' ) ) );
		$item->add_control( 'poster', array( 'label' => 'Video poster / reduced motion image', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'media_type' => 'video' ) ) );
		$item->add_responsive_control( 'height', array( 'label' => 'Media height', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 10, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} .logo-strip-group {{CURRENT_ITEM}}' => 'height: {{SIZE}}{{UNIT}};' ) ) );
		$item->add_control( 'opacity', array( 'label' => 'Opacity', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => .05 ) ), 'selectors' => array( '{{WRAPPER}} .logo-strip-group {{CURRENT_ITEM}}' => 'opacity: {{SIZE}};' ) ) );
		$this->add_control( 'items', array( 'label' => 'Media items', 'type' => Controls_Manager::REPEATER, 'fields' => $item->get_controls(), 'title_field' => '{{{ label || "Logo / media" }}}', 'default' => array(
			array( 'image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/logo-strip/logo-stripe-1.svg' ) ),
			array( 'image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/logo-strip/logo-stripe-2.svg' ) ),
		), 'description' => 'The reference uses two wide images containing multiple logos. Replace these or add separate logos; both loop groups update automatically.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'animation_section', array( 'label' => 'Scrolling' ) );
		$this->add_control( 'animate', array( 'label' => 'Enable scrolling', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'speed', array( 'label' => 'Loop duration (seconds)', 'type' => Controls_Manager::NUMBER, 'min' => 5, 'max' => 200, 'default' => 34, 'selectors' => array( '{{WRAPPER}} .jn-logo-strip' => '--logo-strip-speed: {{VALUE}}s;' ), 'description' => 'Lower values scroll faster.' ) );
		$this->add_control( 'direction', array( 'label' => 'Direction', 'type' => Controls_Manager::SELECT, 'default' => 'normal', 'options' => array( 'normal' => 'Left', 'reverse' => 'Right' ), 'selectors' => array( '{{WRAPPER}} .logo-strip-track' => 'animation-direction: {{VALUE}};' ) ) );
		$this->add_control( 'pause_hover', array( 'label' => 'Pause on hover', 'type' => Controls_Manager::SWITCHER ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout_section', array( 'label' => 'Strip layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#0F0F0F', 'selectors' => array( '{{WRAPPER}} .jn-logo-strip' => '--logo-strip-bg: {{VALUE}}; --jn-logo-strip-fade: color-mix(in srgb, {{VALUE}} 86%, transparent);' ) ) );
		$this->add_responsive_control( 'strip_height', array( 'label' => 'Strip height', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 40, 'max' => 300 ) ), 'selectors' => array( '{{WRAPPER}} .jn-logo-strip' => '--logo-strip-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Space between media', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} .logo-strip-group' => 'gap: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'edge_fade', array( 'label' => 'Fade at edges', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'fade_width', array( 'label' => 'Fade width (%)', 'type' => Controls_Manager::NUMBER, 'min' => 0, 'max' => 50, 'selectors' => array( '{{WRAPPER}} .jn-logo-strip::before, {{WRAPPER}} .jn-logo-strip::after' => 'width: {{VALUE}}%;' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="jn-logo-strip logo-strip" aria-label="<?php echo esc_attr( $s['accessible_label'] ); ?>" data-animate="<?php echo esc_attr( $s['animate'] ); ?>" data-pause-hover="<?php echo esc_attr( $s['pause_hover'] ); ?>" data-edge-fade="<?php echo esc_attr( $s['edge_fade'] ); ?>">
			<div class="logo-strip-track">
				<?php for ( $group = 0; $group < 2; $group++ ) : ?>
					<div class="logo-strip-group"<?php echo $group ? ' aria-hidden="true"' : ''; ?>>
						<?php foreach ( $s['items'] as $index => $item ) :
							$class = 'jn-logo-strip__media elementor-repeater-item-' . $item['_id'] . ( 1 === $index ? ' jn-logo-strip__media--second' : '' );
							if ( 'video' === $item['media_type'] && ! empty( $item['video']['url'] ) ) {
								printf( '<video class="%s" src="%s" poster="%s" aria-label="%s" muted loop playsinline preload="metadata" tabindex="-1"></video>', esc_attr( $class ), esc_url( $item['video']['url'] ), esc_url( $item['poster']['url'] ?? '' ), esc_attr( $group ? '' : $item['label'] ) );
							} elseif ( 'image' === $item['media_type'] && ! empty( $item['image']['url'] ) ) {
								printf( '<img class="%s" src="%s" alt="%s" />', esc_attr( $class ), esc_url( $item['image']['url'] ), esc_attr( $group ? '' : $item['label'] ) );
							}
						endforeach; ?>
					</div>
				<?php endfor; ?>
			</div>
			<button class="jn-logo-strip__motion" type="button" aria-pressed="false" aria-label="<?php echo esc_attr__( 'Pause logo strip', 'jednotka-elementor' ); ?>" data-play-label="<?php echo esc_attr__( 'Play logo strip', 'jednotka-elementor' ); ?>" data-pause-label="<?php echo esc_attr__( 'Pause logo strip', 'jednotka-elementor' ); ?>">Ⅱ</button>
		</section>
		<?php
	}
}
