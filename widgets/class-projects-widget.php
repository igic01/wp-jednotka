<?php
/** Editable tilted, looping project gallery based on temp/projects. */
namespace Jednotka\Elementor\Widgets;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;
defined( 'ABSPATH' ) || exit;

class Projects_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-projects'; }
	public function get_title() { return esc_html__( 'Jednotka Projects', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'projects', 'portfolio', 'gallery', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-projects' ); }
	public function get_script_depends() { return array( 'jednotka-projects' ); }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Projects' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => Controls_Manager::TEXT, 'default' => 'Projekty' ) );
		$this->add_control( 'gallery', array( 'label' => 'Project images', 'type' => Controls_Manager::GALLERY, 'default' => array(), 'description' => 'Select and reorder images together. Images are arranged across the columns automatically; titles, captions, and alternative text come from the Media Library. Leave empty to use the existing project entries below.' ) );
		$item = new Repeater();
		$item->add_control( 'name', array( 'label' => 'Project name', 'type' => Controls_Manager::TEXT ) );
		$item->add_control( 'type', array( 'label' => 'Project type', 'type' => Controls_Manager::TEXT ) );
		$item->add_control( 'photo', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ) );
		$item->add_control( 'alt', array( 'label' => 'Image alternative text', 'type' => Controls_Manager::TEXT, 'description' => 'Leave empty to use the project name.' ) );
		$item->add_control( 'link', array( 'label' => 'Project link', 'type' => Controls_Manager::URL ) );
		$item->add_control( 'ratio', array( 'label' => 'Image proportions', 'type' => Controls_Manager::SELECT, 'default' => '4 / 5', 'options' => array( '4 / 5' => 'Portrait 4:5', '3 / 4' => 'Portrait 3:4', '1 / 1' => 'Square', '4 / 3' => 'Landscape 4:3' ) ) );
		$item->add_control( 'column', array( 'label' => 'Column', 'type' => Controls_Manager::SELECT, 'default' => '1', 'options' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ) ) );
		$this->add_control( 'projects', array( 'label' => 'Projects', 'type' => Controls_Manager::REPEATER, 'fields' => $item->get_controls(), 'title_field' => '{{{ name }}}', 'prevent_empty' => false, 'default' => array(
			array( 'name' => 'Progym', 'type' => 'Fitness centrum', 'ratio' => '4 / 5', 'column' => 1, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-1.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'E-shop s kozmetikou', 'ratio' => '1 / 1', 'column' => 1, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-2.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Nezisková organizácia', 'ratio' => '3 / 4', 'column' => 1, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-3.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'Branding', 'ratio' => '4 / 3', 'column' => 1, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-4.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Grafika', 'ratio' => '1 / 1', 'column' => 1, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'Email automatizácie', 'ratio' => '1 / 1', 'column' => 2, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/team/placeholder.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'Redizajn webu', 'ratio' => '3 / 4', 'column' => 2, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-1.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Branding', 'ratio' => '4 / 3', 'column' => 2, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-2.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'Grafika', 'ratio' => '4 / 5', 'column' => 2, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-3.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'Email marketing', 'ratio' => '1 / 1', 'column' => 2, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-4.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Grafika', 'ratio' => '3 / 4', 'column' => 3, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'PPC reklamy', 'ratio' => '4 / 5', 'column' => 3, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/team/placeholder.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'Web', 'ratio' => '1 / 1', 'column' => 3, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-1.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Redizajn', 'ratio' => '3 / 4', 'column' => 3, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-2.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'Web', 'ratio' => '4 / 3', 'column' => 3, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-3.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'Email marketing', 'ratio' => '4 / 3', 'column' => 4, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-4.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Web', 'ratio' => '3 / 4', 'column' => 4, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'Redizajn', 'ratio' => '4 / 5', 'column' => 4, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/team/placeholder.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'PPC reklamy', 'ratio' => '1 / 1', 'column' => 4, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-1.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Branding', 'ratio' => '3 / 4', 'column' => 4, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-2.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Web', 'ratio' => '4 / 5', 'column' => 5, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-3.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'Branding', 'ratio' => '1 / 1', 'column' => 5, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-4.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'Redizajn', 'ratio' => '3 / 4', 'column' => 5, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'PPC reklamy', 'ratio' => '4 / 3', 'column' => 5, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/team/placeholder.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Email marketing', 'ratio' => '4 / 5', 'column' => 5, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-1.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'Grafika', 'ratio' => '3 / 4', 'column' => 6, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-2.jpg' ) ),
			array( 'name' => 'Cammino OZ', 'type' => 'Redizajn', 'ratio' => '4 / 3', 'column' => 6, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-3.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'Grafika', 'ratio' => '1 / 1', 'column' => 6, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-4.jpg' ) ),
			array( 'name' => 'Štýlová a krásna', 'type' => 'Web', 'ratio' => '4 / 5', 'column' => 6, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ),
			array( 'name' => 'Progym', 'type' => 'Branding', 'ratio' => '3 / 4', 'column' => 6, 'photo' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/team/placeholder.jpg' ) ),
		) ) );
		$this->add_control( 'auto_scroll', array( 'label' => 'Automatic scrolling', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'speed', array( 'label' => 'Loop duration (seconds)', 'type' => Controls_Manager::NUMBER, 'default' => 60, 'min' => 15, 'max' => 180, 'description' => 'Higher values move more slowly. Columns have varied speeds.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'style', array( 'label' => 'Section and cards', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#0F0F0F', 'selectors' => array( '{{WRAPPER}} .jn-projects' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'text_color', array( 'label' => 'Text color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-projects' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_font', 'selector' => '{{WRAPPER}} .projects__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'caption_font', 'selector' => '{{WRAPPER}} .projects__caption' ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-projects' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'height', array( 'label' => 'Gallery height', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 300, 'max' => 1200 ), 'vh' => array( 'min' => 40, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .projects__gallery' => '--gallery-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Card gap', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 60 ) ), 'selectors' => array( '{{WRAPPER}} .jn-projects' => '--project-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'radius', array( 'label' => 'Card corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .projects__item' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}
	private function gallery_projects( $images ) {
		$projects = array();
		$ratios = array( '4 / 5', '1 / 1', '3 / 4', '4 / 3' );
		foreach ( $images as $image ) {
			$id = (int) ( $image['id'] ?? 0 );
			$url = $image['url'] ?? '';
			if ( ! $url && $id ) { $url = wp_get_attachment_image_url( $id, 'full' ); }
			if ( ! $url ) { continue; }
			$index = count( $projects );
			$projects[] = array(
				'_id' => 'gallery-' . $index,
				'name' => $id ? get_the_title( $id ) : '',
				'type' => $id ? wp_get_attachment_caption( $id ) : '',
				'alt' => $id ? get_post_meta( $id, '_wp_attachment_image_alt', true ) : '',
				'photo' => array( 'url' => $url ),
				'ratio' => $ratios[ $index % count( $ratios ) ],
				'column' => $index % 6 + 1,
				'link' => array(),
			);
		}
		return $projects;
	}
	private function render_items( $items, $clone = false, $view = 'desktop' ) {
		foreach ( $items as $item ) {
			$ratio = in_array( $item['ratio'], array( '4 / 5', '3 / 4', '1 / 1', '4 / 3' ), true ) ? $item['ratio'] : '4 / 5';
			$link = ! empty( $item['link']['url'] );
			$tag = $link ? 'a' : 'article';
			$key = 'project-' . $view . '-' . $item['_id'] . ( $clone ? '-clone' : '' );
			if ( $link ) { $this->add_link_attributes( $key, $item['link'] ); }
			?>
			<<?php echo $tag; ?> class="projects__item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>" style="--ratio: <?php echo esc_attr( $ratio ); ?>;" <?php if ( $link ) { $this->print_render_attribute_string( $key ); } ?><?php if ( $clone ) { echo ' aria-hidden="true" tabindex="-1"'; } ?>>
				<?php if ( ! empty( $item['photo']['url'] ) ) : ?><img class="projects__image" src="<?php echo esc_url( $item['photo']['url'] ); ?>" alt="<?php echo esc_attr( $clone ? '' : ( $item['alt'] ?: $item['name'] ) ); ?>" draggable="false" loading="lazy" decoding="async" /><?php endif; ?>
				<span class="projects__caption"><strong><?php echo esc_html( $item['name'] ); ?></strong><span><?php echo esc_html( $item['type'] ); ?></span></span>
			</<?php echo $tag; ?>>
			<?php
		}
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$projects = empty( $s['gallery'] ) ? $s['projects'] : $this->gallery_projects( $s['gallery'] );
		$columns = array_fill( 1, 6, array() );
		foreach ( $projects as $item ) { $columns[ max( 1, min( 6, (int) $item['column'] ) ) ][] = $item; }
		$speed = max( 15, min( 180, (float) $s['speed'] ) );
		$durations = array( 62, 48, 70, 54, 66, 52 );
		?>
		<section class="projects jn-projects" aria-labelledby="projects-title-<?php echo esc_attr( $this->get_id() ); ?>" data-auto="<?php echo esc_attr( $s['auto_scroll'] ); ?>" data-static="yes" data-running="no">
			<h2 class="projects__title" id="projects-title-<?php echo esc_attr( $this->get_id() ); ?>"><?php echo esc_html( $s['heading'] ); ?></h2>
			<div class="projects__gallery">
			<?php foreach ( array( 'desktop', 'mobile' ) as $view ) :
				$view_columns = $columns;
				if ( 'mobile' === $view ) {
					$view_columns = array( 1 => array_merge( $columns[1], $columns[2] ), 2 => array_merge( $columns[3], $columns[4] ), 3 => array_merge( $columns[5], $columns[6] ) );
				}
				?>
				<div class="projects__tilt projects__tilt--<?php echo esc_attr( $view ); ?>">
				<?php foreach ( $view_columns as $index => $items ) : if ( ! $items ) { continue; } ?>
					<div class="projects__column projects__column--<?php echo esc_attr( $index ); ?>" style="--duration: <?php echo esc_attr( $durations[ $index - 1 ] * $speed / 60 ); ?>s;">
						<div class="projects__track"><div class="projects__group"><?php $this->render_items( $items, false, $view ); ?></div><div class="projects__group projects__clone" aria-hidden="true"><?php $this->render_items( $items, true, $view ); ?></div></div>
					</div>
				<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
