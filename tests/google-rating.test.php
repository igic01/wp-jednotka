<?php
/** The badge must never show a rating that was not entered in Settings. */
define( 'ABSPATH', __DIR__ );
function get_option( $key, $default = false ) { return $GLOBALS['options'][ $key ] ?? $default; }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $s ) { return esc_attr( $s ); }
function esc_url( $s ) { return esc_attr( $s ); }
require __DIR__ . '/../includes/class-google-rating.php';
use Jednotka\Elementor\Google_Rating;
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }

$GLOBALS['options'] = array();
check( '' === Google_Rating::markup(), 'Nothing shows without a link' );

$GLOBALS['options'] = array( Google_Rating::URL => 'https://g.page/example' );
$html = Google_Rating::markup();
check( str_contains( $html, 'Prečítajte si recenzie na Google' ) && ! str_contains( $html, 'jn-stars' ), 'A link alone shows no stars' );

$GLOBALS['options'] += array( Google_Rating::RATING => '4,6', Google_Rating::COUNT => 3 );
$html = Google_Rating::markup();
check( str_contains( $html, '<b>4,6</b> na Google' ) && str_contains( $html, '3 recenzie' ), 'Badge shows the saved rating and count' );
check( 0 === substr_count( $html, 'is-empty' ) && 5 === substr_count( $html, '<svg viewBox="0 0 24 24"' ), '4,6 rounds to five stars' );
check( str_contains( $html, 'target="_blank"' ) && str_contains( $html, 'rel="noopener"' ), 'Opens safely in a new tab' );
$GLOBALS['options'][ Google_Rating::RATING ] = '4,2';
check( 1 === substr_count( Google_Rating::markup(), 'is-empty' ), '4,2 leaves one star empty' );

foreach ( array( 1 => '1 recenzia', 4 => '4 recenzie', 5 => '5 recenzií', 12 => '12 recenzií' ) as $count => $label ) {
	$GLOBALS['options'][ Google_Rating::COUNT ] = $count;
	check( str_contains( Google_Rating::markup(), $label ), 'Slovak plural for ' . $count );
}
foreach ( array( '4.9' => '4,9', '5' => '5,0', '4,75' => '4,8', '6' => '', '0' => '', 'abc' => '' ) as $input => $stored ) {
	check( $stored === Google_Rating::sanitize_rating( $input ), 'Sanitize rating ' . $input );
}
echo "Google rating checks passed.\n";
