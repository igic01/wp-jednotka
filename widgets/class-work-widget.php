<?php
/** Editable services process timeline based on temp/work. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Work_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-work'; }
	public function get_title() { return esc_html__( 'Jednotka How We Work', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-time-line'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'work', 'process', 'timeline', 'steps', 'postup', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-work' ); }
	public function get_script_depends() { return array( 'jednotka-work' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Process timeline' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Od prvého rozhovoru po výsledky, ktoré vidíte' ) );
		$step = new Repeater();
		$step->add_control( 'title', array( 'label' => 'Step title', 'type' => Controls_Manager::TEXT ) );
		$step->add_control( 'description', array( 'label' => 'Step description', 'type' => Controls_Manager::TEXTAREA ) );
		$this->add_control( 'steps', array( 'label' => 'Steps', 'type' => Controls_Manager::REPEATER, 'fields' => $step->get_controls(), 'title_field' => '{{{ title }}}', 'prevent_empty' => false, 'default' => array(
			array( 'title' => 'Konzultácia', 'description' => 'Spoznáme váš biznis, ciele aj to, čo doteraz nefungovalo. Bez záväzkov.' ),
			array( 'title' => 'Návrh riešenia', 'description' => 'Pripravíme konkrétny plán. Čo, v akom poradí a s akým očakávaným výsledkom.' ),
			array( 'title' => 'Realizácia', 'description' => 'Tím pracuje naprieč webom, e-shopom, emailami aj reklamou súčasne.' ),
			array( 'title' => 'Optimalizácia', 'description' => 'Sledujeme čísla a priebežne vylepšujeme to, čo prináša výsledky.' ),
		) ) );
		$this->add_control( 'animate', array( 'label' => 'Scroll entrance animation', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => 'Replays when you scroll back above the section.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Section and timeline', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array(
			'background' => array( 'Background', '{{WRAPPER}} .jn-work', 'background-color', '#0f0f0f' ),
			'heading_color' => array( 'Heading color', '{{WRAPPER}} .services-work-heading', 'color', '' ),
			'title_color' => array( 'Step title color', '{{WRAPPER}} .services-timeline-step h3', 'color', '' ),
			'description_color' => array( 'Step description color', '{{WRAPPER}} .services-timeline-step p', 'color', '' ),
			'accent' => array( 'Node and line color', '{{WRAPPER}} .jn-work', '--work-accent', '#5f2df5' ),
			'line_end' => array( 'Line end color', '{{WRAPPER}} .jn-work', '--work-line-end', '#a78bfa' ),
			'number_color' => array( 'Step number color', '{{WRAPPER}} .services-timeline-node', 'color', '' ),
		) as $key => $control ) {
			$this->add_control( $key, array( 'label' => $control[0], 'type' => Controls_Manager::COLOR, 'default' => $control[3], 'selectors' => array( $control[1] => $control[2] . ': {{VALUE}};' ) ) );
		}
		foreach ( array( 'heading' => '.services-work-heading', 'title' => '.services-timeline-step h3', 'description' => '.services-timeline-step p' ) as $key => $selector ) {
			$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_font', 'selector' => '{{WRAPPER}} ' . $selector ) );
		}
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-work' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Step spacing', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 8, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .services-timeline' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		$this->print_render_attribute_string( $key );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="services-work-section jn-work" data-animate="<?php echo esc_attr( $s['animate'] ); ?>" aria-labelledby="work-title-<?php echo esc_attr( $this->get_id() ); ?>">
			<div class="jn-work__layout">
				<h2 class="services-work-heading" id="work-title-<?php echo esc_attr( $this->get_id() ); ?>"><span <?php $this->inline_text( 'heading' ); ?>><?php echo nl2br( esc_html( $s['heading'] ) ); ?></span></h2>
				<?php if ( $s['steps'] ) : ?><div class="services-timeline is-active" style="--step-count: <?php echo esc_attr( count( $s['steps'] ) ); ?>;">
					<div class="services-timeline-line" aria-hidden="true"></div>
					<?php foreach ( $s['steps'] as $index => $step ) : ?>
						<article class="services-timeline-step elementor-repeater-item-<?php echo esc_attr( $step['_id'] ); ?>" style="--i: <?php echo esc_attr( $index ); ?>;">
							<span class="services-timeline-node" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<h3 <?php $this->inline_text( $this->get_repeater_setting_key( 'title', 'steps', $index ) ); ?>><?php echo esc_html( $step['title'] ); ?></h3>
							<p <?php $this->inline_text( $this->get_repeater_setting_key( 'description', 'steps', $index ) ); ?>><?php echo nl2br( esc_html( $step['description'] ) ); ?></p>
						</article>
					<?php endforeach; ?>
				</div><?php endif; ?>
			</div>
		</section>
		<?php
	}
}
