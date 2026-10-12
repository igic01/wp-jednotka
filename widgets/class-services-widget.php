<?php
/** Reference services overview, with Elementor Free editing controls. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Services_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-services'; }
	public function get_title() { return esc_html__( 'Jednotka Services', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-bullet-list'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'services', 'sluzby', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-services' ); }
	public function get_script_depends() { return array( 'jednotka-services' ); }

	private function text_controls( $stack, $key, $label, $selector, $default = '' ) {
		$stack->add_control( $key, array( 'label' => $label, 'type' => in_array( $key, array( 'intro', 'description' ), true ) ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $default ) );
		$stack->add_control( $key . '_color', array( 'label' => $label . ' color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$stack->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_typography', 'label' => $label . ' font / size', 'selector' => $selector ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'heading_section', array( 'label' => 'Heading and introduction' ) );
		$this->text_controls( $this, 'heading', 'Heading', '{{WRAPPER}} .services-overview-header h2', 'Čo robíme' );
		$this->text_controls( $this, 'intro', 'Introduction', '{{WRAPPER}} .services-overview-header p', 'Komplexný online marketing pre biznisy, ktoré chcú rásť' );
		$this->add_control( 'anchor', array( 'label' => 'Section anchor', 'type' => Controls_Manager::TEXT, 'default' => 'sluzby', 'description' => 'Used by navigation links such as #sluzby. Use a unique value if you add this widget more than once.' ) );
		$this->add_control( 'animate', array( 'label' => 'Scroll reveal', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'services_section', array( 'label' => 'Services' ) );
		$item = new Repeater();
		$this->text_controls( $item, 'title', 'Service name', '{{WRAPPER}} {{CURRENT_ITEM}} .service-row strong' );
		$this->text_controls( $item, 'description', 'Description', '{{WRAPPER}} {{CURRENT_ITEM}} .service-copy' );
		$item->add_control( 'link', array( 'label' => 'Service link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => home_url( '/sluzby/' ) ) ) );
		foreach ( array( 'icon' => 'Service icon', 'arrow' => 'Arrow' ) as $prefix => $label ) {
			$options = array( 'image' => 'Image', 'video' => 'Uploaded video' );
			if ( 'arrow' === $prefix ) { $options = array( 'reference' => 'Original arrow' ) + $options; }
			$item->add_control( $prefix . '_type', array( 'label' => $label, 'type' => Controls_Manager::SELECT, 'default' => 'arrow' === $prefix ? 'reference' : 'image', 'options' => $options ) );
			$item->add_control( $prefix . '_image', array( 'label' => $label . ' image', 'type' => Controls_Manager::MEDIA, 'condition' => array( $prefix . '_type' => 'image' ) ) );
			$item->add_control( $prefix . '_video', array( 'label' => $label . ' video', 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ), 'condition' => array( $prefix . '_type' => 'video' ) ) );
			$item->add_control( $prefix . '_poster', array( 'label' => $label . ' video poster', 'type' => Controls_Manager::MEDIA, 'condition' => array( $prefix . '_type' => 'video' ) ) );
		}
		$item->add_control( 'icon_background', array( 'label' => 'Icon badge background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .service-icon' => 'background: {{VALUE}};' ) ) );
		$item->add_responsive_control( 'icon_size', array( 'label' => 'Icon size', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 12, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .service-icon .content-badge-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ) ) );
		$item->add_control( 'arrow_color', array( 'label' => 'Arrow color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .service-arrow' => 'color: {{VALUE}};' ) ) );
		$item->add_control( 'arrow_background', array( 'label' => 'Arrow background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .service-arrow' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'services', array( 'label' => 'Services', 'type' => Controls_Manager::REPEATER, 'fields' => $item->get_controls(), 'title_field' => '{{{ title }}}', 'default' => $this->default_services(), 'description' => 'Add, remove, or reorder services. Click names and descriptions in the preview to edit them.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'layout_section', array( 'label' => 'Section layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-services' => '--section-dark-bg: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-services' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Space between services', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} .services-overview-list' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'header_gap', array( 'label' => 'Space below introduction', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} .services-overview-header' => 'margin-bottom: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function default_services() {
		return array(
			array( 'title' => 'Web', 'description' => 'Weby a e-shopy navrhujeme tak, aby neboli len pekné, ale hlavne prehľadné, funkčné a pripravené premieňať návštevníkov na zákazníkov.', 'icon_image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/web.png' ) ),
			array( 'title' => 'Správa eshopov', 'description' => 'Nastavíme, upravíme aj rozbehneme e-shop na platforme, ktorú už používate. Platobné brány, redizajn, úpravy na mieru — všetko tak, aby e-shop reálne predával.', 'icon_image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/services-eshop.svg' ) ),
			array( 'title' => 'Email marketing', 'description' => 'Tvoríme email marketing a automatizácie, ktoré pracujú s vašou databázou dlhodobo. Od prvého kontaktu až po opakovaný nákup.', 'icon_image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/email.png' ) ),
			array( 'title' => 'PPC Reklamy', 'description' => 'Tvoríme všetky vizuálne materiály, ktoré váš biznis potrebuje: od príspevkov, reklám a bannerov až po tlačoviny a podklady pre každodennú komunikáciu.', 'icon_image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/ppc.png' ) ),
			array( 'title' => 'Grafika', 'description' => 'Tvoríme vizuály a komunikáciu, ktoré držia jednotný štýl naprieč reklamami, sociálnymi sieťami, emailami aj webom.', 'icon_image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/grafika.png' ) ),
			array( 'title' => 'Branding', 'description' => 'Pomáhame značkám vytvoriť jasnú identitu a komunikáciu, vďaka ktorej sú rozpoznateľné, zapamätateľné a odlíšia sa od konkurencie.', 'icon_image' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/branding.png' ) ),
		);
	}

	private function inline_text( $key, $class = '' ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		if ( $class ) { $this->add_render_attribute( $key, 'class', $class ); }
		$this->print_render_attribute_string( $key );
	}

	private function render_media( $item, $prefix, $class ) {
		if ( 'image' === $item[ $prefix . '_type' ] && ! empty( $item[ $prefix . '_image' ]['url'] ) ) {
			?><img class="<?php echo esc_attr( $class ); ?>" src="<?php echo esc_url( $item[ $prefix . '_image' ]['url'] ); ?>" alt="" /><?php
		} elseif ( 'video' === $item[ $prefix . '_type' ] && ! empty( $item[ $prefix . '_video' ]['url'] ) ) {
			?><video class="<?php echo esc_attr( $class ); ?>" src="<?php echo esc_url( $item[ $prefix . '_video' ]['url'] ); ?>" poster="<?php echo esc_url( $item[ $prefix . '_poster' ]['url'] ?? '' ); ?>" muted loop playsinline preload="metadata"></video><?php
		}
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$title_id = 'services-overview-title-' . $this->get_id();
		?>
		<section class="services-overview-section jn-services"<?php if ( ! empty( $s['anchor'] ) ) : ?> id="<?php echo esc_attr( sanitize_title( $s['anchor'] ) ); ?>"<?php endif; ?> aria-labelledby="<?php echo esc_attr( $title_id ); ?>" data-animate="<?php echo esc_attr( $s['animate'] ); ?>">
			<div class="services-overview-header">
				<h2 id="<?php echo esc_attr( $title_id ); ?>" <?php $this->inline_text( 'heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></h2>
				<p <?php $this->inline_text( 'intro' ); ?>><?php echo esc_html( $s['intro'] ); ?></p>
			</div>
			<div class="services-overview-list">
				<?php foreach ( $s['services'] as $index => $item ) :
					$link_key = 'link_' . $index;
					$this->add_render_attribute( $link_key, 'class', 'service-row' );
					$this->add_link_attributes( $link_key, $item['link'] );
					$has_video = ( 'video' === $item['icon_type'] && ! empty( $item['icon_video']['url'] ) ) || ( 'video' === $item['arrow_type'] && ! empty( $item['arrow_video']['url'] ) );
					?>
					<div class="jn-service-item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">
						<a <?php $this->print_render_attribute_string( $link_key ); ?>>
							<span class="content-badge service-icon" aria-hidden="true"><?php $this->render_media( $item, 'icon', 'content-badge-icon' ); ?></span>
							<strong <?php $this->inline_text( $this->get_repeater_setting_key( 'title', 'services', $index ) ); ?>><?php echo esc_html( $item['title'] ); ?></strong>
							<span <?php $this->inline_text( $this->get_repeater_setting_key( 'description', 'services', $index ), 'service-copy' ); ?>><?php echo esc_html( $item['description'] ); ?></span>
							<span class="service-arrow" aria-hidden="true">
								<?php if ( 'reference' === $item['arrow_type'] ) : ?>
									<svg class="service-arrow-icon" viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg>
								<?php else : $this->render_media( $item, 'arrow', 'service-arrow-icon' ); endif; ?>
							</span>
						</a>
						<?php if ( $has_video ) : ?>
							<button class="jn-services__video-toggle" type="button" aria-label="Play service video" aria-pressed="false" data-play-label="Play service video" data-pause-label="Pause service video">▶</button>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
