<?php
/**
 * Elementor integration and widget registration.
 */

namespace Jednotka\Elementor;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	/** Resolve bundled asset URLs saved before the plugin folder was renamed. */
	public static function normalize_asset_urls( $settings ) {
		foreach ( $settings as $key => $value ) {
			if ( is_array( $value ) ) {
				$settings[ $key ] = self::normalize_asset_urls( $value );
			} elseif ( 'url' === $key && is_string( $value ) && preg_match( '~/jednotka-elementor/(assets/[^?#]+)([?#].*)?$~', $value, $matches ) ) {
				$asset_path = realpath( JEDNOTKA_ELEMENTOR_PATH . $matches[1] );
				$assets_root = realpath( JEDNOTKA_ELEMENTOR_PATH . 'assets' );
				if ( false !== $asset_path && false !== $assets_root && is_file( $asset_path ) && 0 === strpos( $asset_path, $assets_root . DIRECTORY_SEPARATOR ) ) {
					$settings[ $key ] = JEDNOTKA_ELEMENTOR_URL . $matches[1] . ( $matches[2] ?? '' );
				}
			}
		}

		return $settings;
	}

	public static function init() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'missing_elementor_notice' ) );
			return;
		}

		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	public static function missing_elementor_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		echo esc_html__( 'Jednotka Elementor requires Elementor to be installed and activated. The free version is sufficient.', 'jednotka-elementor' );
		echo '</p></div>';
	}

	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'jednotka',
			array(
				'title' => esc_html__( 'Jednotka', 'jednotka-elementor' ),
				'icon'  => 'eicon-plug',
			)
		);
	}

	public static function register_widgets( $widgets_manager ) {
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-header-widget.php';
		$widgets_manager->register( new Widgets\Header_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-hero-widget.php';
		$widgets_manager->register( new Widgets\Hero_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-logo-strip-widget.php';
		$widgets_manager->register( new Widgets\Logo_Strip_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-stats-widget.php';
		$widgets_manager->register( new Widgets\Stats_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-approach-widget.php';
		$widgets_manager->register( new Widgets\Approach_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-results-widget.php';
		$widgets_manager->register( new Widgets\Results_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-services-widget.php';
		$widgets_manager->register( new Widgets\Services_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-clients-footer-widget.php';
		$widgets_manager->register( new Widgets\Clients_Footer_Widget() );
	}

	public static function register_assets() {
		wp_register_style( 'jednotka-reference-clients-footer', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-clients-footer.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-clients-footer', JEDNOTKA_ELEMENTOR_URL . 'assets/css/clients-footer.css', array( 'jednotka-reference-clients-footer' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-clients-footer', JEDNOTKA_ELEMENTOR_URL . 'assets/js/clients-footer.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-services', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-services.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-services', JEDNOTKA_ELEMENTOR_URL . 'assets/css/services.css', array( 'jednotka-reference-services' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-services', JEDNOTKA_ELEMENTOR_URL . 'assets/js/services.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-results', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-results.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-results', JEDNOTKA_ELEMENTOR_URL . 'assets/css/results.css', array( 'jednotka-reference-results' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-results', JEDNOTKA_ELEMENTOR_URL . 'assets/js/results.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-approach', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-approach.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-approach', JEDNOTKA_ELEMENTOR_URL . 'assets/css/approach.css', array( 'jednotka-reference-approach' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-approach', JEDNOTKA_ELEMENTOR_URL . 'assets/js/approach.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-header', JEDNOTKA_ELEMENTOR_URL . 'assets/css/header.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-header', JEDNOTKA_ELEMENTOR_URL . 'assets/js/header.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-hero.css', array( 'jednotka-header' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/css/hero.css', array( 'jednotka-reference-hero' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/js/hero.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-stats', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-stats.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-stats', JEDNOTKA_ELEMENTOR_URL . 'assets/css/stats.css', array( 'jednotka-reference-stats' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-stats', JEDNOTKA_ELEMENTOR_URL . 'assets/js/stats.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-logo-strip', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-logo-strip.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-logo-strip', JEDNOTKA_ELEMENTOR_URL . 'assets/css/logo-strip.css', array( 'jednotka-reference-logo-strip' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-logo-strip', JEDNOTKA_ELEMENTOR_URL . 'assets/js/logo-strip.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
	}
}
