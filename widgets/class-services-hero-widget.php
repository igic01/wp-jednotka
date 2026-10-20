<?php
/** Services-page hero, floating badges, and draggable service cards from temp/hero. */
namespace Jednotka\Elementor\Widgets;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
defined( 'ABSPATH' ) || exit;

class Services_Hero_Widget extends Header_Widget {
	public function get_name() { return 'jednotka-services-hero'; }
	public function get_title() { return esc_html__( 'Jednotka Services Hero', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-slider-push'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'services', 'hero', 'sluzby', 'cards', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-services-hero' ); }
	public function get_script_depends() { return array( 'jednotka-header', 'jednotka-services-hero' ); }
	protected function register_controls() {
		$this->start_controls_section( 'navigation_visibility', array( 'label' => 'Hero navigation' ) );
		$this->add_control( 'show_navigation', array( 'label' => 'Show navigation', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->register_header_controls( 'header_' );
		$this->update_control( 'header_background', array( 'default' => 'transparent' ) );
		$this->start_controls_section( 'intro', array( 'label' => 'Heading' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => Controls_Manager::TEXTAREA, 'default' => "Naše služby sú\nna mieru" ) );
		$this->add_control( 'heading_tag', array( 'label' => 'Heading tag', 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'badges_section', array( 'label' => 'Floating badges' ) );
		$this->add_control( 'show_badges', array( 'label' => 'Show badges', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$badge = new Repeater();
		$badge->add_control( 'label', array( 'label' => 'Label', 'type' => Controls_Manager::TEXT ) );
		$badge->add_control( 'icon', array( 'label' => 'Icon', 'type' => Controls_Manager::MEDIA ) );
		$badge->add_control( 'position', array( 'label' => 'Position', 'type' => Controls_Manager::SELECT, 'default' => 'eshopy', 'options' => array( 'eshopy' => 'Upper left', 'web' => 'Upper center', 'emaily' => 'Upper right', 'grafika' => 'Lower left', 'reklamy' => 'Lower right' ) ) );
		$this->add_control( 'badges', array( 'label' => 'Badges', 'type' => Controls_Manager::REPEATER, 'fields' => $badge->get_controls(), 'title_field' => '{{{ label }}}', 'prevent_empty' => false, 'default' => array(
			array( 'position' => 'eshopy', 'icon' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/services-eshop.svg' ), 'label' => 'E-shopy' ),
			array( 'position' => 'web', 'icon' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/web.png' ), 'label' => 'Web' ),
			array( 'position' => 'emaily', 'icon' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/email.png' ), 'label' => 'Emaily' ),
			array( 'position' => 'grafika', 'icon' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/grafika.png' ), 'label' => 'Grafika' ),
			array( 'position' => 'reklamy', 'icon' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/ppc.png' ), 'label' => 'Reklamy' ),
		) ) );
		$this->add_control( 'floating', array( 'label' => 'Idle floating', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'parallax', array( 'label' => 'Pointer movement', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'cards_section', array( 'label' => 'Service cards' ) );
		$card = new Repeater();
		$card->add_control( 'title', array( 'label' => 'Title', 'type' => Controls_Manager::TEXT ) );
		$card->add_control( 'description', array( 'label' => 'Description', 'type' => Controls_Manager::TEXT ) );
		$card->add_control( 'image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ) );
		$card->add_control( 'alt', array( 'label' => 'Image alternative text', 'type' => Controls_Manager::TEXT ) );
		$card->add_control( 'link', array( 'label' => 'Link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'cards', array( 'label' => 'Cards', 'type' => Controls_Manager::REPEATER, 'fields' => $card->get_controls(), 'title_field' => '{{{ title }}}', 'prevent_empty' => false, 'default' => array(
			array( 'title' => 'Weby', 'description' => 'Prezentačné weby' ),
			array( 'title' => 'E-shopy', 'description' => 'Predajné riešenia' ),
			array( 'title' => 'Reklamy', 'description' => 'Výkonnostné kampane' ),
			array( 'title' => 'Emaily', 'description' => 'Email marketing' ),
			array( 'title' => 'Grafika', 'description' => 'Vizuálna identita' ),
		) ) );
		$this->add_control( 'auto_scroll', array( 'label' => 'Automatic scrolling', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'speed', array( 'label' => 'Speed (pixels per second)', 'type' => Controls_Manager::NUMBER, 'default' => 42, 'min' => 5, 'max' => 200 ) );
		$this->add_control( 'track_label', array( 'label' => 'Carousel accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Služby. Potiahnite alebo použite šípky na klávesnici.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Section and cards', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array(
			'background' => array( 'Background', '{{WRAPPER}} .jn-services-hero', 'background-color', '#ffffff' ),
			'gradient' => array( 'Hero background', '{{WRAPPER}} .services-hero', 'background', '' ),
			'heading_color' => array( 'Heading color', '{{WRAPPER}} .services-title', 'color', '' ),
			'badge_color' => array( 'Badge background', '{{WRAPPER}} .hero-badge', 'background', '' ),
			'card_title_color' => array( 'Card title color', '{{WRAPPER}} .services-case-copy strong', 'color', '' ),
			'card_text_color' => array( 'Card description color', '{{WRAPPER}} .services-case-copy span', 'color', '' ),
		) as $key => $control ) {
			$this->add_control( $key, array( 'label' => $control[0], 'type' => Controls_Manager::COLOR, 'default' => $control[3], 'selectors' => array( $control[1] => $control[2] . ': {{VALUE}};' ) ) );
		}
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_font', 'selector' => '{{WRAPPER}} .services-title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'card_title_font', 'selector' => '{{WRAPPER}} .services-case-copy strong' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'card_description_font', 'selector' => '{{WRAPPER}} .services-case-copy span' ) );
		$this->add_responsive_control( 'card_height', array( 'label' => 'Card height', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 200, 'max' => 650 ) ), 'selectors' => array( '{{WRAPPER}} .jn-services-hero' => '--case-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'card_width', array( 'label' => 'Card width', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vw' ), 'range' => array( 'px' => array( 'min' => 200, 'max' => 800 ), 'vw' => array( 'min' => 30, 'max' => 90 ) ), 'selectors' => array( '{{WRAPPER}} .services-case-card' => 'flex-basis: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'card_radius', array( 'label' => 'Card corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .services-case-link' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}
	private function inline_text( $key, $class = '' ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		if ( $class ) { $this->add_render_attribute( $key, 'class', $class ); }
		$this->print_render_attribute_string( $key );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$tag = in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3' ), true ) ? $s['heading_tag'] : 'h1';
		?>
		<section class="services-hero-wrap jn-services-hero" data-floating="<?php echo esc_attr( $s['floating'] ); ?>" data-parallax="<?php echo esc_attr( $s['parallax'] ); ?>" data-auto="<?php echo esc_attr( $s['auto_scroll'] ); ?>" data-speed="<?php echo esc_attr( max( 5, min( 200, (float) $s['speed'] ) ) ); ?>" aria-labelledby="services-title-<?php echo esc_attr( $this->get_id() ); ?>">
			<div class="services-hero">
				<div class="services-hero__content">
				<?php if ( 'yes' === $s['show_navigation'] ) { $this->render_header( 'header_' ); } ?>
				<?php if ( 'yes' === $s['show_badges'] ) : ?><div class="hero-badge-row" aria-hidden="true">
				<?php foreach ( $s['badges'] as $badge_index => $badge ) :
					$position = in_array( $badge['position'], array( 'eshopy', 'web', 'emaily', 'grafika', 'reklamy' ), true ) ? $badge['position'] : 'eshopy'; ?>
					<div class="hero-badge hero-badge--<?php echo esc_attr( $position ); ?> elementor-repeater-item-<?php echo esc_attr( $badge['_id'] ); ?>"><?php if ( ! empty( $badge['icon']['url'] ) ) : ?><img class="hero-badge-icon" src="<?php echo esc_url( $badge['icon']['url'] ); ?>" alt="" decoding="async" /><?php endif; ?><span <?php $this->inline_text( $this->get_repeater_setting_key( 'label', 'badges', $badge_index ) ); ?>><?php echo esc_html( $badge['label'] ); ?></span></div>
				<?php endforeach; ?></div><?php endif; ?>
				<<?php echo $tag; ?> <?php $this->inline_text( 'heading', 'services-title' ); ?> id="services-title-<?php echo esc_attr( $this->get_id() ); ?>"><?php echo nl2br( esc_html( $s['heading'] ) ); ?></<?php echo $tag; ?>>
				</div>
				<?php if ( $s['cards'] ) : ?><div class="services-cases" role="region" tabindex="0" aria-label="<?php echo esc_attr( $s['track_label'] ); ?>">
				<?php foreach ( $s['cards'] as $index => $card ) :
					$linked = ! empty( $card['link']['url'] ); $card_tag = $linked ? 'a' : 'div'; $key = 'card-link-' . $index;
					if ( $linked ) { $this->add_link_attributes( $key, $card['link'] ); }
					?>
					<article class="services-case-card elementor-repeater-item-<?php echo esc_attr( $card['_id'] ); ?>"><<?php echo $card_tag; ?> class="services-case-link" <?php if ( $linked ) { $this->print_render_attribute_string( $key ); } ?>>
						<?php if ( ! empty( $card['image']['url'] ) ) : ?><img class="services-case-image" src="<?php echo esc_url( $card['image']['url'] ); ?>" alt="<?php echo esc_attr( $card['alt'] ); ?>" draggable="false" loading="lazy" decoding="async" /><?php endif; ?>
						<span class="services-case-shade" aria-hidden="true"></span>
						<?php if ( $linked ) : ?><span class="arrow-badge-button services-case-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span><?php endif; ?>
						<span class="services-case-copy"><strong <?php $this->inline_text( $this->get_repeater_setting_key( 'title', 'cards', $index ) ); ?>><?php echo esc_html( $card['title'] ); ?></strong><span <?php $this->inline_text( $this->get_repeater_setting_key( 'description', 'cards', $index ) ); ?>><?php echo esc_html( $card['description'] ); ?></span></span>
					</<?php echo $card_tag; ?>></article>
				<?php endforeach; ?></div><?php endif; ?>
			</div>
		</section>
		<?php
	}
}
