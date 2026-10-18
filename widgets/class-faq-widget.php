<?php
/** Editable FAQ section based on temp/faq. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class FAQ_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-faq'; }
	public function get_title() { return esc_html__( 'Jednotka FAQ', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-help-o'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'faq', 'questions', 'accordion', 'otazky', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-faq' ); }
	public function get_script_depends() { return array( 'jednotka-faq' ); }

	protected function register_controls() {
		$this->start_controls_section( 'intro', array( 'label' => 'Introduction' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => Controls_Manager::TEXT, 'default' => 'Časté otázky' ) );
		$this->add_control( 'description', array( 'label' => 'Description', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Nenašli ste odpoveď? Napíšte nám a ozveme sa do 1 pracovného dňa.' ) );
		$this->add_control( 'show_contact', array( 'label' => 'Show contact link', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'contact_text', array( 'label' => 'Contact link text', 'type' => Controls_Manager::TEXT, 'default' => 'Napísať nám', 'condition' => array( 'show_contact' => 'yes' ) ) );
		$this->add_control( 'contact_link', array( 'label' => 'Contact link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#kontakt' ), 'condition' => array( 'show_contact' => 'yes' ), 'description' => 'For the default link, set the contact section’s Advanced → CSS ID to kontakt.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'questions_section', array( 'label' => 'Questions and answers' ) );
		$item = new Repeater();
		$item->add_control( 'question', array( 'label' => 'Question', 'type' => Controls_Manager::TEXT ) );
		$item->add_control( 'answer', array( 'label' => 'Answer', 'type' => Controls_Manager::TEXTAREA ) );
		$item->add_control( 'initial_open', array( 'label' => 'Initially expanded', 'type' => Controls_Manager::SWITCHER ) );
		$this->add_control( 'stable_height', array( 'label' => 'Keep section height stable', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => 'Reserve room for the tallest answer. Only one question opens at a time.' ) );
		$this->add_control( 'questions', array( 'label' => 'Questions', 'type' => Controls_Manager::REPEATER, 'fields' => $item->get_controls(), 'title_field' => '{{{ question }}}', 'prevent_empty' => false, 'default' => array(
			array( 'question' => 'Koľko to stojí?', 'answer' => 'Cena závisí od rozsahu. Po konzultácii dostanete konkrétnu ponuku, nie balíček od stola.' ),
			array( 'question' => 'Ako dlho trvá spolupráca?', 'answer' => 'Od jednorazových projektov po dlhodobú správu marketingu. Dohodneme si to, čo dáva zmysel.' ),
			array( 'question' => 'Som viazaný zmluvou na roky?', 'answer' => 'Nie. Spolupracujeme na základe výsledkov, nie viazanosti.' ),
			array( 'question' => 'Budem mať prehľad o tom, čo sa deje?', 'answer' => 'Áno. Dostanete pravidelné reporty a jeden kontaktný bod namiesto piatich.' ),
		) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'Section and text', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array(
			'background' => array( 'Background', '{{WRAPPER}} .jn-faq', 'background-color', '#0f0f0f' ),
			'heading_color' => array( 'Heading color', '{{WRAPPER}} .services-faq-head h2', 'color', '' ),
			'description_color' => array( 'Description color', '{{WRAPPER}} .services-faq-head p', 'color', '' ),
			'question_color' => array( 'Question color', '{{WRAPPER}} .services-faq-list summary', 'color', '' ),
			'answer_color' => array( 'Answer color', '{{WRAPPER}} .services-faq-list p', 'color', '' ),
			'icon_color' => array( 'Expand icon color', '{{WRAPPER}} .services-faq-list summary::after', 'color', '#ff6b00' ),
			'border_color' => array( 'Divider color', '{{WRAPPER}} .services-faq-list, {{WRAPPER}} .services-faq-list details', 'border-color', '' ),
			'contact_color' => array( 'Contact link color', '{{WRAPPER}} .services-faq-cta', 'color', '' ),
		) as $key => $control ) {
			$this->add_control( $key, array( 'label' => $control[0], 'type' => Controls_Manager::COLOR, 'default' => $control[3], 'selectors' => array( $control[1] => $control[2] . ': {{VALUE}};' ) ) );
		}
		foreach ( array( 'heading' => '.services-faq-head h2', 'description' => '.services-faq-head p', 'question' => '.services-faq-list summary', 'answer' => '.services-faq-list p', 'contact' => '.services-faq-cta' ) as $key => $selector ) {
			$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_font', 'selector' => '{{WRAPPER}} ' . $selector ) );
		}
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-faq' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Column gap', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 20, 'max' => 160 ) ), 'selectors' => array( '{{WRAPPER}} .jn-faq__layout' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		$this->print_render_attribute_string( $key );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$editing = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$this->add_link_attributes( 'contact_link', $s['contact_link'] );
		$this->add_render_attribute( 'contact_link', 'class', 'services-faq-cta' );
		?>
		<section class="services-faq-section jn-faq" data-stable-height="<?php echo esc_attr( $s['stable_height'] ); ?>" aria-labelledby="faq-title-<?php echo esc_attr( $this->get_id() ); ?>">
			<div class="jn-faq__layout">
				<div class="services-faq-head">
					<h2 id="faq-title-<?php echo esc_attr( $this->get_id() ); ?>"><span <?php $this->inline_text( 'heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></span></h2>
					<p <?php $this->inline_text( 'description' ); ?>><?php echo nl2br( esc_html( $s['description'] ) ); ?></p>
					<?php if ( 'yes' === $s['show_contact'] && ! empty( $s['contact_link']['url'] ) ) : ?>
						<a <?php $this->print_render_attribute_string( 'contact_link' ); ?>><span class="arrow-badge-button" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span><span <?php $this->inline_text( 'contact_text' ); ?>><?php echo esc_html( $s['contact_text'] ); ?></span></a>
					<?php endif; ?>
				</div>
				<div class="services-faq-list">
				<?php foreach ( $s['questions'] as $index => $item ) : ?>
					<details class="elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>" <?php if ( $editing || 'yes' === $item['initial_open'] ) { echo 'open'; } ?>>
						<summary><span <?php $this->inline_text( $this->get_repeater_setting_key( 'question', 'questions', $index ) ); ?>><?php echo esc_html( $item['question'] ); ?></span></summary>
						<p <?php $this->inline_text( $this->get_repeater_setting_key( 'answer', 'questions', $index ) ); ?>><?php echo nl2br( esc_html( $item['answer'] ) ); ?></p>
					</details>
				<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
