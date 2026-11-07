<?php
/** Career Jobs widget markup, with just enough of Elementor to render it. */
namespace Elementor {
	class Controls_Manager { const TEXT = 'text'; const TEXTAREA = 'textarea'; const SELECT = 'select'; const REPEATER = 'repeater'; const WYSIWYG = 'wysiwyg'; const URL = 'url'; const COLOR = 'color'; const SLIDER = 'slider'; const DIMENSIONS = 'dimensions'; const TAB_STYLE = 'style'; }
	class Group_Control_Typography { static function get_type() { return 'typography'; } }
	class Repeater { public $controls = array(); function add_control( $key, $args ) { $this->controls[ $key ] = $args; } function get_controls() { return $this->controls; } }
	class Plugin { public static $instance; }
	class Widget_Base {
		public $controls = array(); public $overrides = array(); public $attributes = array();
		function start_controls_section( $key, $args ) {} function end_controls_section() {}
		function add_control( $key, $args ) { $this->controls[ $key ] = $args; }
		function add_responsive_control( $key, $args ) { $this->controls[ $key ] = $args; }
		function add_group_control( $type, $args ) {}
		function add_inline_editing_attributes( $key, $mode ) {}
		function add_render_attribute( $key, $name, $value ) { $this->attributes[ $key ][] = $name . '="' . $value . '"'; }
		function add_link_attributes( $key, $link ) { $this->attributes[ $key ][] = 'href="' . esc_url( $link['url'] ) . '"'; }
		function print_render_attribute_string( $key ) { echo implode( ' ', $this->attributes[ $key ] ?? array() ); }
		function get_repeater_setting_key( $key, $repeater, $index ) { return $repeater . '.' . $index . '.' . $key; }
		function get_id() { return 'abc'; }
		function get_settings_for_display() { $s = array(); foreach ( $this->controls as $key => $control ) { $s[ $key ] = $control['default'] ?? ''; } return array_replace( $s, $this->overrides ); }
		function output() { $this->register_controls(); ob_start(); $this->render(); return ob_get_clean(); }
	}
}
namespace {
	define( 'ABSPATH', __DIR__ );
	function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
	function esc_html__( $s, $d ) { return esc_html( $s ); }
	function esc_attr( $s ) { return esc_html( $s ); }
	function esc_url( $s ) { return esc_html( $s ); }
	function wp_kses_post( $s ) { return preg_replace( '#<script\b[^>]*>.*?</script>#is', '', (string) $s ); }
	function wp_strip_all_tags( $s ) { return strip_tags( (string) $s ); }
	$edit_mode = false;
	\Elementor\Plugin::$instance = (object) array( 'editor' => new class { function is_edit_mode() { global $edit_mode; return $edit_mode; } } );
	require __DIR__ . '/../widgets/class-career-jobs-widget.php';
	function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }

	$html = ( new \Jednotka\Elementor\Widgets\Career_Jobs_Widget() )->output();
	foreach ( array( 'PPC Senior', 'Account manažér / manažérka', 'Sales Representative (Junior obchodník)', 'Sales Executive (Senior obchodník)' ) as $title ) {
		check( str_contains( $html, '>' . esc_html( $title ) . '</h3>' ), "Shows the $title position by default" );
	}
	check( 4 === substr_count( $html, '<details>' ) && 4 === substr_count( $html, 'class="career-job-details"' ), 'Each position expands to its details, collapsed by default' );
	check( 4 === substr_count( $html, 'class="career-perk ' ) && str_contains( $html, 'Forma spolupráce' ), 'Shows the four benefits, including the form of cooperation' );
	check( str_contains( $html, 'Vhodné pre absolventov</span>' ) && str_contains( $html, 'Zaškolenie</span>' ), 'Comma-separated labels become separate tags' );
	check( str_contains( $html, 'href="mailto:info@jednotka.studio?subject=' . esc_url( rawurlencode( 'Záujem o pozíciu: PPC Senior' ) ) . '"' ), 'The apply button opens an e-mail naming the position' );
	check( str_contains( $html, '<sup class="career-jobs-count">4</sup>' ), 'The heading shows how many positions are open' );
	check( str_contains( $html, 'career-jobs-empty" role="status" hidden' ), 'The empty message is hidden while positions exist' );
	check( ! str_contains( $html, 'kominkatívny' ) && str_contains( $html, 'zdvihnúť telefón' ), 'Uses the corrected copy' );

	$edit_mode = true;
	check( 4 === substr_count( ( new \Jednotka\Elementor\Widgets\Career_Jobs_Widget() )->output(), '<details open>' ), 'All positions are open in the editor' );
	$edit_mode = false;

	$empty = new \Jednotka\Elementor\Widgets\Career_Jobs_Widget();
	$empty->overrides = array( 'positions' => array(), 'perks' => array() );
	$empty_html = $empty->output();
	check( ! str_contains( $empty_html, 'career-perks' ) && ! str_contains( $empty_html, 'career-jobs-count' ), 'Without benefits or positions those parts are hidden' );
	check( str_contains( $empty_html, 'career-jobs-empty" role="status" >' ), 'The empty message shows when there are no positions' );

	$unsafe = new \Jednotka\Elementor\Widgets\Career_Jobs_Widget();
	$unsafe->overrides = array( 'positions' => array( array( 'job_title' => '<b>X</b>', 'tags' => '<i>t</i>', 'description' => '', 'location' => '', 'work_type' => '', 'details' => '<p>ok</p><script>bad()</script>', 'link_text' => 'Go', 'link' => array( 'url' => '' ) ) ) );
	$unsafe_html = $unsafe->output();
	check( ! str_contains( $unsafe_html, '<b>X</b>' ) && ! str_contains( $unsafe_html, '<i>t</i>' ) && ! str_contains( $unsafe_html, '<script>' ), 'Text is escaped and details are filtered' );
	check( ! str_contains( $unsafe_html, 'career-job-link' ) && ! str_contains( $unsafe_html, 'career-job-meta' ), 'Missing link and metadata are skipped' );
	echo "Career Jobs render checks passed.\n";
}
