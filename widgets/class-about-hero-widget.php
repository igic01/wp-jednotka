<?php
/** Simple about-page title and floating badges from the temp example. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class About_Hero_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-about-hero'; }
	public function get_title() { return esc_html__( 'Jednotka About Hero', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-heading'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'about', 'team', 'title', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-about-hero' ); }

	private function text_controls( $key, $label, $default, $selector ) {
		$this->add_control( $key, array( 'label' => $label, 'type' => Controls_Manager::TEXT, 'default' => $default ) );
		$this->add_control( $key . '_color', array( 'label' => $label . ' color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_typography', 'selector' => $selector ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'title_section', array( 'label' => 'Title and logo' ) );
		$this->text_controls( 'heading', 'Heading', 'Toto je', '{{WRAPPER}} .jn-about-hero__heading' );
		$this->add_control( 'heading_tag', array( 'label' => 'Heading tag', 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3' ) ) );
		$this->add_control( 'logo', array( 'label' => 'Logo image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/jednotka-logo.svg' ) ) );
		$this->add_control( 'logo_alt', array( 'label' => 'Logo alternative text', 'type' => Controls_Manager::TEXT, 'default' => 'Jednotka' ) );
		$this->add_responsive_control( 'logo_width', array( 'label' => 'Logo width', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', '%' ), 'range' => array( 'px' => array( 'min' => 80, 'max' => 800 ), '%' => array( 'min' => 10, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .jn-about-hero .about-title img' => 'width: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'badges_section', array( 'label' => 'Floating badges' ) );
		$this->add_control( 'show_badges', array( 'label' => 'Show badges', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->text_controls( 'skills', 'First badge', 'Rôzne skilly', '{{WRAPPER}} .hero-badge--about-skills' );
		$this->text_controls( 'goal', 'Second badge', 'Spoločný cieľ', '{{WRAPPER}} .hero-badge--about-goal' );
		$this->add_control( 'floating', array( 'label' => 'Floating animation', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'icon_color', array( 'label' => 'Icon color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .hero-badge-icon' => 'stroke: {{VALUE}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout_section', array( 'label' => 'Section layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#0F0F0F', 'selectors' => array( '{{WRAPPER}} .jn-about-hero' => 'background: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-about-hero' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key, $class = '' ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		if ( $class ) { $this->add_render_attribute( $key, 'class', $class ); }
		$this->print_render_attribute_string( $key );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$id = $this->get_id();
		$tag = in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3' ), true ) ? $s['heading_tag'] : 'h1';
		?>
		<section class="about-hero jn-about-hero" aria-labelledby="about-hero-title-<?php echo esc_attr( $id ); ?>" data-floating="<?php echo esc_attr( $s['floating'] ); ?>">
			<div class="about-title-wrap">
				<<?php echo $tag; ?> class="about-title" id="about-hero-title-<?php echo esc_attr( $id ); ?>">
					<span <?php $this->inline_text( 'heading', 'jn-about-hero__heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></span>
					<?php if ( ! empty( $s['logo']['url'] ) ) : ?><img src="<?php echo esc_url( $s['logo']['url'] ); ?>" alt="<?php echo esc_attr( $s['logo_alt'] ); ?>" decoding="async" /><?php endif; ?>
				</<?php echo $tag; ?>>
				<?php if ( 'yes' === $s['show_badges'] ) : ?>
					<div class="hero-badge-row">
						<div class="hero-badge hero-badge--about-skills">
							<svg class="hero-badge-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3.5 7.5 12 12l8.5-4.5L12 3Z" /><path d="m3.5 12 8.5 4.5 8.5-4.5" /><path d="m3.5 16.5 8.5 4.5 8.5-4.5" /></svg>
							<span <?php $this->inline_text( 'skills' ); ?>><?php echo esc_html( $s['skills'] ); ?></span>
						</div>
						<div class="hero-badge hero-badge--about-goal">
							<svg class="hero-badge-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8" /><circle cx="12" cy="12" r="3" /><path d="M12 2v3m0 14v3M2 12h3m14 0h3" /></svg>
							<span <?php $this->inline_text( 'goal' ); ?>><?php echo esc_html( $s['goal'] ); ?></span>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
