<?php
/** Editable contact section based on temp/contact. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Contact_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-contact'; }
	public function get_title() { return esc_html__( 'Jednotka Contact', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-mail'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'contact', 'form', 'kontakt', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-contact' ); }
	public function get_script_depends() { return array( 'jednotka-contact' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Contact section' ) );
		foreach ( array(
			'heading' => array( 'Heading', 'Napíšte nám, s čím začneme' ),
			'description' => array( 'Description', 'Do 24 hodín sa vám ozveme s nezáväznou cenovou ponukou. Bez tlaku, bez záväzkov.' ),
			'button' => array( 'Button', 'Získať nezáväznú ponuku' ),
			'note' => array( 'Note below form', 'Bez záväzkov. Ozveme sa do 1 pracovného dňa.' ),
		) as $key => $field ) {
			$this->add_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::TEXTAREA, 'default' => $field[1] ) );
		}
		$this->add_control( 'interests', array( 'label' => 'Service options (one per line)', 'type' => Controls_Manager::TEXTAREA, 'default' => "Web\nSpráva e-shopov\nEmail marketing\nPPC Reklamy\nBranding\nGrafika\nNeviem, poradíte mi" ) );
		$this->add_control( 'delivery_info', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => 'Enquiries are sent to the WordPress administration email from Settings → General using wp_mail. Configure your site’s email delivery before going live.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'fields', array( 'label' => 'Form labels and placeholders' ) );
		foreach ( $this->fields() as $key => $field ) {
			$this->add_control( $key . '_label', array( 'label' => $field[0] . ' label', 'type' => Controls_Manager::TEXT, 'default' => $field[0] ) );
			$this->add_control( $key . '_placeholder', array( 'label' => $field[0] . ' placeholder', 'type' => Controls_Manager::TEXT, 'default' => $field[1] ) );
		}
		$this->add_control( 'interest_label', array( 'label' => 'Service label', 'type' => Controls_Manager::TEXT, 'default' => 'Čo vás zaujíma?' ) );
		$this->add_control( 'interest_placeholder', array( 'label' => 'Service placeholder', 'type' => Controls_Manager::TEXT, 'default' => 'Vyberte možnosť' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'Section and form', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array(
			'background' => array( 'Background', '{{WRAPPER}} .jn-contact', 'background-color', '#0F0F0F' ),
			'heading_color' => array( 'Heading color', '{{WRAPPER}} .services-contact-copy h2', 'color', '' ),
			'body_color' => array( 'Description color', '{{WRAPPER}} .services-contact-copy p', 'color', '' ),
			'button_color' => array( 'Button background', '{{WRAPPER}} .jn-contact .button', 'background', '#5f2df5' ),
			'form_color' => array( 'Form background', '{{WRAPPER}} .services-contact-form', 'background', '' ),
		) as $key => $control ) {
			$this->add_control( $key, array( 'label' => $control[0], 'type' => Controls_Manager::COLOR, 'default' => $control[3], 'selectors' => array( $control[1] => $control[2] . ': {{VALUE}};' ) ) );
		}
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_font', 'selector' => '{{WRAPPER}} .services-contact-copy h2' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'body_font', 'selector' => '{{WRAPPER}} .services-contact-copy p' ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-contact' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Column gap', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 20, 'max' => 160 ) ), 'selectors' => array( '{{WRAPPER}} .jn-contact__layout' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function fields() {
		return array(
			'name' => array( 'Meno a priezvisko', 'Vaše meno', 'text', 'name', 200 ),
			'email' => array( 'Email', 'vas@email.sk', 'email', 'email', 254 ),
			'phone' => array( 'Telefón', '+421 900 000 000', 'tel', 'tel', 80 ),
			'message' => array( 'Popíšte váš projekt', 'Pár viet o tom, čo potrebujete alebo čo vás trápi', 'textarea', '', 5000 ),
		);
	}

	private function render_field( $key, $field, $s ) {
		?>
		<label class="services-contact-<?php echo esc_attr( $key ); ?>">
			<span><?php echo esc_html( $s[ $key . '_label' ] ); ?></span>
			<?php if ( 'textarea' === $field[2] ) : ?>
				<textarea name="<?php echo esc_attr( $key ); ?>" maxlength="<?php echo esc_attr( $field[4] ); ?>" placeholder="<?php echo esc_attr( $s[ $key . '_placeholder' ] ); ?>"></textarea>
			<?php else : ?>
				<input type="<?php echo esc_attr( $field[2] ); ?>" name="<?php echo esc_attr( $key ); ?>" autocomplete="<?php echo esc_attr( $field[3] ); ?>" maxlength="<?php echo esc_attr( $field[4] ); ?>" placeholder="<?php echo esc_attr( $s[ $key . '_placeholder' ] ); ?>" <?php if ( in_array( $key, array( 'name', 'email' ), true ) ) { echo 'required'; } ?> />
			<?php endif; ?>
		</label>
		<?php
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$id = $this->get_id();
		$fields = $this->fields();
		?>
		<section class="services-contact-section jn-contact" aria-labelledby="contact-title-<?php echo esc_attr( $id ); ?>">
			<div class="jn-contact__layout">
				<div class="services-contact-copy"><h2 id="contact-title-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $s['heading'] ); ?></h2><p><?php echo esc_html( $s['description'] ); ?></p></div>
				<form class="services-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" aria-labelledby="contact-title-<?php echo esc_attr( $id ); ?>">
					<input type="hidden" name="action" value="jednotka_contact" />
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'jednotka_contact' ) ); ?>" />
					<div class="jn-contact__trap" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off" /></label></div>
					<?php foreach ( array( 'name', 'email', 'phone' ) as $key ) { $this->render_field( $key, $fields[ $key ], $s ); } ?>
					<label><span><?php echo esc_html( $s['interest_label'] ); ?></span><select name="interest"><option value=""><?php echo esc_html( $s['interest_placeholder'] ); ?></option><?php foreach ( preg_split( '/\R/u', $s['interests'] ) as $interest ) : $interest = trim( $interest ); if ( '' === $interest ) { continue; } ?><option value="<?php echo esc_attr( $interest ); ?>"><?php echo esc_html( $interest ); ?></option><?php endforeach; ?></select></label>
					<?php $this->render_field( 'message', $fields['message'], $s ); ?>
					<button class="button button--primary" type="submit"><span class="button-label"><span class="button-label-track"><span class="button-label-line"><?php echo esc_html( $s['button'] ); ?></span><span class="button-label-line" aria-hidden="true"><?php echo esc_html( $s['button'] ); ?></span></span></span><span class="button-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span></button>
					<p class="services-form-note"><?php echo esc_html( $s['note'] ); ?></p>
					<p class="jn-contact__status" role="status" aria-live="polite" hidden></p>
				</form>
			</div>
		</section>
		<?php
	}
}
