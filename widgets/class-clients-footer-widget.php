<?php
/** Contact CTA with floating projects and a responsive integrated footer. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Clients_Footer_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-clients-footer'; }
	public function get_title() { return esc_html__( 'Jednotka Clients & Footer', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-footer'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'clients', 'cta', 'footer', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-clients-footer', 'elementor-icons-fa-brands' ); }
	public function get_script_depends() { return array( 'jednotka-clients-footer' ); }

	private function text_controls( $stack, $key, $label, $selector, $default = '', $multiline = false ) {
		$stack->add_control( $key, array( 'label' => $label, 'type' => $multiline ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $default ) );
		$stack->add_control( $key . '_color', array( 'label' => $label . ' color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$stack->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_typography', 'label' => $label . ' font / size', 'selector' => $selector ) );
	}
	private function media_controls( $stack, $prefix, $default ) {
		$stack->add_control( $prefix . '_type', array( 'label' => 'Media type', 'type' => Controls_Manager::SELECT, 'default' => 'image', 'options' => array( 'image' => 'Image', 'video' => 'Uploaded video' ) ) );
		$stack->add_control( $prefix . '_image', array( 'label' => 'Image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . $default ), 'condition' => array( $prefix . '_type' => 'image' ) ) );
		$stack->add_control( $prefix . '_video', array( 'label' => 'Video', 'type' => Controls_Manager::MEDIA, 'media_types' => array( 'video' ), 'condition' => array( $prefix . '_type' => 'video' ) ) );
		$stack->add_control( $prefix . '_poster', array( 'label' => 'Video poster', 'type' => Controls_Manager::MEDIA, 'condition' => array( $prefix . '_type' => 'video' ) ) );
	}
	private function link_repeater( $key, $label, $defaults, $social = false ) {
		$item = new Repeater();
		$this->text_controls( $item, 'label', 'Label', '{{WRAPPER}} .' . ( $social ? 'footer-socials' : 'footer-nav' ) . ' {{CURRENT_ITEM}}' );
		$item->add_control( 'link', array( 'label' => 'Link', 'type' => Controls_Manager::URL ) );
		$item->add_control( 'hover_color', array( 'label' => 'Hover color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}:hover' => 'color: {{VALUE}};' ) ) );
		if ( $social ) {
			$item->add_control( 'icon', array( 'label' => 'Social icon', 'type' => Controls_Manager::ICONS, 'recommended' => array( 'fa-brands' => array( 'instagram', 'behance', 'linkedin-in' ) ) ) );
			$item->add_control( 'accessible_name', array( 'label' => 'Accessible name', 'type' => Controls_Manager::TEXT, 'description' => 'Full network name for short labels such as Ig.' ) );
		}
		$this->add_control( $key, array( 'label' => $label, 'type' => Controls_Manager::REPEATER, 'fields' => $item->get_controls(), 'title_field' => '{{{ label }}}', 'default' => $defaults, 'prevent_empty' => false ) );
	}
	protected function register_controls() {
		$this->start_controls_section( 'cta_section', array( 'label' => 'Call to action' ) );
		$this->text_controls( $this, 'heading', 'Heading', '{{WRAPPER}} .clients-cta-content h2', "Dobrý nápad?\nDajme mu život.", true );
		$this->text_controls( $this, 'description', 'Description', '{{WRAPPER}} .jn-cta-description', 'Web, značka alebo marketing. Povedzte nám, čo máte v hlave — spolu z toho spravíme niečo, čo funguje.', true );
		$this->text_controls( $this, 'button_text', 'Button label', '{{WRAPPER}} .clients-cta-content .button', 'Poďme sa porozprávať' );
		$this->add_control( 'button_link', array( 'label' => 'Button link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => home_url( '/kontakt/' ) ) ) );
		$this->add_control( 'button_background', array( 'label' => 'Button background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .clients-cta-content .button' => '--button-bg: {{VALUE}};' ) ) );
		$this->add_control( 'phone_show', array( 'label' => 'Show phone contact', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->text_controls( $this, 'phone_label', 'Phone introduction', '{{WRAPPER}} .jn-cta-phone-label', 'Radšej zavoláte?' );
		$this->text_controls( $this, 'phone_number', 'Phone number', '{{WRAPPER}} .jn-cta-phone-number', '+421 000 000 000' );
		$this->add_control( 'phone_link', array( 'label' => 'Phone link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'tel:+421000000000' ), 'description' => 'Use tel: followed by your phone number, including the country code.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'cards_section', array( 'label' => 'Floating projects' ) );
		$card = new Repeater();
		$card->add_control( 'name', array( 'label' => 'Item name / alternative text', 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$card->add_control( 'category', array( 'label' => 'Project category', 'type' => Controls_Manager::TEXT ) );
		$card->add_control( 'link', array( 'label' => 'Project link (optional)', 'type' => Controls_Manager::URL ) );
		$this->media_controls( $card, 'media', 'assets/images/projects/placeholder.jpg' );
		$card->add_responsive_control( 'width', array( 'label' => 'Card width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 40, 'max' => 500 ) ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}' => '--card-width: {{SIZE}}{{UNIT}};' ) ) );
		$card->add_responsive_control( 'radius', array( 'label' => 'Card corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}}' => '--card-radius: {{SIZE}}{{UNIT}};' ) ) );
		$card->add_control( 'fit', array( 'label' => 'Media fit', 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => 'Fill card', 'contain' => 'Show complete media' ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .jn-project-surface > img, {{WRAPPER}} {{CURRENT_ITEM}} .jn-project-surface > video' => 'object-fit: {{VALUE}};' ) ) );
		$this->add_control( 'cards', array( 'label' => 'Project media', 'type' => Controls_Manager::REPEATER, 'fields' => $card->get_controls(), 'default' => array(
			array( 'name' => 'Webové stránky', 'category' => 'Web & dizajn' ),
			array( 'name' => 'Vizuálna identita', 'category' => 'Branding' ),
			array( 'name' => 'Digitálne zážitky', 'category' => 'Web & dizajn' ),
			array( 'name' => 'E-shopy', 'category' => 'E-commerce' ),
			array( 'name' => 'Kampane', 'category' => 'Marketing' ),
			array( 'name' => 'Nové nápady', 'category' => 'Kreatíva' ),
		), 'title_field' => '{{{ name || "Project media" }}}', 'max_items' => 6, 'prevent_empty' => false, 'description' => 'Up to six floating projects. Replace the demo media with your work. Cards 2 and 6 are hidden on phones to leave room for the CTA. Reorder items to change their positions.' ) );
		foreach ( array( 'reveal' => 'Project entrance animation', 'drift' => 'Floating motion', 'parallax' => 'Pointer movement', 'trail' => 'Mouse glow', 'snap' => 'Snap CTA to viewport' ) as $key => $label ) {
			$this->add_control( $key, array( 'label' => $label, 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		}
		$this->end_controls_section();

		$this->register_footer_controls();

		$this->start_controls_section( 'layout_section', array( 'label' => 'Section and footer layout', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array( 'top' => 'Gradient top', 'middle' => 'Gradient middle', 'bottom' => 'Gradient bottom' ) as $key => $label ) {
			$this->add_control( 'gradient_' . $key, array( 'label' => $label, 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .jn-clients-footer' => '--jn-cta-' . $key . ': {{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'height', array( 'label' => 'Section minimum height', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 600, 'max' => 2600 ) ), 'selectors' => array( '{{WRAPPER}} .jn-clients-footer' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'cta_padding', array( 'label' => 'CTA padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-cta-stage' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_control( 'footer_background', array( 'label' => 'Footer background', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .site-footer-card' => 'background: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'footer_padding', array( 'label' => 'Footer padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .site-footer-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'footer_radius', array( 'label' => 'Footer corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .site-footer-card' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	protected function register_footer_controls() {
		$this->start_controls_section( 'brand_section', array( 'label' => 'Footer brand' ) );
		$this->media_controls( $this, 'logo', 'assets/images/jednotka-logo.svg' );
		$this->add_control( 'logo_name', array( 'label' => 'Logo accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Jednotka' ) );
		$this->add_control( 'logo_link', array( 'label' => 'Logo link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => home_url( '/' ) ) ) );
		$this->add_responsive_control( 'logo_width', array( 'label' => 'Logo width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 40, 'max' => 350 ) ), 'selectors' => array( '{{WRAPPER}} .footer-logo' => 'width: {{SIZE}}{{UNIT}};' ) ) );
		$this->text_controls( $this, 'brand_text', 'Brand description', '{{WRAPPER}} .footer-brand > p', 'Jeden tím pre web, reklamu, emaily a vizuál. Bez chaosu medzi kanálmi.', true );
		$this->end_controls_section();

		$this->start_controls_section( 'newsletter_section', array( 'label' => 'Newsletter (unconnected)' ) );
		$this->add_control( 'newsletter_show', array( 'label' => 'Show newsletter', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => 'The form is not connected to a newsletter service and does not submit or store email addresses.' ) );
		$this->text_controls( $this, 'newsletter_label', 'Label', '{{WRAPPER}} .footer-newsletter label', 'Odber noviniek' );
		$this->text_controls( $this, 'newsletter_placeholder', 'Placeholder', '{{WRAPPER}} .footer-newsletter-field input', 'Váš email' );
		$this->add_control( 'placeholder_color', array( 'label' => 'Placeholder color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .footer-newsletter input::placeholder' => 'color: {{VALUE}}; opacity: 1;' ) ) );
		$this->add_control( 'newsletter_submit', array( 'label' => 'Submit accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Odoslať' ) );
		$this->text_controls( $this, 'newsletter_notice', 'Unconnected message', '{{WRAPPER}} .jn-newsletter-notice', 'Odber noviniek pripravujeme.' );
		$this->end_controls_section();

		$this->start_controls_section( 'footer_links_section', array( 'label' => 'Footer navigation and contact' ) );
		$this->text_controls( $this, 'menu_heading', 'Menu heading', '{{WRAPPER}} .jn-footer-menu-heading', 'Menu' );
		$menu_defaults = array();
		foreach ( array( 'Domov' => '/', 'O nás' => '/o-nas/', 'Služby' => '/sluzby/', 'Kariéra' => '/kariera/', 'Blog' => '/blog/' ) as $label => $url ) {
			$menu_defaults[] = array( 'label' => $label, 'link' => array( 'url' => home_url( $url ) ) );
		}
		$this->link_repeater( 'menu_links', 'Menu links', $menu_defaults );
		$this->text_controls( $this, 'contact_heading', 'Contact heading', '{{WRAPPER}} .jn-footer-contact-heading', 'Kontakt' );
		$this->link_repeater( 'contact_links', 'Contact links', array(
			array( 'label' => 'hello@jednotka.sk', 'link' => array( 'url' => 'mailto:hello@jednotka.sk' ) ),
			array( 'label' => '+421 000 000 000', 'link' => array( 'url' => 'tel:+421000000000' ) ),
			array( 'label' => 'Začať projekt', 'link' => array( 'url' => home_url( '/kontakt/' ) ) ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'social_section', array( 'label' => 'Social links and copyright' ) );
		$this->link_repeater( 'social_links', 'Social links', array(
			array( 'label' => 'Ig', 'accessible_name' => 'Instagram', 'icon' => array( 'value' => 'fab fa-instagram', 'library' => 'fa-brands' ), 'link' => array( 'url' => '#' ) ),
			array( 'label' => 'Bē', 'accessible_name' => 'Behance', 'icon' => array( 'value' => 'fab fa-behance', 'library' => 'fa-brands' ), 'link' => array( 'url' => '#' ) ),
			array( 'label' => 'In', 'accessible_name' => 'LinkedIn', 'icon' => array( 'value' => 'fab fa-linkedin-in', 'library' => 'fa-brands' ), 'link' => array( 'url' => '#' ) ),
		), true );
		$this->text_controls( $this, 'copyright', 'Copyright', '{{WRAPPER}} .footer-side > p', '© 2026 Jednotka' );
		$this->end_controls_section();

	}

	protected function render_footer( $s, $id ) {
		?>
			<footer class="site-footer-card" aria-label="Päta stránky">
				<div class="footer-brand">
					<a <?php $this->print_render_attribute_string( 'logo_link' ); ?>><?php $this->render_media( $s, 'logo' ); ?></a>
					<p <?php $this->inline_text( 'brand_text' ); ?>><?php echo esc_html( $s['brand_text'] ); ?></p>
					<?php if ( 'yes' === $s['newsletter_show'] ) : ?>
						<form class="footer-newsletter" data-unconnected="yes">
							<label for="footer-email-<?php echo esc_attr( $id ); ?>" <?php $this->inline_text( 'newsletter_label' ); ?>><?php echo esc_html( $s['newsletter_label'] ); ?></label>
							<div class="footer-newsletter-field">
								<input id="footer-email-<?php echo esc_attr( $id ); ?>" type="email" placeholder="<?php echo esc_attr( $s['newsletter_placeholder'] ); ?>" autocomplete="email" />
								<button type="button" aria-label="<?php echo esc_attr( $s['newsletter_submit'] ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></button>
							</div>
							<div class="jn-newsletter-notice" role="status" hidden><?php echo esc_html( $s['newsletter_notice'] ); ?></div>
						</form>
					<?php endif; ?>
				</div>
				<div class="footer-nav-wrap"><nav class="footer-nav" aria-label="Päta navigácia">
					<div class="footer-nav-group"><span <?php $this->inline_text( 'menu_heading', 'jn-footer-menu-heading' ); ?>><?php echo esc_html( $s['menu_heading'] ); ?></span><?php $this->render_links( $s, 'menu_links' ); ?></div>
					<div class="footer-nav-group"><span <?php $this->inline_text( 'contact_heading', 'jn-footer-contact-heading' ); ?>><?php echo esc_html( $s['contact_heading'] ); ?></span><?php $this->render_links( $s, 'contact_links' ); ?></div>
				</nav></div>
				<div class="footer-side">
					<div class="footer-socials" aria-label="Sociálne siete"><?php $this->render_links( $s, 'social_links' ); ?></div>
					<p <?php $this->inline_text( 'copyright' ); ?>><?php echo esc_html( $s['copyright'] ); ?></p>
				</div>
			</footer>
		<?php
	}

	private function inline_text( $key, $class = '' ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		if ( $class ) { $this->add_render_attribute( $key, 'class', $class ); }
		$this->print_render_attribute_string( $key );
	}
	private function render_media( $item, $prefix, $alt = '' ) {
		if ( 'video' === $item[ $prefix . '_type' ] && ! empty( $item[ $prefix . '_video' ]['url'] ) ) {
			?><video src="<?php echo esc_url( $item[ $prefix . '_video' ]['url'] ); ?>" poster="<?php echo esc_url( $item[ $prefix . '_poster' ]['url'] ?? '' ); ?>" <?php echo $alt ? 'aria-label="' . esc_attr( $alt ) . '"' : 'aria-hidden="true"'; ?> muted loop playsinline preload="metadata"></video><?php
		} elseif ( 'image' === $item[ $prefix . '_type' ] && ! empty( $item[ $prefix . '_image' ]['url'] ) ) {
			?><img src="<?php echo esc_url( $item[ $prefix . '_image' ]['url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" /><?php
		}
	}
	private function render_links( $s, $key ) {
		foreach ( $s[ $key ] as $index => $item ) {
			$text_key = $this->get_repeater_setting_key( 'label', $key, $index );
			$this->add_link_attributes( $text_key, $item['link'] );
			if ( ! empty( $item['accessible_name'] ) ) { $this->add_render_attribute( $text_key, 'aria-label', $item['accessible_name'] ); }
			$icon = $item['icon'] ?? array();
			if ( 'social_links' === $key && empty( $icon['value'] ) ) {
				// Older saved widgets have text labels only; give known networks their brand icon.
				$brands = array( 'instagram' => 'instagram', 'ig' => 'instagram', 'behance' => 'behance', 'bē' => 'behance', 'linkedin' => 'linkedin-in', 'in' => 'linkedin-in' );
				$brand = $brands[ strtolower( $item['accessible_name'] ?? '' ) ] ?? $brands[ strtolower( $item['label'] ) ] ?? '';
				if ( $brand ) { $icon = array( 'value' => 'fab fa-' . $brand, 'library' => 'fa-brands' ); }
			}
			if ( 'social_links' === $key && ! empty( $icon['value'] ) ) {
				if ( empty( $item['accessible_name'] ) ) { $this->add_render_attribute( $text_key, 'aria-label', $item['label'] ); }
				$this->add_render_attribute( $text_key, 'class', 'elementor-repeater-item-' . $item['_id'] );
				?><a <?php $this->print_render_attribute_string( $text_key ); ?>><?php Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) ); ?></a><?php
			} else {
				?><a <?php $this->inline_text( $text_key, 'elementor-repeater-item-' . $item['_id'] ); ?>><?php echo esc_html( $item['label'] ); ?></a><?php
			}
		}
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$id = $this->get_id();
		$this->add_link_attributes( 'button_link', $s['button_link'] );
		$this->add_render_attribute( 'button_link', 'class', 'button button--primary' );
		$this->add_link_attributes( 'logo_link', $s['logo_link'] );
		$this->add_render_attribute( 'logo_link', array( 'class' => 'footer-logo', 'aria-label' => $s['logo_name'] ) );
		$this->add_link_attributes( 'phone_link', $s['phone_link'] );
		$this->add_render_attribute( 'phone_link', 'class', 'jn-cta-phone-number' );
		$variants = array( 'one', 'two', 'three', 'four', 'five', 'six' );
		?>
		<section class="clients-cta-section jn-clients-footer" aria-labelledby="clients-cta-title-<?php echo esc_attr( $id ); ?>" data-reveal="<?php echo esc_attr( $s['reveal'] ); ?>" data-drift="<?php echo esc_attr( $s['drift'] ); ?>" data-parallax="<?php echo esc_attr( $s['parallax'] ); ?>" data-trail="<?php echo esc_attr( $s['trail'] ); ?>" data-snap="<?php echo esc_attr( $s['snap'] ); ?>" data-editor="<?php echo \Elementor\Plugin::$instance->editor->is_edit_mode() ? 'yes' : 'no'; ?>">
			<div class="cursor-trail-layer" aria-hidden="true"></div>
			<div class="jn-cta-stage">
			<?php foreach ( array_slice( $s['cards'], 0, 6 ) as $index => $card ) : ?>
				<?php
				$project_key = 'project_link_' . $index;
				$linked = ! empty( $card['link']['url'] );
				$tag = $linked ? 'a' : 'div';
				$this->add_render_attribute( $project_key, 'class', 'jn-project-surface' );
				if ( $linked ) {
					$this->add_link_attributes( $project_key, $card['link'] );
					$this->add_render_attribute( $project_key, 'aria-label', $card['name'] ?: 'Pozrieť projekt' );
				}
				?>
				<div class="floating-project-card floating-project-card--<?php echo esc_attr( $variants[ $index ] ); ?> elementor-repeater-item-<?php echo esc_attr( $card['_id'] ); ?>">
					<div class="jn-project-reveal">
					<<?php echo $tag; ?> <?php $this->print_render_attribute_string( $project_key ); ?>>
						<?php $this->render_media( $card, 'media', $linked ? '' : $card['name'] ); ?>
						<?php if ( $card['name'] || ! empty( $card['category'] ) ) : ?>
							<span class="jn-project-caption"><span><?php echo esc_html( $card['name'] ); ?></span><?php if ( ! empty( $card['category'] ) ) : ?><small><?php echo esc_html( $card['category'] ); ?></small><?php endif; ?></span>
						<?php endif; ?>
					</<?php echo $tag; ?>>
					</div>
				</div>
			<?php endforeach; ?>
			<div class="clients-cta-content">
				<h2 id="clients-cta-title-<?php echo esc_attr( $id ); ?>" <?php $this->inline_text( 'heading' ); ?>><?php echo nl2br( wp_kses( $s['heading'], array( 'br' => array() ) ) ); ?></h2>
				<?php if ( $s['description'] ) : ?><p <?php $this->inline_text( 'description', 'jn-cta-description' ); ?>><?php echo esc_html( $s['description'] ); ?></p><?php endif; ?>
				<a <?php $this->print_render_attribute_string( 'button_link' ); ?>>
					<span class="button-label"><span class="button-label-track"><span <?php $this->inline_text( 'button_text', 'button-label-line' ); ?>><?php echo esc_html( $s['button_text'] ); ?></span><span class="button-label-line" aria-hidden="true"><?php echo esc_html( $s['button_text'] ); ?></span></span></span>
					<span class="button-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span>
				</a>
				<?php if ( 'yes' === $s['phone_show'] && $s['phone_number'] ) : ?>
					<div class="jn-cta-phone"><span <?php $this->inline_text( 'phone_label', 'jn-cta-phone-label' ); ?>><?php echo esc_html( $s['phone_label'] ); ?></span><a <?php $this->print_render_attribute_string( 'phone_link' ); ?>><span <?php $this->inline_text( 'phone_number' ); ?>><?php echo esc_html( $s['phone_number'] ); ?></span></a></div>
				<?php endif; ?>
			</div>
			</div>
			<?php $this->render_footer( $s, $id ); ?>
		</section>
		<?php
	}
}
