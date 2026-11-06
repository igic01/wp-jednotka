<?php
/** Public contact form delivery. The recipient is always set by WordPress. */
namespace Jednotka\Elementor;
defined( 'ABSPATH' ) || exit;

final class Contact_Form {
	const MAX_ATTACHMENT_BYTES = 10485760;
	const ATTACHMENT_TYPES = array(
		'pdf' => 'application/pdf',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xls' => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'jpg|jpeg' => 'image/jpeg',
		'png' => 'image/png',
	);

	public static function init() {
		add_action( 'wp_ajax_jednotka_contact', array( __CLASS__, 'submit' ) );
		add_action( 'wp_ajax_nopriv_jednotka_contact', array( __CLASS__, 'submit' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_page_script' ) );
	}

	public static function enqueue_page_script() {
		if ( ! is_page( 'kontakt' ) ) {
			return;
		}
		wp_enqueue_script( 'jednotka-contact-page', JEDNOTKA_ELEMENTOR_URL . 'assets/js/contact-page.js', array(), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_localize_script( 'jednotka-contact-page', 'jednotkaContact', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'jednotka_contact' ) ) );
	}

	/** Validates the optional upload. Returns attachments for wp_mail, keyed by file name. */
	private static function attachment() {
		$file = $_FILES['attachment'] ?? null;
		if ( ! is_array( $file ) || UPLOAD_ERR_NO_FILE === ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			return array();
		}
		if ( UPLOAD_ERR_OK !== $file['error'] || ! is_string( $file['name'] ?? null ) || ! is_string( $file['tmp_name'] ?? null ) || (int) $file['size'] > self::MAX_ATTACHMENT_BYTES || ! is_uploaded_file( $file['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => 'Súbor sa nepodarilo nahrať. Môže mať najviac 10 MB.' ), 400 );
		}
		$name = sanitize_file_name( wp_unslash( $file['name'] ) );
		$type = wp_check_filetype_and_ext( $file['tmp_name'], $name, self::ATTACHMENT_TYPES );
		if ( empty( $type['ext'] ) ) {
			wp_send_json_error( array( 'message' => 'Tento typ súboru nie je podporovaný. Použite pdf, word, excel, jpg alebo png.' ), 400 );
		}
		return array( $name => $file['tmp_name'] );
	}

	public static function submit() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			wp_send_json_error( array( 'message' => 'Použite kontaktný formulár.' ), 405 );
		}
		$nonce = isset( $_POST['nonce'] ) && is_string( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'jednotka_contact' ) ) {
			wp_send_json_error( array( 'message' => 'Platnosť formulára vypršala. Obnovte stránku a skúste znova.' ), 403 );
		}
		$fields = array();
		foreach ( array( 'name' => 200, 'email' => 254, 'phone' => 80, 'interest' => 200, 'message' => 5000, 'website' => 200 ) as $key => $limit ) {
			$value = $_POST[ $key ] ?? '';
			if ( ! is_string( $value ) || strlen( $value ) > $limit * 4 ) {
				wp_send_json_error( array( 'message' => 'Skontrolujte vyplnené údaje. Text je príliš dlhý.' ), 400 );
			}
			$fields[ $key ] = 'message' === $key ? sanitize_textarea_field( wp_unslash( $value ) ) : sanitize_text_field( wp_unslash( $value ) );
		}
		if ( '' !== $fields['website'] || '' === trim( $fields['name'] ) || ! is_email( $fields['email'] ) ) {
			wp_send_json_error( array( 'message' => 'Vyplňte meno a platnú emailovú adresu.' ), 400 );
		}
		$rate_key = 'jn_contact_' . hash_hmac( 'sha256', (string) ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ), wp_salt( 'nonce' ) );
		if ( get_transient( $rate_key ) ) {
			wp_send_json_error( array( 'message' => 'Pred ďalším odoslaním počkajte jednu minútu.' ), 429 );
		}
		$attachments = self::attachment();
		$recipient = get_option( 'admin_email' );
		if ( ! is_email( $recipient ) ) {
			wp_send_json_error( array( 'message' => 'Formulár momentálne nie je dostupný. Skúste nás kontaktovať telefonicky.' ), 503 );
		}
		set_transient( $rate_key, 1, 60 );
		$body = "Nový dopyt z kontaktného formulára\n\n";
		foreach ( array( 'name' => 'Meno', 'email' => 'Email', 'phone' => 'Telefón', 'interest' => 'Záujem', 'message' => 'Správa' ) as $key => $label ) {
			$body .= $label . ': ' . $fields[ $key ] . "\n";
		}
		$sent = wp_mail( $recipient, 'Nový dopyt – Jednotka', $body, array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $fields['email'] ), $attachments );
		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => 'Správu sa nepodarilo odoslať. Skúste to neskôr alebo nám zavolajte.' ), 500 );
		}
		wp_send_json_success( array( 'message' => 'Ďakujeme! Vaša správa bola odoslaná. Čoskoro sa vám ozveme.' ) );
	}
}
