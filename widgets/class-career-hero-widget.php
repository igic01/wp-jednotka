<?php
/** Rounded career-page photo hero adapted from temp/hero. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Career_Hero_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-career-hero'; }
	public function get_title() { return esc_html__( 'Jednotka Career Hero', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-image-bold'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'career', 'kariera', 'hero', 'photo', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-career-hero' ); }

	protected function register_controls() {
		$this->start_controls_section( 'hero_content', array( 'label' => 'Title and image' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => Controls_Manager::TEXT, 'default' => 'Kariéra' ) );
		$this->add_control( 'heading_tag', array( 'label' => 'Heading tag', 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3' ) ) );
		$this->add_control( 'image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/team/placeholder.jpg' ) ) );
		$this->add_control( 'image_alt', array( 'label' => 'Image alternative text', 'type' => Controls_Manager::TEXT, 'description' => 'Leave empty if the image is decorative.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'hero_style', array( 'label' => 'Layout and style', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'heading_color', array( 'label' => 'Heading color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-career-hero__heading' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_font', 'selector' => '{{WRAPPER}} .jn-career-hero__heading' ) );
		$this->add_control( 'background', array( 'label' => 'Section background', 'type' => Controls_Manager::COLOR, 'default' => '#0F0F0F', 'selectors' => array( '{{WRAPPER}} .jn-career-hero' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'shade_opacity', array( 'label' => 'Bottom shade opacity', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.05 ) ), 'selectors' => array( '{{WRAPPER}} .jn-career-hero__shade' => 'opacity: {{SIZE}};' ) ) );
		foreach ( array( 'width' => array( 'Maximum width', 320, 1800, 'px', '.jn-career-hero__card', 'max-width' ), 'height' => array( 'Minimum height', 160, 1000, 'px', '.jn-career-hero__card', 'min-height' ), 'radius' => array( 'Corners', 0, 100, 'px', '.jn-career-hero__card', 'border-radius' ), 'image_x' => array( 'Image horizontal position', 0, 100, '%', '.jn-career-hero__image', '--career-image-x' ), 'image_y' => array( 'Image vertical position', 0, 100, '%', '.jn-career-hero__image', '--career-image-y' ) ) as $key => $field ) {
			$this->add_responsive_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::SLIDER, 'size_units' => array( $field[3] ), 'range' => array( $field[3] => array( 'min' => $field[1], 'max' => $field[2] ) ), 'selectors' => array( '{{WRAPPER}} ' . $field[4] => $field[5] . ': {{SIZE}}{{UNIT}};' ) ) );
		}
		foreach ( array( 'outer_padding' => array( 'Outer spacing', '.jn-career-hero' ), 'title_padding' => array( 'Title padding', '.jn-career-hero__card' ) ) as $key => $field ) {
			$this->add_responsive_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} ' . $field[1] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		}
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$tag = in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3' ), true ) ? $s['heading_tag'] : 'h1';
		$title_id = 'career-title-' . $this->get_id();
		$this->add_inline_editing_attributes( 'heading', 'none' );
		$this->add_render_attribute( 'heading', 'class', 'jn-career-hero__heading' );
		$this->add_render_attribute( 'heading', 'id', $title_id );
		?>
		<div class="jn-career-hero">
			<section class="jn-career-hero__card" <?php if ( $s['heading'] ) : ?>aria-labelledby="<?php echo esc_attr( $title_id ); ?>"<?php else : ?>aria-label="Kariéra"<?php endif; ?>>
				<?php if ( ! empty( $s['image']['url'] ) ) : ?><img class="jn-career-hero__image" src="<?php echo esc_url( $s['image']['url'] ); ?>" alt="<?php echo esc_attr( $s['image_alt'] ); ?>" decoding="async" /><?php endif; ?>
				<span class="jn-career-hero__shade" aria-hidden="true"></span>
				<<?php echo $tag; ?> <?php $this->print_render_attribute_string( 'heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></<?php echo $tag; ?>>
			</section>
		</div>
		<?php
	}
}
