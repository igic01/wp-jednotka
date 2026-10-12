<?php
/** Copied results section with editable projects and media. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Results_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-results'; }
	public function get_title() { return esc_html__( 'Jednotka Results', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'results', 'projects', 'portfolio', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-results' ); }
	public function get_script_depends() { return array( 'jednotka-results' ); }

	private function text_controls( $stack, $key, $label, $selector, $default = '' ) {
		$stack->add_control( $key, array( 'label' => $label, 'type' => 'heading' === $key ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $default ) );
		$stack->add_control( $key . '_color', array( 'label' => $label . ' color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$stack->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_typography', 'label' => $label . ' font / size', 'selector' => $selector ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'heading_section', array( 'label' => 'Heading' ) );
		$this->text_controls( $this, 'heading', 'Heading', '{{WRAPPER}} .jn-results h2', "Naše\nvýsledky" );
		$this->add_control( 'animate', array( 'label' => 'Scroll reveal', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'projects_section', array( 'label' => 'Projects' ) );
		$project = new Repeater();
		$project->add_control( 'column', array( 'label' => 'Column', 'type' => Controls_Manager::SELECT, 'default' => 'left', 'options' => array( 'left' => 'Left — below heading', 'right' => 'Right' ) ) );
		$this->text_controls( $project, 'name', 'Project name', '{{WRAPPER}} {{CURRENT_ITEM}} h3' );
		$this->text_controls( $project, 'description', 'Description', '{{WRAPPER}} {{CURRENT_ITEM}} p' );
		$project->add_control( 'link', array( 'label' => 'Project link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$project->add_control( 'media_type', array( 'label' => 'Project media', 'type' => Controls_Manager::SELECT, 'default' => 'image', 'options' => array( 'image' => 'Image', 'video' => 'Uploaded video' ) ) );
		$project->add_control( 'image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ), 'condition' => array( 'media_type' => 'image' ) ) );
		$project->add_control( 'video', array( 'label' => 'Video', 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ), 'condition' => array( 'media_type' => 'video' ) ) );
		$project->add_control( 'poster', array( 'label' => 'Video poster', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'media_type' => 'video' ) ) );
		$project->add_control( 'alt', array( 'label' => 'Media description', 'type' => Controls_Manager::TEXT, 'description' => 'Alternative text for meaningful media. Leave empty for decorative media.' ) );
		$project->add_control( 'show_badge', array( 'label' => 'Show badge', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->text_controls( $project, 'badge', 'Badge label', '{{WRAPPER}} {{CURRENT_ITEM}} .content-badge', 'Redizajn' );
		$project->add_control( 'badge_background', array( 'label' => 'Badge background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .content-badge' => 'background: {{VALUE}};' ) ) );
		$project->add_control( 'icon_type', array( 'label' => 'Badge icon', 'type' => Controls_Manager::SELECT, 'default' => 'reference', 'options' => array( 'reference' => 'Original reference icon', 'image' => 'Image', 'video' => 'Uploaded video', 'none' => 'No icon' ) ) );
		$project->add_control( 'icon_image', array( 'label' => 'Icon image', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'icon_type' => 'image' ) ) );
		$project->add_control( 'icon_video', array( 'label' => 'Icon video', 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ), 'condition' => array( 'icon_type' => 'video' ) ) );
		$project->add_control( 'icon_poster', array( 'label' => 'Icon video poster', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'icon_type' => 'video' ) ) );
		$project->add_control( 'icon_color', array( 'label' => 'Reference icon color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} svg.content-badge-icon' => 'stroke: {{VALUE}};' ), 'condition' => array( 'icon_type' => 'reference' ) ) );
		$project->add_control( 'media_fit', array( 'label' => 'Media fit', 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => 'Fill frame', 'contain' => 'Show complete media' ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .project-media > img, {{WRAPPER}} {{CURRENT_ITEM}} .project-media > video' => 'object-fit: {{VALUE}};' ) ) );
		$project->add_responsive_control( 'radius', array( 'label' => 'Media corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 150 ) ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .project-media' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$project->add_control( 'media_background', array( 'label' => 'Media background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .project-media' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'projects', array( 'label' => 'Projects', 'type' => Controls_Manager::REPEATER, 'fields' => $project->get_controls(), 'title_field' => '{{{ name }}}', 'default' => array(
			array( 'name' => 'Progym', 'description' => 'Fitness centrum', 'column' => 'left' ),
			array( 'name' => 'Štýlová a krásna', 'description' => 'E-shop s kozmetikou', 'column' => 'left' ),
			array( 'name' => 'Cammino OZ', 'description' => 'Nezisková organizácia', 'column' => 'right' ),
			array( 'name' => 'Štýlová a krásna', 'description' => 'E-shop s kozmetikou', 'column' => 'right' ),
			array( 'name' => 'Štýlová a krásna', 'description' => 'E-shop s kozmetikou', 'column' => 'right' ),
		), 'description' => 'Add, remove, or reorder projects. Each column follows the item order. On mobile, the left column appears first, as in the reference.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'layout_section', array( 'label' => 'Section layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-results' => '--section-dark-bg: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-results' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		foreach ( array( 'column_gap' => array( 'Column gap', '.results-grid' ), 'project_gap' => array( 'Project gap', '.results-column' ) ) as $key => $definition ) {
			$this->add_responsive_control( $key, array( 'label' => $definition[0], 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} ' . $definition[1] => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		}
		$this->end_controls_section();
	}

	private function inline_text( $key ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		$this->print_render_attribute_string( $key );
	}

	private function render_project( $project, $index ) {
		$link_key = 'project_link_' . $index;
		$this->add_render_attribute( $link_key, 'class', 'project-card-link' );
		$this->add_link_attributes( $link_key, $project['link'] );
		$has_video = 'video' === $project['media_type'] && ! empty( $project['video']['url'] );
		$has_icon_video = 'yes' === $project['show_badge'] && 'video' === $project['icon_type'] && ! empty( $project['icon_video']['url'] );
		?>
		<article class="project-card elementor-repeater-item-<?php echo esc_attr( $project['_id'] ); ?>">
			<a <?php $this->print_render_attribute_string( $link_key ); ?>>
				<figure class="project-media">
					<?php if ( $has_video ) : ?>
						<video class="jn-project-video" src="<?php echo esc_url( $project['video']['url'] ); ?>" poster="<?php echo esc_url( $project['poster']['url'] ?? '' ); ?>" <?php echo empty( $project['alt'] ) ? 'aria-hidden="true"' : 'aria-label="' . esc_attr( $project['alt'] ) . '"'; ?> muted loop playsinline preload="metadata"></video>
					<?php elseif ( 'image' === $project['media_type'] && ! empty( $project['image']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $project['image']['url'] ); ?>" alt="<?php echo esc_attr( $project['alt'] ); ?>" />
					<?php endif; ?>
					<?php if ( 'yes' === $project['show_badge'] ) : ?>
						<span class="content-badge">
							<?php if ( 'reference' === $project['icon_type'] ) : ?>
								<svg class="content-badge-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h3l8 4V7l-8 4H4z" /><path d="M7 13v5.5" /><path d="M15 10.5h3" /><circle cx="19" cy="14" r="2.4" /></svg>
							<?php elseif ( 'image' === $project['icon_type'] && ! empty( $project['icon_image']['url'] ) ) : ?>
								<img class="content-badge-icon" src="<?php echo esc_url( $project['icon_image']['url'] ); ?>" alt="" />
							<?php elseif ( $has_icon_video ) : ?>
								<video class="content-badge-icon" src="<?php echo esc_url( $project['icon_video']['url'] ); ?>" poster="<?php echo esc_url( $project['icon_poster']['url'] ?? '' ); ?>" aria-hidden="true" muted loop playsinline preload="metadata"></video>
							<?php endif; ?>
							<span <?php $this->inline_text( $this->get_repeater_setting_key( 'badge', 'projects', $index ) ); ?>><?php echo esc_html( $project['badge'] ); ?></span>
						</span>
					<?php endif; ?>
				</figure>
				<h3 <?php $this->inline_text( $this->get_repeater_setting_key( 'name', 'projects', $index ) ); ?>><?php echo esc_html( $project['name'] ); ?></h3>
				<p <?php $this->inline_text( $this->get_repeater_setting_key( 'description', 'projects', $index ) ); ?>><?php echo esc_html( $project['description'] ); ?></p>
			</a>
			<?php if ( $has_video || $has_icon_video ) : ?>
				<button class="jn-results__video-toggle" type="button" aria-label="Play project video" aria-pressed="false" data-play-label="Play project video" data-pause-label="Pause project video">▶</button>
			<?php endif; ?>
		</article>
		<?php
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$title_id = 'results-title-' . $this->get_id();
		?>
		<section class="results-section jn-results" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" data-animate="<?php echo esc_attr( $s['animate'] ); ?>">
			<div class="results-grid">
				<?php foreach ( array( 'left', 'right' ) as $column ) : ?>
					<div class="results-column results-column--<?php echo esc_attr( $column ); ?>">
						<?php if ( 'left' === $column ) : ?>
							<h2 id="<?php echo esc_attr( $title_id ); ?>" <?php $this->inline_text( 'heading' ); ?>><?php echo nl2br( wp_kses( $s['heading'], array( 'br' => array() ) ) ); ?></h2>
						<?php endif; ?>
						<?php foreach ( $s['projects'] as $index => $project ) : ?>
							<?php if ( $column === $project['column'] ) { $this->render_project( $project, $index ); } ?>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
