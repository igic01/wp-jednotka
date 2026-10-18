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
		$this->start_controls_section( 'heading_section', array( 'label' => 'Heading' ) );
		$this->text_controls( $this, 'heading', 'Heading', '{{WRAPPER}} .services-overview-header h2', 'Čo robíme' );
		$this->add_control( 'anchor', array( 'label' => 'Section anchor', 'type' => Controls_Manager::TEXT, 'default' => 'sluzby', 'description' => 'Used by navigation links such as #sluzby. Use a unique value if you add this widget more than once.' ) );
		$this->add_control( 'animate', array( 'label' => 'Scroll reveal', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'back_text', array( 'label' => 'Back button text', 'type' => Controls_Manager::TEXT, 'default' => 'Všetky služby' ) );
		$this->add_control( 'editor_preview', array( 'label' => 'Preview detail in editor', 'type' => Controls_Manager::SWITCHER ) );
		$this->add_control( 'preview_index', array( 'label' => 'Preview service number', 'type' => Controls_Manager::NUMBER, 'default' => 1, 'min' => 1, 'condition' => array( 'editor_preview' => 'yes' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'services_section', array( 'label' => 'Services' ) );
		$item = new Repeater();
		$this->text_controls( $item, 'title', 'Service name', '{{WRAPPER}} {{CURRENT_ITEM}} .service-row strong' );
		$this->text_controls( $item, 'description', 'Description', '{{WRAPPER}} {{CURRENT_ITEM}} .service-copy' );
		$item->add_control( 'link', array( 'label' => 'Service link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => home_url( '/sluzby/' ) ) ) );
		$item->add_control( 'action', array( 'label' => 'Arrow action', 'type' => Controls_Manager::SELECT, 'default' => 'auto', 'options' => array( 'auto' => 'Detail (preserve custom links)', 'detail' => 'Open service detail', 'link' => 'Follow service link' ), 'description' => 'Auto keeps existing custom URLs. The original default services-page link opens a detail.' ) );
		$item->add_control( 'detail_id', array( 'label' => 'Detail slug', 'type' => Controls_Manager::TEXT, 'description' => 'Optional unique slug for direct links; otherwise derived from the service name.' ) );
		$this->text_controls( $item, 'detail_heading', 'Detail heading', '{{WRAPPER}} {{CURRENT_ITEM}} .sv-copy h2' );
		$item->add_control( 'detail_bullets', array( 'label' => 'Detail — Bullet points (one per line)', 'type' => Controls_Manager::TEXTAREA ) );
		$item->add_control( 'detail_images', array( 'label' => 'Detail — Images', 'type' => Controls_Manager::GALLERY, 'default' => array(), 'description' => 'Choose two or three photos. Existing plugin photos are used until you choose your own.' ) );
		$item->add_control( 'detail_cta', array( 'label' => 'Detail button text', 'type' => Controls_Manager::TEXT ) );
		$item->add_control( 'detail_link', array( 'label' => 'Detail button link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#kontakt' ) ) );
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
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .sv-pane' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Space between services', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} .services-overview-list' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'header_gap', array( 'label' => 'Space below heading', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} .services-overview-header' => 'margin-bottom: {{SIZE}}{{UNIT}};' ) ) );
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

	private function detail_defaults( $item ) {
		$defaults = array(
			'Web' => array( 'slug' => 'weby', 'heading' => 'Weby, ktoré ľudia dočítajú', 'bullets' => 'Dizajn šitý na vašu značku
Rýchle a funkčné na mobile
Pripravené na rast a SEO', 'cta' => 'Chcem web' ),
			'Správa eshopov' => array( 'slug' => 'eshopy', 'heading' => 'E-shopy, ktoré predávajú', 'bullets' => 'Dizajn na mieru, žiadne šablóny
Napojenie na platby aj sklad
Ladenie konverzií po spustení', 'cta' => 'Chcem e-shop' ),
			'Email marketing' => array( 'slug' => 'emaily', 'heading' => 'Emaily, ktoré sa otvárajú', 'bullets' => 'Automatizácie, ktoré pracujú za vás
Segmentácia podľa správania', 'cta' => 'Chcem emaily' ),
			'PPC Reklamy' => array( 'slug' => 'reklamy', 'heading' => 'Reklamy s jasným výsledkom', 'bullets' => 'Kampane na Google a Mete
Meranie bez hádania
Rozpočet tam, kde prináša najviac', 'cta' => 'Chcem kampaň' ),
			'Grafika' => array( 'slug' => 'grafika', 'heading' => 'Grafika, ktorú si zapamätajú', 'bullets' => 'Vizuálna identita od loga po sociálne siete
Jednotný štýl naprieč všetkým
Podklady pripravené na tlač aj web', 'cta' => 'Chcem grafiku' ),
			'Branding' => array( 'slug' => 'branding', 'heading' => 'Značka, ktorá sa odlíši', 'bullets' => 'Jasná identita a komunikácia
Rozpoznateľná na prvý pohľad', 'cta' => 'Chcem branding' ),
		);
		return $defaults[ $item['title'] ] ?? array( 'slug' => sanitize_title( $item['title'] ), 'heading' => $item['title'], 'bullets' => $item['description'], 'cta' => 'Napísať nám' );
	}

	private function render_wall( $images, $index ) {
		$urls = array();
		foreach ( $images as $image ) {
			$url = $image['url'] ?? '';
			if ( ! $url && ! empty( $image['id'] ) ) { $url = wp_get_attachment_image_url( (int) $image['id'], 'full' ); }
			if ( $url ) { $urls[] = $url; }
		}
		if ( ! $urls ) { foreach ( array( 1, 2, 3 ) as $n ) { $urls[] = JEDNOTKA_ELEMENTOR_URL . 'assets/images/showcase/gallery-' . ( ( $index + $n - 1 ) % 4 + 1 ) . '.jpg'; } }
		$ratios = array( '3 / 4', '1 / 1', '4 / 5', '3 / 4', '5 / 6', '1 / 1' );
		?><div class="sv-wall" aria-hidden="true"><?php
		for ( $column = 0; $column < 3; $column++ ) : ?>
			<div class="sv-wall-col sv-wall-col--<?php echo esc_attr( $column + 1 ); ?>"><div class="sv-wall-track">
			<?php for ( $copy = 0; $copy < 2; $copy++ ) : ?><div class="sv-wall-set">
				<?php for ( $n = 0; $n < 6; $n++ ) : ?><div class="sv-tile" style="aspect-ratio: <?php echo esc_attr( $ratios[ ( $n + $column * 2 ) % count( $ratios ) ] ); ?>;"><img src="<?php echo esc_url( $urls[ ( $n + $column ) % count( $urls ) ] ); ?>" alt="" loading="lazy" decoding="async" draggable="false" /></div><?php endfor; ?>
			</div><?php endfor; ?>
			</div></div>
		<?php endfor; ?></div><?php
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
		$models = array(); $used = array();
		foreach ( $s['services'] as $index => $item ) {
			$defaults = $this->detail_defaults( $item );
			$slug = sanitize_title( $item['detail_id'] ?: $defaults['slug'] ) ?: 'service';
			$base = $slug; $suffix = 1;
			while ( isset( $used[ $slug ] ) ) { $slug = $base . '-' . ++$suffix; }
			$used[ $slug ] = true;
			$models[] = array( 'id' => $slug, 'hash' => 'jn-services-' . $this->get_id() . '-' . $slug, 'defaults' => $defaults );
		}
		?>
		<section class="services-overview-section jn-services sv-section" data-sv<?php if ( ! empty( $s['anchor'] ) ) : ?> id="<?php echo esc_attr( sanitize_title( $s['anchor'] ) ); ?>"<?php endif; ?> aria-labelledby="<?php echo esc_attr( $title_id ); ?>" data-animate="<?php echo esc_attr( $s['animate'] ); ?>" data-editor-preview="<?php echo esc_attr( $s['editor_preview'] ); ?>" data-preview-index="<?php echo esc_attr( max( 1, (int) $s['preview_index'] ) ); ?>">
			<div class="sv-viewport"><div class="sv-track">
			<div class="sv-pane sv-pane--list" data-sv-list>
				<div class="services-overview-header sv-header">
					<h2 id="<?php echo esc_attr( $title_id ); ?>" <?php $this->inline_text( 'heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></h2>
				</div>
				<div class="services-overview-list sv-list">
				<?php foreach ( $s['services'] as $index => $item ) :
					$model = $models[ $index ]; $url = $item['link']['url'] ?? '';
					$external = 'link' === $item['action'] || ( 'auto' === $item['action'] && $url && untrailingslashit( $url ) !== untrailingslashit( home_url( '/sluzby/' ) ) );
					$arrow_tag = $external ? 'a' : 'button'; $arrow_key = 'service-arrow-' . $index;
					if ( $external ) { $this->add_link_attributes( $arrow_key, $item['link'] ); }
					$has_video = ( 'video' === $item['icon_type'] && ! empty( $item['icon_video']['url'] ) ) || ( 'video' === $item['arrow_type'] && ! empty( $item['arrow_video']['url'] ) );
					?>
					<div class="jn-service-item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>">
						<div class="service-row sv-row">
							<span class="content-badge service-icon sv-badge" aria-hidden="true"><?php $this->render_media( $item, 'icon', 'content-badge-icon sv-badge-icon' ); ?></span>
							<strong <?php $this->inline_text( $this->get_repeater_setting_key( 'title', 'services', $index ) ); ?>><?php echo esc_html( $item['title'] ); ?></strong>
							<span <?php $this->inline_text( $this->get_repeater_setting_key( 'description', 'services', $index ), 'service-copy sv-row-copy' ); ?>><?php echo esc_html( $item['description'] ); ?></span>
							<<?php echo $arrow_tag; ?> class="service-arrow sv-arrow" <?php if ( $external ) { $this->print_render_attribute_string( $arrow_key ); } else { ?>type="button" data-sv-open="<?php echo esc_attr( $model['id'] ); ?>" aria-controls="<?php echo esc_attr( $model['hash'] ); ?>"<?php } ?> data-sv-editor-open="<?php echo esc_attr( $model['id'] ); ?>" aria-label="<?php echo esc_attr( ( $external ? 'Otvoriť službu: ' : 'Zobraziť detail: ' ) . $item['title'] ); ?>">
							<?php if ( 'reference' === $item['arrow_type'] ) : ?><svg class="service-arrow-icon sv-arrow-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg><?php else : $this->render_media( $item, 'arrow', 'service-arrow-icon sv-arrow-icon' ); endif; ?>
							</<?php echo $arrow_tag; ?>>
						</div>
						<?php if ( $has_video ) : ?><button class="jn-services__video-toggle" type="button" aria-label="Play service video" aria-pressed="false" data-play-label="Play service video" data-pause-label="Pause service video">▶</button><?php endif; ?>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
			<div class="sv-pane sv-pane--detail" data-sv-panel inert aria-hidden="true">
				<button class="sv-back" type="button" data-sv-close><span class="sv-back-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span><span><?php echo esc_html( $s['back_text'] ); ?></span></button>
				<?php foreach ( $s['services'] as $index => $item ) :
					$model = $models[ $index ]; $defaults = $model['defaults'];
					$heading = $item['detail_heading'] ?: $defaults['heading'];
					$bullet_text = $item['detail_bullets'] ?: $defaults['bullets'];
					$bullets = preg_split( '/\R/u', $bullet_text );
					$cta = $item['detail_cta'] ?: $defaults['cta'];
					$cta_key = 'detail-cta-' . $index; $this->add_link_attributes( $cta_key, $item['detail_link'] );
					?>
					<div class="sv-detail elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>" data-sv-detail="<?php echo esc_attr( $model['id'] ); ?>" data-service-index="<?php echo esc_attr( $index ); ?>" data-default-heading="<?php echo esc_attr( $heading ); ?>" data-default-bullets="<?php echo esc_attr( $bullet_text ); ?>" data-default-cta="<?php echo esc_attr( $cta ); ?>" data-sv-hash="<?php echo esc_attr( $model['hash'] ); ?>" id="<?php echo esc_attr( $model['hash'] ); ?>" hidden>
						<div class="sv-copy"><div class="sv-editor-tools"><button type="button" data-sv-edit="detail_bullets">Edit bullet points</button><button type="button" data-sv-edit="detail_images">Edit images</button></div><h2 <?php $this->inline_text( $this->get_repeater_setting_key( 'detail_heading', 'services', $index ) ); ?>><?php echo esc_html( $heading ); ?></h2><div <?php $this->inline_text( $this->get_repeater_setting_key( 'detail_bullets', 'services', $index ), 'sv-bullets-editor' ); ?>><?php echo esc_html( $bullet_text ); ?></div><ul class="sv-bullets sv-bullets-readonly"><?php foreach ( $bullets as $k => $bullet ) : if ( '' === trim( $bullet ) ) { continue; } ?><li style="--k: <?php echo esc_attr( $k ); ?>;"><?php echo esc_html( $bullet ); ?></li><?php endforeach; ?></ul>
						<?php if ( ! empty( $item['detail_link']['url'] ) ) : ?><a class="button button--primary sv-cta" <?php $this->print_render_attribute_string( $cta_key ); ?>><span class="button-label"><span class="button-label-track"><span <?php $this->inline_text( $this->get_repeater_setting_key( 'detail_cta', 'services', $index ), 'button-label-line sv-cta-text' ); ?>><?php echo esc_html( $cta ); ?></span><span class="button-label-line" aria-hidden="true"><?php echo esc_html( $cta ); ?></span></span></span><span class="button-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span></a><?php endif; ?>
						</div><?php $this->render_wall( $item['detail_images'], $index ); ?>
					</div>
				<?php endforeach; ?>
			</div>
			</div></div>
		</section>
		<?php
	}
}
