<?php
/** Fixed post topics and sidebar content for the Jednotka blog. */
namespace Jednotka\Elementor;

defined( 'ABSPATH' ) || exit;

final class Post_Settings {
	const DESCRIPTION = '_jednotka_short_description';
	private static $seeding = false;

	public static function services() {
		return array(
			'web' => array( 'name' => 'Web', 'image' => 'web.png', 'aliases' => array( 'web', 'weby' ) ),
			'e-shopy' => array( 'name' => 'E-shopy', 'image' => 'services-eshop.svg', 'aliases' => array( 'e-shopy', 'services-eshop', 'eshopy', 'ecommerce' ) ),
			'email' => array( 'name' => 'Email', 'image' => 'email.png', 'aliases' => array( 'email', 'emaily', 'e-maily' ) ),
			'ppc' => array( 'name' => 'PPC', 'image' => 'ppc.png', 'aliases' => array( 'ppc', 'reklamy' ) ),
			'grafika' => array( 'name' => 'Grafika', 'image' => 'grafika.png', 'aliases' => array( 'grafika' ) ),
			'branding' => array( 'name' => 'Branding', 'image' => 'branding.png', 'aliases' => array( 'branding' ) ),
		);
	}

	public static function init() {
		add_action( 'init', array( __CLASS__, 'setup' ), 20 );
		add_filter( 'pre_insert_term', array( __CLASS__, 'restrict_creation' ), 10, 3 );
		add_action( 'add_meta_boxes_post', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_post', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'editor_assets' ) );
		add_filter( 'rest_category_query', array( __CLASS__, 'rest_categories' ) );
		add_filter( 'rest_pre_insert_post', array( __CLASS__, 'validate_categories' ), 10, 2 );
	}

	public static function service( $term ) {
		foreach ( self::services() as $key => $service ) {
			if ( in_array( $term->slug, $service['aliases'], true ) || in_array( sanitize_title( $term->name ), $service['aliases'], true ) ) { return $key; }
		}
		return '';
	}

	public static function icon( $term ) {
		$services = self::services();
		$key = self::service( $term );
		return $key ? JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/' . $services[ $key ]['image'] : '';
	}

	/** One real term per service; keep existing IDs and post assignments. */
	public static function terms() {
		$all = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
		$mapped = array();
		if ( ! is_wp_error( $all ) ) {
			foreach ( $all as $term ) {
				$key = self::service( $term );
				if ( $key && ! isset( $mapped[ $key ] ) ) { $mapped[ $key ] = $term; }
			}
		}
		return $mapped;
	}

	public static function filters() {
		$terms = self::terms();
		$result = array();
		foreach ( self::services() as $key => $service ) {
			$result[] = $terms[ $key ] ?? (object) array( 'term_id' => 'service-' . pathinfo( $service['image'], PATHINFO_FILENAME ), 'slug' => $key, 'name' => $service['name'] );
		}
		return $result;
	}

	public static function setup() {
		if ( '1' !== get_option( 'jednotka_post_categories_version' ) ) {
			$terms = self::terms();
			$success = true;
			self::$seeding = true;
			foreach ( self::services() as $slug => $service ) {
				if ( ! isset( $terms[ $slug ] ) && is_wp_error( wp_insert_term( $service['name'], 'category', array( 'slug' => $slug ) ) ) ) { $success = false; }
			}
			self::$seeding = false;
			if ( $success ) { update_option( 'jednotka_post_categories_version', '1', false ); }
		}
		$taxonomy = get_taxonomy( 'category' );
		if ( $taxonomy ) {
			foreach ( array( 'manage_terms', 'edit_terms', 'delete_terms' ) as $cap ) { $taxonomy->cap->$cap = 'do_not_allow'; }
			$taxonomy->show_in_quick_edit = false;
		}
		register_post_meta( 'post', self::DESCRIPTION, array( 'type' => 'string', 'single' => true, 'default' => '', 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_textarea_field', 'auth_callback' => function ( $allowed, $key, $post_id ) { return current_user_can( 'edit_post', $post_id ); } ) );
	}

	public static function restrict_creation( $term, $taxonomy, $args = array() ) {
		if ( 'category' !== $taxonomy || is_wp_error( $term ) || self::$seeding ) { return $term; }
		return new \WP_Error( 'jednotka_fixed_categories', 'Kategórie článkov sú pevne dané službami Jednotka. Nové kategórie nie je možné pridávať.' );
	}

	public static function meta_boxes( $post ) {
		remove_meta_box( 'categorydiv', 'post', 'side' );
		add_meta_box( 'jednotka-post-categories', 'Kategórie služieb', array( __CLASS__, 'categories_box' ), 'post', 'side', 'high' );
		add_meta_box( 'jednotka-short-description', 'Krátky popis', array( __CLASS__, 'description_box' ), 'post', 'side', 'high' );
	}

	public static function categories_box( $post ) {
		$selected = wp_get_post_categories( $post->ID );
		wp_nonce_field( 'jednotka_post_categories', 'jednotka_categories_nonce' );
		echo '<input type="hidden" name="jednotka_categories_present" value="1" />';
		foreach ( self::terms() as $term ) {
			echo '<label style="display:flex;align-items:center;gap:10px;margin:10px 0"><input type="checkbox" name="jednotka_categories[]" value="' . esc_attr( $term->term_id ) . '" ' . checked( in_array( (int) $term->term_id, $selected, true ), true, false ) . ' /><img src="' . esc_url( self::icon( $term ) ) . '" alt="" width="30" height="30" style="object-fit:contain" /><span>' . esc_html( $term->name ) . '</span></label>';
		}
	}

	public static function description_box( $post ) {
		wp_nonce_field( 'jednotka_short_description', 'jednotka_description_nonce' );
		echo '<label for="jednotka-short-description-field">Text pod nadpisom článku v zozname príspevkov.</label><textarea id="jednotka-short-description-field" name="jednotka_short_description" rows="5" class="widefat" style="margin-top:8px">' . esc_textarea( get_post_meta( $post->ID, self::DESCRIPTION, true ) ) . '</textarea><p class="description">Ak ostane prázdny, použije sa existujúci úryvok alebo začiatok článku.</p>';
	}

	public static function save( $post_id, $post ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
		if ( isset( $_POST['jednotka_description_nonce'], $_POST['jednotka_short_description'] ) && is_string( $_POST['jednotka_description_nonce'] ) && is_string( $_POST['jednotka_short_description'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jednotka_description_nonce'] ) ), 'jednotka_short_description' ) ) {
			update_post_meta( $post_id, self::DESCRIPTION, sanitize_textarea_field( wp_unslash( $_POST['jednotka_short_description'] ) ) );
		}
		if ( isset( $_POST['jednotka_categories_nonce'], $_POST['jednotka_categories_present'] ) && is_string( $_POST['jednotka_categories_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jednotka_categories_nonce'] ) ), 'jednotka_post_categories' ) ) {
			$allowed = array_map( function ( $term ) { return (int) $term->term_id; }, array_values( self::terms() ) );
			$selected = isset( $_POST['jednotka_categories'] ) && is_array( $_POST['jednotka_categories'] ) ? array_map( 'absint', array_filter( $_POST['jednotka_categories'], 'is_scalar' ) ) : array();
			wp_set_post_categories( $post_id, array_values( array_intersect( $selected, $allowed ) ) );
		}
	}

	public static function rest_categories( $args ) {
		$args['include'] = array_map( function ( $term ) { return (int) $term->term_id; }, array_values( self::terms() ) ) ?: array( 0 );
		return $args;
	}

	public static function validate_categories( $post, $request ) {
		if ( is_wp_error( $post ) ) { return $post; }
		$categories = $request->get_param( 'categories' );
		if ( null !== $categories ) {
			$allowed = self::rest_categories( array() )['include'];
			if ( array_diff( array_map( 'intval', (array) $categories ), $allowed ) ) { return new \WP_Error( 'jednotka_fixed_categories', 'Vyberte kategóriu z ponuky služieb Jednotka.', array( 'status' => 400 ) ); }
		}
		return $post;
	}

	public static function editor_assets() {
		$screen = get_current_screen();
		if ( $screen && 'post' === $screen->post_type ) {
			wp_enqueue_script( 'jednotka-post-editor', JEDNOTKA_ELEMENTOR_URL . 'assets/js/post-editor.js', array( 'wp-data', 'wp-dom-ready', 'wp-edit-post' ), JEDNOTKA_ELEMENTOR_VERSION, true );
		}
	}
}
