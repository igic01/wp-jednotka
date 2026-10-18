<?php
/** Searchable career positions adapted from temp/jobs. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Career_Jobs_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-career-jobs'; }
	public function get_title() { return esc_html__( 'Jednotka Career Jobs', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-post-list'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'career', 'jobs', 'kariera', 'positions', 'search', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-career-jobs' ); }
	public function get_script_depends() { return array( 'jednotka-career-jobs' ); }

	protected function register_controls() {
		$this->start_controls_section( 'jobs_content', array( 'label' => 'Heading and search' ) );
		foreach ( array( 'heading' => array( 'Heading', 'Otvorené pozície' ), 'search_label' => array( 'Search placeholder', 'Hľadať pozíciu' ), 'empty_message' => array( 'No positions message', 'Momentálne nemáme žiadne otvorené pozície.' ), 'no_results' => array( 'No search results message', 'Nič sme nenašli. Skúste iné slovo.' ) ) as $key => $field ) {
			$this->add_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::TEXT, 'default' => $field[1] ) );
		}
		$this->end_controls_section();
		$this->start_controls_section( 'positions_section', array( 'label' => 'Open positions' ) );
		$job = new Repeater();
		foreach ( array( 'job_title' => array( 'Position title', '' ), 'description' => array( 'Short description', '' ), 'location' => array( 'Location', '' ), 'work_type' => array( 'Work type', '' ), 'link_text' => array( 'Link text', 'Pozrieť pozíciu' ) ) as $key => $field ) {
			$job->add_control( $key, array( 'label' => $field[0], 'type' => 'description' === $key ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $field[1] ) );
		}
		$job->add_control( 'link', array( 'label' => 'Position / application link', 'type' => Controls_Manager::URL ) );
		$this->add_control( 'jobs', array( 'label' => 'Positions', 'type' => Controls_Manager::REPEATER, 'fields' => $job->get_controls(), 'default' => array(), 'title_field' => '{{{ job_title || "Position" }}}', 'prevent_empty' => false, 'description' => 'Leave empty until a position opens. Search matches the title, description, location, and work type.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'jobs_style', array( 'label' => 'Layout and style', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array( 'background' => array( 'Background', '.jn-career-jobs', 'background', '#0f0f0f' ), 'heading_color' => array( 'Heading color', '.career-jobs-heading', 'color', '' ), 'title_color' => array( 'Position title color', '.career-job h3', 'color', '' ), 'text_color' => array( 'Position text color', '.career-job-description, {{WRAPPER}} .career-job-meta', 'color', '' ), 'empty_color' => array( 'Empty message color', '.career-jobs-empty', 'color', '' ), 'link_color' => array( 'Position link color', '.career-job-link', 'color', '' ) ) as $key => $field ) {
			$this->add_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::COLOR, 'default' => $field[3], 'selectors' => array( '{{WRAPPER}} ' . $field[1] => $field[2] . ': {{VALUE}};' ) ) );
		}
		foreach ( array( 'heading' => '.career-jobs-heading', 'title' => '.career-job h3', 'description' => '.career-job-description', 'empty' => '.career-jobs-empty' ) as $key => $selector ) {
			$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_font', 'selector' => '{{WRAPPER}} ' . $selector ) );
		}
		$this->add_responsive_control( 'max_width', array( 'label' => 'Content max width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 320, 'max' => 1800 ) ), 'selectors' => array( '{{WRAPPER}} .career-jobs-inner' => 'max-width: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-career-jobs' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key, $class = '' ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		if ( $class ) { $this->add_render_attribute( $key, 'class', $class ); }
		$this->print_render_attribute_string( $key );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$jobs = $s['jobs'];
		?>
		<section class="jn-career-jobs" aria-labelledby="career-jobs-title-<?php echo esc_attr( $this->get_id() ); ?>"><div class="career-jobs-inner">
			<div class="career-jobs-head">
				<h2 id="career-jobs-title-<?php echo esc_attr( $this->get_id() ); ?>" <?php $this->inline_text( 'heading', 'career-jobs-heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></h2>
				<label class="career-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4.5 4.5" /></svg><span class="career-search-sr"><?php echo esc_html( $s['search_label'] ); ?></span><input type="search" placeholder="<?php echo esc_attr( $s['search_label'] ); ?>" autocomplete="off" /></label>
			</div>
			<ul class="career-jobs-list">
			<?php foreach ( $jobs as $index => $job ) : ?>
				<li class="career-job elementor-repeater-item-<?php echo esc_attr( $job['_id'] ); ?>">
					<div class="career-job-copy"><h3 <?php $this->inline_text( $this->get_repeater_setting_key( 'job_title', 'jobs', $index ) ); ?>><?php echo esc_html( $job['job_title'] ); ?></h3>
						<?php if ( $job['description'] ) : ?><p <?php $this->inline_text( $this->get_repeater_setting_key( 'description', 'jobs', $index ), 'career-job-description' ); ?>><?php echo nl2br( esc_html( $job['description'] ) ); ?></p><?php endif; ?>
						<?php if ( $job['location'] || $job['work_type'] ) : ?><div class="career-job-meta"><?php foreach ( array('location','work_type') as $field ) : if ( ! $job[$field] ) { continue; } ?><span <?php $this->inline_text( $this->get_repeater_setting_key( $field, 'jobs', $index ) ); ?>><?php echo esc_html( $job[$field] ); ?></span><?php endforeach; ?></div><?php endif; ?>
					</div>
					<?php if ( ! empty( $job['link']['url'] ) ) : $key = 'job_link_' . $index; $this->add_link_attributes( $key, $job['link'] ); $this->add_render_attribute( $key, 'class', 'career-job-link' ); ?>
					<a <?php $this->print_render_attribute_string( $key ); ?>><span <?php $this->inline_text( $this->get_repeater_setting_key( 'link_text', 'jobs', $index ) ); ?>><?php echo esc_html( $job['link_text'] ); ?></span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" /></svg></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
			</ul>
			<p class="career-jobs-empty" role="status" <?php if ( $jobs ) { echo 'hidden'; } ?>><span data-jobs-empty <?php $this->inline_text( 'empty_message' ); ?>><?php echo esc_html( $s['empty_message'] ); ?></span><span data-jobs-no-results hidden <?php $this->inline_text( 'no_results' ); ?>><?php echo esc_html( $s['no_results'] ); ?></span></p>
		</div></section>
		<?php
	}
}
