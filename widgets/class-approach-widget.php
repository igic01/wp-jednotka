<?php
/** Reference sticky approach section with editable text and media. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Approach_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-approach'; }
	public function get_title() { return esc_html__( 'Jednotka Approach', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-animation-text'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'approach', 'wave', 'text', 'marketing', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-approach' ); }
	public function get_script_depends() { return array( 'jednotka-approach' ); }

	protected function register_controls() {
		$this->start_controls_section( 'text_section', array( 'label' => 'Approach text' ) );
		$this->add_control( 'text', array( 'label' => 'Text', 'type' => Controls_Manager::TEXTAREA, 'rows' => 6, 'default' => 'Email, reklamu aj web riešime spolu, nie ako samostatné projekty. Výsledok je jednoduchý: viac ľudí, ktorí u vás nakúpia.' ) );
		$this->add_control( 'animate', array( 'label' => 'Scroll color wave', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => 'The text stays editable in the editor. Preview the page to see the original sticky scroll animation.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'media_section', array( 'label' => 'Media window' ) );
		$this->add_control( 'media_type', array( 'label' => 'Display', 'type' => Controls_Manager::SELECT, 'default' => 'placeholder', 'options' => array( 'placeholder' => 'Original gray window', 'image' => 'Image', 'video' => 'Uploaded video' ) ) );
		$this->add_control( 'image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'media_type' => 'image' ) ) );
		$this->add_control( 'video', array( 'label' => 'Video', 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ), 'condition' => array( 'media_type' => 'video' ) ) );
		$this->add_control( 'poster', array( 'label' => 'Video poster', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'media_type' => 'video' ) ) );
		$this->add_control( 'media_alt', array( 'label' => 'Media description', 'type' => Controls_Manager::TEXT, 'description' => 'Describe meaningful media for screen readers. Leave empty for decorative media.', 'condition' => array( 'media_type!' => 'placeholder' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'text_style', array( 'label' => 'Text and wave colors', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'text_typography', 'selector' => '{{WRAPPER}} .gradient-wave-text' ) );
		foreach ( array( 'final' => array( 'Text color after reveal', '#FFFFFF' ), 'muted' => array( 'Text color before reveal', '#585858' ), 'highlight' => array( 'Wave highlight', '#8A5CFF' ) ) as $key => $definition ) {
			$this->add_control( $key . '_color', array( 'label' => $definition[0], 'type' => Controls_Manager::COLOR, 'alpha' => false, 'default' => $definition[1], 'render_type' => 'template' ) );
		}
		$this->end_controls_section();

		$this->start_controls_section( 'layout_style', array( 'label' => 'Section layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-approach' => '--section-dark-bg: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'scroll_height', array( 'label' => 'Scroll section height', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'vh' ), 'range' => array( 'vh' => array( 'min' => 150, 'max' => 500 ) ), 'selectors' => array( '{{WRAPPER}} .jn-approach' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'side_padding', array( 'label' => 'Side padding', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vw' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ), 'vw' => array( 'min' => 0, 'max' => 15 ) ), 'selectors' => array( '{{WRAPPER}} .jn-approach' => '--section-padding-x: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Text / media gap', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} .approach-grid' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'window_background', array( 'label' => 'Media window background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .approach-window' => 'background: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'window_radius', array( 'label' => 'Media corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 150 ) ), 'selectors' => array( '{{WRAPPER}} .approach-window' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'media_fit', array( 'label' => 'Media fit', 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => 'Fill window', 'contain' => 'Show complete media' ), 'selectors' => array( '{{WRAPPER}} .approach-window > img, {{WRAPPER}} .approach-window > video' => 'object-fit: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$editing = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$final = sanitize_hex_color( $s['final_color'] ) ?: '#FFFFFF';
		$muted = sanitize_hex_color( $s['muted_color'] ) ?: '#585858';
		$highlight = sanitize_hex_color( $s['highlight_color'] ) ?: '#8A5CFF';
		$this->add_inline_editing_attributes( 'text', 'none' );
		$this->add_render_attribute( 'text', 'class', 'gradient-wave-text' );
		$this->add_render_attribute( 'text', 'data-gradient-wave-text', '' );
		$this->add_render_attribute( 'text', 'style', '--jn-wave-final: ' . $final . ';' );
		$has_image = 'image' === $s['media_type'] && ! empty( $s['image']['url'] );
		$has_video = 'video' === $s['media_type'] && ! empty( $s['video']['url'] );
		?>
		<section class="approach-section jn-approach" aria-label="Ako premýšľame nad marketingom" data-animate="<?php echo esc_attr( $s['animate'] ); ?>" data-editor="<?php echo $editing ? 'yes' : 'no'; ?>" data-final="<?php echo esc_attr( $final ); ?>" data-muted="<?php echo esc_attr( $muted ); ?>" data-highlight="<?php echo esc_attr( $highlight ); ?>">
			<div class="approach-grid">
				<p <?php $this->print_render_attribute_string( 'text' ); ?>><?php echo esc_html( $s['text'] ); ?></p>
				<div class="approach-window"<?php if ( ! $has_image && ! $has_video ) : ?> aria-hidden="true"<?php endif; ?>>
					<?php if ( $has_image ) : ?>
						<img src="<?php echo esc_url( $s['image']['url'] ); ?>" alt="<?php echo esc_attr( $s['media_alt'] ); ?>" loading="lazy" />
					<?php elseif ( $has_video ) : ?>
						<video src="<?php echo esc_url( $s['video']['url'] ); ?>" poster="<?php echo esc_url( $s['poster']['url'] ?? '' ); ?>" <?php echo empty( $s['media_alt'] ) ? 'aria-hidden="true"' : 'aria-label="' . esc_attr( $s['media_alt'] ) . '"'; ?> muted loop playsinline preload="metadata"></video>
						<button class="jn-approach__video-toggle" type="button" aria-label="Play video" aria-pressed="false" data-play-label="Play video" data-pause-label="Pause video">▶</button>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
