<?php
/** Blog introduction and lead article adapted from temp/featured. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Blog_Featured_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-blog-featured'; }
	public function get_title() { return esc_html__( 'Jednotka Blog Featured', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-posts-grid'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'blog', 'featured', 'post', 'article', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-blog-featured' ); }

	private function post_options() {
		$options = array( '' => 'Choose a post' );
		if ( ! current_user_can( 'edit_posts' ) || ( ! is_admin() && ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) { return $options; }
		$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'ignore_sticky_posts' => true ) );
		foreach ( $posts as $post ) { $options[ $post->ID ] = get_the_title( $post ) . ' (#' . $post->ID . ')'; }
		return $options;
	}

	protected function register_controls() {
		$this->start_controls_section( 'intro_section', array( 'label' => 'Blog heading' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => Controls_Manager::TEXT, 'default' => 'Blog' ) );
		$this->add_control( 'heading_tag', array( 'label' => 'Heading tag', 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3' ) ) );
		$this->end_controls_section();
		$this->start_controls_section( 'post_section', array( 'label' => 'Featured article' ) );
		$this->add_control( 'source', array( 'label' => 'Article source', 'type' => Controls_Manager::SELECT, 'default' => 'manual', 'options' => array( 'manual' => 'Editable article', 'selected' => 'Choose a WordPress post', 'latest' => 'Latest published WordPress post' ), 'description' => 'WordPress sources use the post title, short description/excerpt, thumbnail, category, date and permalink. Edit that content in WordPress Posts.' ) );
		$this->add_control( 'post_id', array( 'label' => 'Post', 'type' => Controls_Manager::SELECT2, 'options' => $this->post_options(), 'default' => '', 'condition' => array( 'source' => 'selected' ), 'description' => 'Search by title. Only published posts without a password are available.' ) );
		foreach ( array(
			'title' => array( 'Article title', 'Email, ktorý sa číta do konca' ),
			'excerpt' => array( 'Excerpt', 'Predmet, rytmus a jedna jasná ponuka.' ),
			'category' => array( 'Category badge', 'Emaily' ),
			'date' => array( 'Date', '18. marec 2026' ),
		) as $key => $field ) {
			$this->add_control( $key, array( 'label' => $field[0], 'type' => in_array( $key, array( 'title', 'excerpt' ), true ) ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $field[1], 'condition' => array( 'source' => 'manual' ) ) );
		}
		$this->add_control( 'link', array( 'label' => 'Article link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#' ), 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'image', array( 'label' => 'Featured image / fallback', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/projects/placeholder.jpg' ) ) );
		$this->add_control( 'image_alt', array( 'label' => 'Image alternative text', 'type' => Controls_Manager::TEXT, 'description' => 'Leave empty for a decorative image alongside the article title.' ) );
		$this->add_control( 'category_icon', array( 'label' => 'Category icon', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/services/email.png' ) ) );
		$this->add_control( 'section_label', array( 'label' => 'Featured section accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Najnovší článok' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'Layout and colors', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array(
			'background' => array( 'Section background', '{{WRAPPER}} .jn-blog-featured', 'background-color', '#0f0f0f' ),
			'heading_color' => array( 'Heading color', '{{WRAPPER}} .blog-intro-heading', 'color', '' ),
			'title_color' => array( 'Article title color', '{{WRAPPER}} .blog-featured-copy h2', 'color', '' ),
			'excerpt_color' => array( 'Excerpt color', '{{WRAPPER}} .blog-featured-copy > p', 'color', '' ),
			'badge_color' => array( 'Badge text color', '{{WRAPPER}} .blog-badge', 'color', '' ),
			'meta_color' => array( 'Date color', '{{WRAPPER}} .blog-meta', 'color', '' ),
		) as $key => $control ) {
			$this->add_control( $key, array( 'label' => $control[0], 'type' => Controls_Manager::COLOR, 'default' => $control[3], 'selectors' => array( $control[1] => $control[2] . ': {{VALUE}};' ) ) );
		}
		foreach ( array( 'heading' => '.blog-intro-heading', 'title' => '.blog-featured-copy h2', 'excerpt' => '.blog-featured-copy > p', 'category' => '.blog-badge', 'meta' => '.blog-meta' ) as $key => $selector ) {
			$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_font', 'selector' => '{{WRAPPER}} ' . $selector ) );
		}
		$this->add_responsive_control( 'max_width', array( 'label' => 'Content max width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 600, 'max' => 1800 ) ), 'selectors' => array( '{{WRAPPER}} .jn-blog-featured' => '--blog-max-width: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'intro_padding', array( 'label' => 'Heading section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .blog-intro' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'Image / text gap', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 12, 'max' => 120 ) ), 'selectors' => array( '{{WRAPPER}} .blog-featured' => 'gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'radius', array( 'label' => 'Image corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .blog-featured-media' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key, $enabled = true ) {
		if ( $enabled ) { $this->add_inline_editing_attributes( $key, 'none' ); $this->print_render_attribute_string( $key ); }
	}

	private function article( $s ) {
		$entry = array( 'title' => $s['title'], 'excerpt' => $s['excerpt'], 'category' => $s['category'], 'date' => $s['date'], 'link' => $s['link'], 'image' => $s['image']['url'] ?? '' );
		if ( 'manual' === $s['source'] ) { return $entry; }
		if ( 'selected' === $s['source'] ) {
			$post_id = absint( $s['post_id'] ?? 0 );
			$post = $post_id ? get_post( $post_id ) : null;
		} else {
			$posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false, 'numberposts' => 1, 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true ) );
			$post = $posts ? $posts[0] : null;
		}
		if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status || ! empty( $post->post_password ) ) { return null; }
		$entry['title'] = get_the_title( $post );
		$short_description = get_post_meta( $post->ID, '_jednotka_short_description', true );
		$entry['excerpt'] = '' !== trim( $short_description ) ? $short_description : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_excerpt ?: $post->post_content ) ), 25, '…' );
		$categories = get_the_category( $post->ID );
		$entry['category'] = $categories ? $categories[0]->name : '';
		$entry['date'] = get_the_date( '', $post );
		$entry['link'] = array( 'url' => get_permalink( $post ) );
		$entry['image'] = get_the_post_thumbnail_url( $post, 'large' ) ?: $entry['image'];
		return $entry;
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$entry = $this->article( $s );
		$manual = 'manual' === $s['source'];
		$editing = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$tag = in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3' ), true ) ? $s['heading_tag'] : 'h1';
		$card_tag = $entry && ! empty( $entry['link']['url'] ) && ! $editing ? 'a' : 'article';
		if ( $entry && 'a' === $card_tag ) { $this->add_link_attributes( 'article_link', $entry['link'] ); }
		?>
		<div class="jn-blog-featured">
			<section class="blog-intro" aria-labelledby="blog-title-<?php echo esc_attr( $this->get_id() ); ?>">
				<<?php echo $tag; ?> class="blog-intro-heading" id="blog-title-<?php echo esc_attr( $this->get_id() ); ?>"><span <?php $this->inline_text( 'heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></span></<?php echo $tag; ?>>
			</section>
			<section class="blog-featured-wrap" aria-label="<?php echo esc_attr( $s['section_label'] ); ?>">
				<?php if ( $entry ) : ?>
				<<?php echo $card_tag; ?> class="blog-featured" <?php if ( 'a' === $card_tag ) { $this->print_render_attribute_string( 'article_link' ); } ?>>
					<figure class="blog-featured-media"><?php if ( $entry['image'] ) : ?><img src="<?php echo esc_url( $entry['image'] ); ?>" alt="<?php echo esc_attr( $s['image_alt'] ); ?>" decoding="async" /><?php endif; ?><span class="blog-featured-shade" aria-hidden="true"></span></figure>
					<div class="blog-featured-copy">
						<?php if ( $entry['category'] ) : ?><span class="blog-badge"><?php if ( ! empty( $s['category_icon']['url'] ) ) : ?><img src="<?php echo esc_url( $s['category_icon']['url'] ); ?>" alt="" decoding="async" /><?php endif; ?><span <?php $this->inline_text( 'category', $manual ); ?>><?php echo esc_html( $entry['category'] ); ?></span></span><?php endif; ?>
						<h2 <?php $this->inline_text( 'title', $manual ); ?>><?php echo esc_html( $entry['title'] ); ?></h2>
						<p <?php $this->inline_text( 'excerpt', $manual ); ?>><?php echo esc_html( $entry['excerpt'] ); ?></p>
						<span class="blog-meta"><span <?php $this->inline_text( 'date', $manual ); ?>><?php echo esc_html( $entry['date'] ); ?></span></span>
						<span class="arrow-badge-button blog-featured-arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17 17 7" /><path d="M8 7h9v9" /></svg></span>
					</div>
				</<?php echo $card_tag; ?>>
				<?php else : ?><p class="blog-featured-empty"><?php echo esc_html__( 'Zatiaľ tu nie sú žiadne články.', 'jednotka-elementor' ); ?></p><?php endif; ?>
			</section>
		</div>
		<?php
	}
}
