<?php
/** Google reviews link and rating, edited once under Settings → General. Shows nothing until a link is saved. */
namespace Jednotka\Elementor;

defined( 'ABSPATH' ) || exit;

final class Google_Rating {
	const URL = 'jednotka_google_url';
	const RATING = 'jednotka_google_rating';
	const COUNT = 'jednotka_google_count';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function register_settings() {
		register_setting( 'general', self::URL, array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw', 'default' => '' ) );
		register_setting( 'general', self::RATING, array( 'type' => 'string', 'sanitize_callback' => array( __CLASS__, 'sanitize_rating' ), 'default' => '' ) );
		register_setting( 'general', self::COUNT, array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 0 ) );
		add_settings_section( 'jednotka_google', 'Google recenzie', array( __CLASS__, 'section_text' ), 'general' );
		add_settings_field( self::URL, 'Odkaz na recenzie', array( __CLASS__, 'url_field' ), 'general', 'jednotka_google', array( 'label_for' => self::URL ) );
		add_settings_field( self::RATING, 'Hodnotenie', array( __CLASS__, 'rating_field' ), 'general', 'jednotka_google', array( 'label_for' => self::RATING ) );
		add_settings_field( self::COUNT, 'Počet recenzií', array( __CLASS__, 'count_field' ), 'general', 'jednotka_google', array( 'label_for' => self::COUNT ) );
	}

	public static function section_text() {
		echo '<p>Zadajte skutočné údaje z Google profilu. Bez odkazu sa na webe nič nezobrazí. Bez hodnotenia sa zobrazí len odkaz na recenzie.</p>';
	}

	public static function url_field() {
		printf( '<input class="regular-text" type="url" id="%1$s" name="%1$s" value="%2$s" />', esc_attr( self::URL ), esc_attr( get_option( self::URL, '' ) ) );
	}

	public static function rating_field() {
		printf( '<input class="small-text" type="text" id="%1$s" name="%1$s" value="%2$s" placeholder="4,9" />', esc_attr( self::RATING ), esc_attr( get_option( self::RATING, '' ) ) );
	}

	public static function count_field() {
		printf( '<input class="small-text" type="number" min="0" id="%1$s" name="%1$s" value="%2$s" />', esc_attr( self::COUNT ), esc_attr( get_option( self::COUNT, 0 ) ) );
	}

	/** Accepts 4,9 or 4.9 and stores the Slovak form. Anything outside 1–5 is cleared. */
	public static function sanitize_rating( $value ) {
		$number = (float) str_replace( ',', '.', trim( (string) $value ) );
		return ( $number >= 1 && $number <= 5 ) ? number_format( $number, 1, ',', '' ) : '';
	}

	private static function reviews_label( $count ) {
		if ( 1 === $count ) { return '1 recenzia'; }
		return $count . ( $count >= 2 && $count <= 4 ? ' recenzie' : ' recenzií' );
	}

	private static function stars( $rating ) {
		$filled = (int) round( (float) str_replace( ',', '.', $rating ) );
		$star = '<path d="m12 2 3 6.6 7.2.8-5.4 4.8 1.500 7.1L12 17.700l-6.300 3.600 1.500-7.100L1.800 9.400 9 8.600z"/>';
		$html = '';
		for ( $index = 1; $index <= 5; $index++ ) {
			$html .= '<svg viewBox="0 0 24 24" aria-hidden="true"' . ( $index > $filled ? ' class="is-empty"' : '' ) . '>' . $star . '</svg>';
		}
		return '<span class="jn-stars" aria-hidden="true">' . $html . '</span>';
	}

	private static function logo() {
		return '<svg class="jn-google-rating__logo" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.200 9.100 3.600l6.800-6.800C35.800 2.400 30.300 0 24 0 14.600 0 6.500 5.400 2.600 13.200l7.900 6.200C12.400 13.700 17.700 9.500 24 9.500z"/><path fill="#4285F4" d="M46.900 24.500c0-1.600-.1-3.100-.4-4.500H24v9h12.900c-.6 3-2.300 5.500-4.800 7.200l7.700 6c4.500-4.200 7.100-10.300 7.100-17.700z"/><path fill="#FBBC05" d="M10.500 28.600A14.500 14.500 0 0 1 9.700 24c0-1.600.3-3.200.8-4.600l-7.900-6.200A24 24 0 0 0 0 24c0 3.900.9 7.500 2.600 10.800l7.900-6.200z"/><path fill="#34A853" d="M24 48c6.500 0 11.900-2.100 15.800-5.800l-7.700-6c-2.100 1.400-4.900 2.300-8.100 2.300-6.300 0-11.600-4.200-13.500-9.900l-7.900 6.200C6.500 42.600 14.600 48 24 48z"/></svg>';
	}

	/** Returns the rating pill, or nothing until a link is saved. */
	public static function markup() {
		$url = (string) get_option( self::URL, '' );
		if ( '' === $url ) { return ''; }
		$rating = (string) get_option( self::RATING, '' );
		$count = (int) get_option( self::COUNT, 0 );
		$label = '' !== $rating ? 'Hodnotenie ' . $rating . ' z 5 na Google' : 'Recenzie na Google';
		if ( $count ) { $label .= ', ' . self::reviews_label( $count ); }
		$open = sprintf( '<a class="jn-google-rating" href="%s" target="_blank" rel="noopener" aria-label="%s">', esc_url( $url ), esc_attr( $label ) );
		if ( '' === $rating ) {
			return $open . self::logo() . '<span>Prečítajte si recenzie na Google</span></a>';
		}
		$score = '<b>' . esc_html( $rating ) . '</b>';
		$reviews = $count ? '<span class="jn-google-rating__sep" aria-hidden="true">·</span><span>' . esc_html( self::reviews_label( $count ) ) . '</span>' : '';
		return $open . self::logo() . self::stars( $rating ) . '<span>' . $score . ' na Google</span>' . $reviews . '</a>';
	}
}
