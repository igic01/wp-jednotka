<?php
/** Copied reference stats and testimonials with Elementor editing controls. */
namespace Jednotka\Elementor\Widgets;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;
defined( 'ABSPATH' ) || exit;

class Stats_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-stats'; }
	public function get_title() { return esc_html__( 'Jednotka Stats & Testimonials', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-testimonial-carousel'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-stats' ); }
	public function get_script_depends() { return array( 'jednotka-stats' ); }
	private function text_controls( $stack, $key, $label, $selector, $default = '' ) {
		$stack->add_control( $key, array( 'label' => $label, 'type' => 'quote' === $key ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $default ) );
		$stack->add_control( $key . '_color', array( 'label' => $label . ' color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$stack->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_typography', 'label' => $label . ' font / size', 'selector' => $selector ) );
	}
	protected function register_controls() {
		$this->start_controls_section( 'stats_content', array( 'label' => 'Statistics' ) );
		$stat = new Repeater();
		$this->text_controls( $stat, 'value', 'Number / value', '{{WRAPPER}} {{CURRENT_ITEM}} .jn-stat-value', '100+' );
		$this->text_controls( $stat, 'label', 'Label', '{{WRAPPER}} {{CURRENT_ITEM}} .jn-stat-label', 'klientov' );
		$this->add_control( 'stats', array( 'label' => 'Statistics', 'type' => Controls_Manager::REPEATER, 'fields' => $stat->get_controls(), 'title_field' => '{{{ value }}} — {{{ label }}}', 'default' => array( array( 'value' => '100+', 'label' => 'klientov' ), array( 'value' => '5', 'label' => 'rokov praxe' ), array( 'value' => '15+', 'label' => 'odvetví' ) ), 'description' => 'Click numbers and labels to edit. Whole numbers with optional suffixes count up; other text displays as entered.' ) );
		$this->add_control( 'count_animation', array( 'label' => 'Count-up animation', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'intro_content', array( 'label' => 'Testimonials heading' ) );
		$this->text_controls( $this, 'eyebrow', 'Small heading', '{{WRAPPER}} .testimonials-intro > span', 'Klienti' );
		$this->text_controls( $this, 'heading', 'Heading', '{{WRAPPER}} .testimonials-intro h2', 'Za číslami sú naši klienti' );
		$this->end_controls_section();
		$this->start_controls_section( 'cards_content', array( 'label' => 'Testimonials' ) );
		$card = new Repeater();
		$this->text_controls( $card, 'quote', 'Quote', '{{WRAPPER}} {{CURRENT_ITEM}} p' );
		$this->text_controls( $card, 'name', 'Client name', '{{WRAPPER}} {{CURRENT_ITEM}} strong' );
		$this->text_controls( $card, 'role', 'Client description', '{{WRAPPER}} {{CURRENT_ITEM}} small' );
		$card->add_control( 'avatar_type', array( 'label' => 'Avatar', 'type' => Controls_Manager::SELECT, 'default' => 'reference', 'options' => array( 'reference' => 'Original reference avatar', 'image' => 'Image', 'video' => 'Uploaded video' ) ) );
		$card->add_control( 'avatar_image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'avatar_type' => 'image' ) ) );
		$card->add_control( 'avatar_video', array( 'label' => 'Video', 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ), 'condition' => array( 'avatar_type' => 'video' ) ) );
		$card->add_control( 'avatar_poster', array( 'label' => 'Video poster', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'avatar_type' => 'video' ) ) );
		$card->add_control( 'background', array( 'label' => 'Card background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .testimonial-card-inner' => 'background: {{VALUE}};' ) ) );
		$card->add_responsive_control( 'avatar_size', array( 'label' => 'Avatar size', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 20, 'max' => 150 ) ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}' => '--testimonial-avatar-size: {{SIZE}}{{UNIT}};' ) ) );
		$card->add_responsive_control( 'padding', array( 'label' => 'Card padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em' ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .testimonial-card-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_control( 'testimonials', array( 'label' => 'Testimonials', 'type' => Controls_Manager::REPEATER, 'fields' => $card->get_controls(), 'title_field' => '{{{ name }}}', 'default' => $this->default_testimonials(), 'description' => 'The original five-card layout is used by default. More than five cards use a grid to avoid overlapping.' ) );
		$this->add_control( 'reveal_animation', array( 'label' => 'Reference scroll reveal', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'drift_animation', array( 'label' => 'Floating card motion', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'layout_style', array( 'label' => 'Section layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#0F0F0F', 'selectors' => array( '{{WRAPPER}} .jn-stats' => '--section-dark-bg: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-stats' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'stats_gap', array( 'label' => 'Statistics gap', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 150 ) ), 'selectors' => array( '{{WRAPPER}} .stats-grid' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}
	private function default_testimonials() {
		return array(
			array( 'quote' => '“Jednotka nám upratala marketing do jedného systému. Konečne vieme, čo funguje a prečo.”', 'name' => 'Mária Kováčová', 'role' => 'E-shop s kozmetikou' ),
			array( 'quote' => '“Web, reklamy aj emaily začali spolupracovať. Výsledky prišli rýchlejšie, než sme čakali.”', 'name' => 'Tomáš Hric', 'role' => 'Fitness centrum' ),
			array( 'quote' => '“Najviac oceňujem, že neriešili len pekný dizajn, ale celý predajný tok.”', 'name' => 'Lucia Benková', 'role' => 'Lokálna značka' ),
			array( 'quote' => '“Komunikácia bola jednoduchá, výstupy čisté a kampane mali jasný smer.”', 'name' => 'Peter Varga', 'role' => 'B2B služby' ),
			array( 'quote' => '“Zrazu sme mali jasné priority. Menej chaosu, viac objednávok a lepší prehľad.”', 'name' => 'Nina Poláková', 'role' => 'Prémiový retail' ),
		);
	}
	private function inline_text( $key, $class = '' ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		if ( $class ) $this->add_render_attribute( $key, 'class', $class );
		$this->print_render_attribute_string( $key );
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$title_id = 'testimonials-title-' . $this->get_id();
		$variants = array( 'one', 'two', 'three', 'four', 'five' );
		?>
		<section class="stats-section jn-stats" aria-label="Výsledky agentúry" data-count="<?php echo esc_attr( $s['count_animation'] ); ?>" data-reveal="<?php echo esc_attr( $s['reveal_animation'] ); ?>" data-drift="<?php echo esc_attr( $s['drift_animation'] ); ?>" data-many="<?php echo count( $s['testimonials'] ) > 5 ? 'yes' : 'no'; ?>">
			<div class="stats-grid">
				<?php foreach ( $s['stats'] as $index => $stat ) : ?>
					<div class="stat-item elementor-repeater-item-<?php echo esc_attr( $stat['_id'] ); ?>">
						<strong <?php $this->inline_text( $this->get_repeater_setting_key( 'value', 'stats', $index ), 'jn-stat-value' ); ?><?php if ( preg_match( '/^(\d+)([^\d]*)$/u', trim( $stat['value'] ), $match ) ) : ?> data-count-to="<?php echo esc_attr( $match[1] ); ?>" data-count-suffix="<?php echo esc_attr( $match[2] ); ?>"<?php endif; ?>><?php echo esc_html( $stat['value'] ); ?></strong>
						<span <?php $this->inline_text( $this->get_repeater_setting_key( 'label', 'stats', $index ), 'jn-stat-label' ); ?>><?php echo esc_html( $stat['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="testimonials-wrap" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
				<div class="testimonials-intro">
					<span <?php $this->inline_text( 'eyebrow' ); ?>><?php echo esc_html( $s['eyebrow'] ); ?></span>
					<h2 id="<?php echo esc_attr( $title_id ); ?>" <?php $this->inline_text( 'heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></h2>
				</div>
				<div class="testimonials-field">
					<?php foreach ( $s['testimonials'] as $index => $card ) : $variant = $variants[ $index % 5 ]; ?>
						<article class="testimonial-card testimonial-card--<?php echo esc_attr( $variant ); ?> elementor-repeater-item-<?php echo esc_attr( $card['_id'] ); ?>">
							<div class="testimonial-card-inner">
								<p <?php $this->inline_text( $this->get_repeater_setting_key( 'quote', 'testimonials', $index ) ); ?>><?php echo esc_html( $card['quote'] ); ?></p>
								<footer>
									<span class="testimonial-avatar<?php echo 'reference' === $card['avatar_type'] ? ' testimonial-avatar--' . esc_attr( $variant ) : ' jn-testimonial-avatar--custom'; ?>" aria-hidden="true">
										<?php if ( 'image' === $card['avatar_type'] && ! empty( $card['avatar_image']['url'] ) ) : ?>
											<img src="<?php echo esc_url( $card['avatar_image']['url'] ); ?>" alt="" />
										<?php elseif ( 'video' === $card['avatar_type'] && ! empty( $card['avatar_video']['url'] ) ) : ?>
											<video src="<?php echo esc_url( $card['avatar_video']['url'] ); ?>" poster="<?php echo esc_url( $card['avatar_poster']['url'] ?? '' ); ?>" muted loop playsinline preload="metadata"></video>
										<?php endif; ?>
									</span>
									<span>
										<strong <?php $this->inline_text( $this->get_repeater_setting_key( 'name', 'testimonials', $index ) ); ?>><?php echo esc_html( $card['name'] ); ?></strong>
										<small <?php $this->inline_text( $this->get_repeater_setting_key( 'role', 'testimonials', $index ) ); ?>><?php echo esc_html( $card['role'] ); ?></small>
									</span>
								</footer>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
			<button class="jn-stats__motion" type="button" aria-pressed="false" aria-label="Pause testimonials" data-play-label="Play testimonials" data-pause-label="Pause testimonials">Ⅱ</button>
		</section>
		<?php
	}
}
