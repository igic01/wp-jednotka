<?php
/** Scroll-driven statement and sequential photos adapted from temp/approach. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Our_Story_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-our-story'; }
	public function get_title() { return esc_html__( 'Jednotka Our Story', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-animation-text'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'story', 'about', 'scroll', 'statement', 'pribeh', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-our-story' ); }
	public function get_script_depends() { return array( 'jednotka-our-story' ); }

	protected function register_controls() {
		$this->start_controls_section( 'story_section', array( 'label' => 'Our story' ) );
		$this->add_control( 'text', array( 'label' => 'Statement', 'type' => Controls_Manager::TEXTAREA, 'rows' => 6, 'default' => 'Email, reklamu aj web riešime spolu, nie ako samostatné projekty. Výsledok je jednoduchý: viac ľudí, ktorí u vás nakúpia.' ) );
		$this->add_control( 'section_label', array( 'label' => 'Section accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Náš príbeh' ) );
		$this->add_control( 'animate', array( 'label' => 'Scroll animation', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => 'Keep the parent Elementor container overflow visible. Editor and reduced-motion views show a static, readable statement.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'images_section', array( 'label' => 'Story photos' ) );
		$photo = new Repeater();
		$photo->add_control( 'name', array( 'label' => 'Item name', 'type' => Controls_Manager::TEXT ) );
		$photo->add_control( 'image', array( 'label' => 'Photo', 'type' => Controls_Manager::MEDIA, 'description' => 'Decorative background photo. Replace the bundled demo with your own image.' ) );
		$photo->add_control( 'ratio', array( 'label' => 'Aspect ratio', 'type' => Controls_Manager::SELECT, 'default' => '4 / 5', 'options' => array( '4 / 5' => '4:5', '10 / 7' => '10:7', '7 / 9' => '7:9', '8 / 11' => '8:11', '1 / 1' => 'Square', '16 / 9' => '16:9' ) ) );
		foreach ( array( 'x' => array( 'Left position (%)', 4 ), 'y' => array( 'Top position (%)', 8 ), 'width' => array( 'Width (%)', 22 ), 'start' => array( 'Appear at scroll progress (%)', 0 ), 'end' => array( 'Disappear at scroll progress (%)', 100 ) ) as $key => $definition ) {
			$photo->add_control( $key, array( 'label' => $definition[0], 'type' => Controls_Manager::NUMBER, 'min' => 'width' === $key ? 1 : 0, 'max' => 100, 'step' => 1, 'default' => $definition[1] ) );
		}
		$defaults = array();
		$positions = array(
			array( '4 / 5', 4, 8, 22, 2, 27, 'assets/images/team/placeholder.jpg' ),
			array( '10 / 7', 74, 12, 20, 27, 52, 'assets/images/projects/placeholder.jpg' ),
			array( '7 / 9', 6, 52, 21, 52, 77, 'assets/images/showcase/gallery-3.jpg' ),
			array( '8 / 11', 72, 50, 24, 77, 98, 'assets/images/showcase/gallery-4.jpg' ),
		);
		foreach ( $positions as $index => $values ) {
			$defaults[] = array( 'name' => 'Photo ' . ( $index + 1 ), 'ratio' => $values[0], 'x' => $values[1], 'y' => $values[2], 'width' => $values[3], 'start' => $values[4], 'end' => $values[5], 'image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . $values[6] ) );
		}
		$this->add_control( 'photos', array( 'label' => 'Photos', 'type' => Controls_Manager::REPEATER, 'fields' => $photo->get_controls(), 'default' => $defaults, 'title_field' => '{{{ name || "Story photo" }}}', 'prevent_empty' => false ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_section', array( 'label' => 'Statement and layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'text_typography', 'selector' => '{{WRAPPER}} .jn-our-story__text' ) );
		$this->add_responsive_control( 'text_width', array(
			'label' => 'Text width',
			'type' => Controls_Manager::SLIDER,
			'size_units' => array( 'px', '%', 'vw' ),
			'range' => array(
				'px' => array( 'min' => 240, 'max' => 2000 ),
				'%' => array( 'min' => 20, 'max' => 100 ),
				'vw' => array( 'min' => 20, 'max' => 100 ),
			),
			'description' => 'Set separately for desktop, tablet, and mobile. The text stays centered and fits inside the frame.',
			'selectors' => array( '{{WRAPPER}} .jn-our-story__text' => '--story-text-width: {{SIZE}}{{UNIT}};' ),
		) );
		foreach ( array( 'final' => array( 'Revealed text color', '#FFFFFF' ), 'muted' => array( 'Unrevealed text color', '#585858' ), 'highlight' => array( 'Wave color', '#8A5CFF' ) ) as $key => $definition ) {
			$this->add_control( $key . '_color', array( 'label' => $definition[0], 'type' => Controls_Manager::COLOR, 'alpha' => false, 'default' => $definition[1], 'render_type' => 'template' ) );
		}
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#0F0F0F', 'selectors' => array( '{{WRAPPER}} .jn-our-story' => 'background: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'scroll_height', array( 'label' => 'Scroll section height', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'vh' ), 'range' => array( 'vh' => array( 'min' => 150, 'max' => 600 ) ), 'selectors' => array( '{{WRAPPER}} .jn-our-story' => '--story-scroll-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Sticky frame padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-our-story__sticky' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'radius', array( 'label' => 'Photo corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .jn-our-story__image' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$editing = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$colors = array();
		foreach ( array( 'final' => '#FFFFFF', 'muted' => '#585858', 'highlight' => '#8A5CFF' ) as $key => $fallback ) {
			$colors[ $key ] = sanitize_hex_color( $s[ $key . '_color' ] ) ?: $fallback;
		}
		$this->add_inline_editing_attributes( 'text', 'none' );
		$this->add_render_attribute( 'text', 'class', 'jn-our-story__text' );
		?>
		<section class="jn-our-story" aria-label="<?php echo esc_attr( $s['section_label'] ); ?>" data-animate="<?php echo esc_attr( $s['animate'] ); ?>" data-editor="<?php echo $editing ? 'yes' : 'no'; ?>" data-static="<?php echo $editing || 'yes' !== $s['animate'] ? 'yes' : 'no'; ?>" data-final="<?php echo esc_attr( $colors['final'] ); ?>" data-muted="<?php echo esc_attr( $colors['muted'] ); ?>" data-highlight="<?php echo esc_attr( $colors['highlight'] ); ?>" style="--story-final: <?php echo esc_attr( $colors['final'] ); ?>;">
			<div class="jn-our-story__sticky">
				<div class="jn-our-story__images" aria-hidden="true">
					<?php foreach ( $s['photos'] as $photo ) : if ( empty( $photo['image']['url'] ) ) { continue; }
						$x = max( 0, min( 100, (float) $photo['x'] ) );
						// Bring right-side photos inward, including photos in existing widgets.
						if ( $x > 50 ) {
							$x = max( 50, $x - 10 );
						}
						$y = max( 0, min( 100, (float) $photo['y'] ) );
						$width = max( 1, min( 100, (float) $photo['width'] ) );
						$start = max( 0, min( 0.99, (float) $photo['start'] / 100 ) );
						$end = max( $start + 0.01, min( 1, (float) $photo['end'] / 100 ) );
						$ratio = in_array( $photo['ratio'], array( '4 / 5', '10 / 7', '7 / 9', '8 / 11', '1 / 1', '16 / 9' ), true ) ? $photo['ratio'] : '4 / 5';
						?>
						<img class="jn-our-story__image elementor-repeater-item-<?php echo esc_attr( $photo['_id'] ); ?>" src="<?php echo esc_url( $photo['image']['url'] ); ?>" alt="" draggable="false" loading="lazy" decoding="async" data-start="<?php echo esc_attr( $start ); ?>" data-end="<?php echo esc_attr( $end ); ?>" style="--x: <?php echo esc_attr( $x ); ?>%; --y: <?php echo esc_attr( $y ); ?>%; --w: <?php echo esc_attr( $width ); ?>%; --ratio: <?php echo esc_attr( $ratio ); ?>;" />
					<?php endforeach; ?>
				</div>
				<p <?php $this->print_render_attribute_string( 'text' ); ?>><?php echo esc_html( $s['text'] ); ?></p>
			</div>
		</section>
		<?php
	}
}
