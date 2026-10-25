<?php
/** Scroll-stacked value cards adapted from temp/values. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Values_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-values'; }
	public function get_title() { return esc_html__( 'Jednotka Values', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-posts-group'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'values', 'principles', 'cards', 'hodnoty', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-values' ); }
	public function get_script_depends() { return array( 'jednotka-values' ); }

	private function text_controls( $stack, $key, $label, $selector, $default = '', $multiline = false ) {
		$stack->add_control( $key, array( 'label' => $label, 'type' => $multiline ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $default ) );
		$stack->add_control( $key . '_color', array( 'label' => $label . ' color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$stack->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_typography', 'selector' => $selector ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'intro_section', array( 'label' => 'Introduction' ) );
		$this->text_controls( $this, 'heading', 'Heading', '{{WRAPPER}} .values__title', 'Čomu veríme' );
		$this->text_controls( $this, 'description', 'Description', '{{WRAPPER}} .values__lead', 'Päť zásad, podľa ktorých pracujeme s každým klientom a v každom projekte.', true );
		$this->end_controls_section();

		$this->start_controls_section( 'values_section', array( 'label' => 'Values' ) );
		$value = new Repeater();
		$this->text_controls( $value, 'title', 'Title', '{{WRAPPER}} {{CURRENT_ITEM}} .values__card-title' );
		$this->text_controls( $value, 'text', 'Description', '{{WRAPPER}} {{CURRENT_ITEM}} .values__card-text', '', true );
		$value->add_control( 'background', array( 'label' => 'Card background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}' => '--card-color: {{VALUE}};' ) ) );
		$this->add_control( 'values', array( 'label' => 'Value cards', 'type' => Controls_Manager::REPEATER, 'fields' => $value->get_controls(), 'title_field' => '{{{ title }}}', 'prevent_empty' => false, 'default' => array(
			array( 'title' => 'Jeden tím', 'text' => 'Web, reklama, emaily aj grafika sa riešia spolu. Žiadne prehadzovanie zodpovednosti medzi agentúrami.' ),
			array( 'title' => 'Jasná reč', 'text' => 'Hovoríme po ľudsky, bez marketingového žargónu. Vždy viete, čo robíme a prečo to robíme.' ),
			array( 'title' => 'Výsledky pred efektmi', 'text' => 'Pekný dizajn je základ, nie cieľ. Meriame to, čo sa reálne predáva, a podľa toho sa rozhodujeme.' ),
			array( 'title' => 'Poctivosť', 'text' => 'Ak niečo nefunguje, dozviete sa to ako prví. Radšej nepríjemná pravda než pekný report.' ),
			array( 'title' => 'Stále sa učíme', 'text' => 'Kanály sa menia rýchlo. Testujeme, skúšame a vylepšujeme, aby váš marketing nezostal pozadu.' ),
		) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'motion_section', array( 'label' => 'Animation' ) );
		$this->add_control( 'animate', array( 'label' => 'Scroll animation', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => 'Desktop follows scroll distance. Phones and tablets (up to 1024px) lock page scrolling while each vertical swipe or wheel gesture animates one card. A fresh outward gesture at the first or last card releases the page. Keep the parent container overflow visible. Editor and reduced-motion views show a static list.' ) );
		$this->add_control( 'mode', array( 'label' => 'Stack transition', 'type' => Controls_Manager::SELECT, 'default' => 'stack', 'options' => array( 'stack' => 'Build the stack', 'fall' => 'Cards fall away' ) ) );
		$this->add_control( 'pointer', array( 'label' => 'Mouse tilt and glow', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_section', array( 'label' => 'Section and card layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array( 'background' => array( 'Section background', '{{WRAPPER}} .jn-values', 'background: {{VALUE}};', '#0F0F0F' ), 'card_color' => array( 'Card color', '{{WRAPPER}} .jn-values', '--card-color: {{VALUE}};', '#5F2DF5' ), 'glow_color' => array( 'Glow color', '{{WRAPPER}} .jn-values', '--glow-color: {{VALUE}};', '' ) ) as $key => $definition ) {
			$this->add_control( $key, array( 'label' => $definition[0], 'type' => Controls_Manager::COLOR, 'default' => $definition[3], 'selectors' => array( $definition[1] => $definition[2] ) ) );
		}
		$this->add_responsive_control( 'scroll_distance', array( 'label' => 'Scroll distance per card', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'vh' ), 'default' => array( 'size' => 107, 'unit' => 'vh' ), 'range' => array( 'vh' => array( 'min' => 20, 'max' => 150 ) ), 'selectors' => array( '{{WRAPPER}} .jn-values' => '--card-scroll: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Layout padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .values__layout' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'card_radius', array( 'label' => 'Card corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .jn-values' => '--card-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'card_padding', array( 'label' => 'Card padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .values__face' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key, $class ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		$this->add_render_attribute( $key, 'class', $class );
		$this->print_render_attribute_string( $key );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$mode = 'fall' === $s['mode'] ? 'fall' : 'stack';
		?>
		<section class="values jn-values" aria-labelledby="values-title-<?php echo esc_attr( $this->get_id() ); ?>" data-animate="<?php echo esc_attr( $s['animate'] ); ?>" data-mode="<?php echo esc_attr( $mode ); ?>" data-pointer="<?php echo esc_attr( $s['pointer'] ); ?>" data-static="yes" style="--count: <?php echo esc_attr( count( $s['values'] ) ); ?>;">
			<div class="values__sticky"><div class="values__layout">
				<div class="values__intro">
					<h2 id="values-title-<?php echo esc_attr( $this->get_id() ); ?>" <?php $this->inline_text( 'heading', 'values__title' ); ?>><?php echo esc_html( $s['heading'] ); ?></h2>
					<p <?php $this->inline_text( 'description', 'values__lead' ); ?>><?php echo esc_html( $s['description'] ); ?></p>
				</div>
				<div class="values__stage">
					<?php foreach ( $s['values'] as $index => $value ) : ?>
						<article class="values__card elementor-repeater-item-<?php echo esc_attr( $value['_id'] ); ?>"><div class="values__face">
							<span class="values__orb" aria-hidden="true"></span><span class="values__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<div class="values__card-body"><h3 <?php $this->inline_text( $this->get_repeater_setting_key( 'title', 'values', $index ), 'values__card-title' ); ?>><?php echo esc_html( $value['title'] ); ?></h3><p <?php $this->inline_text( $this->get_repeater_setting_key( 'text', 'values', $index ), 'values__card-text' ); ?>><?php echo esc_html( $value['text'] ); ?></p></div>
						</div></article>
					<?php endforeach; ?>
				</div>
			</div></div>
		</section>
		<?php
	}
}
