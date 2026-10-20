<?php
/**
 * Elementor integration and widget registration.
 */

namespace Jednotka\Elementor;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	public static function init() {
		require_once JEDNOTKA_ELEMENTOR_PATH . 'includes/class-post-settings.php';
		Post_Settings::init();

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'missing_elementor_notice' ) );
			return;
		}

		require_once JEDNOTKA_ELEMENTOR_PATH . 'includes/class-contact-form.php';
		Contact_Form::init();

		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/element/container/section_layout_container/after_section_end', array( __CLASS__, 'set_container_width_default' ) );
		add_action( 'elementor/element/container/section_layout/after_section_end', array( __CLASS__, 'set_container_padding_default' ) );
		add_action( 'elementor/editor/after_enqueue_scripts', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'enqueue_editor_preview_styles' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	public static function set_container_width_default( $container ) {
		$container->update_control( 'content_width', array( 'default' => 'full' ) );
	}

	public static function set_container_padding_default( $container ) {
		// Smaller breakpoints inherit this value unless their padding is explicitly set.
		$container->update_control(
			'padding',
			array(
				'default' => array(
					'unit'     => 'px',
					'top'      => '0',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'isLinked' => true,
				),
			)
		);
	}

	public static function enqueue_editor_assets() {
		wp_enqueue_script(
			'jednotka-editor',
			JEDNOTKA_ELEMENTOR_URL . 'assets/js/editor.js',
			array( 'elementor-editor' ),
			JEDNOTKA_ELEMENTOR_VERSION,
			true
		);
	}

	public static function enqueue_editor_preview_styles() {
		wp_enqueue_style( 'jednotka-editor-preview', JEDNOTKA_ELEMENTOR_URL . 'assets/css/editor-preview.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
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
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-footer-widget.php';
		$widgets_manager->register( new Widgets\Footer_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-gallery-widget.php';
		$widgets_manager->register( new Widgets\Gallery_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-showcase-widget.php';
		$widgets_manager->register( new Widgets\Showcase_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-career-jobs-widget.php';
		$widgets_manager->register( new Widgets\Career_Jobs_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-career-hero-widget.php';
		$widgets_manager->register( new Widgets\Career_Hero_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-about-hero-widget.php';
		$widgets_manager->register( new Widgets\About_Hero_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-team-widget.php';
		$widgets_manager->register( new Widgets\Team_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-our-story-widget.php';
		$widgets_manager->register( new Widgets\Our_Story_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-posts-widget.php';
		$widgets_manager->register( new Widgets\Posts_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-blog-featured-widget.php';
		$widgets_manager->register( new Widgets\Blog_Featured_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-work-widget.php';
		$widgets_manager->register( new Widgets\Work_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-faq-widget.php';
		$widgets_manager->register( new Widgets\FAQ_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-services-hero-widget.php';
		$widgets_manager->register( new Widgets\Services_Hero_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-contact-widget.php';
		$widgets_manager->register( new Widgets\Contact_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-projects-widget.php';
		$widgets_manager->register( new Widgets\Projects_Widget() );
		require_once JEDNOTKA_ELEMENTOR_PATH . 'widgets/class-values-widget.php';
		$widgets_manager->register( new Widgets\Values_Widget() );
	}

	public static function register_assets() {
		wp_register_style( 'jednotka-common', JEDNOTKA_ELEMENTOR_URL . 'assets/css/common.css', array(), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-career-jobs', JEDNOTKA_ELEMENTOR_URL . 'assets/css/career-jobs.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-career-jobs', JEDNOTKA_ELEMENTOR_URL . 'assets/js/career-jobs.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-career-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/css/career-hero.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-gallery', JEDNOTKA_ELEMENTOR_URL . 'assets/css/gallery.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-gallery', JEDNOTKA_ELEMENTOR_URL . 'assets/js/gallery.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-footer', JEDNOTKA_ELEMENTOR_URL . 'assets/css/footer.css', array( 'jednotka-clients-footer' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-footer', JEDNOTKA_ELEMENTOR_URL . 'assets/js/footer.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-posts', JEDNOTKA_ELEMENTOR_URL . 'assets/css/posts.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-posts', JEDNOTKA_ELEMENTOR_URL . 'assets/js/posts.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-blog-featured', JEDNOTKA_ELEMENTOR_URL . 'assets/css/blog-featured.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-work', JEDNOTKA_ELEMENTOR_URL . 'assets/css/work.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-work', JEDNOTKA_ELEMENTOR_URL . 'assets/js/work.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_script( 'jednotka-faq', JEDNOTKA_ELEMENTOR_URL . 'assets/js/faq.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-faq', JEDNOTKA_ELEMENTOR_URL . 'assets/css/faq.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-services-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/css/services-hero.css', array( 'jednotka-header' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-services-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/js/services-hero.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-contact', JEDNOTKA_ELEMENTOR_URL . 'assets/css/contact.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-contact', JEDNOTKA_ELEMENTOR_URL . 'assets/js/contact.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-projects', JEDNOTKA_ELEMENTOR_URL . 'assets/css/projects.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-projects', JEDNOTKA_ELEMENTOR_URL . 'assets/js/projects.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-values', JEDNOTKA_ELEMENTOR_URL . 'assets/css/values.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-values', JEDNOTKA_ELEMENTOR_URL . 'assets/js/values.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-our-story', JEDNOTKA_ELEMENTOR_URL . 'assets/css/our-story.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-our-story', JEDNOTKA_ELEMENTOR_URL . 'assets/js/our-story.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-team', JEDNOTKA_ELEMENTOR_URL . 'assets/css/team.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-team', JEDNOTKA_ELEMENTOR_URL . 'assets/js/team.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-about-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/css/about-hero.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-showcase', JEDNOTKA_ELEMENTOR_URL . 'assets/css/showcase.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-showcase', JEDNOTKA_ELEMENTOR_URL . 'assets/js/showcase.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-clients-footer', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-clients-footer.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-clients-footer', JEDNOTKA_ELEMENTOR_URL . 'assets/css/clients-footer.css', array( 'jednotka-reference-clients-footer' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-clients-footer', JEDNOTKA_ELEMENTOR_URL . 'assets/js/clients-footer.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-services', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-services.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-services', JEDNOTKA_ELEMENTOR_URL . 'assets/css/services.css', array( 'jednotka-reference-services' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-services', JEDNOTKA_ELEMENTOR_URL . 'assets/js/services.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-results', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-results.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-results', JEDNOTKA_ELEMENTOR_URL . 'assets/css/results.css', array( 'jednotka-reference-results' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-results', JEDNOTKA_ELEMENTOR_URL . 'assets/js/results.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-approach', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-approach.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-approach', JEDNOTKA_ELEMENTOR_URL . 'assets/css/approach.css', array( 'jednotka-reference-approach' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-approach', JEDNOTKA_ELEMENTOR_URL . 'assets/js/approach.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-header', JEDNOTKA_ELEMENTOR_URL . 'assets/css/header.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-header', JEDNOTKA_ELEMENTOR_URL . 'assets/js/header.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-hero.css', array( 'jednotka-header' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/css/hero.css', array( 'jednotka-reference-hero' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-hero', JEDNOTKA_ELEMENTOR_URL . 'assets/js/hero.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-stats', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-stats.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-stats', JEDNOTKA_ELEMENTOR_URL . 'assets/css/stats.css', array( 'jednotka-reference-stats' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-stats', JEDNOTKA_ELEMENTOR_URL . 'assets/js/stats.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		wp_register_style( 'jednotka-reference-logo-strip', JEDNOTKA_ELEMENTOR_URL . 'assets/css/reference-logo-strip.css', array( 'jednotka-common' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_style( 'jednotka-logo-strip', JEDNOTKA_ELEMENTOR_URL . 'assets/css/logo-strip.css', array( 'jednotka-reference-logo-strip' ), JEDNOTKA_ELEMENTOR_VERSION );
		wp_register_script( 'jednotka-logo-strip', JEDNOTKA_ELEMENTOR_URL . 'assets/js/logo-strip.js', array( 'elementor-frontend' ), JEDNOTKA_ELEMENTOR_VERSION, true );
	}
}
