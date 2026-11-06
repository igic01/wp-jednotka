<?php
/** Focus widget markup, with just enough of Elementor to render it. */
namespace Elementor {
	class Controls_Manager { const TEXT = 'text'; const TEXTAREA = 'textarea'; const SWITCHER = 'switcher'; const MEDIA = 'media'; const REPEATER = 'repeater'; }
	class Repeater { public $controls = array(); function add_control( $key, $args ) { $this->controls[ $key ] = $args; } function get_controls() { return $this->controls; } }
	class Plugin { public static $instance; public $editor; }
	class Widget_Base {
		public $controls = array(); public $overrides = array(); public $attributes = array();
		function start_controls_section( $key, $args ) {} function end_controls_section() {}
		function add_control( $key, $args ) { $this->controls[ $key ] = $args; }
		function add_inline_editing_attributes( $key, $mode ) {}
		function add_render_attribute( $key, $name, $value ) { $this->attributes[ $key ] = $name . '="' . $value . '"'; }
		function print_render_attribute_string( $key ) { echo $this->attributes[ $key ]; }
		function get_settings_for_display() { $s = array(); foreach ( $this->controls as $key => $control ) { $s[ $key ] = $control['default'] ?? ''; } return array_replace( $s, $this->overrides ); }
		function output() { $this->register_controls(); ob_start(); $this->render(); return ob_get_clean(); }
	}
}
namespace {
	define( 'ABSPATH', __DIR__ );
	define( 'JEDNOTKA_ELEMENTOR_URL', '/plugin/' );
	function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
	function esc_html__( $s, $d ) { return esc_html( $s ); }
	function esc_attr( $s ) { return esc_html( $s ); }
	function esc_url( $s ) { return esc_html( $s ); }
	\Elementor\Plugin::$instance = (object) array( 'editor' => new class { function is_edit_mode() { return false; } } );
	require __DIR__ . '/../widgets/class-focus-widget.php';
	function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }

	$widget = new \Jednotka\Elementor\Widgets\Focus_Widget();
	$html = $widget->output();
	check( str_contains( $html, 'class="jn-our-story jn-focus"' ) && str_contains( $html, 'data-breaks="yes"' ), 'Uses the Our Story reveal and keeps line breaks' );
	check( str_contains( $html, "zákazníkov?\nPomôžeme" ), 'Question and answer sit on separate lines' );
	check( str_contains( $html, "v\u{00A0}marketingu" ) && str_contains( $html, "a\u{00A0}zistiť" ), 'Short prepositions never end a line' );
	check( 5 === substr_count( $html, 'class="jn-focus__badge"' ) && str_contains( $html, 'services-eshop.svg' ), 'Shows five service badges with their icons' );
	check( 5 === substr_count( $html, 'data-stay="yes"' ), 'Badges stay visible once they appear' );
	check( ! str_contains( $html, 'Google reklamy' ) && ! str_contains( $html, 'Meta' ), 'Badges name services, not platforms' );

	$many = new \Jednotka\Elementor\Widgets\Focus_Widget();
	$many->overrides = array( 'badges' => array_fill( 0, 7, array( 'label' => '<b>X</b>', 'icon' => array( 'url' => '' ) ) ) );
	$many_html = $many->output();
	check( 5 === substr_count( $many_html, 'class="jn-focus__badge"' ), 'Extra badges without a position are ignored' );
	check( ! str_contains( $many_html, '<b>X</b>' ) && ! str_contains( $many_html, '<img' ), 'Labels are escaped and a missing icon is skipped' );
	echo "Focus render checks passed.\n";
}
