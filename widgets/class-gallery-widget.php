<?php
/** Responsive 1–9 image mosaic adapted from temp/gallery. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Gallery_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-gallery'; }
	public function get_title() { return esc_html__( 'Jednotka Gallery', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'gallery', 'images', 'mosaic', 'lightbox', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-gallery' ); }
	public function get_script_depends() { return array( 'jednotka-gallery' ); }

	protected function register_controls() {
		$this->start_controls_section( 'gallery_content', array( 'label' => 'Gallery' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => Controls_Manager::TEXT, 'default' => 'Galéria' ) );
		$this->add_control( 'heading_tag', array( 'label' => 'Heading tag', 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4' ) ) );
		$defaults = array();
		for ( $i = 1; $i <= 4; $i++ ) { $defaults[] = array( 'id' => 0, 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-' . $i . '.jpg' ); }
		$this->add_control( 'images', array( 'label' => 'Images', 'type' => Controls_Manager::GALLERY, 'default' => $defaults, 'description' => 'Choose and reorder up to nine images. The layout adapts to the image count; only the first nine are displayed. Alternative text comes from the Media Library.' ) );
		$this->add_control( 'lightbox', array( 'label' => 'Enable lightbox', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'gallery_style', array( 'label' => 'Layout and style', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#0f0f0f', 'selectors' => array( '{{WRAPPER}} .jn-gallery' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'heading_color', array( 'label' => 'Heading color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-gallery__heading' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_font', 'selector' => '{{WRAPPER}} .jn-gallery__heading' ) );
		foreach ( array( 'width' => array( 'Content max width', 320, 1800, '{{WRAPPER}} .jn-gallery__inner', 'max-width' ), 'gap' => array( 'Image gap', 0, 80, '{{WRAPPER}} .jn-gallery__grid', 'gap' ), 'radius' => array( 'Image corners', 0, 100, '{{WRAPPER}} .jn-gallery__item', 'border-radius' ), 'row_height' => array( 'Grid row height', 80, 600, '{{WRAPPER}} .jn-gallery__grid', '--gallery-row' ), 'heading_gap' => array( 'Heading spacing', 0, 150, '{{WRAPPER}} .jn-gallery__heading', 'margin-bottom' ) ) as $key => $field ) {
			$this->add_responsive_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => $field[1], 'max' => $field[2] ) ), 'selectors' => array( $field[3] => $field[4] . ': {{SIZE}}{{UNIT}};' ) ) );
		}
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-gallery' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$images = array();
		foreach ( $s['images'] as $image ) {
			$id = absint( $image['id'] ?? 0 );
			$full = $id ? wp_get_attachment_image_url( $id, 'full' ) : ( $image['url'] ?? '' );
			if ( ! $full ) { continue; }
			$images[] = array( 'full' => $full, 'thumbnail' => ( $id ? wp_get_attachment_image_url( $id, 'large' ) : '' ) ?: $full, 'alt' => $id ? get_post_meta( $id, '_wp_attachment_image_alt', true ) : '' );
			if ( count( $images ) === 9 ) { break; }
		}
		$layouts = array(
			1 => array( array(12,2) ), 2 => array( array(6,2),array(6,2) ), 3 => array( array(6,2),array(6,1),array(6,1) ),
			4 => array_fill(0,4,array(6,1)), 5 => array(array(6,1),array(6,1),array(4,1),array(4,1),array(4,1)),
			6 => array_fill(0,6,array(4,1)), 7 => array(array(6,2),array(6,1),array(6,1),array(3,1),array(3,1),array(3,1),array(3,1)),
			8 => array_fill(0,8,array(3,1)), 9 => array_fill(0,9,array(4,1)),
		);
		$count = count( $images );
		$tag = in_array( $s['heading_tag'], array('h2','h3','h4'), true ) ? $s['heading_tag'] : 'h2';
		$this->add_inline_editing_attributes( 'heading', 'none' );
		$this->add_render_attribute( 'heading', 'class', 'jn-gallery__heading' );
		?>
		<section class="jn-gallery" aria-label="<?php echo esc_attr( $s['heading'] ?: 'Galéria' ); ?>" data-lightbox="<?php echo esc_attr( $s['lightbox'] ); ?>"><div class="jn-gallery__inner">
			<?php if ( $s['heading'] ) : ?><<?php echo $tag; ?> <?php $this->print_render_attribute_string( 'heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></<?php echo $tag; ?>><?php endif; ?>
			<div class="jn-gallery__grid jn-gallery__grid--<?php echo esc_attr( $count ); ?>">
				<?php foreach ( $images as $index => $image ) : ?>
				<a class="jn-gallery__item" href="<?php echo esc_url( $image['full'] ); ?>" data-elementor-open-lightbox="no" style="--c: <?php echo (int) $layouts[$count][$index][0]; ?>; --r: <?php echo (int) $layouts[$count][$index][1]; ?>;" aria-label="<?php echo esc_attr( sprintf( 'Zväčšiť obrázok %d', $index + 1 ) ); ?>"><img src="<?php echo esc_url( $image['thumbnail'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>" loading="lazy" decoding="async" draggable="false" /></a>
				<?php endforeach; ?>
			</div>
			<?php if ( ! $count && \Elementor\Plugin::$instance->editor->is_edit_mode() ) : ?><p>Vyberte obrázky v nastavení galérie.</p><?php endif; ?>
		</div></section>
		<?php
	}
}
