<?php
/** Searchable, paginated WordPress articles styled after temp/posts. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Posts_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-posts'; }
	public function get_title() { return esc_html__( 'Jednotka Posts', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-posts-grid'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'blog', 'posts', 'search', 'filter', 'articles' ); }
	public function get_style_depends() { return array( 'jednotka-posts' ); }
	public function get_script_depends() { return array( 'jednotka-posts' ); }

	protected function register_controls() {
		$this->start_controls_section( 'posts_content', array( 'label' => 'Posts and search' ) );
		$this->add_control( 'posts_per_page', array( 'label' => 'Posts per page', 'type' => Controls_Manager::NUMBER, 'default' => 6, 'min' => 1, 'max' => 24, 'description' => 'Published WordPress posts, newest first. Search and categories apply across all pages. Edit articles under WordPress Posts.' ) );
		foreach ( array( 'search_label' => array( 'Search placeholder', 'Hľadať v článkoch' ), 'submit_label' => array( 'Search button accessible label', 'Hľadať' ), 'empty_label' => array( 'Empty blog message', 'Zatiaľ tu nie sú žiadne články.' ), 'no_results' => array( 'No search results', 'Nič sme nenašli. Skúste iné slovo alebo tému.' ) ) as $key => $field ) {
			$this->add_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::TEXT, 'default' => $field[1] ) );
		}
		$this->add_control( 'fallback_image', array( 'label' => 'Fallback post image', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'posts_style', array( 'label' => 'Layout and style', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#0f0f0f', 'selectors' => array( '{{WRAPPER}} .jn-posts' => '--posts-background: {{VALUE}};' ) ) );
		$this->add_control( 'text_color', array( 'label' => 'Title color', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .blog-card h2' => 'color: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'max_width', array( 'label' => 'Content max width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 600, 'max' => 1800 ) ), 'selectors' => array( '{{WRAPPER}} .jn-posts' => '--posts-max-width: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'sticky_offset', array( 'label' => 'Sticky toolbar top offset', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 200 ) ), 'selectors' => array( '{{WRAPPER}} .jn-posts' => '--posts-sticky-offset: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'bottom_padding', array( 'label' => 'Bottom padding', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 240 ) ), 'selectors' => array( '{{WRAPPER}} .jn-posts' => 'padding-bottom: {{SIZE}}{{UNIT}};' ) ) );
		foreach ( array( 'title' => '.blog-card h2', 'excerpt' => '.blog-card p', 'date' => '.blog-meta' ) as $name => $selector ) {
			$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $name . '_font', 'selector' => '{{WRAPPER}} ' . $selector ) );
		}
		$this->end_controls_section();
	}

	private function parameter( $key ) {
		return isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
	}

	private function visible_category( $term ) { return (bool) \Jednotka\Elementor\Post_Settings::icon( $term ); }
	private function icon( $term ) { return \Jednotka\Elementor\Post_Settings::icon( $term ); }
	private function filters() { return \Jednotka\Elementor\Post_Settings::filters(); }

	protected function render() {
		$s = $this->get_settings_for_display();
		$id = 'jn-posts-' . $this->get_id();
		$prefix = 'jn_' . $this->get_id() . '_';
		$search = mb_substr( $this->parameter( $prefix . 'search' ), 0, 200 );
		$terms = $this->filters();
		$requested_category = $this->parameter( $prefix . 'category' );
		$category = 0;
		foreach ( $terms as $term ) {
			if ( (string) $term->term_id === $requested_category || ( $this->icon( $term ) && 'service-' . pathinfo( $this->icon( $term ), PATHINFO_FILENAME ) === $requested_category ) ) { $category = $term->term_id; break; }
		}
		$page = max( 1, absint( $this->parameter( $prefix . 'page' ) ) );
		$args = array( 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'posts_per_page' => min( 24, max( 1, (int) $s['posts_per_page'] ) ), 'paged' => $page, 'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ), 'ignore_sticky_posts' => true, 's' => $search );
		if ( $category ) {
			if ( is_numeric( $category ) ) { $args['cat'] = (int) $category; }
			else { $args['post__in'] = array( 0 ); } // Service has no WordPress category yet.
		}
		$query = new \WP_Query( $args );
		// Old links remain useful if posts were removed or the page size changed.
		if ( $page > max( 1, (int) $query->max_num_pages ) ) {
			$page = max( 1, (int) $query->max_num_pages );
			$args['paged'] = $page;
			$query = new \WP_Query( $args );
		}
		$base = remove_query_arg( array( $prefix . 'search', $prefix . 'category', $prefix . 'page' ) );
		$editing = \Elementor\Plugin::$instance->editor->is_edit_mode();
		?>
		<div class="jn-posts" id="<?php echo esc_attr( $id ); ?>" data-posts-widget data-error="Články sa nepodarilo načítať. Skúste to znova.">
			<form class="blog-toolbar" method="get" action="<?php echo esc_url( $base . '#' . $id ); ?>" aria-label="Hľadanie a filter článkov">
				<?php foreach ( $_GET as $key => $value ) : if ( ! is_scalar( $value ) || in_array( $key, array( $prefix . 'search', $prefix . 'category', $prefix . 'page' ), true ) ) { continue; } ?>
				<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( (string) $value ) ) ); ?>" />
				<?php endforeach; ?>
				<div class="blog-search"><button class="blog-search-submit" type="submit" aria-label="<?php echo esc_attr( $s['submit_label'] ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4.5 4.5" /></svg></button><label class="blog-search-sr" for="<?php echo esc_attr( $id . '-search' ); ?>"><?php echo esc_html( $s['search_label'] ); ?></label><input id="<?php echo esc_attr( $id . '-search' ); ?>" type="search" name="<?php echo esc_attr( $prefix . 'search' ); ?>" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( $s['search_label'] ); ?>" maxlength="200" autocomplete="off" /></div>
				<input type="hidden" data-current-category name="<?php echo esc_attr( $prefix . 'category' ); ?>" value="<?php echo esc_attr( $category ); ?>" />
				<div class="blog-filters" role="group" aria-label="Filtrovať podľa témy">
					<?php foreach ( $terms as $term ) : if ( ! $this->visible_category( $term ) ) { continue; } $icon = $this->icon( $term ); ?>
					<button class="blog-filter" data-category="<?php echo esc_attr( $term->term_id ); ?>" type="submit" name="<?php echo esc_attr( $prefix . 'category' ); ?>" value="<?php echo esc_attr( (string) $category === (string) $term->term_id ? 0 : $term->term_id ); ?>" aria-label="<?php echo esc_attr( $term->name ); ?>" aria-pressed="<?php echo (string) $category === (string) $term->term_id ? 'true' : 'false'; ?>"><?php if ( $icon ) : ?><img src="<?php echo esc_url( $icon ); ?>" alt="" /><?php else : ?><span><?php echo esc_html( $term->name ); ?></span><?php endif; ?></button>
					<?php endforeach; ?>
				</div>
			</form>
			<div class="blog-results" aria-live="polite" aria-atomic="false">
				<p class="blog-count" role="status"><?php echo esc_html( sprintf( 'Články: %d', $query->found_posts ) ); ?></p>
				<section class="blog-list" aria-label="Články">
				<?php foreach ( $query->posts as $post ) :
					if ( 'publish' !== $post->post_status || ! empty( $post->post_password ) ) { continue; }
					$categories = get_the_category( $post->ID );
					$categories = array_values( array_filter( $categories, array( $this, 'visible_category' ) ) );
					$term = $categories ? $categories[0] : null;
					$description = get_post_meta( $post->ID, \Jednotka\Elementor\Post_Settings::DESCRIPTION, true );
					$image = get_the_post_thumbnail_url( $post, 'large' ) ?: ( $s['fallback_image']['url'] ?? '' );
					?>
					<article class="blog-card"><a class="blog-card-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>" <?php if ( $editing ) { echo 'data-editor-post-link'; } ?>>
						<figure class="blog-card-image">
							<?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async" width="800" height="606" /><?php endif; ?><span class="blog-card-shade" aria-hidden="true"></span>
							<?php if ( $term ) : $icon = $this->icon( $term ); ?><span class="blog-badge"><?php if ( $icon ) : ?><img src="<?php echo esc_url( $icon ); ?>" alt="" loading="lazy" /><?php endif; ?><?php echo esc_html( $term->name ); ?></span><?php endif; ?>
							<span class="blog-card-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span>
						</figure><h2><?php echo esc_html( get_the_title( $post ) ); ?></h2><p><?php echo esc_html( '' !== trim( $description ) ? $description : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_excerpt ?: $post->post_content ) ), 25, '…' ) ); ?></p><span class="blog-meta"><?php echo esc_html( get_the_date( '', $post ) ); ?></span>
					</a></article>
				<?php endforeach; ?>
				</section>
				<?php if ( ! $query->found_posts ) : ?><p class="blog-empty"><?php echo esc_html( $search || $category ? $s['no_results'] : $s['empty_label'] ); ?></p><?php endif; ?>
				<?php if ( $query->max_num_pages > 1 ) :
					$url = add_query_arg( array( $prefix . 'search' => $search, $prefix . 'category' => $category, $prefix . 'page' => 999999999 ), $base ); ?>
				<nav class="blog-pagination" aria-label="Stránkovanie článkov"><?php echo wp_kses_post( paginate_links( array( 'base' => str_replace( '999999999', '%#%', $url ), 'format' => '', 'current' => $page, 'total' => $query->max_num_pages, 'mid_size' => 1, 'end_size' => 1, 'prev_text' => '←', 'next_text' => '→', 'add_fragment' => '#' . $id, 'add_args' => false ) ) ); ?></nav>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
