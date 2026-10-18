<?php
/** Public contact form delivery. The recipient is always set by WordPress. */
namespace Jednotka\Elementor;
defined( 'ABSPATH' ) || exit;

final class Contact_Form {
	public static function init() {
		add_action( 'wp_ajax_jednotka_contact', array( __CLASS__, 'submit' ) );
		add_action( 'wp_ajax_nopriv_jednotka_contact', array( __CLASS__, 'submit' ) );
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
		$recipient = get_option( 'admin_email' );
		if ( ! is_email( $recipient ) ) {
			wp_send_json_error( array( 'message' => 'Formulár momentálne nie je dostupný. Skúste nás kontaktovať telefonicky.' ), 503 );
		}
		set_transient( $rate_key, 1, 60 );
		$body = "Nový dopyt z kontaktného formulára\n\n";
		foreach ( array( 'name' => 'Meno', 'email' => 'Email', 'phone' => 'Telefón', 'interest' => 'Záujem', 'message' => 'Správa' ) as $key => $label ) {
			$body .= $label . ': ' . $fields[ $key ] . "\n";
		}
		$sent = wp_mail( $recipient, 'Nový dopyt – Jednotka', $body, array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $fields['email'] ) );
		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => 'Správu sa nepodarilo odoslať. Skúste to neskôr alebo nám zavolajte.' ), 500 );
		}
		wp_send_json_success( array( 'message' => 'Ďakujeme! Vaša správa bola odoslaná. Čoskoro sa vám ozveme.' ) );
	}
}
