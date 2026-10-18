<?php
/** Sticky gallery and before/after comparison adapted from the temp reference. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Showcase_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-showcase'; }
	public function get_title() { return esc_html__( 'Jednotka Showcase & Comparison', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-image-before-after'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'showcase', 'gallery', 'before', 'after', 'comparison', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-showcase' ); }
	public function get_script_depends() { return array( 'jednotka-showcase' ); }

	private function text_controls( $key, $label, $selector, $default, $multiline = false ) {
		$this->add_control( $key, array( 'label' => $label, 'type' => $multiline ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $default ) );
		$this->add_control( $key . '_color', array( 'label' => $label . ' color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_typography', 'label' => $label . ' font / size', 'selector' => $selector ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'intro_section', array( 'label' => 'Gallery introduction' ) );
		$this->text_controls( 'heading', 'Heading', '{{WRAPPER}} .jn-showcase__heading', 'Designing things people love to use.', true );
		$this->add_control( 'heading_tag', array( 'label' => 'Heading tag', 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3' ) ) );
		$this->text_controls( 'description', 'Description', '{{WRAPPER}} .jn-showcase__lead', 'A small studio focused on clean interfaces, thoughtful details and products that feel effortless. Scroll to explore a selection of recent work.', true );
		$this->add_control( 'sticky', array( 'label' => 'Sticky gallery on desktop', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => 'Keep the parent Elementor container overflow visible. Gallery and introduction stack on smaller screens.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'gallery_section', array( 'label' => 'Gallery items' ) );
		$image = new Repeater();
		$image->add_control( 'content_type', array( 'label' => 'Content type', 'type' => Controls_Manager::SELECT, 'default' => 'image', 'options' => array( 'image' => 'Image', 'text' => 'Text' ) ) );
		$image->add_control( 'text', array( 'label' => 'Text', 'type' => Controls_Manager::TEXTAREA, 'condition' => array( 'content_type' => 'text' ) ) );
		$image->add_control( 'text_color', array( 'label' => 'Text color', 'type' => Controls_Manager::COLOR, 'condition' => array( 'content_type' => 'text' ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .jn-showcase__item-text' => 'color: {{VALUE}};' ) ) );
		$image->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'item_text_font', 'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .jn-showcase__item-text', 'condition' => array( 'content_type' => 'text' ) ) );
		$image->add_control( 'image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'content_type' => 'image' ) ) );
		$image->add_control( 'alt', array( 'label' => 'Alternative text', 'type' => Controls_Manager::TEXT, 'condition' => array( 'content_type' => 'image' ), 'description' => 'Describe the image; leave empty for decorative images.' ) );
		$image->add_control( 'fit', array( 'condition' => array( 'content_type' => 'image' ), 'label' => 'Image fit', 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => 'Fill frame', 'contain' => 'Show complete image' ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} img' => 'object-fit: {{VALUE}};' ) ) );
		$defaults = array();
		foreach ( array( 'River valley between mountains', 'Misty forest landscape', 'Green hills under a clear sky', 'Waterfall in the woods' ) as $index => $alt ) {
			$defaults[] = array( 'image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-' . ( $index + 1 ) . '.jpg' ), 'alt' => $alt );
		}
		$this->add_control( 'gallery', array( 'label' => 'Items', 'type' => Controls_Manager::REPEATER, 'fields' => $image->get_controls(), 'default' => $defaults, 'title_field' => '{{{ content_type === "text" ? "Text" : (alt || "Gallery image") }}}', 'prevent_empty' => false ) );
		$this->end_controls_section();

		$this->start_controls_section( 'comparison_section', array( 'label' => 'Before / after comparison' ) );
		$this->add_control( 'show_comparison', array( 'label' => 'Show before / after section', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->text_controls( 'compare_heading', 'Heading', '{{WRAPPER}} .jn-showcase__compare-heading', 'See the difference.', true );
		$this->text_controls( 'compare_description', 'Description', '{{WRAPPER}} .jn-showcase__compare-description', 'Drag the line left or right to reveal how a rough starting point turns into a finished result. Every detail matters.', true );
		foreach ( array( 'before' => 'Before', 'after' => 'After' ) as $key => $label ) {
			$this->add_control( $key . '_image', array( 'label' => $label . ' image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/' . $key . '.jpg' ) ) );
			$this->add_control( $key . '_alt', array( 'label' => $label . ' image alternative text', 'type' => Controls_Manager::TEXT, 'default' => $label ) );
			$this->text_controls( $key . '_label', $label . ' label', '{{WRAPPER}} .jn-showcase__label--' . $key, $label );
		}
		$this->add_control( 'slider_label', array( 'label' => 'Slider accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Before and after comparison' ) );
		$this->add_control( 'position', array( 'label' => 'Comparison position (%)', 'type' => Controls_Manager::NUMBER, 'min' => 0, 'max' => 100, 'step' => 1, 'default' => 65, 'description' => 'Before is on the left; After is on the right. Fully left shows After; fully right shows Before.' ) );
		$this->add_control( 'animate', array( 'label' => 'Intro sweep when visible', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'snap', array( 'label' => 'Snap comparison to viewport', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout_section', array( 'label' => 'Theme and layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array( 'background' => 'Gallery background', 'compare' => 'Comparison background', 'accent' => 'Comparison accent' ) as $key => $label ) {
			$this->add_control( $key . '_color', array( 'label' => $label, 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-showcase' => '--jn-showcase-' . $key . ': {{VALUE}};' ) ) );
		}
		foreach ( array( 'gap' => 'Gallery gap', 'radius' => 'Image corners' ) as $key => $label ) {
			$this->add_responsive_control( $key, array( 'label' => $label, 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'rem' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ), 'rem' => array( 'min' => 0, 'max' => 8 ) ), 'selectors' => array( '{{WRAPPER}} .jn-showcase' => '--jn-showcase-' . $key . ': {{SIZE}}{{UNIT}};' ) ) );
		}
		$this->add_responsive_control( 'gallery_padding', array( 'label' => 'Gallery padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-showcase__split' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'compare_padding', array( 'label' => 'Comparison padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-showcase__compare' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'compare_width', array( 'label' => 'Comparison maximum width', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', '%' ), 'range' => array( 'px' => array( 'min' => 200, 'max' => 1800 ), '%' => array( 'min' => 20, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .jn-showcase__slider' => 'max-width: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key, $class ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		$this->add_render_attribute( $key, 'class', $class );
		$this->print_render_attribute_string( $key );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$id = $this->get_id();
		$heading_tag = in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3' ), true ) ? $s['heading_tag'] : 'h1';
		$position = max( 0, min( 100, (float) $s['position'] ) );
		?>
		<div class="jn-showcase" data-sticky="<?php echo esc_attr( $s['sticky'] ); ?>" data-animate="<?php echo esc_attr( $s['animate'] ); ?>" data-snap="<?php echo esc_attr( $s['snap'] ); ?>" data-position="<?php echo esc_attr( $position ); ?>">
			<section class="jn-showcase__split" aria-labelledby="showcase-title-<?php echo esc_attr( $id ); ?>">
				<div class="jn-showcase__text"><div class="jn-showcase__text-inner">
					<<?php echo $heading_tag; ?> id="showcase-title-<?php echo esc_attr( $id ); ?>" <?php $this->inline_text( 'heading', 'jn-showcase__heading' ); ?>><?php echo nl2br( esc_html( $s['heading'] ) ); ?></<?php echo $heading_tag; ?>>
					<p <?php $this->inline_text( 'description', 'jn-showcase__lead' ); ?>><?php echo esc_html( $s['description'] ); ?></p>
				</div></div>
				<div class="jn-showcase__images">
					<?php foreach ( $s['gallery'] as $index => $item ) : ?>
						<?php if ( 'text' === ( $item['content_type'] ?? 'image' ) ) : ?>
							<div class="jn-showcase__text-item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>"><p <?php $this->inline_text( $this->get_repeater_setting_key( 'text', 'gallery', $index ), 'jn-showcase__item-text' ); ?>><?php echo nl2br( esc_html( $item['text'] ?? '' ) ); ?></p></div>
						<?php continue; endif; if ( empty( $item['image']['url'] ) ) { continue; } ?>
						<figure class="jn-showcase__image elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>"><img src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" loading="lazy" decoding="async" width="1200" height="900" /></figure>
					<?php endforeach; ?>
				</div>
			</section>
			<?php if ( 'yes' === ( $s['show_comparison'] ?? 'yes' ) ) : ?>
			<section class="jn-showcase__compare" aria-labelledby="showcase-compare-title-<?php echo esc_attr( $id ); ?>">
				<div class="jn-showcase__compare-text">
					<h2 id="showcase-compare-title-<?php echo esc_attr( $id ); ?>" <?php $this->inline_text( 'compare_heading', 'jn-showcase__compare-heading' ); ?>><?php echo nl2br( esc_html( $s['compare_heading'] ) ); ?></h2>
					<p id="showcase-compare-description-<?php echo esc_attr( $id ); ?>" <?php $this->inline_text( 'compare_description', 'jn-showcase__compare-description' ); ?>><?php echo esc_html( $s['compare_description'] ); ?></p>
				</div>
				<div class="jn-showcase__slider" data-edge="<?php echo $position <= 0 ? 'left' : ( $position >= 100 ? 'right' : 'none' ); ?>" style="--cx: <?php echo esc_attr( $position ); ?>%;">
					<div class="jn-showcase__media">
					<?php foreach ( array( 'after', 'before' ) as $key ) : if ( empty( $s[ $key . '_image' ]['url'] ) ) { continue; } ?>
						<img class="jn-showcase__compare-image<?php echo 'before' === $key ? ' jn-showcase__compare-image--before' : ''; ?>" src="<?php echo esc_url( $s[ $key . '_image' ]['url'] ); ?>" alt="<?php echo esc_attr( $s[ $key . '_alt' ] ); ?>" loading="lazy" decoding="async" width="1600" height="1000" />
					<?php endforeach; ?>
					</div>
					<span <?php $this->inline_text( 'before_label', 'jn-showcase__label jn-showcase__label--before' ); ?>><?php echo esc_html( $s['before_label'] ); ?></span>
					<span <?php $this->inline_text( 'after_label', 'jn-showcase__label jn-showcase__label--after' ); ?>><?php echo esc_html( $s['after_label'] ); ?></span>
					<input class="jn-showcase__range" type="range" min="0" max="100" step="1" value="<?php echo esc_attr( $position ); ?>" aria-label="<?php echo esc_attr( $s['slider_label'] ); ?>" aria-describedby="showcase-compare-description-<?php echo esc_attr( $id ); ?>" />
					<div class="jn-showcase__handle" aria-hidden="true"><span class="jn-showcase__knob"><svg viewBox="0 0 24 24"><path d="m8 7-5 5 5 5m8-10 5 5-5 5M3 12h18" /></svg></span></div>
				</div>
			</section>
			<?php endif; ?>
		</div>
		<?php
	}
}
