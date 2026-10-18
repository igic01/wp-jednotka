<?php
/** Standalone footer sharing the Clients & Footer controls and markup. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Footer_Widget extends Clients_Footer_Widget {
	public function get_name() { return 'jednotka-footer'; }
	public function get_title() { return esc_html__( 'Jednotka Footer', 'jednotka-elementor' ); }
	public function get_keywords() { return array( 'footer', 'contact', 'navigation', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-footer' ); }
	public function get_script_depends() { return array( 'jednotka-footer' ); }

	protected function register_controls() {
		$this->register_footer_controls();
		$this->start_controls_section( 'footer_layout', array( 'label' => 'Footer layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'section_background', array( 'label' => 'Surrounding background', 'type' => Controls_Manager::COLOR, 'default' => '#0F0F0F', 'selectors' => array( '{{WRAPPER}} .jn-footer-only' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'footer_background', array( 'label' => 'Footer background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .site-footer-card' => 'background: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'section_padding', array( 'label' => 'Outer padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-footer-only' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'footer_padding', array( 'label' => 'Footer padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .site-footer-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'footer_radius', array( 'label' => 'Footer corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .site-footer-card' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'footer_width', array( 'label' => 'Footer max width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 320, 'max' => 1800 ) ), 'selectors' => array( '{{WRAPPER}} .site-footer-card' => 'max-width: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->add_link_attributes( 'logo_link', $s['logo_link'] );
		$this->add_render_attribute( 'logo_link', array( 'class' => 'footer-logo', 'aria-label' => $s['logo_name'] ) );
		?>
		<div class="jn-clients-footer jn-footer-only">
			<?php $this->render_footer( $s, $this->get_id() ); ?>
		</div>
		<?php
	}
}
